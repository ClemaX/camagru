(() => {
	"use strict";

	/**
	 * @param {MouseEvent} e
	 */
	const handleLike = async (e) => {
		/** @type {HTMLInputElement} */
		const trigger = e.target;

		trigger.disabled = true;

		const label = document.querySelector(`label[for="${trigger.id}"]`)
		const postId = trigger.getAttribute("data-app-post-id");
		const wasLiked = trigger.getAttribute("data-app-post-liked") === 'true';

		const response = await fetch(`/post/${postId}/like`, {
			method: wasLiked ? "DELETE" : "PUT",
		});
		const likeCount = await response.json();
		const isLiked = !wasLiked;

		trigger.setAttribute('data-app-post-liked', isLiked);
		trigger.checked = isLiked;

		const likeIcon =  label.getElementsByTagName('i')[0];
		const likeCountSpan = label.getElementsByTagName('span')[0];

		if (isLiked) {
			likeIcon.classList.replace('bi-heart', 'bi-heart-fill');
		}
		else {
			likeIcon.classList.replace('bi-heart-fill', 'bi-heart');
		}

		likeCountSpan.innerText = `${likeCount} Like${likeCount !== 1 ? 's' : ''}`;

		trigger.disabled = false;
	};
	document.addEventListener("DOMContentLoaded", () => {
		const likeTriggers = document.querySelectorAll(
			'input[type="checkbox"][data-app-post-action="like"]'
		);

		likeTriggers.forEach((trigger) => {
			trigger.addEventListener("click", handleLike);
		});
	});
})();
