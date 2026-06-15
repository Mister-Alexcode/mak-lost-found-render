<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'points_used', 'reward_tier', 'status'];

    public function user() { return $this->belongsTo(User::class); }

    public static function tiers(): array {
        return [
            ['points' => 50,  'tier' => 'certificate',  'icon' => '📜', 'label' => 'Certificate of Appreciation', 'description' => 'A digital certificate recognising your contribution to the MAK community.'],
            ['points' => 100, 'tier' => 'voucher',      'icon' => '🎟️', 'label' => 'Campus Voucher (UGX 5,000)',   'description' => 'A voucher redeemable at selected campus outlets.'],
            ['points' => 200, 'tier' => 'voucher_10k',  'icon' => '🎟️', 'label' => 'Campus Voucher (UGX 10,000)',  'description' => 'A premium voucher redeemable at selected campus outlets.'],
        ];
    }
}
