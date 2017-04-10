<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\RequestThrottler;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails, RequestThrottler;

	protected $throttleRequests = true;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
		$this->setThrottlingParameters();
    }

	public function setThrottlingParameters()
	{
		$this->maxAttempts = 5;
		$this->lockoutDuration = 2; // In minutes
	}

	public function showLinkRequestForm()
	{
		return view('front.pages.auth.passwordResetEmail')->with([
			'success' => session('success', false),
			'message' => session('message', false)
		]);
	}

	public function sendResetLinkEmail(Request $request)
	{
		$attemptsMessage = false;
		if ($this->throttleRequests) {
			$attemptsMessage = $this->processRequest($request);

			if ($request->hasTooManyAttempts) {
				return back()->with(compact('message'));
			}
		}

		$rules = ['email' => 'required|email|max:255'];

		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$message = "Incorrect email format.";

			if ($attemptsMessage) {
				$message .= "<br>" . $attemptsMessage;
			}

			return back()->withErrors($validator)->withInput()->with(compact('message'));
		}

		// We will send the password reset link to this user. Once we have attempted
		// to send the link, we will examine the response then see the message we
		// need to show to the user. Finally, we'll send out a proper response.
		$response = $this->broker()->sendResetLink(
			$request->only('email')
		);

		if ($response === Password::RESET_LINK_SENT) {
			$this->lock($this->throttleKey($request), $this->maxAttempts, $this->lockoutDuration);

			return back()->with([
				'message' => trans($response),
				'success' => true
			]);
		}

		// If an error was returned by the password broker, we will get this message
		// translated so we can notify a user of the problem. We'll redirect back
		// to where the users came from so they can attempt this process again.
		$message = trans($response);

		if ($attemptsMessage) {
			$message .= "<br>" . $attemptsMessage;
		}

		return back()->with(compact('message'));
	}
}
