<?php
namespace App\Http\Controllers;

use App\Models\FoundItem;
use App\Services\MatchingService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FoundItemController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->middleware('auth');
        $this->matchingService = $matchingService;
    }

    public function index()
    {
        $query = Auth::user()->isAdmin()
            ? FoundItem::with('user')->latest()
            : FoundItem::where('user_id', Auth::id())->latest();

        $foundItems = $query->paginate(10);
        $isAdmin = Auth::user()->isAdmin();
        return view('found-items.index', compact('foundItems', 'isAdmin'));
    }

    public function create()
    {
        return view('found-items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name'     => 'required|string|max:255',
            'category'      => 'required|string',
            'description'   => 'required|string',
            'color'         => 'required|string',
            'brand'         => 'nullable|string',
            'location_found'=> 'required|string',
            'date_found'    => 'required|date|before_or_equal:today',
            'photo'         => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
        ]);

        $photoPath = $request->file('photo')->store('found-items', 'public');

        $foundItem = FoundItem::create([
            'user_id'       => Auth::id(),
            'item_name'     => $request->item_name,
            'category'      => $request->category,
            'description'   => $request->description,
            'color'         => $request->color,
            'brand'         => $request->brand,
            'location_found'=> $request->location_found,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'date_found'    => $request->date_found,
            'photo'         => $photoPath,
            'status'        => 'active',
            'tracking_id'   => 'FOUND-' . strtoupper(Str::random(8)),
        ]);

        // Award points for reporting a found item
        $foundItem->user->increment('reward_points', 10);
        \App\Models\Reward::create([
            'user_id'       => Auth::id(),
            'claim_id'      => null,
            'action_type'   => 'reported_found_item',
            'points_awarded'=> 10,
        ]);

        // Run matching algorithm
        $matches = $this->matchingService->findMatchesForFoundItem($foundItem);
        if ($matches->count() > 0) {
            foreach ($matches as $match) {
                NotificationDispatcher::send(
                    $match->lostItem->user,
                    'match_found',
                    'Good news! A potential match has been found for your lost '
                        . $match->lostItem->item_name
                        . '. Confidence score: ' . $match->confidence_score . '%. Log in to view and claim.'
                );
            }
        }

        return redirect()->route('found-items.index')
            ->with('success', 'Found item reported successfully! You earned 10 reward points. Tracking ID: ' . $foundItem->tracking_id);
    }

    public function show(FoundItem $foundItem)
    {
        if (!Auth::user()->isAdmin()) {
            $this->authorize('view', $foundItem);
        }
        $matches = $foundItem->matches()->with('lostItem.user')->get();
        return view('found-items.show', compact('foundItem', 'matches'));
    }

    public function edit(FoundItem $foundItem)
    {
        $this->authorize('update', $foundItem);
        return view('found-items.edit', compact('foundItem'));
    }

    public function update(Request $request, FoundItem $foundItem)
    {
        $this->authorize('update', $foundItem);

        $request->validate([
            'item_name'     => 'required|string|max:255',
            'category'      => 'required|string',
            'description'   => 'required|string',
            'color'         => 'required|string',
            'brand'         => 'nullable|string',
            'location_found'=> 'required|string',
            'date_found'    => 'required|date|before_or_equal:today',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
        ]);

        $photoPath = $foundItem->photo;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('found-items', 'public');
        }

        $foundItem->update([
            'item_name'     => $request->item_name,
            'category'      => $request->category,
            'description'   => $request->description,
            'color'         => $request->color,
            'brand'         => $request->brand,
            'location_found'=> $request->location_found,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'date_found'    => $request->date_found,
            'photo'         => $photoPath,
        ]);

        return redirect()->route('found-items.index')
            ->with('success', 'Found item updated successfully!');
    }

    public function destroy(FoundItem $foundItem)
    {
        if (!Auth::user()->isAdmin()) {
            $this->authorize('delete', $foundItem);
        }
        $foundItem->delete();
        return redirect()->route('found-items.index')
            ->with('success', 'Found item report deleted.');
    }
}