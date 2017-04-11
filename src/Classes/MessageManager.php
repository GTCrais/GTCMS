<?php

namespace App\Classes;

class MessageManager
{
	public static function getException()
	{
		$message = session('exceptionMsg', null);
		self::flush('exceptionMsg');

		return $message;
	}

	public static function setException($message)
	{
		session(['exceptionMsg' => $message]);
	}

	public static function getSuccess()
	{
		$message = session('successMsg', null);
		self::flush('successMsg');

		return $message;
	}

	public static function setSuccess($message)
	{
		session(['successMsg' => $message]);
	}

	public static function getError()
	{
		$message = session('errorMsg', null);
		self::flush('errorMsg');

		return $message;
	}

	public static function setError($message)
	{
		session(['errorMsg' => $message]);
	}

	public static function getCustom($key)
	{
		$message = session($key, null);
		self::flush($key);

		return $message;
	}

	public static function setCustom($key, $message)
	{
		session([$key => $message]);
	}

	public static function flush($messageType = 'all')
	{
		if ($messageType == 'all') {
			\Session::forget('exceptionMsg');
			\Session::forget('successMsg');
			\Session::forget('errorMsg');
		} else {
			\Session::forget($messageType);
		}
	}
}