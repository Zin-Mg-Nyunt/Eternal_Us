<?php

namespace App\Services;

use App\Models\Wish;
use App\Models\User;

class WishService
{
    public function createForUser(User $user, string $message): void
    {
        Wish::create([
            'user_id' => $user->id,
            'message' => $message,
        ]);
    }
}
