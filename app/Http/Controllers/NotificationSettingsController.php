<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $prefs = $user->notification_preferences ?? [];
        return view('profile.notifications', compact('prefs'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $user->notification_preferences = [
            'email'    => $request->boolean('email'),
            'sms'      => $request->boolean('sms'),
            'whatsapp' => $request->boolean('whatsapp'),
        ];
        $user->save();

        return back()->with('success', 'Notification preferences updated.');
    }
}
