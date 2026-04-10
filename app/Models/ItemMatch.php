<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemMatch extends Model {
    use HasFactory;

    protected $fillable = [
        'lost_item_id', 'found_item_id',
        'confidence_score', 'match_status'
    ];

    public function lostItem() { return $this->belongsTo(LostItem::class, 'lost_item_id'); }
    public function foundItem() { return $this->belongsTo(FoundItem::class, 'found_item_id'); }
    public function claims() { return $this->hasMany(Claim::class, 'match_id'); }
}