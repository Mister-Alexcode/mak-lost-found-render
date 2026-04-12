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
        'notification_preferences',
        'is_blocked', 'blocked_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'phone_verified_at'  => 'datetime',
        'blocked_at'         => 'datetime',
        'password'           => 'hashed',
        'notification_preferences' => 'array',
        'is_blocked'         => 'boolean',
    ];

    public function isBlocked(): bool { return (bool) $this->is_blocked; }

    public function getNotificationChannels(): array
    {
        // Users who have never touched notification settings get email + whatsapp
        // on by default so match/claim alerts actually reach them.
        $prefs = $this->notification_preferences ?? ['email' => true, 'whatsapp' => true];
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