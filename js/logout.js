(() => {
	"use strict";

	/**
	 * @param {MouseEvent} e
	 */
	const handleLogout = (e) => {
		e.preventDefault();

		/** @type {HTMLFormElement} */
		const logoutForm = document.getElementById('logout-form');

		logoutForm.submit();
	};

	const triggers = document.querySelectorAll('[data-app-action="logout"]');

	for (const trigger of triggers) {
		trigger.addEventListener("click", handleLogout);
	}
})();
