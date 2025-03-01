<h1>Reset Password</h1>

@if($isEmailSent)
<div class="alert alert-success mt-3" role="alert">
	An email has been sent to the provided address with instructions to reset your password.
</div>
@endif

<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
	@csrf
	<div class="form-group">
		<label for="email">Email address</label>
		<input type="email" class="form-control"
					 id="email" name="email" required
					 pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" maxlength="254"
					 value="{{ $email }}">
		<div class="invalid-feedback">
			Please enter a valid email address.
		</div>
	</div>

	<button type="submit" class="btn btn-primary">Reset Password</button>
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
