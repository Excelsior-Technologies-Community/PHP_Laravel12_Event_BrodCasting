@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-9">
            <h5 class="mb-3">Total Posts: <span id="post-count">{{ count($posts) }}</span></h5>

            <div class="card mb-4">
                <div class="card-header">Create Post</div>
                <div class="card-body">
                    <form id="post-form" method="POST" action="{{ route('posts.store') }}">
                        @csrf
                        <input type="text" id="post-title" name="title" placeholder="Title" class="form-control mb-2" required>
                        <textarea id="post-body" name="body" placeholder="Body" class="form-control mb-2" required></textarea>
                        <div id="typing-indicator" class="text-muted small mb-2" style="height: 20px;"></div>
                        <button class="btn btn-primary">Create Post</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">All Posts</div>
                <div class="card-body">
                    <table class="table table-bordered" id="posts-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Body</th>
                                <th>User</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                                <tr id="post-{{ $post->id }}">
                                    <td>{{ $post->id }}</td>
                                    <td>{{ $post->title }}</td>
                                    <td>{{ $post->body }}</td>
                                    <td>{{ $post->user->name }}</td>
                                    <td>{{ $post->created_at->format('d-m-Y H:i') }}</td>
                                    <td>
                                        @if(Auth::user()->is_admin || $post->user_id == Auth::id())
                                            <form method="POST" action="{{ route('posts.delete', $post->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-success text-white">Online Users</div>
                <ul class="list-group list-group-flush" id="online-users">
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection

@section('script')
<script type="module">
document.addEventListener("DOMContentLoaded", () => {
    const checkEcho = setInterval(() => {
        if (window.Echo) {
            clearInterval(checkEcho);

            let typingTimer;

            window.Echo.join('posts')
                .here((users) => {
                    const userList = document.getElementById('online-users');
                    userList.innerHTML = '';
                    users.forEach(user => {
                        const li = `<li class="list-group-item" id="user-${user.id}"><span class="badge bg-success rounded-circle p-1"> </span> ${user.name}</li>`;
                        userList.insertAdjacentHTML('beforeend', li);
                    });
                })
                .joining((user) => {
                    const userList = document.getElementById('online-users');
                    const li = `<li class="list-group-item" id="user-${user.id}"><span class="badge bg-success rounded-circle p-1"> </span> ${user.name}</li>`;
                    userList.insertAdjacentHTML('beforeend', li);
                    
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: `${user.name} is now online`,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                })
                .leaving((user) => {
                    document.getElementById(`user-${user.id}`)?.remove();
                })
                .listen('.create', (e) => {
                    const tableBody = document.querySelector('#posts-table tbody');
                    const isAdmin = {{ Auth::user()->is_admin ? 'true' : 'false' }};
                    const currentUserId = {{ Auth::id() }};

                    let deleteBtn = '';
                    if (isAdmin || e.post.user_id == currentUserId) {
                        deleteBtn = `
                            <form method="POST" action="/posts/${e.post.id}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>`;
                    }

                    const newRow = `
                        <tr id="post-${e.post.id}">
                            <td>${e.post.id}</td>
                            <td>${e.post.title}</td>
                            <td>${e.post.body}</td>
                            <td>${e.post.user ? e.post.user.name : 'User'}</td>
                            <td>${new Date(e.post.created_at).toLocaleString()}</td>
                            <td>${deleteBtn}</td>
                        </tr>
                    `;

                    tableBody.insertAdjacentHTML('beforeend', newRow);
                    let count = document.getElementById('post-count');
                    count.innerText = parseInt(count.innerText) + 1;

                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: `New post: ${e.post.title}`,
                            text: `By ${e.post.user.name}`,
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    }
                })
                .listen('.delete', (e) => {
                    document.getElementById(`post-${e.postId}`)?.remove();
                    let count = document.getElementById('post-count');
                    count.innerText = Math.max(0, parseInt(count.innerText) - 1);
                })
                .listenForWhisper('typing', (e) => {
                    const indicator = document.getElementById('typing-indicator');
                    indicator.innerText = `${e.name} is typing...`;
                    
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        indicator.innerText = '';
                    }, 2000);
                });

            const postTitle = document.getElementById('post-title');
            const postBody = document.getElementById('post-body');

            [postTitle, postBody].forEach(el => {
                el.addEventListener('input', () => {
                    window.Echo.join('posts')
                        .whisper('typing', {
                            name: "{{ Auth::user()->name }}"
                        });
                });
            });
        }
    }, 100);
});
</script>
@endsection