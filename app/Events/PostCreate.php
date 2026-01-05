<?php
namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;

class PostCreate implements ShouldBroadcast {
    use InteractsWithSockets;

    public $post;

    public function __construct(Post $post) {
        $this->post = $post;
    }

    public function broadcastOn() {
        return new Channel('posts');
    }

    public function broadcastAs() {
        return 'create';
    }
}
