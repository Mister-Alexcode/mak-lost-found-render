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

    public function visit(\Illuminate\Http\Request $request, ItemNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['is_read' => true]);

        $link = $notification->link;
        if (!$link) {
            return redirect()->route('notifications.index');
        }

        $parts = parse_url($link);
        $path = $parts['path'] ?? '/';
        $suffix = (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

        $base = rtrim(parse_url(config('app.url'), PHP_URL_PATH) ?? '', '/');
        if ($base && !Str::startsWith($path, $base . '/') && $path !== $base) {
            $path = $base . $path;
        }

        return redirect()->away($request->getSchemeAndHttpHost() . $path . $suffix);
    }

    public function markAllRead()
    {
        ItemNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
