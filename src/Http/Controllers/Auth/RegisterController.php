<?php

namespace App\Http\Controllers\Auth;

use App\Traits\RequestThrottler;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users as well as their
	| validation and creation. By default this controller uses a trait to
	| provide this functionality without requiring any additional code.
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
		$this->middleware('guest');
		$this->setThrottlingParameters();
	}

	public function setThrottlingParameters()
	{
		$this->maxAttempts = 5;
		$this->lockoutDuration = 1; // In minutes
	}

	public function showRegistrationForm()
	{
		return view()->make('front.pages.auth.register')->with(['errorMessage' => session('errorMessage', false)]);
	}

	public function register(Request $request)
	{
		$attemptsMessage = false;

		if ($this->throttleRequests) {
			$attemptsMessage = $this->processRequest($request);

			if ($request->hasTooManyAttempts) {
				return back()->with(['errorMessage' => $attemptsMessage]);
			}
		}

		$validator = $this->validator($request->all());
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput()->with(['errorMessage' => $attemptsMessage]);
		}

		try {
			event(new Registered($user = $this->create($request)));
			auth()->login($user);

			$this->clear($this->throttleKey($request));

			return redirect()->route('home');
		} catch (\Exception $e) {
			\Log::error("User registration error: " . $e->getMessage());
			\Log::error($e);

			$errorMessage = trans('front.errorHasOccurred');

			return back()->withInput()->with(compact('errorMessage'));
		}
	}

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function validator(array $data)
	{
		return Validator::make($data, [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|min:6|confirmed',
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	protected function create(Request $request)
	{
		return User::create([
			'name' => $request->get('name'),
			'email' => $request->get('email'),
			'password' => Hash::make($request->get('password')),
			'role' => 'user'
		]);
	}
}
