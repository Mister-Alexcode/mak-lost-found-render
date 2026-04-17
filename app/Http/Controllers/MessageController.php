<?php
namespace App\Http\Controllers;

use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\ItemNotification;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        // Admins observe every active match conversation; regular users see only their own.
        $matchQuery = ItemMatch::where('match_status', '!=', 'dismissed');
        if (!$isAdmin) {
            $matchQuery->where(function ($q) use ($userId) {
                $q->whereHas('lostItem', fn($sub) => $sub->where('user_id', $userId))
                  ->orWhereHas('foundItem', fn($sub) => $sub->where('user_id', $userId));
            });
        }
        $matches = $matchQuery->with(['lostItem.user', 'foundItem.user'])->get();

        $directUserIds = Message::whereNull('match_id')
            ->whereNull('claim_id')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->get()
            ->map(fn($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
            ->unique();
        $directConversations = User::whereIn('id', $directUserIds)->get();

        return view('messages.index', compact('matches', 'directConversations', 'isAdmin'));
    }

    public function show(ItemMatch $match)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();
        $isOwner  = $match->lostItem->user_id === $userId;
        $isFinder = $match->foundItem->user_id === $userId;

        if (!$isOwner && !$isFinder && !$isAdmin) abort(403);

        $isHighValue = $match->lostItem->is_high_value || $match->foundItem->is_high_value;

        $messages = Message::where(function ($q) use ($match) {
                $q->where('match_id', $match->id)
                  ->orWhereHas('claim', fn($sub) => $sub->where('match_id', $match->id));
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        $otherUser = $isAdmin
            ? $match->foundItem->user
            : ($isOwner ? $match->foundItem->user : $match->lostItem->user);

        // Mark received messages as read
        Message::where('receiver_id', $userId)
            ->where(function ($q) use ($match) {
                $q->where('match_id', $match->id)
                  ->orWhereHas('claim', fn($sub) => $sub->where('match_id', $match->id));
            })
            ->update(['is_read' => true]);

        // Mark related message notifications as read
        ItemNotification::where('user_id', $userId)
            ->where('type', 'new_message')
            ->where('is_read', false)
            ->where('link', route('messages.show', $match->id))
            ->update(['is_read' => true]);

        return view('messages.show', compact('match', 'messages', 'otherUser', 'isOwner', 'isAdmin', 'isHighValue'));
    }

    public function store(Request $request, ItemMatch $match)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();
        $isOwner  = $match->lostItem->user_id === $userId;
        $isFinder = $match->foundItem->user_id === $userId;

        if (!$isOwner && !$isFinder && !$isAdmin) abort(403);

        // Admin observes chats only — they don't post in the match thread.
        // High-value items disallow any finder↔claimant chat entirely.
        if ($isAdmin) {
            return back()->with('error', 'Admins observe match chats but do not post in them. Use a direct message instead.');
        }
        $isHighValue = $match->lostItem->is_high_value || $match->foundItem->is_high_value;
        if ($isHighValue) {
            return back()->with('error', 'High-value items must be handled at the admin office — no direct chat between finder and claimant.');
        }

        $request->validate(['content' => 'required|string|max:1000']);

        $receiverId = $isOwner ? $match->foundItem->user_id : $match->lostItem->user_id;

        $claim = $match->claims()->first();

        Message::create([
            'sender_id'   => $userId,
            'receiver_id' => $receiverId,
            'claim_id'    => $claim?->id,
            'match_id'    => $match->id,
            'content'     => $request->content,
            'is_read'     => false,
        ]);

        $receiver = User::find($receiverId);
        NotificationDispatcher::send(
            $receiver,
            'new_message',
            'You have a new message from ' . Auth::user()->name . '.',
            route('messages.show', $match->id)
        );

        return back();
    }

    /**
     * Direct messaging between two users. Allowed when:
     *   - the current user is an admin (or target is an admin), OR
     *   - the two users already have a prior direct conversation, OR
     *   - ?about={found_item_id} points to an active, non-high-value found
     *     item owned by the target user (peer claim fast-path).
     * High-value items always route through the admin-mediated claim flow.
     */
    public function directShow(Request $request, User $user)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        if ($userId === $user->id) {
            abort(403);
        }

        $context = $this->authorizeDirectChat($request, $user, $isAdmin);
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }
        $contextItem = $context;

        $messages = Message::whereNull('match_id')
            ->whereNull('claim_id')
            ->where(function ($q) use ($userId, $user) {
                $q->where(function ($q2) use ($userId, $user) {
                    $q2->where('sender_id', $userId)->where('receiver_id', $user->id);
                })->orWhere(function ($q2) use ($userId, $user) {
                    $q2->where('sender_id', $user->id)->where('receiver_id', $userId);
                });
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        // Mark received as read
        Message::whereNull('match_id')
            ->whereNull('claim_id')
            ->where('sender_id', $user->id)
            ->where('receiver_id', $userId)
            ->update(['is_read' => true]);

        // Mark related message notifications as read
        ItemNotification::where('user_id', $userId)
            ->where('type', 'new_message')
            ->where('is_read', false)
            ->where('link', route('messages.direct', $user->id))
            ->update(['is_read' => true]);

        return view('messages.direct', compact('user', 'messages', 'contextItem'));
    }

    public function directStore(Request $request, User $user)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        if ($userId === $user->id) {
            abort(403);
        }

        $context = $this->authorizeDirectChat($request, $user, $isAdmin);
        if ($context instanceof \Illuminate\Http\RedirectResponse) {
            return $context;
        }

        $request->validate(['content' => 'required|string|max:1000']);

        Message::create([
            'sender_id'   => $userId,
            'receiver_id' => $user->id,
            'claim_id'    => null,
            'match_id'    => null,
            'content'     => $request->content,
            'is_read'     => false,
        ]);

        NotificationDispatcher::send(
            $user,
            'new_message',
            'You have a new message from ' . Auth::user()->name . '.',
            route('messages.direct', Auth::id())
        );

        return back();
    }

    /**
     * Decide whether the current user may open a direct chat with $target.
     * Returns the context FoundItem (or null) on success, or a RedirectResponse
     * to bounce the user to a safer destination on denial.
     */
    private function authorizeDirectChat(Request $request, User $target, bool $isAdmin)
    {
        // Admins can always DM anyone; users may always reply to an admin.
        if ($isAdmin || $target->isAdmin()) {
            return null;
        }

        $userId = Auth::id();

        // If they already have direct message history, the chat is legitimate.
        $hasHistory = Message::whereNull('match_id')
            ->whereNull('claim_id')
            ->where(function ($q) use ($userId, $target) {
                $q->where(function ($q2) use ($userId, $target) {
                    $q2->where('sender_id', $userId)->where('receiver_id', $target->id);
                })->orWhere(function ($q2) use ($userId, $target) {
                    $q2->where('sender_id', $target->id)->where('receiver_id', $userId);
                });
            })
            ->exists();

        if ($hasHistory) {
            return null;
        }

        // Otherwise require an ?about={found_item_id} context pointing at an
        // active, non-high-value found item owned by the target user.
        $aboutId = $request->query('about') ?? $request->input('about');
        if (!$aboutId) {
            abort(403, 'Direct messages require a shared item context.');
        }

        $foundItem = FoundItem::find($aboutId);
        if (!$foundItem || $foundItem->user_id !== $target->id || $foundItem->status !== 'active') {
            abort(403, 'That item is no longer available for direct chat.');
        }

        if ($foundItem->is_high_value) {
            return redirect()->route('claims.claim-found', $foundItem)
                ->with('error', 'High-value items require admin verification. Please file a claim — an admin will mediate.');
        }

        return $foundItem;
    }
}
