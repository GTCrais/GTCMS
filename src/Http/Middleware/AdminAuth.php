<?php

namespace App\Http\Middleware;


use App\User;
use Closure;
use App\MessageManager;
use App\Tools;

class AdminAuth {

	public function handle($request, Closure $next, $guard = null)  {

		\App::setLocale(config('gtcmslang.defaultAdminLocale'));

		$showLoginMessage = true;
		if (config('gtcms.adminAutoLogin') && \Auth::guest()) {
			$user = User::where('role', 'admin')->first();
			\Auth::login($user);
			$showLoginMessage = false;
		}

		$allowedUserRoles = config('gtcms.allowedUserRoles');

		if(\Auth::guest() || !in_array(\Auth::user()->role, $allowedUserRoles)) {
			if (\Route::current()->uri() != "admin/login") {
				if (\Request::ajax() && \Request::get('getIgnore_isAjax')) {
					$data = array(
						'success' => false,
						'message' => "Session timeout",
						'redirectToLogin' => true
					);

					return \Response::json($data);
				} else {
					return \Redirect::to('/admin/login');
				}
			}
		} else if (\Route::current()->uri() == "admin/login") {
			if ($showLoginMessage) {
				MessageManager::setError(trans('gtcms.alreadyLoggedIn'));
			}
			return \Redirect::to("/admin");
		}

		if(\Session::get('accessDenied')) {
			if (\Route::currentRouteName() != "restricted") {
				\Session::put('accessDenied', true);
				return \Redirect::to('/access-denied');
			}
		} else {
			if (\Route::currentRouteName() == "restricted") {
				MessageManager::setError(trans('gtcms.accessGranted'));
				\Session::put('accessDenied', false);
				return \Redirect::to("/admin");
			}
		}

		return $next($request);
	}

}
