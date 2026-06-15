<?php

namespace Tests\Unit;

use App\Models\OtpCode;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['whatsapp.provider' => 'log', 'sms.provider' => 'log']);
    }

    public function test_send_creates_a_six_digit_code(): void
    {
        $otp = OtpService::send('test@example.com', 'registration', 'email');

        $this->assertInstanceOf(OtpCode::class, $otp);
        $this->assertEquals('test@example.com', $otp->identifier);
        $this->assertEquals('registration', $otp->purpose);
        $this->assertEquals('email', $otp->channel);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp->code);
        $this->assertNull($otp->verified_at);
        $this->assertTrue($otp->expires_at->isFuture());
    }

    public function test_verify_with_correct_code_marks_code_as_verified(): void
    {
        $otp = OtpService::send('user@example.com', 'registration', 'email');

        $result = OtpService::verify('user@example.com', 'registration', $otp->code);

        $this->assertTrue($result);
        $this->assertNotNull($otp->fresh()->verified_at);
    }

    public function test_verify_with_wrong_code_returns_false(): void
    {
        OtpService::send('user@example.com', 'registration', 'email');

        $result = OtpService::verify('user@example.com', 'registration', '000000');

        $this->assertFalse($result);
    }

    public function test_verify_with_expired_code_returns_false(): void
    {
        $otp = OtpService::send('user@example.com', 'registration', 'email');
        // Force expiry
        $otp->update(['expires_at' => now()->subMinute()]);

        $result = OtpService::verify('user@example.com', 'registration', $otp->code);

        $this->assertFalse($result);
    }

    public function test_sending_a_new_code_invalidates_prior_unverified_codes(): void
    {
        $first  = OtpService::send('user@example.com', 'registration', 'email');
        $second = OtpService::send('user@example.com', 'registration', 'email');

        $this->assertNull(OtpCode::find($first->id), 'Prior unverified code should be deleted');
        $this->assertNotNull(OtpCode::find($second->id));
        $this->assertEquals(1, OtpCode::where('identifier', 'user@example.com')->count());
    }

    public function test_isVerified_returns_true_within_window(): void
    {
        $otp = OtpService::send('user@example.com', 'registration', 'email');
        OtpService::verify('user@example.com', 'registration', $otp->code);

        $this->assertTrue(OtpService::isVerified('user@example.com', 'registration'));
    }

    public function test_isVerified_returns_false_when_no_recent_verification(): void
    {
        $this->assertFalse(OtpService::isVerified('stranger@example.com', 'registration'));
    }

    public function test_purposes_are_isolated(): void
    {
        $reg = OtpService::send('user@example.com', 'registration', 'email');

        // A reset code for the same identifier should not collide
        $reset = OtpService::send('user@example.com', 'password_reset', 'email');

        $this->assertEquals(2, OtpCode::where('identifier', 'user@example.com')->count());
        $this->assertNotEquals($reg->id, $reset->id);

        $this->assertFalse(OtpService::verify('user@example.com', 'password_reset', $reg->code));
        $this->assertTrue(OtpService::verify('user@example.com', 'password_reset', $reset->code));
    }
}
