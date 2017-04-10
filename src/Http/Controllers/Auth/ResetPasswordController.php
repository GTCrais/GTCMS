<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\RequestThrottler;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords, RequestThrottler;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
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
		$this->lockoutDuration = 1; // In minutes
	}

	public function showResetForm(Request $request, $token = null)
	{
		return view('front.pages.auth.resetPassword')->with([
			'errorMessage' => session('errorMessage', false),
			'token' => $token,
			'email' => $request->get('email')
		]);
	}

	public function reset(Request $request)
	{
		$attemptsMessage = false;
		if ($this->throttleRequests) {
			$attemptsMessage = $this->processRequest($request);

			if ($request->hasTooManyAttempts) {
				return back()->with(['errorMessage' => $attemptsMessage]);
			}
		}

		$validator = \Validator::make($request->all(), $this->rules());
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput()->with(['errorMessage' => $attemptsMessage]);
		}

		// Here we will attempt to reset the user's password. If it is successful we
		// will update the password on an actual user model and persist it to the
		// database. Otherwise we will parse the error and return the response.
		$response = $this->broker()->reset(
			$this->credentials($request), function ($user, $password) {
				$this->resetPassword($user, $password);
			}
		);

		if ($response == Password::PASSWORD_RESET) {
			return redirect()->route('login')->with(['passwordResetSuccess' => trans($response)]);
		}

		$errorMessage = trans($response);

		if ($attemptsMessage) {
			$errorMessage .= "<br>" . $attemptsMessage;
		}

		return back()->with(compact('errorMessage'));
	}

	protected function resetPassword($user, $password)
	{
		$user->forceFill([
			'password' => bcrypt($password),
			'remember_token' => Str::random(60),
		])->save();
	}

	protected function rules()
	{
		return [
			'token' => 'required',
			'email' => 'required|email|max:255',
			'password' => 'required|confirmed|min:6',
		];
	}
}
