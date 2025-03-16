<div id="gallery" class="gallery-container">
	@foreach($posts as $post)
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
			<object type="image/svg+xml" data="{{ $post->pictureUrl }}" alt="{{ $post->description }}">
				<div class="placeholder w-100 h-100"></div>
			</object>
		</div>
		<div class="card-body">
			<h5 class="card-title">{{ $post->title }}</h5>
			<p class="card-text">{{ $post->description }}</p>
			<div class="d-flex gap-3">
				<div>
					<input type="checkbox" class="btn-check" id="btn-check-{{ $post->id }}" autocomplete="off"
						data-app-post-id="{{ $post->id }}" data-app-post-action="like" data-app-post-liked="{{ $post->isLiked ? 'true' : 'false' }}"
						@role("GUEST")
						disabled="true"
						@endrole>
					<label class="btn btn-danger d-flex gap-2 justify-content-center" for="btn-check-{{ $post->id }}"
						style="min-width: 7em;">
						<i class="bi {{ $post->isLiked ? 'bi-heart-fill' : 'bi-heart'}}"></i>
						<span>{{ $post->likeCount }} {{ $post->likeCount === 1 ? 'Like' : 'Likes' }}<span>
					</label>
				</div>
				@role("USER", "ADMIN")
				<form class="post-comment-form needs-validation flex-grow-1 d-flex gap-2" method="post"
					action="/post/{{ $post->id }}/comments" data-app-post-id="{{ $post->id }}">
					@csrf
					<div class="form-group flex-grow-1">
						<input type="text" class="form-control" id="body" name="body" aria-label="Comment body" minlength="1"
							maxlength="512" required>
						<div class="invalid-feedback">
							Comment is required.
						</div>
					</div>
					<button class="btn btn-primary d-flex gap-2" aria-label="Post comment">
						<i class="bi-send"></i><span>Post</span>
					</button>
				</form>
				@endrole
			</div>

		</div>
		<div class="accordion accordion-flush" id="post-accordion-{{ $post->id }}">
			<div class="accordion-item">
				<h2 class="accordion-header">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						data-bs-target="#comments-collapse-{{ $post->id }}"
						data-app-post-id="{{ $post->id }}" data-app-post-action="loadComments" data-app-post-comments-loaded="false"
						aria-expanded="false" aria-controls="comments-collapse-{{ $post->id }}">
						<span id="comment-count-{{ $post->id }}">{{ $post->commentCount }} {{ $post->commentCount === 1 ? 'Comment' : 'Comments' }}<span>
					</button>
				</h2>
				<div id="comments-collapse-{{ $post->id }}" class="accordion-collapse collapse" data-bs-parent="#post-accordion-{{ $post->id }}">
					<div class="accordion-body">
						<ul id="comment-list-{{ $post->id }}" class="list-group list-group-flush">
							@foreach($i = 1 to {{ $post->commentCount }})
							<li class="list-group-item">
								<div class="ms-2 me-auto">
									<h6 class="comment-title placeholder-glow">
										<span class="comment-author fw-bold placeholder col-3"></span>
										<span class="comment-body placeholder col-4"></span>
									</h6>
									<p class="comment-body placeholder-glow">
										<span class="placeholder col-2"></span>
										<span class="placeholder col-4"></span>
										<span class="placeholder col-2"></span>
									</p>
								</div>
							</li>
							@endforeach
						</ul>
					</div>
				</div>
			</div>
		</div>
	</article>
	@endforeach
	@if(!empty($posts))
	<template id="comment-list-item">
		<li class="list-group-item">
			<div class="ms-2 me-auto">
				<h6 class="comment-title">
					<span class="comment-author fw-bold"></span>
					-
					<span class="comment-time"></span>
				</h6>
				<p class="comment-body"></p>
			</div>
		</li>
	</template>
	@else
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