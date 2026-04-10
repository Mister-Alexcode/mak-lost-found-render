<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItem extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'item_name', 'category', 'description',
        'color', 'brand', 'location_lost', 'date_lost',
        'photo', 'status', 'tracking_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function matches() { return $this->hasMany(ItemMatch::class, 'lost_item_id'); }
}