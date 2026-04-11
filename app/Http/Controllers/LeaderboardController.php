<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function index()
    {
        $topUsers = User::where('role', 'user')
            ->orderByDesc('reward_points')
            ->take(20)
            ->get();

        $myRank = null;
        if (Auth::check()) {
            $myRank = User::where('role', 'user')
                ->where('reward_points', '>', Auth::user()->reward_points)
                ->count() + 1;
        }

        return view('leaderboard.index', compact('topUsers', 'myRank'));
    }
}
