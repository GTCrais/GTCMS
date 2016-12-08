<?php

namespace App\Classes;

class MessageManager {

	public static function getException() {
		$message = \Session::get('exceptionMsg', NULL);
		self::flush('exceptionMsg');
		return $message;
	}

	public static function setException($message) {
		\Session::put('exceptionMsg', $message);
	}

	public static function getSuccess() {
		$message = \Session::get('successMsg', NULL);
		self::flush('successMsg');
		return $message;
	}

	public static function setSuccess($message) {
		\Session::put('successMsg', $message);
	}

	public static function getError() {
		$message = \Session::get('errorMsg', NULL);
		self::flush('errorMsg');
		return $message;
	}

	public static function setError($message) {
		\Session::put('errorMsg', $message);
	}

	public static function getCustom($key) {
		$message = \Session::get($key, NULL);
		self::flush($key);
		return $message;
	}

	public static function setCustom($key, $message) {
		\Session::put($key, $message);
	}

	public static function flush($messageType = 'all') {
		if ($messageType == 'all') {
			\Session::forget('exceptionMsg');
			\Session::forget('successMsg');
			\Session::forget('errorMsg');
		} else {
			\Session::forget($messageType);
		}
	}

}