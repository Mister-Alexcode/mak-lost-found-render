<?php
namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\LostItem;
use App\Models\Reward;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClaimController extends Controller
{
    public function index()
    {
        $claims = Claim::where('claimant_id', Auth::id())
            ->with(['match.lostItem', 'match.foundItem'])
            ->latest()->paginate(10);
        return view('claims.index', compact('claims'));
    }

    public function create(ItemMatch $match)
    {
        // Only the owner of the lost item can initiate a claim
        if ($match->lostItem->user_id !== Auth::id()) {
            abort(403);
        }
        // Prevent duplicate pending claims
        $existing = Claim::where('match_id', $match->id)
            ->where('claimant_id', Auth::id())
            ->whereIn('claim_status', ['pending', 'under_review'])
            ->first();
        return view('claims.create', compact('match', 'existing'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'match_id'             => 'required|exists:item_matches,id',
            'verification_details' => 'required|string|min:20',
        ]);

        $match = ItemMatch::findOrFail($request->match_id);

        if ($match->lostItem->user_id !== Auth::id()) {
            abort(403);
        }

        if ($match->match_status === 'confirmed' || $match->match_status === 'dismissed') {
            return redirect()->route('lost-items.show', $match->lost_item_id)
                ->with('error', 'This item has already been resolved.');
        }

        $claim = Claim::create([
            'match_id'             => $match->id,
            'claimant_id'          => Auth::id(),
            'verification_details' => $request->verification_details,
            'claim_status'         => 'pending',
        ]);

        // Notify the finder (owner of found item)
        NotificationDispatcher::send(
            $match->foundItem->user,
            'claim_submitted',
            Auth::user()->name . ' has submitted a claim for your found item: '
                . $match->foundItem->item_name . '. An admin will verify shortly.'
        );

        return redirect()->route('claims.show', $claim)
            ->with('success', 'Claim submitted successfully! An admin will review it shortly.');
    }

    public function show(Claim $claim)
    {
        if ($claim->claimant_id !== Auth::id()) {
            abort(403);
        }
        $claim->load(['match.lostItem', 'match.foundItem.user']);
        return view('claims.show', compact('claim'));
    }

    /**
     * Owner confirms return for non-high-value items (peer-to-peer).
     */
    public function confirmReturn(ItemMatch $match)
    {
        $userId = Auth::id();
        $isOwner  = $match->lostItem->user_id === $userId;
        $isFinder = $match->foundItem->user_id === $userId;

        if (!$isOwner && !$isFinder) abort(403);

        // High-value items must go through admin
        if ($match->lostItem->is_high_value || $match->foundItem->is_high_value) {
            return back()->with('error', 'High-value items require admin verification. Please file a claim instead.');
        }

        // Already confirmed
        if ($match->match_status === 'confirmed') {
            return back()->with('error', 'This item has already been marked as returned.');
        }

        // Mark items as returned
        $match->lostItem->update(['status' => 'returned']);
        $match->foundItem->update(['status' => 'returned']);
        $match->update(['match_status' => 'confirmed']);

        // Auto-create an approved claim record
        $claim = Claim::create([
            'match_id'             => $match->id,
            'claimant_id'          => $match->lostItem->user_id,
            'verification_details' => 'Peer-to-peer return confirmed by ' . Auth::user()->name,
            'claim_status'         => 'approved',
            'resolved_at'          => now(),
        ]);

        // Award points: the finder gets the bigger reward for returning the
        // item; the owner gets a smaller bonus for using the system.
        $owner  = $match->lostItem->user;
        $finder = $match->foundItem->user;

        $finder->increment('reward_points', 20);
        Reward::create([
            'user_id'        => $finder->id,
            'claim_id'       => $claim->id,
            'action_type'    => 'successful_return',
            'points_awarded' => 20,
        ]);

        $owner->increment('reward_points', 10);
        Reward::create([
            'user_id'        => $owner->id,
            'claim_id'       => $claim->id,
            'action_type'    => 'item_recovered',
            'points_awarded' => 10,
        ]);

        // Notify both parties
        NotificationDispatcher::send(
            $finder,
            'item_returned',
            'The item you found (' . $match->foundItem->item_name . ') was returned to its owner. You earned 20 reward points for your honesty!',
            route('found-items.show', $match->foundItem->id)
        );

        NotificationDispatcher::send(
            $owner,
            'item_returned',
            'Your ' . $match->lostItem->item_name . ' has been marked as returned. You earned 10 reward points!',
            route('lost-items.show', $match->lostItem->id)
        );

        return back()->with('success', 'Item marked as returned! The finder earned 20 points and the owner earned 10.');
    }

    /**
     * Show the claim form for a found item (user claims "this is mine").
     */
    public function claimFoundItem(FoundItem $foundItem)
    {
        if ($foundItem->user_id === Auth::id()) {
            return redirect()->route('found-items.show', $foundItem)
                ->with('error', 'You cannot claim your own found item.');
        }

        if ($foundItem->status !== 'active') {
            return redirect()->route('found-items.show', $foundItem)
                ->with('error', 'This item has already been returned.');
        }

        // Check for existing pending claim by this user for this found item
        $existingClaim = Claim::whereHas('match', fn($q) => $q->where('found_item_id', $foundItem->id))
            ->where('claimant_id', Auth::id())
            ->whereIn('claim_status', ['pending', 'under_review'])
            ->first();

        // Get user's lost items that could be the match
        $myLostItems = Auth::user()->lostItems()
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('claims.claim-found', compact('foundItem', 'existingClaim', 'myLostItems'));
    }

    /**
     * Process the claim for a found item.
     */
    public function storeClaimFoundItem(Request $request, FoundItem $foundItem)
    {
        if ($foundItem->user_id === Auth::id()) {
            abort(403);
        }

        $request->validate([
            'verification_details' => 'required|string|min:20',
            'lost_item_id'         => 'nullable|exists:lost_items,id',
        ]);

        $user = Auth::user();

        // Use selected lost item or auto-create one
        if ($request->lost_item_id) {
            $lostItem = LostItem::where('id', $request->lost_item_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            // Auto-create a lost item report from found item details
            $lostItem = LostItem::create([
                'user_id'       => $user->id,
                'item_name'     => $foundItem->item_name,
                'category'      => $foundItem->category,
                'description'   => 'Claimed from found item report: ' . $foundItem->tracking_id,
                'color'         => $foundItem->color,
                'brand'         => $foundItem->brand,
                'location_lost' => $foundItem->location_found,
                'date_lost'     => $foundItem->date_found,
                'status'        => 'active',
                'is_high_value' => $foundItem->is_high_value,
                'tracking_id'   => 'LOST-' . strtoupper(Str::random(8)),
            ]);
        }

        // Find or create a match
        $match = ItemMatch::where('lost_item_id', $lostItem->id)
            ->where('found_item_id', $foundItem->id)
            ->first();

        if (!$match) {
            $match = ItemMatch::create([
                'lost_item_id'     => $lostItem->id,
                'found_item_id'    => $foundItem->id,
                'confidence_score' => 90,
                'match_status'     => 'pending',
            ]);
        }

        // Check for duplicate claim
        $existing = Claim::where('match_id', $match->id)
            ->where('claimant_id', $user->id)
            ->whereIn('claim_status', ['pending', 'under_review'])
            ->first();

        if ($existing) {
            return redirect()->route('claims.show', $existing)
                ->with('error', 'You already have a pending claim for this item.');
        }

        $claim = Claim::create([
            'match_id'             => $match->id,
            'claimant_id'          => $user->id,
            'verification_details' => $request->verification_details,
            'claim_status'         => 'pending',
        ]);

        // Notify the finder
        NotificationDispatcher::send(
            $foundItem->user,
            'claim_submitted',
            $user->name . ' claims that your found item "' . $foundItem->item_name
                . '" belongs to them and has submitted a claim.',
            route('found-items.show', $foundItem->id)
        );

        // Notify admins for high-value items
        if ($foundItem->is_high_value || $lostItem->is_high_value) {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                NotificationDispatcher::send(
                    $admin,
                    'claim_submitted',
                    '[High-Value] ' . $user->name . ' has submitted a claim for found item "'
                        . $foundItem->item_name . '". Admin verification required.',
                    route('admin.claims')
                );
            }
        }

        return redirect()->route('claims.show', $claim)
            ->with('success', 'Claim submitted successfully! The finder and admin have been notified.');
    }
}
