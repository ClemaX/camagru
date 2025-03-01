<h1>Choose Password</h1>

@if($isUrlInvalid)
<div class="alert alert-danger mt-3" role="alert">
	The password reset URL is invalid or has expired.
</div>
@endif

<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
	@csrf

	<input type="hidden" name="userId" value="{{ $userId }}" />
	<input type="hidden" name="token" value="{{ $token }}" />

	<div class="form-group">
		<label for="password">Password</label>
		<input type="password" class="form-control" id="password" name="password" required
					 pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\-+_!@#$%^&*., ?]).{8,}$" maxlength="254">
		<div class="invalid-feedback">
			Password must be at least 8 characters long and contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character.
		</div>
	</div>

	<button type="submit" class="btn btn-primary">Change Password</button>
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
