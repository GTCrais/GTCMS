<?php

namespace App\Classes;

use App\Mail\ContactFormMessage;
use App\Mail\PasswordReset;
use App\Mail\SimpleMail;

class Mailer
{
	public static function sendPasswordResetLink($user, $token) {
		\Mail::to($user)->send(new PasswordReset($user, $token));
	}

	public static function sendMessage($inputData)
	{
		$data = [
			'name' => $inputData['name'],
			'email' => $inputData['email'],
			'messageSubject' => $inputData['subject'],
			'messageContent' => $inputData['message']
		];

		\Mail::to(config('gtcms.adminEmail'))->send(new ContactFormMessage($data));
	}

	public static function sendEmail($body, $email = false, $subject = false)
	{
		if (!$email) {
			$email = config('gtcms.adminEmail');
		}

		if (!$subject) {
			$subject = "[Testing email functionality]";
		}

		\Mail::to($email)->send(new SimpleMail($email, $subject, $body));
	}
}