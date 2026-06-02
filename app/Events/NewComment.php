<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment->load('user');
    }

    public function broadcastOn()
    {
        return new Channel('posts');
    }

    public function broadcastAs()
    {
        return 'comment';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'content' => $this->comment->content,
            'user' => [
                'id' => $this->comment->user->id,
                'name' => $this->comment->user->name
            ],
            'created_at' => $this->comment->created_at->diffForHumans()
        ];
    }
}