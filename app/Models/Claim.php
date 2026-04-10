<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model {
    use HasFactory;

    protected $fillable = [
        'match_id', 'claimant_id', 'admin_id',
        'verification_details', 'claim_status', 'resolved_at'
    ];

    public function match() { return $this->belongsTo(ItemMatch::class, 'match_id'); }
    public function claimant() { return $this->belongsTo(User::class, 'claimant_id'); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
    public function rewards() { return $this->hasMany(Reward::class); }
    public function messages() { return $this->hasMany(Message::class); }
}