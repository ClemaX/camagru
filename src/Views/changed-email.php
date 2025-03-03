@if($isChanged)
<h1>Your email address has been changed</h1>
<p>
	From now on, you will receive notifications on your newly verified
	email address.
</p>
@else
<h1>Your email could not be verified</h1>
<p>
	The verification URL is invalid or has expired.
</p>
@endif
