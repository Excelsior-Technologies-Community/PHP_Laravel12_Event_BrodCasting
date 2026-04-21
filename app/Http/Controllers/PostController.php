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
        $this->middleware('auth'); // ADD THIS
    }
    public function index()
    {
        $posts = Post::orderBy('id', 'asc')->get(); //  ASC order
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
        $post->delete();

        event(new PostDelete($id));

        return back()->with('success', 'Post deleted');
    }
}
