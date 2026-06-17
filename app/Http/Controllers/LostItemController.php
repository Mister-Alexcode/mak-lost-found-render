<?php
namespace App\Http\Controllers;

use App\Models\LostItem;
use App\Models\ItemMatch;
use App\Services\MatchingService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LostItemController extends Controller
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
        $query = LostItem::with('user');
        if (!$isAdmin) {
            $query->where('status', 'active');
        }
        $lostItems = $query->latest()->paginate(10);
        return view('lost-items.index', compact('lostItems', 'isAdmin'));
    }

    public function create()
    {
        return view('lost-items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name'    => 'required|string|max:255',
            'category'     => 'required|string',
            'description'  => 'required|string',
            'color'        => 'required|string',
            'brand'        => 'nullable|string',
            'location_lost'=> 'required|string',
            'date_lost'    => 'required|date|before_or_equal:today',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'reward_offer' => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = \App\Services\ImageUploadService::store($request->file('photo'), 'lost-items');
        }

        $isHighValue = $request->boolean('is_high_value')
            || in_array($request->category, ['Electronics']);

        $lostItem = LostItem::create([
            'user_id'       => Auth::id(),
            'item_name'     => $request->item_name,
            'category'      => $request->category,
            'description'   => $request->description,
            'color'         => $request->color,
            'brand'         => $request->brand,
            'location_lost' => $request->location_lost,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'date_lost'     => $request->date_lost,
            'photo'         => $photoPath,
            'status'        => 'active',
            'is_high_value' => $isHighValue,
            'tracking_id'   => 'LOST-' . strtoupper(Str::random(8)),
            'reward_offer'  => $request->reward_offer,
        ]);

        $matches = $this->matchingService->findMatchesForLostItem($lostItem);
        if ($matches->count() > 0) {
            $best = $matches->first();
            $bestHighValue = $lostItem->is_high_value || $best->foundItem->is_high_value;
            // Notify the reporter of the lost item about their best match.
            NotificationDispatcher::send(
                Auth::user(),
                'match_found',
                $bestHighValue
                    ? 'A potential match has been found for your lost ' . $lostItem->item_name
                        . ' (' . $best->confidence_score . '% match). Please visit the admin office with proof of ownership — an administrator will verify and hand over the item.'
                    : 'A potential match has been found for your lost ' . $lostItem->item_name
                        . '! Confidence score: ' . $best->confidence_score . '%. Click here to view and claim.',
                route('lost-items.show', $lostItem->id)
            );

            // Notify each found-item owner once (best match per user) so they know
            // someone is looking for an item that resembles what they reported.
            $bestPerUser = [];
            foreach ($matches as $match) {
                $uid = $match->foundItem->user_id;
                if (!isset($bestPerUser[$uid]) || $match->confidence_score > $bestPerUser[$uid]->confidence_score) {
                    $bestPerUser[$uid] = $match;
                }
            }
            foreach ($bestPerUser as $match) {
                // Don't double-notify the reporter if they also own a found item.
                if ($match->foundItem->user_id === Auth::id()) continue;
                $isHighValue = $match->lostItem->is_high_value || $match->foundItem->is_high_value;
                NotificationDispatcher::send(
                    $match->foundItem->user,
                    'match_found',
                    $isHighValue
                        ? 'Someone is looking for a lost ' . $lostItem->item_name
                            . ' that may match the item you found (' . $match->confidence_score . '% match). Please deliver the item to the admin office — an administrator will verify ownership and handle the handover.'
                        : 'Someone is looking for a lost ' . $lostItem->item_name
                            . ' that may match the item you found! Confidence score: '
                            . $match->confidence_score . '%. Click here to view.',
                    route('found-items.show', $match->foundItem->id)
                );
            }
        }

        return redirect()->route('lost-items.index')
            ->with('success', 'Lost item reported successfully! Your tracking ID is: ' . $lostItem->tracking_id);
    }

    public function show(LostItem $lostItem)
    {
        if (!Auth::user()->isAdmin()) {
            $this->authorize('view', $lostItem);
        }
        $matches = $lostItem->matches()->with(['foundItem.user', 'claims'])->get();
        return view('lost-items.show', compact('lostItem', 'matches'));
    }

    public function edit(LostItem $lostItem)
    {
        $this->authorize('update', $lostItem);
        return view('lost-items.edit', compact('lostItem'));
    }

    public function update(Request $request, LostItem $lostItem)
    {
        $this->authorize('update', $lostItem);

        $request->validate([
            'item_name'    => 'required|string|max:255',
            'category'     => 'required|string',
            'description'  => 'required|string',
            'color'        => 'required|string',
            'brand'        => 'nullable|string',
            'location_lost'=> 'required|string',
            'date_lost'    => 'required|date|before_or_equal:today',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'reward_offer' => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ]);

        $photoPath = $lostItem->photo;
        if ($request->hasFile('photo')) {
            \App\Services\ImageUploadService::delete($lostItem->photo);
            $photoPath = \App\Services\ImageUploadService::store($request->file('photo'), 'lost-items');
        }

        $lostItem->update([
            'item_name'    => $request->item_name,
            'category'     => $request->category,
            'description'  => $request->description,
            'color'        => $request->color,
            'brand'        => $request->brand,
            'location_lost'=> $request->location_lost,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
            'date_lost'    => $request->date_lost,
            'photo'        => $photoPath,
            'is_high_value'=> $request->boolean('is_high_value') || in_array($request->category, ['Electronics']),
            'reward_offer' => $request->reward_offer,
        ]);

        return redirect()->route('lost-items.index')
            ->with('success', 'Lost item updated successfully!');
    }

    public function destroy(LostItem $lostItem)
    {
        if (!Auth::user()->isAdmin()) {
            $this->authorize('delete', $lostItem);
        }
        $lostItem->delete();
        return redirect()->route('lost-items.index')
            ->with('success', 'Lost item report deleted.');
    }
}
