<?php

namespace App\Http\Middleware;


use App\Classes\AdminHelper;
use App\Classes\AdminHistoryManager;
use App\Classes\Dbar;
use App\Models\User;
use Closure;
use App\Classes\MessageManager;
use App\Classes\Tools;

class AdminAuth
{
	public function handle($request, Closure $next, $guard = null)
	{
		app()->setLocale(config('gtcmslang.defaultAdminLocale'));

		/** @var \Illuminate\Http\Request $request */
		$receivedCsrf = $request->header('X-CSRF-TOKEN');
		$ajaxRequest = request()->ajax() ? true : false;
		$gtcmsAjaxRequest = $ajaxRequest && request()->get('getIgnore_isAjax') ? true : false;

		$showLoginMessage = true;
		if (config('gtcms.adminAutoLogin') && \Auth::guest()) {
			$user = User::where('role', 'admin')->first();
			\Auth::login($user);
			$showLoginMessage = false;
		}

		$allowedUserRoles = config('gtcms.allowedUserRoles');

		if(\Auth::guest() || !in_array(\Auth::user()->role, $allowedUserRoles)) {
			if (\Route::currentRouteName() != "adminLogin") {
				if (request()->ajax() && request()->get('getIgnore_isAjax')) {
					$data = array(
						'success' => false,
						'message' => "Session timeout",
						'redirectToLogin' => true
					);

					return response()->json($data);
				} else {
					return redirect()->to(AdminHelper::getCmsPrefix() . 'login');
				}
			}
		} else if ($gtcmsAjaxRequest && $receivedCsrf != csrf_token()) {
			$message = trans('gtcms.errorHasOccurred');
			$data = [
				'success' => false,
				'exception' => $message
			];

			return response()->json($data);
		} else if (\Route::currentRouteName() == "adminLogin") {
			if ($showLoginMessage) {
				MessageManager::setError(trans('gtcms.alreadyLoggedIn'));
			}

			return redirect()->to(AdminHelper::getCmsPrefix());
		}

		if(session('accessDenied')) {
			if (\Route::currentRouteName() != "restricted") {
				session(['accessDenied' => true]);
				return redirect()->route('restricted', ['getIgnore_isAjax' => request()->get('getIgnore_isAjax')]);
			}
		} else {
			if (\Route::currentRouteName() == "restricted") {
				MessageManager::setError(trans('gtcms.accessGranted'));
				session(['accessDenied' => false]);
				return redirect()->to(AdminHelper::getCmsPrefix());
			}
		}

		return $next($request);
	}
}
