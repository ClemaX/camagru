<div class="container">
	<div class="card">
		<div class="card-header d-flex justify-content-between">
			<h1 class="card-title">{{ $username }}</h1>
			<button class="btn btn-primary d-flex gap-2 align-items-center" aria-label="Edit Profile"
				data-bs-toggle="modal" data-bs-target="#profileEditModal">
				<i class="bi-pencil"></i><span>Edit</span>
			</button>
		</div>
		<div class="card-body">
			@if($profile->description === '')
			<div class="alert alert-info mt-3">
				Your profile description is currently empty.
				You can update your profile information by pressing the edit button.
			</div>
			@else
			<p class="card-text text-pre-line">{{ $profile->description }}</p>
			@endif
		</div>
	</div>
	@role("ADMIN")
	<div class="alert alert-info mt-3" role="alert">
		You're the administrator!
	</div>
	@endrole
</div>

<div class="modal fade" id="profileEditModal" tabindex="-1" aria-hidden="true"
	aria-labelledby="profileEditModalLabel">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="profileEditModalLabel">Profile</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<form class="needs-validation d-flex flex-column gap-3" novalidate method="post">
					@csrf
					@method('PATCH')
					<div class="form-group">
						<label for="username">Username</label>
						<input type="text"
							class="form-control @if($conflict === 'username') is-invalid @endif"
							id="username" name="username" required
							pattern="^[a-zA-Z0-9_\-]{3,16}$" maxlength="16"
							value="{{ $formUsername }}">
						<div class="invalid-feedback">
							@if($conflict === 'username')
								This username is already taken. Please choose another.
							@else
								Username must be 3-16 characters long and contain only letters, numbers, underscores, and hyphens.
							@endif
						</div>
					</div>

					<div class="form-group">
						<label for="description">Description</label>
						<textarea class="form-control no-resize" id="description"
							name="description" rows="3" maxlength="140">{{ $formDescription }}</textarea>
						<div class="invalid-feedback">
							Description must be at most 140 characters long.
						</div>
					</div>
					<button type="submit" class="btn btn-primary">Update Profile</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="module" src="/js/form.js" async></script>

<script type="module" async>
	import { getOrCreateModal } from '/js/modal.js';

	window.addEventListener('load', () => {
		@if($conflict !== null)
		getOrCreateModal(document.getElementById('profileEditModal')).show();
		@endif
	}, false);
</script>
