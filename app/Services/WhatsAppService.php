<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message.
     *
     * Configure in .env:
     *   WHATSAPP_PROVIDER=twilio  (or meta)
     *   TWILIO_SID=your_sid
     *   TWILIO_AUTH_TOKEN=your_token
     *   TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
     */
    public static function send(string $phone, string $message): bool
    {
        $provider = config('services.whatsapp.provider', env('WHATSAPP_PROVIDER', 'log'));

        $phone = self::normalizePhone($phone);

        if ($provider === 'twilio') {
            return self::sendViaTwilio($phone, $message);
        }

        // Default: just log it (for development/demo)
        Log::info("WhatsApp to {$phone}: {$message}");
        return true;
    }

    private static function sendViaTwilio(string $phone, string $message): bool
    {
        $sid   = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from  = env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');

        if (!$sid || !$token) {
            Log::warning('WhatsApp: Twilio credentials not configured');
            return false;
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To'   => 'whatsapp:' . $phone,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            Log::info("WhatsApp sent to {$phone}");
            return true;
        }

        Log::error("WhatsApp failed to {$phone}: " . $response->body());
        return false;
    }

    private static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (preg_match('/^0[7][0-9]{8}$/', $phone)) {
            $phone = '+256' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '+')) {
            $phone = '+256' . $phone;
        }
        return $phone;
    }
}
