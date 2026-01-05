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
