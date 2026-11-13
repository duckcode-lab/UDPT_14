<h1>{{ $movie->title }}</h1>
<p>{{ $movie->description }}</p>

<hr>

<h3>Bình luận</h3>

@auth
    <form action="{{ route('movies.comment', $movie->id) }}" method="POST">
        @csrf
        <textarea name="content" placeholder="Nhập bình luận..." required></textarea><br><br>
        <button type="submit">Gửi bình luận</button>
    </form>
    @else
        <p>Bạn cần <a href="{{ route('login') }}">đăng nhập</a> để bình luận.</p>
@endauth

    <h4>Các bình luận:</h4>
    @forelse($movie->comments as $comment)
        <p><strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}</p>
    @empty
        <p>Chưa có bình luận nào.</p>
@endforelse
