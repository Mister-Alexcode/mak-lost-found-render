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
     *   SMS_PROVIDER=twilio  (or africas_talking, or log)
     *
     *   # Twilio (reuses TWILIO_SID / TWILIO_AUTH_TOKEN from WhatsApp setup)
     *   TWILIO_SMS_FROM=+1XXXXXXXXXX
     *
     *   # Africa's Talking
     *   SMS_API_KEY=your_api_key
     *   SMS_USERNAME=your_username
     *   SMS_SENDER_ID=MAK_LNF
     */
    public static function send(string $phone, string $message): bool
    {
        $provider = config('services.sms.provider', env('SMS_PROVIDER', 'log'));

        $phone = self::normalizePhone($phone);

        return match ($provider) {
            'twilio'          => self::sendViaTwilio($phone, $message),
            'africas_talking' => self::sendViaAfricasTalking($phone, $message),
            default           => self::logMessage($phone, $message),
        };
    }

    private static function sendViaTwilio(string $phone, string $message): bool
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.sms_from');

        if (!$sid || !$token || !$from) {
            Log::warning('SMS: Twilio credentials or TWILIO_SMS_FROM not configured');
            return false;
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->connectTimeout(15)
                ->timeout(25)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To'   => $phone,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("SMS sent to {$phone} via Twilio");
                return true;
            }

            Log::error("SMS Twilio failed to {$phone}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("SMS Twilio exception for {$phone}: " . $e->getMessage());
            return false;
        }
    }

    private static function logMessage(string $phone, string $message): bool
    {
        Log::info("[SMS-Log] To: {$phone} | Message: {$message}");
        return true;
    }

    private static function sendViaAfricasTalking(string $phone, string $message): bool
    {
        $apiKey   = env('SMS_API_KEY');
        $username = env('SMS_USERNAME');
        $senderId = env('SMS_SENDER_ID');

        if (!$apiKey || !$username) {
            Log::warning('SMS: Africa\'s Talking credentials not configured');
            return false;
        }

        $endpoint = $username === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        $payload = [
            'username' => $username,
            'to'       => $phone,
            'message'  => $message,
        ];
        if ($senderId && $username !== 'sandbox') {
            $payload['from'] = $senderId;
        }

        $response = Http::withHeaders([
            'apiKey'       => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept'       => 'application/json',
        ])->asForm()->post($endpoint, $payload);

        if ($response->successful()) {
            $body = $response->json();
            $status = $body['SMSMessageData']['Recipients'][0]['status'] ?? 'Unknown';
            if ($status === 'Success') {
                Log::info("SMS sent to {$phone} via Africa's Talking");
                return true;
            }
            Log::error("SMS to {$phone} rejected by AT: " . $response->body());
            return false;
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
