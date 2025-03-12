(() => {
	"use strict";

	/**
	 * @param {MouseEvent} e
	 */
	const handleLike = async (e) => {
		const postId = e.target.getAttribute("data-app-post-id");
		const postLiked = e.target.getAttribute("data-app-post-liked");

		const response = await fetch(`/post/${postId}/like`, { method: "PUT" });

		console.debug(postLiked, await response.text());
	}
	document.addEventListener("DOMContentLoaded", () => {
		const likeTriggers = document.querySelectorAll(
			'input[type="checkbox"][data-app-post-action="like"]'
		);

		likeTriggers.forEach((trigger) => {
			trigger.addEventListener("click", handleLike);
		});
	});
})();
