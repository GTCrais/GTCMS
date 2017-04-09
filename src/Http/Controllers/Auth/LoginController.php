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

	protected $throttleLogins = true;
	protected $maxLoginAttempts = 5;
	protected $lockoutDuration = 1; // In minutes

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

	public function showLoginForm()
	{
		return view()->make('front.pages.auth.login')->with(['errorMessage' => session('errorMessage', false)]);
	}

	public function login(Request $request)
	{
		$email = $request->get('email');
		$password = $request->get('password');
		$remember = $request->get('remember_me') ? true : false;

		if ($this->throttleLogins) {
			if ($this->hasTooManyAttempts($request, $this->maxLoginAttempts, $this->lockoutDuration)) {
				$errorMessage = trans('auth.throttle', ['seconds' => $this->availableIn($this->throttleKey($request))]);

				return back()->with(compact('errorMessage'));
			}

			$this->incrementAttempts($request, $this->lockoutDuration);
		}

		if (auth()->attempt(['email' => $email, 'password' => $password], $remember)) {
			return redirect()->route('home');
		}

		$errorMessage = trans('front.incorrectLoginField');

		if ($this->throttleLogins) {
			$retriesLeft = $this->retriesLeft($this->throttleKey($request), $this->maxLoginAttempts);
			if ($retriesLeft <= 0) {
				$retriesLeft = 0;
			}

			if (!$retriesLeft) {
				$errorMessage .= "<br>" . trans('auth.throttle', ['seconds' => $this->lockoutDuration * 60]);
			} else {
				$errorMessage .= "<br>" . trans_choice('auth.attemptsLeft', $retriesLeft, ['attemptsLeft' => $retriesLeft]);
			}

			// We need to add this line to start the lockout timer here
			// instead of on the next login attempt
			$this->hasTooManyAttempts($request, $this->maxLoginAttempts, $this->lockoutDuration);
		}

		return back()->with(compact('errorMessage'))->withInput();
	}

	public function logout()
	{
		if (auth()->check()) {
			auth()->logout();
		}

		return redirect()->route('home');
	}
}
