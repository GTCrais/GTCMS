<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function login(Request $request)
	{
		if (auth()->user()) {
			return redirect()->route('home');
		}

		$email = $request->get('email');
		$password = $request->get('password');
		$remember = $request->has('remember_me') && $request->get('remember_me') ? true : false;

		if (!$email || !$password) {
			return redirect()->route('home')->with(['loginError' => trans('t.emptyLoginField')])->withInput();
		}

		if (auth()->attempt(['email' => $email, 'password' => $password], $remember)) {
			return redirect()->route('home');
		} else {
			return redirect()->route('home')->with(['loginError' => trans('t.incorrectLoginField')])->withInput();
		}
	}

	public function logout(Request $request)
	{
		if (auth()->user()) {
			auth()->logout();
		}

		return redirect()->route('home');
	}

	public function register(Request $request)
	{
		if (auth()->user()) {
			redirect()->route('home');
		}

		if ($request->all()) {
			$rules = [];
			$rules['email'] = "required|email|unique:users,email";
			$rules['password'] = 'required|min:7|confirmed';
			$rules['firstName'] = "required";
			$rules['lastName'] = "required";

			$validator = \Validator::make($request->all(), $rules);
			if ($validator->fails()) {
				return redirect()->route('register')->withErrors($validator)->withInput();
			} else {
				$user = User::create($request->all());
				auth()->login($user);

				return redirect()->route('home');
			}
		}

		return view()->make('elements.register');
	}
}