<h1>Log In</h1>

@if($errorMessage !== null)
<div class="alert alert-danger mt-3" role="alert">
	{{ $errorMessage }}
</div>
@endif

<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
	@csrf
	<div class="form-group">
		<label for="username">Username</label>
		<input type="text" class="form-control"
					 id="username" name="username" required
					 value="{{ $username }}">
		<div class="invalid-feedback">
			Username is required.
		</div>
	</div>

	<div class="form-group">
		<label for="password">Password</label>
		<input type="password" class="form-control" id="password" name="password" required>
		<div class="invalid-feedback">
			Password is required.
		</div>
	</div>

	<button type="submit" class="btn btn-primary">Log In</button>
</form>

<script>
(function() {
	'use strict';
	window.addEventListener('load', function() {
		var forms = document.getElementsByClassName('needs-validation');
		var validation = Array.prototype.filter.call(forms, function(form) {
			form.addEventListener('submit', function(event) {
				if (form.checkValidity() === false) {
					event.preventDefault();
					event.stopPropagation();
				}
				form.classList.add('was-validated');
			}, false);
		});
	}, false);
})();
</script>
