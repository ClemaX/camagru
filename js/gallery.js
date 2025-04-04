import { getOrCreateModal } from "./modal.js";
import { getOrCreateToast } from "./toast.js";

/**
 * @typedef CommentDTO
 * @type {object}
 * @property {User} author
 * @property {string} body
 * @property {number} createdAt
 * @property {number} updatedAt
 */

/**
 * @param {Date} date
 */
const formatDate = (date) => {
	/** @type {Intl.DateTimeFormatOptions} */
	const options = {
		day: "2-digit",
		month: "2-digit",
		year: "2-digit",
		hour: "2-digit",
		minute: "2-digit",
	};
	return new Intl.DateTimeFormat("en-US", options).format(date);
};

/**
 * @param {CommentDTO} comment
 * @returns {Node}
 */
const createCommentListItem = (comment) => {
	/** @type {HTMLTemplateElement} */
	const template = document.getElementById("comment-list-item");
	/** @type {DocumentFragment} */
	const commentNode = template.content.cloneNode(true);

	const createdAt = new Date(comment.createdAt * 1000);

	commentNode.querySelector(".comment-author").textContent =
		comment.author.username;
	commentNode.querySelector(".comment-time").textContent =
		formatDate(createdAt);
	commentNode.querySelector(".comment-body").textContent = comment.body;

	return commentNode;
};

/**
 * @param {number} postId
 */
const loadComments = async (postId) => {
	const response = await fetch(`/post/${postId}/comments`);

	if (response.ok) {
		/** @type {Comment[]} */
		const comments = await response.json();

		const commentList = document.getElementById(`comment-list-${postId}`);
		const commentCountSpan = document.getElementById(`comment-count-${postId}`);
		const commentCount = comments.length;
		const commentListItems = comments.map((comment) =>
			createCommentListItem(comment)
		);

		commentList.replaceChildren(...commentListItems);
		commentCountSpan.textContent = `${commentCount} Comment${
			commentCount !== 1 ? "s" : ""
		}`;
	}
};

/**
 * @param {MouseEvent} e
 */
const handleLike = async (e) => {
	/** @type {HTMLInputElement} */
	const trigger = e.currentTarget;

	trigger.disabled = true;

	const label = document.querySelector(`label[for="${trigger.id}"]`);
	const postId = trigger.getAttribute("data-app-post-id");
	const wasLiked = trigger.getAttribute("data-app-post-liked") === "true";
	const csrfToken = document.head
		.querySelector('meta[name="csrf-token"]')
		.getAttribute("content");

	const response = await fetch(`/post/${postId}/like`, {
		method: wasLiked ? "DELETE" : "PUT",
		headers: {
			Accept: "application/json",
			"Content-Type": "application/json",
		},
		body: JSON.stringify({ _token: csrfToken }),
	});

	if (response.ok) {
		const { likeCount } = await response.json();
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

		likeCountSpan.textContent = `${likeCount} Like${
			likeCount !== 1 ? "s" : ""
		}`;
	} else {
		trigger.checked = wasLiked;
	}

	trigger.disabled = false;
};

/**
 * @param {MouseEvent} e
 */
const handleLoadComments = async (e) => {
	/** @type {HTMLInputElement} */
	const trigger = e.currentTarget;
	const wasLoaded =
		trigger.getAttribute("data-app-post-comments-loaded") === "true";

	if (wasLoaded) return;

	trigger.disabled = true;

	const postId = Number.parseInt(trigger.getAttribute("data-app-post-id"));

	await loadComments(postId);

	trigger.setAttribute("data-app-post-comments-loaded", "true");
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
		const postId = Number.parseInt(form.getAttribute("data-app-post-id"));
		loadComments(postId);
	}

	form.reset();
};

/**
 * @param {MouseEvent} e
 */
const handleDelete = async (e) => {
	const hashParams = new URLSearchParams(window.location.hash.slice(1));
	const postId = hashParams.get("postId");
	const csrfToken = document.head
		.querySelector('meta[name="csrf-token"]')
		.getAttribute("content");

	const response = await fetch(`/post/${postId}`, {
		method: "DELETE",
		headers: {
			Accept: "application/json",
			"Content-Type": "application/json",
		},
		body: JSON.stringify({ _token: csrfToken }),
	});

	if (response.ok) {
		const postArticle = document.getElementById(`post-${postId}`);
		postArticle.remove();

		window.location.hash = "#";

		const deleteModal = getOrCreateModal("postDeleteModal");
		deleteModal.hide();
	}
};

/**
 * @param {MouseEvent} e
 */
const handleShare = async (e) => {
	/** @type {HTMLElement} */
	const trigger = e.currentTarget;

	const postId = trigger.getAttribute("data-app-post-id");

	const postUrl = new URL(postId, window.location.origin);

	navigator.clipboard.writeText(postUrl);

	const postShareToast = getOrCreateToast(document.getElementById("postShareToast"));

	postShareToast.show();
};

const init = () => {
	const gallery = document.getElementById("gallery");

	const postCommentForms = gallery.getElementsByClassName("post-comment-form");

	for (const form of postCommentForms) {
		form.addEventListener("formdata", handleCommentFormData);
	}

	const posts = gallery.getElementsByTagName("article");

	for (const post of posts) {
		post.addEventListener("submit", handleCommentSubmit);
	}

	const likeTriggers = gallery.querySelectorAll(
		'input[type="checkbox"][data-app-post-action="like"]'
	);

	for (const trigger of likeTriggers) {
		trigger.addEventListener("click", handleLike);
	}

	const commentLoadTriggers = gallery.querySelectorAll(
		'button[data-app-post-action="loadComments"]'
	);

	for (const trigger of commentLoadTriggers) {
		trigger.addEventListener("click", handleLoadComments);
	}

	const deleteTriggers = document.querySelectorAll(
		'button[data-app-post-action="delete"]'
	);

	for (const trigger of deleteTriggers) {
		trigger.addEventListener("click", handleDelete);
	}

	const shareTriggers = document.querySelectorAll(
		'button[data-app-post-action="share"]'
	);

	for (const trigger of shareTriggers) {
		trigger.addEventListener("click", handleShare);
	}

	const hashParams = new URLSearchParams(window.location.hash.slice(1));
	const postAction = hashParams.get("postAction");
	// const postId = hashParams.get("postId");

	switch (postAction) {
		case "delete":
			getOrCreateModal(document.getElementById("postDeleteModal")).show();
			break;

		default:
			break;
	}
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", init);
} else {
	init();
}
