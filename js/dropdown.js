(() => {
	"use strict";

	const handleDismiss = async (e) => {
		e.stopPropagation();
		e.currentTarget.removeEventListener('click', handleDismiss, true);

		const visibleDropDownMenus =
			document.getElementsByClassName("dropdown-menu show");

		for (const dropDownMenu of visibleDropDownMenus) {
			dropDownMenu.classList.remove("show");
			dropDownMenu.style.removeProperty("position");
			dropDownMenu.style.removeProperty("inset");
			dropDownMenu.style.removeProperty("margin");
			dropDownMenu.style.removeProperty("transform");
		}
	};

	/**
	 *
	 * @param {MouseEvent} e
	 */
	const handleTriggerClick = async (e) => {
		/** @type {HTMLElement} */
		const trigger = e.currentTarget;
		const triggerBounds = trigger.getBoundingClientRect();
		const dropDownContainer = trigger.parentElement;
		const dropDownMenus =
			dropDownContainer.getElementsByClassName("dropdown-menu");
		var anyIsShown = false;

		for (const dropDownMenu of dropDownMenus) {
			const isShown = dropDownMenu.classList.contains("show");

			if (isShown) {
				dropDownMenu.classList.remove("show");
				dropDownMenu.style.removeProperty("position");
				dropDownMenu.style.removeProperty("inset");
				dropDownMenu.style.removeProperty("margin");
				dropDownMenu.style.removeProperty("transform");
			} else {
				anyIsShown = true;

				const translate = {
					x: 0,
					y: triggerBounds.height,
				};

				dropDownMenu.style.position = "absolute";
				dropDownMenu.style.inset = "0px auto auto 0px";
				dropDownMenu.style.margin = "margin: 0px";

				dropDownMenu.classList.add("show");

				if (dropDownMenu.classList.contains("dropdown-menu-end")) {
					/** @type {DOMRect} */
					const dropDownBounds = dropDownMenu.getBoundingClientRect();

					translate.x = triggerBounds.width - dropDownBounds.width;
				}

				dropDownMenu.style.transform = `translate(${translate.x}px, ${translate.y}px)`;
			}
		}

		if (anyIsShown) {
			document.addEventListener('click', handleDismiss, true);
		}
		else {
			document.removeEventListener('click', handleDismiss, true);
		}
	};

	document.addEventListener("DOMContentLoaded", () => {
		const dropdownTriggers = document.querySelectorAll(
			'[data-bs-toggle="dropdown"]'
		);

		dropdownTriggers.forEach((trigger) => {
			trigger.addEventListener("click", handleTriggerClick);
		});
	});
})();
