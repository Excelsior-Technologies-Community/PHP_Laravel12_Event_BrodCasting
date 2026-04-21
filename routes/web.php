<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Auth;


Auth::routes();

Route::get('/', [PostController::class, 'index']);

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth')
    ->name('posts.store');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

Route::delete('/posts/{id}', [PostController::class, 'destroy'])
    ->middleware('auth') // 
    ->name('posts.delete');
