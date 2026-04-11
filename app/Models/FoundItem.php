<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'item_name', 'category', 'description',
        'color', 'brand', 'location_found', 'latitude', 'longitude',
        'date_found', 'photo', 'status', 'tracking_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function matches() { return $this->hasMany(ItemMatch::class, 'found_item_id'); }
}