<?php

namespace App\Traits;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;

trait RequestThrottler {

	use ThrottlesLogins;

	protected function hasTooManyAttempts($requestOrKey, $maxAttempts, $lockoutDuration)
	{
		if ($requestOrKey instanceof Request) {
			$key = $this->throttleKey($requestOrKey);
		} else {
			$key = $requestOrKey;
		}

		return $this->limiter()->tooManyAttempts(
			$key, $maxAttempts, $lockoutDuration
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

	protected function clear($key) {
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

	protected function availableIn($key) {
		return $this->limiter()->availableIn($key);
	}

	protected function lock($key, $maxAttempts, $lockoutDuration) {
		$retriesLeft = $this->retriesLeft($key, $maxAttempts);

		if ($retriesLeft) {
			for ($i = 1; $i <= $retriesLeft; $i++) {
				$this->limiter()->hit($key, $lockoutDuration);
			}
		}
	}

	protected function attemptsWord($attemptsLeft) {
		return $attemptsLeft == 1 ? "attempt" : "attempts";
	}

}