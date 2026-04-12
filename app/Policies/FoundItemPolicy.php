<?php
namespace App\Policies;

use App\Models\FoundItem;
use App\Models\User;

class FoundItemPolicy
{
    public function view(User $user, FoundItem $foundItem): bool {
        return true;
    }
    public function update(User $user, FoundItem $foundItem): bool {
        return $user->id === $foundItem->user_id;
    }
    public function delete(User $user, FoundItem $foundItem): bool {
        return $user->id === $foundItem->user_id || $user->isAdmin();
    }
}
