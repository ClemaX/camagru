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
				<!-- <button class="btn btn-danger d-flex gap-2"
					data-app-post-action="like" data-app-target="{{ $post->id }}">
					<i class="bi-heart"></i>
					{{ $post->likeCount }}
					Likes
				</button> -->
				<div>
					<input type="checkbox" class="btn-check" id="btn-check"
						autocomplete="off" data-app-post-action="like"
						data-app-target="{{ $post->id }}">
					<label class="btn btn-danger d-flex gap-2" for="btn-check">
						<i class="bi-heart"></i>
						{{ $post->likeCount }} Likes
					</label>
				</div>

				<button class="btn btn-info d-flex gap-2">
					<i class="bi-chat"></i>
					Comment
				</button>
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
					<i class="bi-send-fill"></i>
					Post something
				</div>
			</a>
			@endrole
		</div>
	</div>
	@endif
</div>

<script src="/js/gallery.min.js"></script>

