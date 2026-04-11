<?php
namespace App\Http\Controllers;

use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $type     = $request->get('type', 'lost');
        $query    = $request->get('q', '');
        $category = $request->get('category', '');
        $location = $request->get('location', '');

        $lostItems  = collect();
        $foundItems = collect();

        if ($type === 'lost' || $type === 'both') {
            $q = LostItem::where('status', 'active')
                ->with('user');
            if ($query) {
                $q->where(function ($b) use ($query) {
                    $b->where('item_name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('brand', 'like', "%{$query}%");
                });
            }
            if ($category) $q->where('category', $category);
            if ($location) $q->where('location_lost', 'like', "%{$location}%");
            $lostItems = $q->latest()->paginate(12, ['*'], 'lost_page');
        }

        if ($type === 'found' || $type === 'both') {
            $q = FoundItem::where('status', 'active')
                ->with('user');
            if ($query) {
                $q->where(function ($b) use ($query) {
                    $b->where('item_name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('brand', 'like', "%{$query}%");
                });
            }
            if ($category) $q->where('category', $category);
            if ($location) $q->where('location_found', 'like', "%{$location}%");
            $foundItems = $q->latest()->paginate(12, ['*'], 'found_page');
        }

        $categories = ['Electronics', 'Documents', 'Stationery', 'Clothing', 'Keys', 'Other'];

        return view('search.index', compact('lostItems', 'foundItems', 'type', 'query', 'category', 'location', 'categories'));
    }
}
