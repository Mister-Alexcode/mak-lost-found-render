<?php
namespace App\Http\Controllers;

use App\Models\ItemMatch;
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

        // Get match-based conversations
        $matches = ItemMatch::where('match_status', '!=', 'dismissed')
            ->where(function ($q) use ($userId) {
                $q->whereHas('lostItem', fn($sub) => $sub->where('user_id', $userId))
                  ->orWhereHas('foundItem', fn($sub) => $sub->where('user_id', $userId));
            })
            ->with(['lostItem.user', 'foundItem.user'])
            ->get();

        // Get direct conversations (messages not tied to a match — admin messaging)
        $directConversations = collect();
        if ($isAdmin) {
            // Find users the admin has exchanged direct messages with
            $directUserIds = Message::whereNull('match_id')
                ->whereNull('claim_id')
                ->where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->get()
                ->map(fn($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
                ->unique();
            $directConversations = User::whereIn('id', $directUserIds)->get();
        } else {
            // Check if admin has messaged this user directly
            $directUserIds = Message::whereNull('match_id')
                ->whereNull('claim_id')
                ->where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->get()
                ->map(fn($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
                ->unique();
            $directConversations = User::whereIn('id', $directUserIds)->get();
        }

        return view('messages.index', compact('matches', 'directConversations', 'isAdmin'));
    }

    public function show(ItemMatch $match)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();
        $isOwner  = $match->lostItem->user_id === $userId;
        $isFinder = $match->foundItem->user_id === $userId;

        if (!$isOwner && !$isFinder && !$isAdmin) abort(403);

        $messages = Message::where(function ($q) use ($match) {
                $q->where('match_id', $match->id)
                  ->orWhereHas('claim', fn($sub) => $sub->where('match_id', $match->id));
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        $otherUser = $isOwner ? $match->foundItem->user : $match->lostItem->user;

        // Mark received messages as read
        Message::where('receiver_id', $userId)
            ->where(function ($q) use ($match) {
                $q->where('match_id', $match->id)
                  ->orWhereHas('claim', fn($sub) => $sub->where('match_id', $match->id));
            })
            ->update(['is_read' => true]);

        return view('messages.show', compact('match', 'messages', 'otherUser', 'isOwner'));
    }

    public function store(Request $request, ItemMatch $match)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();
        $isOwner  = $match->lostItem->user_id === $userId;
        $isFinder = $match->foundItem->user_id === $userId;

        if (!$isOwner && !$isFinder && !$isAdmin) abort(403);

        $request->validate(['content' => 'required|string|max:1000']);

        // Determine receiver
        if ($isAdmin) {
            // Admin can message either party — default to lost item owner
            $receiverId = $match->lostItem->user_id;
            if ($request->has('receiver_id')) {
                $receiverId = $request->receiver_id;
            }
        } else {
            $receiverId = $isOwner ? $match->foundItem->user_id : $match->lostItem->user_id;
        }

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
            'You have a new message from ' . Auth::user()->name . '.'
        );

        return back();
    }

    /**
     * Direct messaging (admin to any user, or user replying to admin)
     */
    public function directShow(User $user)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        // Only admin can initiate direct chats, but users can view/reply
        if (!$isAdmin && !Message::whereNull('match_id')->whereNull('claim_id')
            ->where(function ($q) use ($userId, $user) {
                $q->where(function ($q2) use ($userId, $user) {
                    $q2->where('sender_id', $userId)->where('receiver_id', $user->id);
                })->orWhere(function ($q2) use ($userId, $user) {
                    $q2->where('sender_id', $user->id)->where('receiver_id', $userId);
                });
            })->exists()) {
            abort(403);
        }

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

        return view('messages.direct', compact('user', 'messages'));
    }

    public function directStore(Request $request, User $user)
    {
        $userId = Auth::id();

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
            'You have a new message from ' . Auth::user()->name . '.'
        );

        return back();
    }
}
