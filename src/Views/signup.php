<h1>Sign Up</h1>
<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
	@csrf
	<div class="form-group">
		<label for="username">Username</label>
		<input type="text" class="form-control @if($conflict === 'username') is-invalid @endif"
					 id="username" name="username" required
					 pattern="^[a-zA-Z0-9_-]{1,16}$" maxlength="16"
					 value="{{ $username }}">
		<div class="invalid-feedback">
			@if($conflict === 'username')
				This username is already taken. Please choose another.
			@else
				Username must be 1-16 characters long and contain only letters, numbers, underscores, and hyphens.
			@endif
		</div>
	</div>

	<div class="form-group">
		<label for="email">Email address</label>
		<input type="email" class="form-control @if($conflict === 'email') is-invalid @endif"
					 id="email" name="email" required
					 pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" maxlength="254"
					 value="{{ $email }}">
		<div class="invalid-feedback">
			@if($conflict === 'email')
				This email is already registered. Please use another email or log in.
			@else
				Please enter a valid email address.
			@endif
		</div>
	</div>

	<div class="form-group">
		<label for="password">Password</label>
		<input type="password" class="form-control" id="password" name="password" required
					 minlength="8">
		<div class="invalid-feedback">
			Password must be at least 8 characters long.
		</div>
	</div>

	<button type="submit" class="btn btn-primary">Sign Up</button>
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
