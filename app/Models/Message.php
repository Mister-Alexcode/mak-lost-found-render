<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    use HasFactory;

    protected $fillable = [
        'sender_id', 'receiver_id', 'claim_id', 'match_id', 'content', 'is_read'
    ];

    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
    public function claim() { return $this->belongsTo(Claim::class); }
    public function match() { return $this->belongsTo(ItemMatch::class, 'match_id'); }
}
