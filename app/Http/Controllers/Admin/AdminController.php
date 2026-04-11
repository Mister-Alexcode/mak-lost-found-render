<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'lost_items'        => LostItem::count(),
            'found_items'       => FoundItem::count(),
            'pending_claims'    => Claim::where('claim_status', 'pending')->count(),
            'users'             => User::where('role', 'user')->count(),
            'returned'          => LostItem::where('status', 'returned')->count(),
            'pending_redemptions' => Redemption::where('status', 'pending')->count(),
        ];
        $recentClaims = Claim::with(['match.lostItem', 'match.foundItem', 'claimant'])
            ->latest()->take(5)->get();
        return view('admin.dashboard', compact('stats', 'recentClaims'));
    }

    public function lostItems()
    {
        $items = LostItem::with('user')->latest()->paginate(20);
        return view('admin.lost-items', compact('items'));
    }

    public function foundItems()
    {
        $items = FoundItem::with('user')->latest()->paginate(20);
        return view('admin.found-items', compact('items'));
    }

    public function claims()
    {
        $claims = Claim::with(['match.lostItem', 'match.foundItem', 'claimant'])
            ->latest()->paginate(20);
        return view('admin.claims', compact('claims'));
    }

    public function approveClaim(Claim $claim)
    {
        $claim->update([
            'claim_status' => 'approved',
            'admin_id'     => Auth::id(),
            'resolved_at'  => now(),
        ]);

        // Mark matched items as returned
        $claim->match->lostItem->update(['status' => 'returned']);
        $claim->match->foundItem->update(['status' => 'returned']);
        $claim->match->update(['match_status' => 'confirmed']);

        // Award +20 points to claimant (successful return)
        $claimant = $claim->claimant;
        $claimant->increment('reward_points', 20);
        Reward::create([
            'user_id'       => $claimant->id,
            'claim_id'      => $claim->id,
            'action_type'   => 'successful_return',
            'points_awarded'=> 20,
        ]);

        // Notify claimant
        NotificationDispatcher::send(
            $claimant,
            'claim_approved',
            'Your claim has been approved! Your item has been marked as returned. You earned 20 reward points.'
        );

        // Notify finder
        NotificationDispatcher::send(
            $claim->match->foundItem->user,
            'claim_approved',
            'The claim for the item you found (' . $claim->match->foundItem->item_name . ') has been approved. Thank you for your honesty!'
        );

        return back()->with('success', 'Claim approved and item marked as returned.');
    }

    public function rejectClaim(Request $request, Claim $claim)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $claim->update([
            'claim_status' => 'rejected',
            'admin_id'     => Auth::id(),
            'resolved_at'  => now(),
        ]);

        NotificationDispatcher::send(
            $claim->claimant,
            'claim_rejected',
            'Your claim for ' . $claim->match->lostItem->item_name
                . ' was rejected.' . ($request->reason ? ' Reason: ' . $request->reason : '')
        );

        return back()->with('success', 'Claim rejected.');
    }

    public function users()
    {
        $users = User::where('role', 'user')->orderByDesc('reward_points')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function redemptions()
    {
        $redemptions = Redemption::with('user')->latest()->paginate(20);
        return view('admin.redemptions', compact('redemptions'));
    }

    public function approveRedemption(Redemption $redemption)
    {
        $redemption->update(['status' => 'claimed']);

        NotificationDispatcher::send(
            $redemption->user,
            'redemption_approved',
            'Your reward redemption (' . $redemption->reward_tier . ') has been approved! Please collect it from the admin office.'
        );

        return back()->with('success', 'Redemption approved.');
    }

    public function updateItemStatus(Request $request, string $type, int $id)
    {
        $request->validate(['status' => 'required|in:active,returned,expired,donated']);

        if ($type === 'lost') {
            LostItem::findOrFail($id)->update(['status' => $request->status]);
        } else {
            FoundItem::findOrFail($id)->update(['status' => $request->status]);
        }

        return back()->with('success', 'Item status updated.');
    }
}
