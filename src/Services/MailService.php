<?php

namespace App\Services;

use App\Renderer;

class MailService
{
	public function __construct(private readonly Renderer $renderer)
	{
	}

	/**
	 * @param array<string, mixed> $params
	 */
	public function send(
		string $to,
		string $subject,
		string $templateName,
		array $params = [],
		string $contentType = 'text/html'
	): void {
		$message = $this->renderer->render($templateName, $params);

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: " . $contentType . "; charset=UTF-8\r\n";
		$headers .= "Content-Transfer-Encoding: quoted-printable\r\n";

		$subjectIsAscii = mb_detect_encoding($subject, 'ASCII', true) !== false;
		$subjectEncoded = $subjectIsAscii
			? $subject
			: '=?utf-8?Q?'. quoted_printable_decode($subject) . "?=";

		$messageEncoded = quoted_printable_encode($message);

		mail($to, $subjectEncoded, $messageEncoded, $headers);
	}
}
