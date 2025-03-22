const setTheme = (prefersDarkScheme) =>  {
	const prefferedTheme = prefersDarkScheme.matches ? 'dark' : 'light';
	document.documentElement.setAttribute('data-bs-theme', prefferedTheme);
}

const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

setTheme(prefersDarkScheme);

prefersDarkScheme.addEventListener('change', setTheme);
