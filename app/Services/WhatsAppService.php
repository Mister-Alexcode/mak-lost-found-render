<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message.
     *
     * Supported providers:
     *   - twilio:  Uses Twilio WhatsApp sandbox/production
     *   - meta:    Uses Meta WhatsApp Cloud API directly
     *   - log:     Development mode — logs the message only
     *
     * Configure in .env:
     *   WHATSAPP_PROVIDER=twilio  (or meta, or log)
     *
     *   # Twilio
     *   TWILIO_SID=your_sid
     *   TWILIO_AUTH_TOKEN=your_token
     *   TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
     *
     *   # Meta Cloud API
     *   WHATSAPP_META_TOKEN=your_permanent_token
     *   WHATSAPP_META_PHONE_ID=your_phone_number_id
     */
    public static function send(string $phone, string $message): bool
    {
        $provider = config('services.whatsapp.provider', env('WHATSAPP_PROVIDER', 'log'));
        $phone = self::normalizePhone($phone);

        return match ($provider) {
            'twilio' => self::sendViaTwilio($phone, $message),
            'meta'   => self::sendViaMeta($phone, $message),
            default  => self::logMessage($phone, $message),
        };
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

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->connectTimeout(15)
                ->timeout(25)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To'   => 'whatsapp:' . $phone,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp sent to {$phone} via Twilio");
                return true;
            }

            Log::error("WhatsApp Twilio failed to {$phone}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp Twilio exception for {$phone}: " . $e->getMessage());
            return false;
        }
    }

    private static function sendViaMeta(string $phone, string $message): bool
    {
        $token   = env('WHATSAPP_META_TOKEN');
        $phoneId = env('WHATSAPP_META_PHONE_ID');

        if (!$token || !$phoneId) {
            Log::warning('WhatsApp: Meta Cloud API credentials not configured');
            return false;
        }

        // Strip the leading + for Meta API
        $to = ltrim($phone, '+');

        try {
            $response = Http::withToken($token)
                ->connectTimeout(15)
                ->timeout(25)
                ->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp sent to {$phone} via Meta Cloud API");
                return true;
            }

            Log::error("WhatsApp Meta failed to {$phone}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp Meta exception for {$phone}: " . $e->getMessage());
            return false;
        }
    }

    private static function logMessage(string $phone, string $message): bool
    {
        Log::info("[WhatsApp-Log] To: {$phone} | Message: {$message}");
        return true;
    }

    /**
     * Normalize Ugandan phone numbers to international format.
     */
    private static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        // Ugandan mobile: 07XXXXXXXX -> +2567XXXXXXXX
        if (preg_match('/^0[7][0-9]{8}$/', $phone)) {
            $phone = '+256' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '+')) {
            $phone = '+256' . $phone;
        }
        return $phone;
    }
}
