@if($isActivated)
<h1>Your account is now activated</h1>
<p>
    You can now Login to Camagru using your credentials.
</p>
@else
<h1>Your account could not be activated</h1>
<p>
    The activation URL is invalid or has expired.
</p>
@endif