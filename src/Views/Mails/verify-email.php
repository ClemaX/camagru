<tr>
	<td
		style="padding: 20px; text-align: center; background-color: #007bff"
	>
		<h1 style="color: #ffffff; margin: 0; font-size: 24px">
			Camagru Email Verification
		</h1>
	</td>
</tr>
<tr>
	<td style="padding: 20px">
		<p style="font-size: 16px; line-height: 1.5; color: #333333">
			Dear {{ $username }},
		</p>
		<p style="font-size: 16px; line-height: 1.5; color: #333333">
			To verify your new email address, please click
			the button below:
		</p>
		<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td align="center" style="padding: 20px 0">
					<a
						href="{{ url($verifyUrl) }}"
						style="
							display: inline-block;
							padding: 10px 20px;
							background-color: #007bff;
							color: #ffffff;
							text-decoration: none;
							border-radius: 5px;
							font-weight: bold;
						"
					>
						Verify Email
					</a>
				</td>
			</tr>
		</table>
		<p style="font-size: 16px; line-height: 1.5; color: #333333">
			If the button doesn't work, you can also copy and paste this link
			into your browser:
		</p>
		<p style="font-size: 14px; line-height: 1.5; color: #666666; word-break: break-all;">
			{{ url($verifyUrl) }}
		</p>
		<p style="font-size: 16px; line-height: 1.5; color: #333333">
			This link will expire in {{ $urlLifetime }}.
		</p>
		<p style="font-size: 16px; line-height: 1.5; color: #333333">
			Best regards,<br />The Camagru Team
		</p>
	</td>
</tr>
<tr>
	<td
		style="padding: 20px; text-align: center; background-color: #f4f4f4"
	>
		<p style="font-size: 12px; color: #666666">
			This is an automated message. Please do not reply to this email.
		</p>
	</td>
</tr>
