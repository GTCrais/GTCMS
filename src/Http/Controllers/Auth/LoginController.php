<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\RequestThrottler;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use RequestThrottler;

	protected $throttleRequests = true;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
		$this->setThrottlingParameters();
    }

	public function setThrottlingParameters()
	{
		$this->maxAttempts = 5;
		$this->lockoutDuration = 1; // In minutes
	}

	public function showLoginForm()
	{
		return view()->make('front.pages.auth.login')->with([
			'errorMessage' => session('errorMessage', false),
			'passwordResetSuccess' => session('passwordResetSuccess', false)
		]);
	}

	public function login(Request $request)
	{
		$email = $request->get('email');
		$password = $request->get('password');
		$remember = $request->get('remember_me') ? true : false;

		$attemptsMessage = false;

		if ($this->throttleRequests) {
			$attemptsMessage = $this->processRequest($request);

			if ($request->hasTooManyAttempts) {
				return back()->with(compact('errorMessage'));
			}
		}

		if (auth()->attempt(['email' => $email, 'password' => $password], $remember)) {
			$this->clear($this->throttleKey($request));

			return redirect()->route('home');
		}

		$errorMessage = trans('front.incorrectLoginField');

		if ($attemptsMessage) {
			$errorMessage .= "<br>" . $attemptsMessage;
		}

		return back()->withInput()->with(compact('errorMessage'));
	}

	public function logout()
	{
		if (auth()->check()) {
			auth()->logout();
		}

		return redirect()->route('home');
	}
}
