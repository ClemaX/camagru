<div class="post-container flex-column flex-md-row gap-4">
	<div>
		<div id="sticker-sheet" class="h-100 overflow-auto p-4 fade">
			<ul class="sticker-list d-flex flex-md-column gap-3" style="min-width: min-content;">
				<li class="card">
					<img src="/img/icon.svg" draggable="true" alt="Test">
				</li>
				<li class="card">
					<img src="/img/test.svg" draggable="true" alt="Test">
				</li>
			</ul>
		</div>
	</div>
	<div class="my-auto flex-fill">
		<div class="card mx-auto" style="max-width: 66vmin;">
			<div class="ratio ratio-1x1">
				<video id="video" class="object-fit-cover placeholder card-img-top">
					Video stream not available.
				</video>
				<canvas id="canvas" width="1080" height="1080" style="display: none;"></canvas>
			</div>
			<div class="card-body d-flex justify-content-center">
				<button id="snapshot-button" class="btn btn-primary" disabled="true">Take picture</button>
			</div>
		</div>
	</div>
	<div class="flex-shrink-1 p-4">
		<div class="sticker-list-placeholder"></div>
	</div>
</div>

<script src="/js/canvas-editor.min.js"></script>

<script>
	const stickerSheet = document.getElementById('sticker-sheet');
	const video = document.getElementById('video');
	const canvas = document.getElementById('canvas');
	const snapshotButton = document.getElementById('snapshot-button');

	const dragStartHandler = (e) => {
		const rect = e.target.getBoundingClientRect();
		const offsetX = e.clientX - rect.left;
		const offsetY = e.clientY - rect.top;

		e.dataTransfer.dropEffect = "copy";

		e.dataTransfer.setData('application/sticker', JSON.stringify({
			offsetX,
			offsetY,
			width: rect.width,
			height: rect.height,
		}));
	};

	const stickers = stickerSheet.getElementsByTagName('img');

	for (const sticker of stickers) {
		console.debug("adding event listener...");
		sticker.addEventListener('dragstart', dragStartHandler);
	}

	const editor = new CanvasEditor(canvas);

	navigator.mediaDevices
		.getUserMedia({ video: true, audio: false })
		.then(async (stream) => {
			video.srcObject = stream;
			await video.play();
			video.classList.remove('placeholder');
			snapshotButton.disabled = false;
		})
		.catch((err) => {
			console.error('Error accessing the webcam:', err);
		});

	snapshotButton.addEventListener('click', () => {
		const minDimension = Math.min(video.videoWidth, video.videoHeight);

		// canvas.width = minDimension;
		// canvas.height = minDimension;

		const sourceX = (video.videoWidth - minDimension) / 2;
		const sourceY = (video.videoHeight - minDimension) / 2;

		editor.setBackgroundImage(video, sourceX, sourceY,
			minDimension, minDimension);

		video.style.display = 'none';
		canvas.style.display = 'block';

		stickerSheet.classList.add('show');
	});
</script>
