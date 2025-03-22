<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="darkreader-lock" />
		<meta name="csrf-token" content="{{ $csrfToken }}">
		<title>Camagru</title>
		<link href="/css/main.min.css" rel="stylesheet" />
		<link rel="icon" href="/img/icon.svg" type="image/svg+xml" />
		<script type="module" src="/js/main.js" async></script>
		<script type="module" src="/js/collapse.js" async></script>
	</head>
	<body class="overflow-hidden">
		<nav class="navbar navbar-expand-sm fixed-top bg-body-tertiary">
			<div class="container-fluid">
				<a class="navbar-brand" href="{{ url('/') }}">
					<img
						src="/img/icon.svg"
						alt="Logo"
						width="30"
						height="30"
						class="d-inline-block align-text-top"
					/>
					Camagru
				</a>
				<button
					class="navbar-toggler"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target="#navbarNav"
					aria-controls="navbarNav"
					aria-expanded="false"
					aria-label="Toggle navigation"
				>
					<span class="navbar-toggler-icon"></span>
				</button>
				<div
					class="collapse navbar-collapse justify-content-between"
					id="navbarNav"
				>
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
							<a
								class="nav-link"
								href="{{ url('/') }}"
								data-app-action="logout"
							>
								Log Out
							</a>
						</li>
						@endrole
					</ul>
				</div>
			</div>
		</nav>
		<main>{{ $content }}</main>

		@role("USER", "ADMIN")
		<form id="logout-form" action="{{ url('/auth/logout') }}" method="POST" style="display: none;">
			@csrf
		</form>
		<script type="module" src="/js/logout.js" async></script>
		@endrole @env("development")
		<script id="__bs_script__">
			//<![CDATA[
			(() => {
				try {
					var script = document.createElement("script");
					if ("async") {
						script.async = true;
					}
					script.src = "/browser-sync/browser-sync-client.js?v=2.29.3";
					if (document.body) {
						document.body.appendChild(script);
					} else if (document.head) {
						document.head.appendChild(script);
					}
				} catch (e) {
					console.error("Browsersync: could not append script tag", e);
				}
			})();
			//]]>
		</script>
		@endenv
	</body>
</html>
