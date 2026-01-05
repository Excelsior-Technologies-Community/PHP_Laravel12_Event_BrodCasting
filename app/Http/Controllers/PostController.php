<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Events\PostCreate;
use Illuminate\Http\Request;

class PostController extends Controller {

    public function index() {
        $posts = Post::latest()->get();
        return view('posts', compact('posts'));
    }

    public function store(Request $request) {
        $request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);

        $post = Post::create([
            'user_id'=>auth()->id(),
            'title'=>$request->title,
            'body'=>$request->body,
        ]);

        event(new PostCreate($post));

        return back()->with('success','Post created successfully.');
    }
}
