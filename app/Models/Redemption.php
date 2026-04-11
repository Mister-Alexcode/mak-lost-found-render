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
            ['points' => 50,  'tier' => 'certificate',  'label' => 'Certificate of Appreciation',   'description' => 'A digital certificate recognising your contribution to the MAK community.'],
            ['points' => 100, 'tier' => 'voucher',       'label' => 'Campus Voucher (UGX 5,000)',      'description' => 'A voucher redeemable at selected campus outlets.'],
            ['points' => 200, 'tier' => 'trophy',        'label' => 'Community Hero Trophy',          'description' => 'A physical trophy awarded at the next student awards ceremony.'],
        ];
    }
}
