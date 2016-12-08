<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller {

	public static function login() {

		if (\Auth::user()) {
			return \Redirect::route('home');
		}

		$email = \Request::get('email');
		$password = \Request::get('password');
		$remember = \Request::has('remember_me') && \Request::get('remember_me') ? true : false;

		if (!$email || !$password) {
			return \Redirect::route('home')->with(array('loginError' => trans('t.emptyLoginField')))->withInput();
		}

		if (\Auth::attempt(array('email' => $email, 'password' => $password), $remember)) {
			return \Redirect::route('home');
		} else {
			return \Redirect::route('home')->with(array('loginError' => trans('t.incorrectLoginField')))->withInput();
		}

	}

	public static function logout() {
		if (\Auth::user()) {
			\Auth::logout();
		}
		return \Redirect::route('home');
	}

	public static function register() {
		if (\Auth::user()) {
			\Redirect::route('home');
		}

		if (\Request::all()) {
			$rules = array();
			$rules['email'] = "required|email|unique:users,email";
			$rules['password'] = 'required|min:7|confirmed';
			$rules['firstName'] = "required";
			$rules['lastName'] = "required";

			$validator = \Validator::make(\Request::all(), $rules);
			if ($validator->fails()) {
				return \Redirect::route('register')->withErrors($validator)->withInput();
			} else {
				$user = User::create(\Request::all());
				\Auth::login($user);

				return \Redirect::route('home');
			}
		}

		return \View::make('elements.register');
	}

}