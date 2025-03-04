<div class="card">
	<div class="card-header d-flex justify-content-between">
		<h1 class="card-title">Settings</h1>
		<button class="btn btn-primary d-flex gap-2 align-items-center" aria-label="Edit Settings"
			data-bs-toggle="modal" data-bs-target="#settingsEditModal">
			<i class="bi-pencil"></i><span>Edit</span>
		</button>
	</div>
	<div class="card-body">
		<p class="card-text">
			<strong>Email Address:</strong> {{ $email }}
		</p>
		<p class="card-text">
			<strong>Comment Notifications:</strong> {{ $settings->commentNotification ? "Enabled" : "Disabled" }}
		</p>
		<div class="d-flex gap-3">
			<button class="btn btn-primary d-flex gap-2 align-items-center" aria-label="Edit Settings"
				data-bs-toggle="modal" data-bs-target="#emailEditModal">
				Change Email Address
			</button>
			<button class="btn btn-primary d-flex gap-2 align-items-center" aria-label="Edit Settings"
				data-bs-toggle="modal" data-bs-target="#passwordEditModal">
				Change Password
			</button>
		</div>
	</div>
</div>

<div class="modal fade" id="settingsEditModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
					@csrf
					@method('PATCH')
					<div class="form-group">
						<input class="form-check-input" type="checkbox"
							id="commentNotification" name="commentNotification"
							@if($settings->commentNotification)checked="checked"@endif>
						<label for="commentNotification">Comment Notifications</label>
					</div>
					<button type="submit" class="btn btn-primary">Update Settings</button>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="emailEditModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<form class="needs-validation d-flex flex-column gap-3" novalidate
					method="post" action="{{ url('/user/self/new-email') }}">
					@csrf
					@method('PUT')
					<div class="form-group">
						<label for="email">Email address</label>
						<input type="email" class="form-control @if($conflict === 'email') is-invalid @endif"
							id="email" name="email" required
							pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" maxlength="254"
							value="{{ $formEmail }}">
						<div class="invalid-feedback">
							@if($conflict === 'email')
								This email is already registered. Please use another email or log in.
							@else
								Please enter a valid email address.
							@endif
						</div>
					</div>
					<button type="submit" class="btn btn-primary">Change Email</button>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="passwordEditModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<form class="needs-validation d-flex flex-column gap-3" novalidate
					method="post" action="{{ url('/user/self/password') }}">
					@csrf
					@method('PUT')
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password" required
									pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\-+_!@#$%^&*., ?]).{8,}$" maxlength="254">
						<div class="invalid-feedback">
							Password must be at least 8 characters long and contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character.
						</div>
					</div>
					<button type="submit" class="btn btn-primary">Change Password</button></form>
			</div>
		</div>
	</div>
</div>

<script src="/js/form.min.js"></script>
<script src="/js/modal.min.js"></script>

<script>
	(() => {
		'use strict';
		window.addEventListener('load', () => {
			@if($conflict === 'email')
		new Modal(document.getElementById('emailEditModal')).show();
			@endif
		}, false);
	})();
</script>
