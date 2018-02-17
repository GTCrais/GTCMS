<?php

namespace App\Traits;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;

trait RequestThrottler
{
	use ThrottlesLogins;

	protected $maxAttempts = 5;
	protected $lockoutDuration = 1; // In minutes

	protected function processRequest(Request $request)
	{
		$request->hasTooManyAttempts = false;

		if ($this->hasTooManyAttempts($request, $this->maxAttempts)) {
			$errorMessage = trans('auth.throttle', ['seconds' => $this->availableIn($this->throttleKey($request))]);
			$request->hasTooManyAttempts = true;

			return $errorMessage;
		}

		$this->incrementAttempts($request, $this->lockoutDuration);

		$retriesLeft = $this->retriesLeft($this->throttleKey($request), $this->maxAttempts);
		if ($retriesLeft < 0) {
			$retriesLeft = 0;
		}

		if (!$retriesLeft) {
			$errorMessage = trans('auth.throttle', ['seconds' => $this->availableIn($this->throttleKey($request))]);
		} else {
			$errorMessage = trans_choice('auth.attemptsLeft', $retriesLeft, ['attemptsLeft' => $retriesLeft]);
		}

		return $errorMessage;
	}

	protected function hasTooManyAttempts($requestOrKey, $maxAttempts)
	{
		if ($requestOrKey instanceof Request) {
			$key = $this->throttleKey($requestOrKey);
		} else {
			$key = $requestOrKey;
		}

		return $this->limiter()->tooManyAttempts(
			$key, $maxAttempts
		);
	}

	protected function incrementAttempts($requestOrKey, $lockoutDuration)
	{
		if ($requestOrKey instanceof Request) {
			$key = $this->throttleKey($requestOrKey);
		} else {
			$key = $requestOrKey;
		}

		$this->limiter()->hit($key, $lockoutDuration);
	}

	protected function throttleKey(Request $request, $url = false, $append = "")
	{
		if (!$url) {
			$url = $request->url();
		}

		$url = rtrim($url, "/");

		return $url . "_" . $request->ip() . $append;
	}

	protected function resetAttempts($key)
	{
		$this->limiter()->resetAttempts($key);
	}

	protected function clear($key)
	{
		$this->limiter()->clear($key);
	}

	protected function retriesLeft($key, $maxAttempts)
	{
		$retriesLeft = $this->limiter()->retriesLeft($key, $maxAttempts);
		if ($retriesLeft <= 0) {
			$retriesLeft = 0;
		}

		return $retriesLeft;
	}

	protected function availableIn($key)
	{
		return $this->limiter()->availableIn($key);
	}

	protected function lock($key, $maxAttempts, $lockoutDuration)
	{
		$retriesLeft = $this->retriesLeft($key, $maxAttempts);

		if ($retriesLeft) {
			for ($i = 1; $i <= $retriesLeft; $i++) {
				$this->limiter()->hit($key, $lockoutDuration);
			}
		}
	}

	protected function attemptsWord($attemptsLeft)
	{
		return $attemptsLeft == 1 ? "attempt" : "attempts";
	}
}