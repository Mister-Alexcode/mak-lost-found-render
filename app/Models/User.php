<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'phone_number', 'student_id', 'reward_points',
        'notification_preferences'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notification_preferences' => 'array',
    ];

    public function getNotificationChannels(): array
    {
        $prefs = $this->notification_preferences ?? [];
        // in-app is always enabled
        $channels = ['in-app'];
        if (!empty($prefs['email'])) $channels[] = 'email';
        if (!empty($prefs['sms'])) $channels[] = 'sms';
        if (!empty($prefs['whatsapp'])) $channels[] = 'whatsapp';
        return $channels;
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function lostItems() { return $this->hasMany(LostItem::class); }
    public function foundItems() { return $this->hasMany(FoundItem::class); }
    public function claims() { return $this->hasMany(Claim::class, 'claimant_id'); }
    public function rewards() { return $this->hasMany(Reward::class); }
    public function sentMessages() { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(Message::class, 'receiver_id'); }
    public function notifications() { return $this->hasMany(ItemNotification::class); }
    public function admin() { return $this->hasOne(Admin::class); }
}