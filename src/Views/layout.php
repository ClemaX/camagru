<!DOCTYPE html>
<html lang="en" data-bs-theme="dark" class="no-js">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="darkreader-lock" />
	<title>Camagru</title>
	<link href="/css/main.css" rel="stylesheet">
	<link rel="icon" href="/img/icon.svg" type="image/svg+xml">
</head>
<body>
	<nav class="navbar navbar-expand-lg">
		<div class="container">
			<a class="navbar-brand" href="{{ url('/') }}">Camagru</a>
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" href="{{ url('/') }}">Home</a>
				</li>
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
					<a class="nav-link" href="{{ url('/user/self/settings') }}">Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{ url('/') }}" onclick="logout(); return false;">Log Out</a>
				</li>
				@endrole
			</ul>
		</div>
	</nav>

	<div class="container mt-4">
		{{ $content }}
	</div>

	<script>
		const logout = async () => {
			await fetch("{{ url('/auth/logout') }}", { method: 'POST' });
			window.location = "{{ url('/') }}";
		}
	</script>
</body>
</html>
