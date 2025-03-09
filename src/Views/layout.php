<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="darkreader-lock" />
	<title>Camagru</title>
	<link href="/css/main.min.css" rel="stylesheet">
	<link rel="icon" href="/img/icon.svg" type="image/svg+xml">
	<script src="/js/main.min.js" async></script>
	<script src="/js/collapse.min.js" async></script>
</head>
<body class="overflow-hidden">
	<nav class="navbar navbar-expand-sm fixed-top bg-body-tertiary">
		<div class="container-fluid">
			<a class="navbar-brand" href="{{ url('/') }}">
				<img src="/img/icon.svg" alt="Logo" width="30" height="30"
					class="d-inline-block align-text-top">
				Camagru
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse"
				data-bs-target="#navbarNav" aria-controls="navbarNav"
				aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse justify-content-between"
				id="navbarNav">
				<ul class="navbar-nav">
					@role("USER", "ADMIN")
					<li class="nav-item">
						<a class="nav-link" href="{{ url('/post') }}">Post</a>
					</li>
					@endrole
				</ul>
				<ul class="navbar-nav">
					@role("GUEST")
					<li class="nav-item">
						<a class="nav-link" href="{{ url('/auth/signup') }}">Sign Up</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ url('/auth/login') }}">Log In</a>
					</li>
					@else
					<li class="nav-item">
						<a class="nav-link" href="{{ url('/user/self') }}">Profile</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{ url('/user/self/settings') }}">
							Settings
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="logout-button"
							data-logout-url="{{ url('/auth/logout') }}"
							href="{{ url('/') }}">
							Log Out
						</a>
					</li>
					@endrole
				</ul>
			</div>
		</div>
	</nav>
	<main>{{ $content }}</main>

	<script>
		(() => {
			'use strict';

			const logout = async (logoutUrl) => {
				const response = await fetch(logoutUrl, { method: 'POST' });
				window.location = response.headers['Location'];
			};

			const logoutButton = document.getElementById('logout-button');

			logoutButton.addEventListener('click', () => {
				logout(logoutButton.getAttribute('data-logout-url'));
				return false;
			});
		})();
	</script>

	@env("development")
	<script id="__bs_script__">//<![CDATA[
		(() => {
			try {
				var script = document.createElement('script');
				if ('async') {
					script.async = true;
				}
				script.src = '/browser-sync/browser-sync-client.js?v=2.29.3';
				if (document.body) {
					document.body.appendChild(script);
				} else if (document.head) {
					document.head.appendChild(script);
				}
			} catch (e) {
				console.error("Browsersync: could not append script tag", e);
			}
		})()
	//]]></script>
	@endenv
</body>
</html>
