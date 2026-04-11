<?php
namespace App\Http\Controllers;

use App\Models\Redemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedemptionController extends Controller
{
    public function index()
    {
        $tiers = Redemption::tiers();
        $myPoints = Auth::user()->reward_points;
        $myRedemptions = Redemption::where('user_id', Auth::id())->latest()->get();
        return view('redemptions.index', compact('tiers', 'myPoints', 'myRedemptions'));
    }

    public function store(Request $request)
    {
        $request->validate(['tier' => 'required|string']);

        $tiers = collect(Redemption::tiers());
        $tier = $tiers->firstWhere('tier', $request->tier);

        if (!$tier) {
            return back()->withErrors(['tier' => 'Invalid reward tier.']);
        }

        $user = Auth::user();

        if ($user->reward_points < $tier['points']) {
            return back()->withErrors(['tier' => 'You do not have enough points for this reward.']);
        }

        // Deduct points
        $user->decrement('reward_points', $tier['points']);

        Redemption::create([
            'user_id'     => $user->id,
            'points_used' => $tier['points'],
            'reward_tier' => $tier['tier'],
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Redemption request submitted! An admin will process it shortly.');
    }
}
