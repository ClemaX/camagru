<!DOCTYPE html>
<html lang="en" data-bs-theme="dark" class="no-js">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Camagru</title>
	<style>.no-js body { visibility: hidden; }</style>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<script>document.documentElement.classList.remove('no-js');</script>
	<nav class="navbar navbar-expand-lg">
		<div class="container">
			<a class="navbar-brand" href="<?= $router->basePath ?>">Camagru</a>
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" href="<?= $router->basePath ?>">Home</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?= $router->basePath ?>auth/signup">Sign Up</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?= $router->basePath ?>auth/login">Log In</a>
				</li>
			</ul>
		</div>
	</nav>

	<div class="container mt-4">
		<?= $content ?>
	</div>

	<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

	<noscript>
		<style>body { visibility: visible !important; }</style>
	</noscript>
</body>
</html>
