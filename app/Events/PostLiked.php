<?php

namespace App\Events;

use App\Models\Like;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLiked
{
    use Dispatchable, SerializesModels;

    public function __construct(public Like $like) {}
}
