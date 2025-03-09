<div class="post-container flex-column flex-md-row gap-4">
	<div>
		<div id="sticker-sheet" class="h-100 overflow-auto p-4 fade">
			<ul
				class="sticker-list d-flex flex-md-column gap-3"
				style="min-width: min-content"
			>
				<li class="card">
					<img src="/img/icon.svg" draggable="true" alt="Test" />
				</li>
				<li class="card">
					<img src="/img/test.svg" draggable="true" alt="Test" />
				</li>
			</ul>
		</div>
	</div>
	<div class="my-auto flex-fill">
		<div class="card mx-auto" style="max-width: 66vmin">
			<div class="ratio ratio-1x1">
				<video id="video" class="object-fit-cover placeholder card-img-top">
					Video stream not available.
				</video>
				<canvas
					id="canvas"
					width="1080"
					height="1080"
				></canvas>
			</div>
			<div class="card-body d-flex flex-wrap justify-content-between gap-3">
				<button
					id="delete-button"
					class="btn"
					title="Delete"
					aria-label="Delete"
					disabled="true"
				>
					<i class="bi bi-trash-fill"></i>
				</button>
				<button
					id="action-button"
					class="btn btn-primary"
					title="Snap"
					aria-label="Snap"
					disabled="true"
				>
					<i class="bi bi-camera-fill"></i>
				</button>
				<button
					id="download-button"
					class="btn"
					title="Download"
					aria-label="Download"
					disabled="true"
				>
					<i class="bi bi-download"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="flex-shrink-1 p-4">
		<div class="sticker-list-placeholder"></div>
	</div>
</div>

<script src="/js/canvas-editor.min.js" defer></script>
<script src="/js/post.min.js" defer></script>
