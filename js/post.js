import { CanvasEditor } from "./canvas-editor.js";
import { getOrCreateModal } from "./modal.js";

const init = () => {
	/** @type {HTMLVideoElement} */
	const video = document.getElementById("video");

	/** @type {HTMLCanvasElement} */
	const canvas = document.getElementById("canvas");

	const stickerSheet = document.getElementById("sticker-sheet");
	const stickers = stickerSheet.getElementsByTagName("img");

	const postEditForm = document.getElementById("postEditForm");
	const deleteButton = document.getElementById("delete-button");
	const downloadButton = document.getElementById("download-button");

	const actionButton = document.getElementById("action-button");
	const postEditModal = getOrCreateModal(
		document.getElementById("postEditModal")
	);

	const editor = new CanvasEditor(canvas);

	/**
	 * @param {MediaStream} stream
	 */
	const handleMediaStream = async (stream) => {
		video.srcObject = stream;
		await video.play();
		video.classList.remove("placeholder");
		actionButton.disabled = false;
	};

	const handleMediaStreamError = (err) => {
		// TODO: Show error in toast
		console.error("Error accessing the webcam:", err);
	};

	/**
	 * @param {DragEvent} e
	 */
	const handleStickerDragStart = (e) => {
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

	/**
	 * @param {SubmitEvent} e
	 */
	const handleSubmit = async (e) => {
		e.preventDefault();

		const form = e.target;
		const formData = new FormData(form);

		const response = await fetch(form.action, {
			method: form.method,
			body: formData,
			redirect: "manual",
		});

		if (response.ok) {
			window.location = response.headers.get("Location");
		}
	};

	/**
	 * @param {FormDataEvent} e
	 */
	const handleFormData = (e) => {
		const formData = e.formData;

		formData.set("picture", editor.export());
	};

	const handleSnapshot = () => {
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

		stickerSheet.classList.add("show");

		actionButton
			.getElementsByTagName("i")[0]
			.classList.replace("bi-camera-fill", "bi-send-fill");
		actionButton.title = "Post";
		actionButton.ariaLabel = actionButton.title;

		deleteButton.disabled = false;
		downloadButton.disabled = false;
	};

	const handleDelete = () => {
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
	};

	const handleDownload = () => {
		const svgBlob = editor.export();

		const link = document.createElement("a");

		link.href = URL.createObjectURL(svgBlob);

		link.download = "picture.svg";
		link.click();

		URL.revokeObjectURL(link.href);
	};

	navigator.mediaDevices
		.getUserMedia({
			video: {
				width: { ideal: 4096 },
				height: { ideal: 2160 },
			},
			audio: false,
		})
		.then(handleMediaStream)
		.catch(handleMediaStreamError);

	for (const sticker of stickers) {
		sticker.addEventListener("dragstart", handleStickerDragStart);
	}

	actionButton.addEventListener("click", () => {
		if (editor.background) {
			postEditModal.show();
		} else {
			handleSnapshot();
		}
	});
	postEditModal.element.addEventListener("submit", handleSubmit);
	postEditForm.addEventListener("formdata", handleFormData);
	deleteButton.addEventListener("click", handleDelete);
	downloadButton.addEventListener("click", handleDownload);
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", init);
} else {
	init();
}
