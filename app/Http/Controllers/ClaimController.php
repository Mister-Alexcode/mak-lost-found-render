<?php
namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ItemMatch;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
