<?php
namespace App\Http\Controllers;

use App\Models\LostItem;
use App\Models\ItemMatch;
use App\Services\MatchingService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
        $query = Auth::user()->isAdmin()
            ? LostItem::with('user')->latest()
            : LostItem::where('user_id', Auth::id())->latest();

        $lostItems = $query->paginate(10);
        $isAdmin = Auth::user()->isAdmin();
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
            $photoPath = $request->file('photo')->store('lost-items', 'public');
        }

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
            'tracking_id'   => 'LOST-' . strtoupper(Str::random(8)),
            'reward_offer'  => $request->reward_offer,
        ]);

        $matches = $this->matchingService->findMatchesForLostItem($lostItem);
        if ($matches->count() > 0) {
            $best = $matches->first();
            NotificationDispatcher::send(
                Auth::user(),
                'match_found',
                'A potential match has been found for your lost ' . $lostItem->item_name
                    . '! Confidence score: ' . $best->confidence_score . '%. Click here to view.'
            );
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
            $photoPath = $request->file('photo')->store('lost-items', 'public');
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
