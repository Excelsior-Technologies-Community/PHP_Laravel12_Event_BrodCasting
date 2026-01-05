# PHP_Laravel12_Event_BrodCasting

---

## Project Description

The PHP_Laravel12_Event_BrodCasting project is a web-based real-time post management system built using Laravel 12. The main objective of this project is to demonstrate how Laravel event broadcasting works by updating the frontend in real-time whenever a user creates a new post, without requiring the page to reload.

This project integrates authentication, database management, and WebSocket-based event broadcasting to create a simple, yet dynamic and interactive application.


## Advantages of the Project:

Demonstrates real-time functionality using Laravel 12.

Implements event-driven architecture which is widely used in modern web applications.

Provides interactive user experience without page reloads.

Serves as a foundation for advanced features like chat applications, live notifications, or admin dashboards.


## Use Cases:

Real-time notification system for posts, messages, or alerts.

Admin dashboards for monitoring live activities.

Educational project to understand Laravel events, broadcasting, and WebSockets.


## Conclusion

The PHP_Laravel12_Event_BrodCasting project is a simple, interactive, and real-time post management system built using Laravel 12. It demonstrates the practical usage of event broadcasting and provides a solid base for developing real-time applications like chat systems, notifications, and dashboards.


---


# Project SetUp

---

## STEP 1: Create New Laravel 12 Project

### Run Command :

```
composer create-project laravel/laravel PHP_Laravel12_Event_BrodCasting "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_Event_BrodCasting

```

Make sure Laravel 12 installed successfully.



### Database Configuration

Open .env file and update database credentials:

```

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_broadcasting
DB_USERNAME=root
DB_PASSWORD=

```

### Create database:

```
event_broadcasting

```


## Step 2 — Install Auth Scaffolding

```

composer require laravel/ui
php artisan ui bootstrap --auth
npm install
npm run build

```

This creates login, register, and dashboard UI.


## Step 3 — Create Migrations

### Add an is_admin column and a posts table:

```

php artisan make:migration add_is_admin_column_to_users_table
php artisan make:migration create_posts_table

```

### Open the new migration files and update:

#### add_is_admin_column_to_users_table.php

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up() {
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(0);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};

```

#### create_posts_table.php

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up() {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('body');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

```

### Then run migrations:

```
php artisan migrate

```


## Step 4 — Create Post Model

### Run Command:

```
php artisan make:model Post

```

### Update app/Models/Post.php:

```<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use HasFactory;

    protected $fillable = ['user_id','title','body'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

```

### Update app/Models/User.php:

```

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',   //  IMPORTANT (add this)
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

```


## Step 5 — Enable Broadcasting & Install Reverb

Laravel comes with broadcasting disabled by default — enable it:

### Run Command:

```
php artisan install:broadcasting

```

Select Reverb when prompted.

### Or manually install:

```
composer require laravel/reverb
php artisan reverb:install

```

### Install Echo:

```
npm install --save-dev laravel-echo

```

### Update resources/js/echo.js:

```
import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws','wss'],
});

```

### you see this keys(after all install) in .env:

```

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your_id
REVERB_APP_KEY=your_key
REVERB_APP_SECRET=your_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

```

### Rebuild assets:

```
npm run build

```

This config enables real-time broadcast with Reverb



## Step 6 — Create Event


### Run Command:

```
php artisan make:event PostCreate

```

### Update app/Events/PostCreate.php:

```

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

```


## Step 7 — Routes

### Open routes/web.php and add:

```

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});


Auth::routes();

Route::get('/', [PostController::class, 'index']);

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth')
    ->name('posts.store');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');


```



## Step 8 — Create PostController

### Run Command:

```
php artisan make:controller PostController

```

### Update: app/Http/Controllers/PostController.php

```

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

```


## Step 9 — Blade Views

### resources/views/posts.blade.php

