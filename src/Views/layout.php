<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="darkreader-lock" />
	<title>Camagru</title>
	<link href="/css/main.min.css" rel="stylesheet">
	<link rel="icon" href="/img/icon.svg" type="image/svg+xml">
</head>
<body>
	<nav class="navbar navbar-expand-sm bg-body-tertiary">
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
			<div class="collapse navbar-collapse justify-content-end"
				id="navbarNav">
				<ul class="navbar-nav">
					@role(GUEST)
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
						<a class="nav-link" href="{{ url('/') }}"
							onclick="logout(); return false;">
							Log Out
						</a>
					</li>
					@endrole
				</ul>
			</div>
		</div>
	</nav>
	<div class="container mt-4">
		{{ $content }}
	</div>

	<script src="/js/main.min.js"></script>
	<script src="/js/collapse.min.js" defer></script>

	<script>
		const logout = async () => {
			await fetch("{{ url('/auth/logout') }}", { method: 'POST' });
			window.location = "{{ url('/') }}";
		}
	</script>

	@env(development)
	<script id="__bs_script__">//<![CDATA[
		(() => {
			try {
				var script = document.createElement('script');
				if ('async') {
					script.async = true;
				}
				script.src = 'http://HOST:80/browser-sync/browser-sync-client.js?v=2.29.3'.replace("HOST", location.hostname);
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
