<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS message.
     *
     * Configure in .env:
     *   SMS_PROVIDER=africas_talking  (or twilio)
     *   SMS_API_KEY=your_api_key
     *   SMS_USERNAME=your_username
     *   SMS_SENDER_ID=MAK_LNF
     */
    public static function send(string $phone, string $message): bool
    {
        $provider = config('services.sms.provider', env('SMS_PROVIDER', 'log'));

        // Normalize Uganda phone number
        $phone = self::normalizePhone($phone);

        if ($provider === 'africas_talking') {
            return self::sendViaAfricasTalking($phone, $message);
        }

        // Default: just log it (for development/demo)
        Log::info("SMS to {$phone}: {$message}");
        return true;
    }

    private static function sendViaAfricasTalking(string $phone, string $message): bool
    {
        $apiKey   = env('SMS_API_KEY');
        $username = env('SMS_USERNAME');
        $senderId = env('SMS_SENDER_ID', 'MAK_LNF');

        if (!$apiKey || !$username) {
            Log::warning('SMS: Africa\'s Talking credentials not configured');
            return false;
        }

        $response = Http::withHeaders([
            'apiKey'       => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept'       => 'application/json',
        ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
            'username' => $username,
            'to'       => $phone,
            'message'  => $message,
            'from'     => $senderId,
        ]);

        if ($response->successful()) {
            Log::info("SMS sent to {$phone}");
            return true;
        }

        Log::error("SMS failed to {$phone}: " . $response->body());
        return false;
    }

    private static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        // Uganda: convert 07xx to +2567xx
        if (preg_match('/^0[7][0-9]{8}$/', $phone)) {
            $phone = '+256' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '+')) {
            $phone = '+256' . $phone;
        }
        return $phone;
    }
}
