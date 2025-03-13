<div id="gallery" class="gallery-container">
	@foreach ($posts as $post)
	<article class="card mb-4" aria-hidden="true">
		<div class="card-header">
			<div class="d-flex flex-column">
				<h2 class="card-title">
					{{ $post->author->username }}
				</h2>
				<h6 class="card-title">
					{{ date_format($post->createdAt, 'd/m/Y H:i:s') }}
				</h6>
			</div>
		</div>
		<div class="ratio ratio-1x1">
			<object
				type="image/svg+xml"
				data="{{ $post->pictureUrl }}"
				alt="{{ $post->description }}">
				<div class="placeholder w-100 h-100"></div>
			</object>
		</div>
		<div class="card-body">
			<h5 class="card-title">{{ $post->title }}</h5>
			<p class="card-text">{{ $post->description }}</p>
			<div class="d-flex gap-3">
				<div>
					<input type="checkbox" class="btn-check" id="btn-check-{{ $post->id }}"
						autocomplete="off" data-app-post-action="like" data-app-post-liked="{{ $post->isLiked ? 'true' : 'false' }}"
						data-app-post-id="{{ $post->id }}">
					<label class="btn btn-danger d-flex gap-2 justify-content-center" for="btn-check-{{ $post->id }}" style="min-width: 7em;">
						<i class="bi {{ $post->isLiked ? 'bi-heart-fill' : 'bi-heart'}}"></i>
						<span>{{ $post->likeCount }} {{ $post->likeCount === 1 ? 'Like' : 'Likes' }}<span>
					</label>
				</div>
				<form class="post-comment-form needs-validation flex-grow-1 d-flex gap-2"
					method="post" action="/post/{{ $post->id }}/comments">
					@csrf
					<div class="form-group flex-grow-1">
						<input type="text" class="form-control"
							id="body" name="body" aria-label="Comment body"
							minlength="1" maxlength="512" required>
						<div class="invalid-feedback">
							Comment is required.
						</div>
					</div>
					<button class="btn btn-primary d-flex gap-2" aria-label="Post comment">
						<i class="bi-send"></i><span>Post</span>
					</button>
				</form>
			</input>
			</div>
		</div>
	</article>
	@endforeach
	@if(empty($posts))
	<div class="card">
		<div class="card-header">
			<div class="d-flex flex-column">
				<h1 class="card-title">
					Welcome to Camagru
				</h1>
			</div>
		</div>
		<div class="card-body">
			<p class="card-text">
				Nobody has posted anything yet.
			</p>
			@role("USER", "ADMIN")
			<a href="/post" class="btn btn-primary">
				<div class="d-flex gap-2">
					<i class="bi bi-send-fill"></i>
					Post something
				</div>
			</a>
			@endrole
		</div>
	</div>
	@endif
</div>

<script src="/js/gallery.min.js" defer></script>
<script src="/js/form.min.js" defer></script>
