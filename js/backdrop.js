export class Backdrop {
	constructor() {
		this.element = null;
	}

	hide() {
		const backdrop = this.element;

		if (backdrop === null) {
			return;
		}

		const dispose = () => {
			if (!this.element) {
				return;
			}
			this.element.remove();
			this.element = null;
		};

		backdrop.addEventListener("transitionend", dispose);
		backdrop.classList.remove("show");
	}

	show() {
		if (this.element !== null) return;

		const backdrop = document.createElement("div");
		backdrop.className = "modal-backdrop fade";

		document.body.appendChild(backdrop);

		// Force reflow to ensure the class change takes effect
		backdrop.offsetHeight;

		backdrop.classList.add("show");

		this.element = backdrop;
	}
}
