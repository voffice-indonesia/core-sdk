<?php

namespace VoxDev\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VoxDev\Core\Auth\CoreAuthUser;

class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    /**
     * The authenticated user.
     */
    public CoreAuthUser $user;

    /**
     * Create a new event instance.
     */
    public function __construct(CoreAuthUser $user)
    {
        $this->user = $user;
    }
}
