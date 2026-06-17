<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'item_name', 'category', 'description',
        'color', 'brand', 'location_found', 'latitude', 'longitude',
        'date_found', 'photo', 'status', 'is_high_value', 'tracking_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function matches() { return $this->hasMany(ItemMatch::class, 'found_item_id'); }

    /**
     * Render the photo whether it's a Cloudinary URL or a local disk path.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }
        return str_starts_with($this->photo, 'http')
            ? $this->photo
            : asset('storage/' . $this->photo);
    }
}