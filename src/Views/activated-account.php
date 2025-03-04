<div class="container">
	<div class="card">
		<div class="card-header">
			<h1 class="card-title fs-5">
				@if($isActivated)
				Your account is now activated
				@else
				Your account could not be activated
				@endif
			</h1>
		</div>
		<div class="card-body">
			<p class="card-text">
				@if($isActivated)
				You can now Login to Camagru using your credentials.
				@else
				The activation URL is invalid or has expired.
				@endif
			</p>
		</div>
	</div>
</div>
