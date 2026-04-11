<?php
namespace App\Services;

use App\Models\ItemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ItemNotificationMail;

class NotificationDispatcher
{
    /**
     * Send a notification to a user via all their preferred channels.
     * In-app is always created regardless of preferences.
     */
    public static function send(User $user, string $type, string $message): ItemNotification
    {
        // Always create in-app notification
        $notification = ItemNotification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'message' => $message,
            'channel' => 'in-app',
            'is_read' => false,
        ]);

        $channels = $user->getNotificationChannels();

        // Email
        if (in_array('email', $channels) && $user->email) {
            try {
                Mail::to($user->email)->queue(new ItemNotificationMail($type, $message, $user->name));
            } catch (\Exception $e) {
                \Log::warning('Email notification failed for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        // SMS
        if (in_array('sms', $channels) && $user->phone_number) {
            try {
                SmsService::send($user->phone_number, $message);
            } catch (\Exception $e) {
                \Log::warning('SMS notification failed for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        // WhatsApp
        if (in_array('whatsapp', $channels) && $user->phone_number) {
            try {
                WhatsAppService::send($user->phone_number, $message);
            } catch (\Exception $e) {
                \Log::warning('WhatsApp notification failed for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        return $notification;
    }
}
