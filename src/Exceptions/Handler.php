<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		//
	];

	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 *
	 * @var array
	 */
	protected $dontFlash = [
		'password',
		'password_confirmation',
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Exception $exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception)
	{
		if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
			$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;
			$message = "Your session has expired. Please refresh the page and try again.";

			if ($ajaxRequest) {
				$data = [
					'success' => false,
					'message' => $message,
					'tokenMismatch' => true
				];

				return response()->json($data);
			} else {
				return redirect()->back()->withInput()->with(['message' => $message]);
			}
		}

		return parent::render($request, $exception);
	}
}
