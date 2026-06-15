<?php
namespace App\Http\Controllers;

use App\Models\Redemption;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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

        DB::transaction(function () use ($user, $tier) {
            $user->decrement('reward_points', $tier['points']);

            Redemption::create([
                'user_id'     => $user->id,
                'points_used' => $tier['points'],
                'reward_tier' => $tier['tier'],
                'status'      => 'pending',
            ]);
        });

        return back()->with('success', 'Redemption request submitted! An admin will process it shortly.');
    }

    public function certificate(Redemption $redemption)
    {
        abort_unless($redemption->user_id === Auth::id(), Response::HTTP_FORBIDDEN);

        if ($redemption->reward_tier !== 'certificate' || $redemption->status !== 'claimed') {
            abort(Response::HTTP_FORBIDDEN, 'This certificate is not available yet.');
        }

        $reference = 'MAK-LF-' . str_pad((string) $redemption->id, 5, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('redemptions.certificate', [
            'user'       => $redemption->user,
            'redemption' => $redemption,
            'issuedOn'   => $redemption->updated_at->format('d M Y'),
            'reference'  => $reference,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('certificate-of-appreciation-' . $reference . '.pdf');
    }
}
