<div id="gallery" class="gallery-container">
	@foreach ($posts as $post)
	<article class="card mb-4" aria-hidden="true">
		<div class="card-header">
			<div class="d-flex flex-column">
				<h6 class="card-title">{{ $post->author->username }}</h2>
				<h6 class="card-title">{{ '$post->createdAt' }}</h2>
			</div>
		</div>
		<div class="ratio ratio-1x1">
			<img src="https://picsum.photos/id/{{ $post->id }}/1024" class="placeholder"
				alt="{{ $post->description }}">
		</div>
		<div class="card-body">
			<h5 class="card-title">{{ $post->title }}</h5>
			<p class="card-text">{{ $post->description }}</p>
			<div class="d-flex gap-3">
				<button class="btn btn-danger d-flex gap-2">
					<i class="bi-heart"></i>
					1 Like
				</button>
				<button class="btn btn-info d-flex gap-2">
					<i class="bi-chat"></i>
					Comment
				</button>
			</div>
		</div>
	</article>
	@endforeach
</div>

<script>
	const gallery = document.getElementById('gallery');

	gallery.querySelectorAll('img.placeholder').forEach(img => {
		img.addEventListener('load', (event) => {
			event.target.classList.remove('placeholder');
		});
	});
</script>
