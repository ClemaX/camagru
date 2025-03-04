<div class="container">
	<div class="card">
		<div class="card-header">
			<h1 class="card-title fs-5">
				@if($isChanged)
				Your email address has been changed
				@else
				Your email could not be verified
				@endif
			</h1>
		</div>
		<div class="card-body">
		<p class="card-text">
			@if($isChanged)
			From now on, you will receive notifications on your newly verified
			email address.
			@else
			The verification URL is invalid or has expired.
			@endif
		</p>
		</div>
	</div>
</div>
