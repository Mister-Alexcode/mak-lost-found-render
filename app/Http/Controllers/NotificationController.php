<?php
namespace App\Http\Controllers;

use App\Models\ItemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ItemNotification::where('user_id', Auth::id())
            ->latest()->paginate(20);
        return response()
            ->view('notifications.index', compact('notifications'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    public function markRead(ItemNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['is_read' => true]);
        return back();
    }

    public function visit(ItemNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['is_read' => true]);

        $link = $notification->link;
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $linkHost = $link ? parse_url($link, PHP_URL_HOST) : null;

        $isSafe = $link && (
            Str::startsWith($link, '/') ||
            ($linkHost && $appHost && $linkHost === $appHost)
        );

        return redirect($isSafe ? $link : route('notifications.index'));
    }

    public function markAllRead()
    {
        ItemNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
