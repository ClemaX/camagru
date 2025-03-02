<div id="gallery" class="container">
	<div class="row g-3">
		@foreach ($images as $image)
			<div class="col-4">
				<div class="card" aria-hidden="true">
					<div class="ratio ratio-1x1">
						<img src="{{ $image->url }}" class="card-img-top placeholder" alt="{{ $image->description }}">
					</div>
					<div class="card-body">
						<h5 class="card-title">{{ $image->title }}</h5>
						<p class="card-text">{{ $image->description }}</p>
						<!-- <a href="#" tabindex="-1" class="btn btn-primary disabled placeholder col-6"></a> -->
					</div>
				</div>
			</div>
		@endforeach
	</div>
</div>
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body p-0">
					<img src="" class="img-fluid" id="modalImage">
			</div>
		</div>
	</div>
</div>

<script src="/js/modal.min.js"></script>

<script>
	const gallery = document.getElementById('gallery');

	gallery.querySelectorAll('.card-img-top').forEach(img => {
		// console.debug(img);

		img.addEventListener('load', function (event) {
			// console.debug(event);
			event.target.classList.remove('placeholder');
		});
		img.addEventListener('click', function(event) {
			document.getElementById('modalImage').src = this.src;
			new Modal(document.getElementById('imageModal')).show();
		});
	});
</script>
