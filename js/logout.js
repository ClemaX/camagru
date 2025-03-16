(() => {
	'use strict';

	const logout = async (logoutUrl) => {
		const response = await fetch(logoutUrl, { method: 'POST' });
		window.location = response.headers.get('Location');
	};

	const logoutButton = document.getElementById('logout-button');

	logoutButton.addEventListener('click', () => {
		logout(logoutButton.getAttribute('data-logout-url'));
		return false;
	});
})();