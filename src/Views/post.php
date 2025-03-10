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

<div class="modal fade" id="postEditModal" tabindex="-1" aria-hidden="true"
	aria-labelledby="postEditModalLabel">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="postEditModalLabel">Post</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<form id="postEditForm" class="needs-validation d-flex flex-column gap-3" novalidate method="post">
					@csrf
					<div class="form-group">
					<label for="title">Title</label>
					<input type="text"
						class="form-control"
						id="title" name="title" required
						minlength="3" maxlength="64" pattern="^\S(.*\S)?$">
					<div class="invalid-feedback">
						Title must be 3-64 characters long and contain at least one non-whitespace character.
					</div>
				</div>

				<div class="form-group">
					<label for="description">Description</label>
					<textarea class="form-control no-resize" id="description"
						name="description" rows="3"></textarea>
					<div class="invalid-feedback">
						Description must be at most 140 characters long.
					</div>
				</div>
					<button type="submit" class="btn btn-primary">Post</button>
				</form>
			</div>
		</div>
	</div>
</div>


<script src="/js/canvas-editor.min.js" defer></script>
<script src="/js/form.min.js" defer></script>
<script src="/js/modal.min.js" defer></script>
<script src="/js/post.min.js" defer></script>
