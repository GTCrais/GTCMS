<?php

namespace App;

class Mailer {

	public static function sendMessage($inputData) {
		$body = "
			<br>
			Name: " . \Html::entities($inputData['name']) . "<br>
			Email: " . \Html::entities($inputData['email']) . "<br>
			Subject: " . \Html::entities($inputData['subject']) . "<br>
			Message: <br>" . \Html::entities($inputData['message']) . "<br>
		";

		\Mail::send('templates.email.standard', array('body' => $body), function($message) {
			$message->to(config('gtcms.adminEmail'))->subject(config('gtcms.contactMessageSubject'));
		});
	}

	public static function sendEmail($body, $email = false, $subject = false) {
		if (!$email) {
			$email = config('gtcms.adminEmail');
		}
		if (!$subject) {
			$subject = "[Testing email functionality]";
		}
		\Mail::send('templates.email.standard', array('body' => $body), function($message) use ($email, $subject){
			$message->to($email)->from(config('gtcms.fromEmail'), config('gtcms.fromPerson'))->subject($subject);
		});
	}

}