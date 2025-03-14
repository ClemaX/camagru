(() => {
	"use strict";

	/**
	 * @param {MouseEvent} e
	 */
	const handleLike = async (e) => {
		/** @type {HTMLInputElement} */
		const trigger = e.target;

		trigger.disabled = true;

		const label = document.querySelector(`label[for="${trigger.id}"]`);
		const postId = trigger.getAttribute("data-app-post-id");
		const wasLiked = trigger.getAttribute("data-app-post-liked") === "true";

		const response = await fetch(`/post/${postId}/like`, {
			method: wasLiked ? "DELETE" : "PUT",
		});
		const likeCount = await response.json();
		const isLiked = !wasLiked;

		trigger.setAttribute("data-app-post-liked", isLiked);
		trigger.checked = isLiked;

		const likeIcon = label.getElementsByTagName("i")[0];
		const likeCountSpan = label.getElementsByTagName("span")[0];

		if (isLiked) {
			likeIcon.classList.replace("bi-heart", "bi-heart-fill");
		} else {
			likeIcon.classList.replace("bi-heart-fill", "bi-heart");
		}

		likeCountSpan.innerText = `${likeCount} Like${likeCount !== 1 ? "s" : ""}`;

		trigger.disabled = false;
	};

	/**
	 * @param {FormDataEvent} e
	 */
	const handleCommentFormData = (e) => {
		// const formData = e.formData;
		// formData.set("subjectId", null);
	};

	/**
	 * @param {FormDataEvent} e
	 */
	const handleCommentSubmit = async (e) => {
		e.preventDefault();

		/** @type {HTMLFormElement} */
		const form = e.target;
		const formData = new FormData(form);

		const response = await fetch(form.action, {
			method: form.method,
			body: formData,
		});

		if (response.ok) {
			console.debug(await response.json());
		}

		form.reset();
	};

	document.addEventListener("DOMContentLoaded", () => {
		const gallery = document.getElementById("gallery");
		const likeTriggers = gallery.querySelectorAll(
			'input[type="checkbox"][data-app-post-action="like"]'
		);

		const postCommentForms =
			gallery.getElementsByClassName("post-comment-form");

		const posts = gallery.getElementsByTagName("article");

		for (const form of postCommentForms) {
			form.addEventListener("formdata", handleCommentFormData);
		}

		for (const post of posts) {
			post.addEventListener("submit", handleCommentSubmit);
		}

		likeTriggers.forEach((trigger) => {
			trigger.addEventListener("click", handleLike);
		});
	});
})();
