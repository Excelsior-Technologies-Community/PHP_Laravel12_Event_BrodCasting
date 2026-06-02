<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Events\PostCreate;
use App\Events\PostUpdate;
use App\Events\PostDelete;
use App\Events\PostLike;
use App\Events\NewComment;
use App\Events\UserTyping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $posts = Post::with(['user', 'comments.user', 'likes'])->orderBy('id', 'desc')->get();
        
        // Add custom attributes
        foreach ($posts as $post) {
            $post->likes_count = $post->likes->count();
            $post->is_liked_by_user = $post->isLikedByUser(Auth::id());
        }
        
        return view('posts', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:3|max:255',
            'body' => 'required|min:5'
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
        ]);

        $post->load('user');
        $post->likes_count = 0;
        $post->is_liked_by_user = false;

        broadcast(new PostCreate($post))->toOthers();

        return response()->json([
            'success' => true,
            'post' => $post,
            'message' => 'Post created successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        if ($post->user_id !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|min:3|max:255',
            'body' => 'required|min:5'
        ]);

        $post->update([
            'title' => $request->title,
            'body' => $request->body
        ]);

        broadcast(new PostUpdate($post))->toOthers();

        return response()->json([
            'success' => true,
            'post' => $post,
            'message' => 'Post updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (auth()->user()->is_admin || $post->user_id === auth()->id()) {
            $postId = $post->id;
            $post->delete();
            
            broadcast(new PostDelete($postId))->toOthers();
            
            return response()->json([
                'success' => true,
                'post_id' => $postId,
                'message' => 'Post deleted successfully.'
            ]);
        }

        return response()->json(['error' => 'Unauthorized action.'], 403);
    }

    public function like($id)
    {
        $post = Post::findOrFail($id);
        $user = auth()->user();
        
        $existingLike = $post->likes()->where('user_id', $user->id)->first();
        
        if ($existingLike) {
            $existingLike->delete();
            $action = 'unlike';
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $action = 'like';
        }
        
        $likesCount = $post->likes()->count();
        
        broadcast(new PostLike($post->id, $user, $action))->toOthers();
        
        return response()->json([
            'success' => true,
            'likes_count' => $likesCount,
            'is_liked' => !$existingLike,
            'action' => $action
        ]);
    }

    public function comment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|min:1|max:500'
        ]);
        
        $post = Post::findOrFail($id);
        
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);
        
        $comment->load('user');
        
        broadcast(new NewComment($comment))->toOthers();
        
        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'Comment added successfully.'
        ]);
    }

    public function typing(Request $request)
    {
        broadcast(new UserTyping(auth()->user()))->toOthers();
        return response()->json(['success' => true]);
    }

    public function updateLastSeen()
    {
        auth()->user()->update(['last_seen' => now()]);
        return response()->json(['success' => true]);
    }
}