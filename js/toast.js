class Toast {
	/**
	 * @param {HTMLElement} element
	 */
	constructor(element, options = {}) {
		this.options = {
			autohide: true,
			delay: 5000,
			...options,
		};

		this.element = element;
		this.isShown = !element.classList.contains("hide");
	}

	show() {
		if (this.isShown) return;

		this.isShown = true;
		this.element.classList.add("show");

		if (this.options.autohide) {
			setTimeout(() => this.hide(), this.options.delay)
		}
	}

	hide() {
		if (!this.isShown) return;

		this.element.classList.remove("show");
		this.isShown = false;
	}
}

const toasts = {};

/**
 * Get or create a toast instance.
 *
 * @param {HTMLElement} target
 * @returns {Toast}
 */
export const getOrCreateToast = (target) => {
	if (!(target.id in toasts)) {
		toasts[target.id] = new Toast(target);
	}

	return toasts[target.id];
};

/**
 * @param {Element} trigger
 * @returns {Modal}
 */
const getTargetOrClosest = (trigger) => {
	const selector = trigger.getAttribute("data-bs-target");
	const target = selector
		? document.querySelector(selector)
		: trigger.closest(".toast");

	if (!target) return null;

	return getOrCreateToast(target);
};

/**
 * @param {MouseEvent} e
 */
const handleDismiss = (e) => {
	/** @type {HTMLButtonElement} */
	const trigger = e.currentTarget;

	const toast = getTargetOrClosest(trigger);

	if (!toast) return;

	toast.hide();
};

const init = () => {
	const toastDimsissTriggers = document.querySelectorAll(
		'[data-bs-dismiss="toast"]'
	);

	for (const trigger of toastDimsissTriggers) {
		trigger.addEventListener("click", handleDismiss);
	}
};

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", init);
} else {
	init();
}
