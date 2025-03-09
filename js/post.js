(() => {
	"use strict";

	const stickerSheet = document.getElementById("sticker-sheet");

	/** @type {HTMLVideoElement} */
	const video = document.getElementById("video");
	/** @type {HTMLCanvasElement} */
	const canvas = document.getElementById("canvas");

	const actionButton = document.getElementById("action-button");
	const downloadButton = document.getElementById("download-button");
	const deleteButton = document.getElementById("delete-button");

	const dragStartHandler = (e) => {
		const rect = e.target.getBoundingClientRect();
		const offsetX = e.clientX - rect.left;
		const offsetY = e.clientY - rect.top;

		e.dataTransfer.dropEffect = "copy";

		e.dataTransfer.setData(
			"application/sticker",
			JSON.stringify({
				offsetX,
				offsetY,
				width: rect.width,
				height: rect.height,
			})
		);
	};

	const stickers = stickerSheet.getElementsByTagName("img");

	for (const sticker of stickers) {
		sticker.addEventListener("dragstart", dragStartHandler);
	}

	const editor = new CanvasEditor(canvas);

	navigator.mediaDevices
		.getUserMedia({
			video: {
				width: { ideal: 4096 },
				height: { ideal: 2160 },
			},
			audio: false,
		})
		.then(async (stream) => {
			video.srcObject = stream;
			await video.play();
			video.classList.remove("placeholder");
			actionButton.disabled = false;
		})
		.catch((err) => {
			console.error("Error accessing the webcam:", err);
		});

	actionButton.addEventListener("click", () => {
		if (editor.background) {
			console.debug("TODO: Open modal for title and description");
			return;
		}

		const minDimension = Math.min(video.videoWidth, video.videoHeight);

		const sourceX = (video.videoWidth - minDimension) / 2;
		const sourceY = (video.videoHeight - minDimension) / 2;

		editor.setBackgroundImage(
			video,
			sourceX,
			sourceY,
			minDimension,
			minDimension
		);

		if (video.srcObject !== null) {
			video.pause();
			for (const track of video.srcObject.getTracks()) {
				track.stop();
			}
			video.srcObject = null;
		}

		// video.style.display = "none";
		// canvas.style.display = "block";

		stickerSheet.classList.add("show");

		actionButton
			.getElementsByTagName("i")[0]
			.classList.replace("bi-camera-fill", "bi-send-fill");
		actionButton.title = "Post";
		actionButton.ariaLabel = actionButton.title;

		deleteButton.disabled = false;
		downloadButton.disabled = false;
	});

	deleteButton.addEventListener("click", () => {
		deleteButton.disabled = true;

		actionButton.disabled = true;
		actionButton
			.getElementsByTagName("i")[0]
			.classList.replace("bi-send-fill", "bi-camera-fill");
		actionButton.title = "Snap";
		actionButton.ariaLabel = actionButton.title;

		downloadButton.disabled = true;

		navigator.mediaDevices
			.getUserMedia({
				video: {
					width: { ideal: 4096 },
					height: { ideal: 2160 },
				},
				audio: false,
			})
			.then(async (stream) => {
				video.srcObject = stream;
				await video.play();
				actionButton.disabled = false;
				editor.removeBackgroundImage();
				// video.style.display = "block";
			})
			.catch((err) => {
				console.error("Error accessing the webcam:", err);
			});

	});

	downloadButton.addEventListener("click", () => {
		const svgBlob = editor.export();

		const link = document.createElement("a");

		link.href = URL.createObjectURL(svgBlob);

		link.download = "picture.svg";
		link.click();

		URL.revokeObjectURL(link.href);
	});
})();
