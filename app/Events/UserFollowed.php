<?php

namespace App\Events;

use App\Models\Follow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFollowed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Follow $follow) {}
}
