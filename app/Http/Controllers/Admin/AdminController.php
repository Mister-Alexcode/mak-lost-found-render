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
        $recentClaims = Claim::with(['match.lostItem.user', 'match.foundItem.user', 'claimant'])
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
        $claims = Claim::with(['match.lostItem.user', 'match.foundItem.user', 'claimant'])
            ->latest()->paginate(20);
        return view('admin.claims', compact('claims'));
    }

    public function approveClaim(Claim $claim)
    {
        if ($claim->claim_status !== 'pending' && $claim->claim_status !== 'under_review') {
            return back()->with('error', 'This claim has already been resolved.');
        }

        $claim->update([
            'claim_status' => 'approved',
            'admin_id'     => Auth::id(),
            'resolved_at'  => now(),
        ]);

        // Mark matched items as returned
        $claim->match->lostItem->update(['status' => 'returned']);
        $claim->match->foundItem->update(['status' => 'returned']);
        $claim->match->update(['match_status' => 'confirmed']);

        // Award points: the finder gets the bigger reward for returning the
        // item; the claimant (owner) gets a smaller bonus for using the system.
        $claimant = $claim->claimant;
        $finder   = $claim->match->foundItem->user;

        $finder->increment('reward_points', 20);
        Reward::create([
            'user_id'       => $finder->id,
            'claim_id'      => $claim->id,
            'action_type'   => 'successful_return',
            'points_awarded'=> 20,
        ]);

        $claimant->increment('reward_points', 10);
        Reward::create([
            'user_id'       => $claimant->id,
            'claim_id'      => $claim->id,
            'action_type'   => 'item_recovered',
            'points_awarded'=> 10,
        ]);

        // Notify claimant
        NotificationDispatcher::send(
            $claimant,
            'claim_approved',
            'Your claim has been approved! Your item has been marked as returned. You earned 10 reward points.',
            route('claims.show', $claim->id)
        );

        // Notify finder
        NotificationDispatcher::send(
            $finder,
            'claim_approved',
            'The claim for the item you found (' . $claim->match->foundItem->item_name . ') has been approved. You earned 20 reward points for your honesty!',
            route('found-items.show', $claim->match->foundItem->id)
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
                . ' was rejected.' . ($request->reason ? ' Reason: ' . $request->reason : ''),
            route('claims.show', $claim->id)
        );

        return back()->with('success', 'Claim rejected.');
    }

    public function users()
    {
        $users = User::where('role', 'user')->orderByDesc('reward_points')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function editUser(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.users')->with('error', 'Admin accounts cannot be edited here.');
        }
        return view('admin.user-edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.users')->with('error', 'Admin accounts cannot be edited here.');
        }

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_number'  => 'nullable|string|max:30',
            'student_id'    => 'nullable|string|max:50',
            'reward_points' => 'required|integer|min:0',
        ]);

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function blockUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'You cannot block an admin account.');
        }
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot block yourself.');
        }

        $user->update(['is_blocked' => true, 'blocked_at' => now()]);

        return back()->with('success', $user->name . ' has been blocked.');
    }

    public function unblockUser(User $user)
    {
        $user->update(['is_blocked' => false, 'blocked_at' => null]);
        return back()->with('success', $user->name . ' has been unblocked.');
    }

    public function destroyUser(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'You cannot delete an admin account.');
        }
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users')->with('success', $name . ' deleted.');
    }

    public function redemptions()
    {
        $redemptions = Redemption::with('user')->latest()->paginate(20);
        return view('admin.redemptions', compact('redemptions'));
    }

    public function approveRedemption(Redemption $redemption)
    {
        $redemption->update(['status' => 'claimed']);

        $message = $redemption->reward_tier === 'certificate'
            ? 'Your Certificate of Appreciation has been approved! You can now download it from your Rewards page.'
            : 'Your reward redemption (' . str_replace('_', ' ', $redemption->reward_tier) . ') has been approved! Please collect it from the admin office.';

        NotificationDispatcher::send(
            $redemption->user,
            'redemption_approved',
            $message
        );

        return back()->with('success', 'Redemption approved.');
    }

    public function updateItemStatus(Request $request, string $type, int $id)
    {
        $request->validate(['status' => 'required|in:active,returned,expired']);

        if ($type === 'lost') {
            LostItem::findOrFail($id)->update(['status' => $request->status]);
        } else {
            FoundItem::findOrFail($id)->update(['status' => $request->status]);
        }

        return back()->with('success', 'Item status updated.');
    }
}
