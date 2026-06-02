<?php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLike implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;
    public $userId;
    public $userName;
    public $action;

    public function __construct($postId, User $user, $action)
    {
        $this->postId = $postId;
        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->action = $action; // 'like' or 'unlike'
    }

    public function broadcastOn()
    {
        return new Channel('posts');
    }

    public function broadcastAs()
    {
        return 'like';
    }

    public function broadcastWith()
    {
        return [
            'post_id' => $this->postId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'action' => $this->action
        ];
    }
}