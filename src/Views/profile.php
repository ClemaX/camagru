<h1>Profile</h1>

<div class="mb-2">
	<strong>Username:</strong> {{ $username }}
</div>
<div class="mb-2">
	<strong>Description:</strong> @if($profile->description === '') Empty @else {{ $profile->description }} @endif
</div>
