<div class="container">
	<div class="card">
		<div class="card-header">
			<h1 class="card-title">Sign Up</h1>
		</div>
		<div class="card-body">
			<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
				@csrf
				<div class="form-group">
					<label for="username">Username</label>
					<input type="text" class="form-control @if($conflict === 'username') is-invalid @endif"
						id="username" name="username" required
						pattern="^[a-zA-Z0-9_\-]{3,16}$" maxlength="16"
						value="{{ $username }}">
					<div class="invalid-feedback">
						@if($conflict === 'username')
							This username is already taken. Please choose another.
						@else
							Username must be 3-16 characters long and contain only letters, numbers, underscores, and hyphens.
						@endif
					</div>
				</div>

				<div class="form-group">
					<label for="email">Email address</label>
					<input type="email" class="form-control @if($conflict === 'email') is-invalid @endif"
						id="email" name="email" required
						pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$"
						maxlength="254" value="{{ $email }}">
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
								pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\-+_!@#$%^&*., ?]).{8,}$" maxlength="254">
					<div class="invalid-feedback">
						Password must be at least 8 characters long and contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character.
					</div>
				</div>

				<button type="submit" class="btn btn-primary">Sign Up</button>
			</form>
		</div>
	</div>
</div>

<script src="/js/form.min.js" defer></script>
