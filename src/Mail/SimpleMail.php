<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SimpleMail extends Mailable
{
    use Queueable, SerializesModels;

	protected $email;
	protected $emailSubject;
	protected $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $subject, $body)
    {
		$this->email = $email;
		$this->emailSubject = $subject;
		$this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		return $this->subject($this->emailSubject)
			->view('front.emails.simple')
			->with([
				'body' => $this->body
			]);
    }
}
