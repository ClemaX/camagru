class Collapse {
	constructor(element) {
		this.element = element;
		this.isTransitioning = false;
	}

	getDimension() {
		return this.element.classList.contains("collapse-horizontal")
			? "width"
			: "height";
	}

	queueAfterTransition(callback) {
		const transitionDurationS = parseFloat(
			getComputedStyle(this.element)["transitionDuration"]
		);

		const delayMs = transitionDurationS * 1000 + 5;

		setTimeout(callback, delayMs);
	}

	/**
	 * @returns {Promise<boolean>}
	 */
	show() {
		if (this.isShown() || this.isTransitioning) {
			return true;
		}

		const dimension = this.getDimension();
		const capitalizedDimension =
			dimension[0].toUpperCase() + dimension.slice(1);

		this.element.classList.remove("collapse");
		this.element.classList.add("collapsing");
		this.element.style[dimension] = 0;

		this.isTransitioning = true;

		this.queueAfterTransition(() => {
			this.isTransitioning = false;

			this.element.classList.remove("collapsing");
			this.element.classList.add("collapse", "show");

			this.element.style[dimension] = "";
		});

		const scrollSize = `scroll${capitalizedDimension}`;

		this.element.style[dimension] = `${this.element[scrollSize]}px`;

		return true;
	}

	/**
	 * @returns {Promise<boolean>}
	 */
	hide() {
		if (!this.isShown() || this.isTransitioning) {
			return false;
		}

		const dimension = this.getDimension();

		this.element.style[dimension] = `${
			this.element.getBoundingClientRect()[dimension]
		}px`;

		// Reflow element
		this.element.offsetHeight;

		this.element.classList.add("collapsing");
		this.element.classList.remove("collapse", "show");

		this.isTransitioning = true;

		this.element.style[dimension] = "";

		this.queueAfterTransition(() => {
			this.isTransitioning = false;

			this.element.classList.remove("collapsing");
			this.element.classList.add("collapse");
		});

		return false;
	}

	/**
	 * @returns {Promise<boolean>}
	 */
	toggle() {
		return !this.isShown() ? this.show() : this.hide();
	}

	isShown() {
		return this.element.classList.contains("show");
	}
}

const _collapses = {};

/**
 * @param {HTMLElement} target
 * @returns {Collapse}
 */
const getOrCreateCollapse = (target) => {
	if (!(target.id in _collapses)) {
		_collapses[target.id] = new Collapse(target);
	}

	return _collapses[target.id];
};

(() => {
	"use strict";
	/**
	 *
	 * @param {MouseEvent} e
	 */
	const handleTriggerClick = async (e) => {
		/** @type {HTMLElement} */
		const trigger = e.currentTarget;
		const collapseId = trigger.getAttribute("data-bs-target");
		const collapse = getOrCreateCollapse(document.querySelector(collapseId));

		const isShown = await collapse.toggle();

		if (isShown) {
			trigger.classList.remove("collapsed");
		} else {
			trigger.classList.add("collapsed");
		}
	};

	document.addEventListener("DOMContentLoaded", () => {
		const collapseTriggers = document.querySelectorAll(
			'[data-bs-toggle="collapse"]'
		);

		collapseTriggers.forEach((trigger) => {
			trigger.addEventListener("click", handleTriggerClick);
		});
	});
})();
