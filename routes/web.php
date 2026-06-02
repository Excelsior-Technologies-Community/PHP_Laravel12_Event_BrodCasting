<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', [PostController::class, 'index']);
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::middleware('auth')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.delete');
    Route::post('/posts/{id}/like', [PostController::class, 'like'])->name('posts.like');
    Route::post('/posts/{id}/comment', [PostController::class, 'comment'])->name('posts.comment');
    Route::post('/typing', [PostController::class, 'typing'])->name('typing');
    Route::post('/update-last-seen', [PostController::class, 'updateLastSeen'])->name('update.last.seen');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');