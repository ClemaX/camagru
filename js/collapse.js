class Collapse {
	constructor(element) {
		this.element = element;
		this.isTransitioning = false;
	}

	getDimension() {
		return this.element.classList.contains('collapse-horizontal')
			? 'width' : 'height';
	}

	queueAfterTransition(callback) {
		const transitionDurationS = parseFloat(
			getComputedStyle(this.element)['transitionDuration']);

		const delayMs = transitionDurationS * 1000 + 5;

		setTimeout(callback, delayMs);
	}

	show() {
		if (this.isShown() || this.isTransitioning) return;

		const dimension = this.getDimension();
		const capitalizedDimension = dimension[0].toUpperCase()
			+ dimension.slice(1);

		this.element.classList.remove("collapse");
		this.element.classList.add("collapsing");
		this.element.style[dimension] = 0;

		this.isTransitioning = true;

		this.queueAfterTransition(() => {
			this.isTransitioning = false;

			this.element.classList.remove('collapsing');
			this.element.classList.add('collapse', 'show');

			this.element.style[dimension] = '';
		});

		const scrollSize = `scroll${capitalizedDimension}`;

		this.element.style[dimension] = `${this.element[scrollSize]}px`;
	}

	hide() {
		if (!this.isShown() || this.isTransitioning) return;

		const dimension = this.getDimension();

		this.element.style[dimension] =
			`${this.element.getBoundingClientRect()[dimension]}px`;

		// Reflow element
		this.element.offsetHeight;

		this.element.classList.add('collapsing');
		this.element.classList.remove('collapse', 'show');

		this.isTransitioning = true;

		this.element.style[dimension] = '';

		this.queueAfterTransition(() => {
			this.isTransitioning = false;

			this.element.classList.remove('collapsing');
			this.element.classList.add('collapse');
		});
	}

	toggle() {
		!this.isShown() ? this.show() : this.hide();
	}

	isShown() {
		return this.element.classList.contains('show');
	}
}

(function() {
	'use strict';
	document.addEventListener('DOMContentLoaded', function() {
		const collapseTriggers = document.querySelectorAll(
			'[data-bs-toggle="collapse"]');

		collapseTriggers.forEach((trigger, index) => {
			const targetcollapseId = trigger.getAttribute('data-bs-target');

			const collapse = new Collapse(document.querySelector(targetcollapseId));

			trigger.addEventListener('click', () => collapse.toggle());
		});
	});
})();
