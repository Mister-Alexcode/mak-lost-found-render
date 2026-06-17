<?php
namespace App\Http\Controllers;

use App\Models\FoundItem;
use App\Services\MatchingService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $isAdmin = Auth::user()->isAdmin();
        $query = FoundItem::with('user');
        if (!$isAdmin) {
            $query->where('status', 'active');
        }
        $foundItems = $query->latest()->paginate(10);
        return view('found-items.index', compact('foundItems', 'isAdmin'));
    }

    public function create(Request $request)
    {
        $prefill = [];
        if ($request->has('from_lost')) {
            $lostItem = \App\Models\LostItem::find($request->from_lost);
            if ($lostItem) {
                $prefill = [
                    'item_name' => $lostItem->item_name,
                    'category'  => $lostItem->category,
                    'color'     => $lostItem->color,
                    'brand'     => $lostItem->brand,
                ];
            }
        }
        return view('found-items.create', compact('prefill'));
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

        $photoPath = \App\Services\ImageUploadService::store($request->file('photo'), 'found-items');

        $isHighValue = $request->boolean('is_high_value')
            || in_array($request->category, ['Electronics']);

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
            'is_high_value' => $isHighValue,
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

        // Direct "I Found This Item" link — create a high-confidence match
        if ($request->from_lost_id) {
            $lostItem = \App\Models\LostItem::where('id', $request->from_lost_id)
                ->where('status', 'active')->first();

            if ($lostItem) {
                $existing = \App\Models\ItemMatch::where('lost_item_id', $lostItem->id)
                    ->where('found_item_id', $foundItem->id)->first();

                if (!$existing) {
                    // Start at 95%, adjust based on description and location similarity
                    $score = 95;
                    similar_text(
                        strtolower($lostItem->description ?? ''),
                        strtolower($foundItem->description ?? ''),
                        $descPct
                    );
                    similar_text(
                        strtolower($lostItem->location_lost ?? ''),
                        strtolower($foundItem->location_found ?? ''),
                        $locPct
                    );
                    // Nudge down if description or location differ significantly
                    if ($descPct < 30) $score -= 3;
                    if ($locPct < 30) $score -= 2;

                    $match = \App\Models\ItemMatch::create([
                        'lost_item_id'     => $lostItem->id,
                        'found_item_id'    => $foundItem->id,
                        'confidence_score' => $score,
                        'match_status'     => 'pending',
                    ]);

                    $isHighValue = $lostItem->is_high_value || $foundItem->is_high_value;
                    NotificationDispatcher::send(
                        $lostItem->user,
                        'match_found',
                        $isHighValue
                            ? 'Great news! Someone found your lost ' . $lostItem->item_name
                                . ' (' . $score . '% match). Please visit the admin office with proof of ownership — an administrator will verify and hand over the item.'
                            : 'Great news! Someone found your lost ' . $lostItem->item_name
                                . ' and reported it (' . $score . '% match). Chat with the finder to confirm and reclaim your item.',
                        route('lost-items.show', $lostItem->id)
                    );
                }
            }
        }

        // Also run the general matching algorithm for other potential matches.
        // Dedupe notifications by recipient so a user with multiple matching lost
        // items only receives one alert (their best match).
        $matches = $this->matchingService->findMatchesForFoundItem($foundItem);
        $bestPerUser = [];
        foreach ($matches as $match) {
            $uid = $match->lostItem->user_id;
            if (!isset($bestPerUser[$uid]) || $match->confidence_score > $bestPerUser[$uid]->confidence_score) {
                $bestPerUser[$uid] = $match;
            }
        }
        foreach ($bestPerUser as $match) {
            $isHighValue = $match->lostItem->is_high_value || $match->foundItem->is_high_value;
            NotificationDispatcher::send(
                $match->lostItem->user,
                'match_found',
                $isHighValue
                    ? 'Good news! Someone may have found your lost ' . $match->lostItem->item_name
                        . ' (' . $match->confidence_score . '% match). Please visit the admin office with proof of ownership — an administrator will verify and hand over the item.'
                    : 'Good news! Someone may have found your lost ' . $match->lostItem->item_name
                        . '! Confidence score: ' . $match->confidence_score . '%. Click here to view the match and reclaim your item.',
                route('lost-items.show', $match->lostItem->id)
            );
        }

        if ($foundItem->is_high_value) {
            NotificationDispatcher::send(
                $foundItem->user,
                'match_found',
                'Thank you for reporting the ' . $foundItem->item_name
                    . '. This item is high-value — please deliver it to the admin office at your earliest convenience. An administrator will verify ownership with the claimant and handle the handover.',
                route('found-items.show', $foundItem->id)
            );

            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                NotificationDispatcher::send(
                    $admin,
                    'match_found',
                    '[High-Value] ' . $foundItem->user->name . ' reported a found ' . $foundItem->item_name
                        . '. Awaiting delivery to admin office.',
                    route('admin.found-items')
                );
            }

            return redirect()->route('found-items.index')
                ->with('success', 'Thank you! Please deliver this high-value item to the admin office for verified handover. Tracking ID: ' . $foundItem->tracking_id);
        }

        return redirect()->route('found-items.index')
            ->with('success', 'Found item reported successfully! You earned 10 reward points. Tracking ID: ' . $foundItem->tracking_id);
    }

    public function show(FoundItem $foundItem)
    {
        if (!Auth::user()->isAdmin()) {
            $this->authorize('view', $foundItem);
        }
        $matches = $foundItem->matches()->with(['lostItem.user', 'claims'])->get();
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
            \App\Services\ImageUploadService::delete($foundItem->photo);
            $photoPath = \App\Services\ImageUploadService::store($request->file('photo'), 'found-items');
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