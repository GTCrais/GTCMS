<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

	protected $user;
	protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $token)
    {
		$this->user = $user;
		$this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		return $this->subject(config('gtcms.siteName') . ' - Password reset request')
			->view('front.emails.auth.passwordReset')
			->with([
				'user' => $this->user,
				'token' => $this->token
			]);
    }
}
