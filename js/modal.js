import { Backdrop } from "./backdrop.js";

export class Modal {
	constructor(element, options = {}) {
		this.element = element;
		this.options = {
			backdrop: true,
			keyboard: true,
			focus: true,
			...options,
		};
		this.isShown = false;
		/** @type {Backdrop} */
		this.backdrop = new Backdrop();
		this.setupEventListeners();
	}

	show() {
		if (this.isShown) return;

		this.element.style.display = "block";
		setTimeout(() => {
			this.element.classList.add("show");
			this.isShown = true;
		}, 10); // Small delay to ensure the display change has taken effect
		document.body.classList.add("modal-open");

		if (this.options.backdrop) {
			this.backdrop.show();
		}

		if (this.options.focus) {
			this.element.focus();
		}
	}

	hide() {
		if (!this.isShown) return;

		this.isShown = false;

		const onTransitionEnd = (event) => {
			event.target.style.display = "none";
			document.body.classList.remove("modal-open");
			event.target.removeEventListener("transitionend", onTransitionEnd);
		};

		this.element.addEventListener("transitionend", onTransitionEnd);

		this.element.classList.remove("show");

		this.backdrop.hide();
	}

	toggle() {
		return !this.isShown ? this.show() : this.hide();
	}

	setupEventListeners() {
		if (this.options.keyboard) {
			const onKeyDown = (event) => {
				if (event.key === "Escape" && this.isShown) {
					this.hide();
				}
			};

			document.addEventListener("keydown", onKeyDown);
		}
		if (this.options.backdrop) {
			const onMouseDown = (event) => {
				if (event.target === this.element) {
					this.hide();
				}
			};
			this.element.addEventListener("mousedown", onMouseDown);
		}
	}
}

const modals = {};

/**
 * Get or create a modal instance.
 *
 * @param {HTMLElement} target
 * @returns {Modal}
 */
export const getOrCreateModal = (target) => {
	if (!(target.id in modals)) {
		modals[target.id] = new Modal(target);
	}

	return modals[target.id];
};

/**
 * @param {Element} trigger
 * @returns {Modal}
 */
const getTargetOrClosest = (trigger) => {
	const selector = trigger.getAttribute("data-bs-target");
	const target = selector
		? document.querySelector(selector)
		: trigger.closest(".modal");

	if (!target) return null;

	return getOrCreateModal(target);
};

/**
 * @param {MouseEvent} e
 */
const handleToggle = (e) => {
	/** @type {HTMLButtonElement} */
	const trigger = e.currentTarget;

	const modal = getTargetOrClosest(trigger);
	if (!modal) return;

	modal.toggle();
};

/**
 * @param {MouseEvent} e
 */
const handleDismiss = (e) => {
	/** @type {HTMLButtonElement} */
	const trigger = e.currentTarget;

	const modal = getTargetOrClosest(trigger);
	if (!modal) return;

	modal.hide();
};

const init = () => {
	const modalToggleTriggers = document.querySelectorAll(
		'[data-bs-toggle="modal"]'
	);

	for (const trigger of modalToggleTriggers) {
		trigger.addEventListener("click", handleToggle);
	}

	const modalDimsissTriggers = document.querySelectorAll(
		'[data-bs-dismiss="modal"]'
	);

	for (const trigger of modalDimsissTriggers) {
		trigger.addEventListener("click", handleDismiss);
	}
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", init);
} else {
	init();
}
