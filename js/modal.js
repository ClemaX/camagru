class Modal {
	constructor(element, options = {}) {
		this.element = element;
		this.options = {
			backdrop: true,
			keyboard: true,
			focus: true,
			...options,
		};
		this.isShown = false;
		this.backdrop = null;
		this.setupEventListeners();
	}

	show() {
		if (this.isShown) return;

		this.isShown = true;
		this.element.style.display = "block";
		setTimeout(() => {
			this.element.classList.add("show");
		}, 10); // Small delay to ensure the display change has taken effect
		document.body.classList.add("modal-open");

		if (this.options.backdrop) {
			this.showBackdrop();
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

		if (this.backdrop) {
			this.hideBackdrop();
		}
	}

	showBackdrop() {
		this.backdrop = document.createElement("div");
		this.backdrop.className = "modal-backdrop fade";
		document.body.appendChild(this.backdrop);

		// Force reflow to ensure the class change takes effect
		this.backdrop.offsetHeight;

		this.backdrop.classList.add("show");

		if (this.options.backdrop === true) {
			const onMouseDown = () => {
				this.hide();
				this.backdrop.addEventListener("mousedown", onMouseDown);
			};

			this.backdrop.addEventListener("mousedown", onMouseDown);
		}
	}

	hideBackdrop() {
		if (!this.backdrop) return;

		this.backdrop.addEventListener("transitionend", (event) => {
			if (!this.backdrop) return;
			this.backdrop.remove();
			this.backdrop = null;
		});

		this.backdrop.classList.remove("show");
	}

	setupEventListeners() {
		if (this.options.keyboard) {
			const onKeyDown = (event) => {
				if (event.key === "Escape" && this.isShown) {
					this.hide();
					document.removeEventListener("keydown", onKeyDown);
				}
			};

			document.addEventListener("keydown", onKeyDown);
		}
		if (this.options.backdrop) {
			const onMouseDown = (event) => {
				if (event.target === this.element) {
					if (this.isShown) {
						this.hide();
						this.element.removeEventListener("mousedown", onMouseDown);
					}
				}
			};
			this.element.addEventListener("mousedown", onMouseDown);
		}
	}
}
