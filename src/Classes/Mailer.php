<?php

namespace App\Classes;

class Mailer
{
	public static function sendPasswordResetLink($user, $token) {
		\Mail::send('front.emails.auth.passwordReset', ['user' => $user, 'token' => $token], function ($message) use ($user) {
			$message->to($user->email)->subject(config('gtcms.siteName') . " - Password reset request");
		});
	}

	public static function sendMessage($inputData)
	{
		$body = "
			<br>
			<strong>Name:</strong> " . \Html::entities($inputData['name']) . "<br>
			<strong>Email:</strong> " . \Html::entities($inputData['email']) . "<br>
			<strong>Subject:</strong> " . \Html::entities($inputData['subject']) . "<br>
			<strong>Message:</strong><br>" . \Html::entities($inputData['message']) . "<br>
		";

		\Mail::send('front.emails.simple', ['body' => $body], function ($message) {
			$message->to(config('gtcms.adminEmail'))->subject(config('gtcms.siteName') . " - New message");
		});
	}

	public static function sendEmail($body, $email = false, $subject = false)
	{
		if (!$email) {
			$email = config('gtcms.adminEmail');
		}

		if (!$subject) {
			$subject = "[Testing email functionality]";
		}

		\Mail::send('front.emails.simple', ['body' => $body], function ($message) use ($email, $subject) {
			$message->to($email)->subject($subject);
		});
	}
}