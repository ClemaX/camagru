<div class="container">
	<div class="card">
		<div class="card-header">
			<h1 class="card-title">Log In</h1>
		</div>
		<div class="card-body">
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

				<a href="{{ url('/auth/reset-password') }}">Reset password</a>

				<button type="submit" class="btn btn-primary">Log In</button>
			</form>

			@if($errorMessage !== null)
			<p class="card-text alert alert-danger mt-3" role="alert">
				{{ $errorMessage }}
			</p>
			@endif
		</div>
	</div>
</div>

<script type="module" src="/js/form.js" async></script>
