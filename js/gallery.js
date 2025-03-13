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
		const postLiked = trigger.getAttribute("data-app-post-liked") === 'true';

		const response = await fetch(`/post/${postId}/like`, {
			method: postLiked ? "DELETE" : "PUT",
		});
		const likeCount = await response.json();

		trigger.setAttribute('data-app-post-liked', !postLiked);
		trigger.checked = !postLiked;

		const likeCountSpan = label.getElementsByTagName('span')[0];

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
