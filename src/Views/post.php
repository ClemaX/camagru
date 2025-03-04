<div class="row">
	<div class="col-md-3 sidebar">
		<div class="card mb-3">
			<img src="/img/icon.svg" class="card-img-top" alt="...">
		</div>
		<div class="card mb-3">
			<img src="/img/icon.svg" class="card-img-top" alt="...">
		</div>
	</div>

	<div class="col-md-9">
		<div class="card" style="max-width: 70vmin;">
			<div class="ratio ratio-1x1">
				<video id="video" class="object-fit-cover placeholder card-img-top">Video stream not available.</video>
			</div>

			<div class="card-body d-flex justify-content-center">
				<button id="snapshot-button" class="btn btn-primary">Take picture</button>
			</div>
		</div>
	</div>
</div>
<script>
	const video = document.getElementById('video');
	// const canvas = document.getElementById('canvas');
	// const context = canvas.getContext('2d');
	const snapshotBtn = document.getElementById('snapshot-button');

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

	// snapshotBtn.addEventListener('click', () => {
	// 	canvas.width = video.videoWidth;
	// 	canvas.height = video.videoHeight;
	// 	context.drawImage(video, 0, 0, canvas.width, canvas.height);
	// 	video.style.display = 'none';
	// 	canvas.style.display = 'block';
	// });
</script>
