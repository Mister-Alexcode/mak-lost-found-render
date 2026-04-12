<?php
namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate and send an OTP code.
     *
     * @param string $identifier  Email address or phone number
     * @param string $purpose     registration, password_reset, phone_verify
     * @param string $channel     email, whatsapp
     */
    public static function send(string $identifier, string $purpose, string $channel): OtpCode
    {
        // Invalidate any existing codes for this identifier + purpose
        OtpCode::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpCode::create([
            'identifier' => $identifier,
            'code'       => $code,
            'purpose'    => $purpose,
            'channel'    => $channel,
            'expires_at' => now()->addMinutes(10),
        ]);

        $purposeLabels = [
            'registration'   => 'Registration',
            'password_reset' => 'Password Reset',
            'phone_verify'   => 'Phone Verification',
        ];
        $label = $purposeLabels[$purpose] ?? 'Verification';

        $message = "Your MAK Lost & Found {$label} code is: {$code}. It expires in 10 minutes. Do not share this code with anyone.";

        if ($channel === 'email') {
            self::sendViaEmail($identifier, $label, $code, $message);
        } elseif ($channel === 'whatsapp') {
            WhatsAppService::send($identifier, $message);
        }

        return $otp;
    }

    /**
     * Verify an OTP code.
     */
    public static function verify(string $identifier, string $purpose, string $code): bool
    {
        $otp = OtpCode::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        if ($otp->isExpired()) {
            return false;
        }

        $otp->update(['verified_at' => now()]);
        return true;
    }

    /**
     * Check if an identifier has been verified for a given purpose.
     */
    public static function isVerified(string $identifier, string $purpose): bool
    {
        return OtpCode::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>', now()->subMinutes(30))
            ->exists();
    }

    private static function sendViaEmail(string $email, string $label, string $code, string $message): bool
    {
        try {
            Mail::raw($message, function ($mail) use ($email, $label) {
                $mail->to($email)
                     ->subject("MAK Lost & Found — {$label} Code");
            });
            return true;
        } catch (\Exception $e) {
            Log::warning("OTP email failed for {$email}: " . $e->getMessage());
            return false;
        }
    }
}
