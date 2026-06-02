<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function broadcastOn()
    {
        return new Channel('posts');
    }

    public function broadcastAs()
    {
        return 'update';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->post->id,
            'title' => $this->post->title,
            'body' => $this->post->body,
            'updated_at' => $this->post->updated_at->toDateTimeString(),
            'user' => [
                'id' => $this->post->user->id,
                'name' => $this->post->user->name
            ]
        ];
    }
}