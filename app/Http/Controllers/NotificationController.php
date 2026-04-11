<?php
namespace App\Http\Controllers;

use App\Models\ItemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ItemNotification::where('user_id', Auth::id())
            ->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(ItemNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        ItemNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
