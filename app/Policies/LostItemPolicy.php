<?php
namespace App\Policies;

use App\Models\LostItem;
use App\Models\User;

class LostItemPolicy
{
    public function view(User $user, LostItem $lostItem): bool {
        return true;
    }
    public function update(User $user, LostItem $lostItem): bool {
        return $user->id === $lostItem->user_id;
    }
    public function delete(User $user, LostItem $lostItem): bool {
        return $user->id === $lostItem->user_id || $user->isAdmin();
    }
}