```

@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <!-- Form to create a post -->
    <div class="card mb-4">
        <div class="card-header">Create Post</div>
        <div class="card-body">
            <form method="POST" action="{{ route('posts.store') }}">
                @csrf
                <div class="mb-3">
                    <input type="text" name="title" placeholder="Title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <textarea name="body" placeholder="Body" class="form-control" required></textarea>
                </div>
                <button class="btn btn-primary">Create Post</button>
            </form>
        </div>
    </div>

    <!-- Real-time Notifications -->
    <div class="mb-4">
        <h5>Notifications (Real-Time)</h5>
        <ul id="notification" class="list-group"></ul>
    </div>

    <!-- Posts Table -->
    <div class="card">
        <div class="card-header">All Posts</div>
        <div class="card-body">
            <table class="table table-bordered" id="posts-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Body</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr id="post-{{ $post->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->body }}</td>
                            <td>{{ $post->user->name }}</td>
                            <td>{{ $post->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('script')
<script type="module">
    // Real-time notification via Laravel Echo
    window.Echo.channel('posts')
        .listen('.create', (e) => {
            console.log('New post arrived', e.post);

            // Add to notification list
            document.getElementById('notification').insertAdjacentHTML(
                'beforeend',
                `<li class="list-group-item">New Post: ${e.post.title}</li>`
            );

            // Add to posts table dynamically
            const tableBody = document.querySelector('#posts-table tbody');
            const rowCount = tableBody.rows.length + 1;
            const newRow = `
                <tr id="post-${e.post.id}">
                    <td>${rowCount}</td>
                    <td>${e.post.title}</td>
                    <td>${e.post.body}</td>
                    <td>${e.post.user.name}</td>
                    <td>${new Date(e.post.created_at).toLocaleString()}</td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRow);
        });
</script>
@endsection

```


## Step 10 — Create Admin User

### Run Command:

```
php artisan make:seeder CreateAdminUser

```

### Update database/seeders/CreateAdminUser.php:

```
<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateAdminUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
    'name'=>'Admin',
    'email'=>'admin@gmail.com',
    'password'=>bcrypt('123456'),
    'is_admin'=>1
]);

    }
}

```

### Run seed:

```
php artisan db:seed --class=CreateAdminUser

```



## Step 11 — Start the App

### In one terminal:
```
php artisan serve

```

### In another:

```
php artisan reverb:start

```

### Now open:

```
 http://localhost:8000

```


## Now You can see this type Output:

### Register Page:


<img width="1911" height="961" alt="Screenshot 2026-01-05 120748" src="https://github.com/user-attachments/assets/89df2cae-b79f-46ee-b389-fb0f02d8e253" />


### Login Page:


<img width="1911" height="949" alt="Screenshot 2026-01-05 120930" src="https://github.com/user-attachments/assets/4f3a5329-33ea-4220-a8f9-c47c40e013ed" />


### Posts Page:


<img width="1917" height="963" alt="Screenshot 2026-01-05 120952" src="https://github.com/user-attachments/assets/3eb71a72-b103-4cc1-bf3a-569af41d09a0" />

(after create) Post Page:

<img width="1918" height="971" alt="Screenshot 2026-01-05 121053" src="https://github.com/user-attachments/assets/65f65932-f459-4bdf-a79e-61d5028d79e4" />



---

# Project Folder Structure:

```
PHP_Laravel12_Event_BrodCasting/
├── app/
│   ├── Events/
│   │   └── PostCreate.php          # Event that broadcasts new posts
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── PostController.php  # Handles post CRUD & triggers event
│   ├── Models/
│   │   ├── Post.php                # Post model
│   │   └── User.php                # User model (with is_admin)
│   ├── Providers/
│   │   └── BroadcastServiceProvider.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── broadcasting.php            # Broadcasting config
│   └── database.php
├── database/
│   ├── factories/
│   ├── migrations/
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   ├── xxxx_add_is_admin_column_to_users_table.php
│   │   └── xxxx_create_posts_table.php
│   └── seeders/
│       └── CreateAdminUser.php    # Admin user seeder
├── public/
│   └── index.php
├── resources/
│   ├── js/
│   │   └── echo.js                # Laravel Echo JS configuration
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php      # Default layout from Auth scaffolding
│       └── posts.blade.php        # Post form, table, real-time notifications
├── routes/
│   └── web.php                     # Routes for auth + posts
├── .env                            # Database & Reverb configuration
├── composer.json
└── package.json

```
