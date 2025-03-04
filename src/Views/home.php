<div id="gallery" class="gallery-container">
	@foreach ($posts as $post)
	<div class="card mb-4" aria-hidden="true">
		<div class="ratio ratio-1x1">
			<img src="{{ $post->imageUrl }}" class="card-img-top placeholder"
				alt="{{ $post->description }}">
		</div>
		<div class="card-body">
			<h5 class="card-title">{{ $post->title }}</h5>
			<p class="card-text">{{ $post->description }}</p>
			<div class="d-flex gap-3">
				<button class="btn btn-danger d-flex gap-2">
					<i class="bi-heart"></i>
					Like
				</button>
				<button class="btn btn-info d-flex gap-2">
					<i class="bi-chat"></i>
					Comment
				</button>
			</div>
		</div>
	</div>
	@endforeach
</div>

<script>
	const gallery = document.getElementById('gallery');

	gallery.querySelectorAll('.card-img-top').forEach(img => {
		img.addEventListener('load', (event) => {
			event.target.classList.remove('placeholder');
		});
	});
</script>
