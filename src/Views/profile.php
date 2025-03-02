<h1>Profile</h1>
@role(ADMIN)
<div class="alert alert-info mt-3" role="alert">
	You're the administrator!
</div>
@endrole
<div class="mb-2">
	<strong>Username:</strong> {{ $username }}
</div>
<div class="mb-2">
	<strong>Description:</strong> @if($profile->description === '') Empty @else {{ $profile->description }} @endif
</div>
