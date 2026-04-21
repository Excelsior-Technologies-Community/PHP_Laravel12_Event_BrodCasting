<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // ✅ CHANGE
use Illuminate\Broadcasting\InteractsWithSockets;

class PostCreate implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $post;

    public function __construct(Post $post)
    {
        // ✅ IMPORTANT (to get user name in frontend)
        $this->post = $post->load('user');
    }

    public function broadcastOn()
    {
        return new Channel('posts');
    }

    public function broadcastAs()
    {
        return 'create';
    }
}