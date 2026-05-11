<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Events\PostCreate;
use Illuminate\Http\Request;
use App\Events\PostDelete;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $posts = Post::with('user')->orderBy('id', 'asc')->get();
        return view('posts', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
        ]);

        event(new PostCreate($post));

        return back()->with('success', 'Post created successfully.');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (auth()->user()->is_admin || $post->user_id === auth()->id()) {
            $post->delete();
            event(new PostDelete($id));
            return back()->with('success', 'Post deleted');
        }

        return back()->with('error', 'Unauthorized action.');
    }
}