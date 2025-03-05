<!-- <div class="row">
	<div class="col-xs-2 col-sm-3 col-md-2">
		<div class="card mb-3">
			<img src="/img/icon.svg" class="card-img-top" alt="...">
		</div>
		<div class="card mb-3">
			<img src="/img/icon.svg" class="card-img-top" alt="...">
		</div>
	</div>


	<div class="col">
		<div class="card mx-auto" style="max-width: 70vmin;">
			<div class="ratio ratio-1x1">
				<video id="video" class="object-fit-cover placeholder card-img-top">Video stream not available.</video>
			</div>

			<div class="card-body d-flex justify-content-center">
				<button id="snapshot-button" class="btn btn-primary">Take picture</button>
			</div>
		</div>
	</div>
</div> -->
<div class="h-100 d-flex flex-column flex-md-row gap-4">
	<div id="sticker-sheet" class="overflow-auto p-4 fade">
		<div class="d-flex flex-md-column gap-3" style="min-width: min-content;">
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/test.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
			<div class="card" style="width: 15vh; height: 15vh;">
				<img src="/img/icon.svg" draggable="true" alt="Test">
			</div>
		</div>
	</div>
  <main class="flex-grow-1 py-4">
		<div class="card mx-auto" style="max-width: 70vmin;">
			<div class="ratio ratio-1x1">
				<video id="video" class="object-fit-cover placeholder card-img-top">
					Video stream not available.
				</video>
				<canvas id="canvas"></canvas>
			</div>

			<div class="card-body d-flex justify-content-center">
				<button id="snapshot-button" class="btn btn-primary">Take picture</button>
			</div>
		</div>
  </main>
</div>

<!-- <div class="d-flex">
	<img src="/img/icon.svg" class="w-25" alt="Test">
  <main class="p-2 flex-grow-1">Flex item</main>
</div> -->

<script>
	const stickerSheet = document.getElementById('sticker-sheet');
	const video = document.getElementById('video');
	const canvas = document.getElementById('canvas');
	const context = canvas.getContext('2d');
	const snapshotButton = document.getElementById('snapshot-button');

	context.imageSmoothingEnabled = false;

	const dragStartHandler = (e) => {
		const rect = e.target.getBoundingClientRect();
		const offsetX = e.clientX - rect.left;
		const offsetY = e.clientY - rect.top;

		e.dataTransfer.dropEffect = "copy";

		// e.dataTransfer.setData('text/url-list', );
		e.dataTransfer.setData('application/sticker', JSON.stringify({
			offsetX,
			offsetY,
		}));

		console.debug("Drag start!");
	};

	const stickers = stickerSheet.getElementsByTagName('img');

	for (const sticker of stickers) {
		console.debug("adding event listener...");
		sticker.addEventListener('dragstart', dragStartHandler);
	}

	canvas.addEventListener('dragover', (e) => {
		e.preventDefault();
		canvas.classList.add('highlight');
	});

	canvas.addEventListener('dragleave', () => {
		canvas.classList.remove('highlight');
	});

	canvas.addEventListener("drop", (e) => {
		e.preventDefault();
		canvas.classList.remove('highlight');

		console.debug(e);

		// const dropX = e.offsetX;
		// const dropY = e.offsetY;

		const files = e.dataTransfer.files;

		var imageSource = null;
		var dropX = e.offsetX;
		var dropY = e.offsetY;

		var dragOffsetX = 0;
		var dragOffsetY = 0;

		if (files.length === 1 && files[0].type.startsWith('image/')) {
			const file = files[0];
			console.debug('Loading image...');
			const reader = new FileReader();
			reader.onload = (e) => {
				console.debug('Loaded image:', e);

				imageSource = e.target.result;
			};
			reader.readAsDataURL(file);
		}
		else {
			console.warn(e.dataTransfer.getData('application/sticker'));
			const {offsetX, offsetY} = JSON.parse(e.dataTransfer.getData('application/sticker'));
0
			dragOffsetX = offsetX;
			dragOffsetY = offsetY;

			imageSource = e.dataTransfer.getData('text/uri-list');
		}

		console.debug(imageSource);

		if (imageSource === null) return;

		const img = new Image();

		img.onload = (e) => {
			const scale = Math.min(256 / img.width, 256 / img.height, 1);
			console.debug("scale:", scale);
			const width = img.width * scale;
			const height = img.height * scale;

			const x = dropX - dragOffsetX * scale - width/2;
			const y = dropY - dragOffsetY * scale - height/2;
			// const x = 0;
			// const y = 0;

			// console.debug(img, x, y, width, height);

			// context.drawImage(img, x, y, width, height);
			context.drawImage(img, x, y, width, height);
		}

		img.src = imageSource;
	});

	navigator.mediaDevices
		.getUserMedia({ video: true, audio: false })
		.then(async (stream) => {
			video.srcObject = stream;
			await video.play();
			video.classList.remove('placeholder');
		})
		.catch((err) => {
			console.error('Error accessing the webcam:', err);
		});

	snapshotButton.addEventListener('click', () => {
		const minDimension = Math.min(video.videoWidth, video.videoHeight);

		canvas.width = minDimension;
		canvas.height = minDimension;

		const sourceX = (video.videoWidth - minDimension) / 2;
		const sourceY = (video.videoHeight - minDimension) / 2;

		context.drawImage(video, sourceX, sourceY, minDimension, minDimension, 0, 0, canvas.width, canvas.height);

		video.style.display = 'none';
		canvas.style.display = 'block';

		stickerSheet.classList.add('show');
	});
</script>
