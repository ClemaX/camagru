<h1>Welcome to Camagru!</h1>
@if($username !== null)
Hello {{ $username }}!
@else
<p>Sign up or log in to get started!</p>
@endif
