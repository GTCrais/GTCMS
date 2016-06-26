<?php

namespace App\Http\Controllers;

use App\Dbar;
use App\Mailer;

class ContactController extends Controller {

	protected static $requestType = 'ajax';
	protected static $rules = array(
		'name' => 'required',
		'email' => 'required|email',
		'subject' => 'required',
		'message' => 'required'
	);

	public static function handler() {

		$data = array(
			'success' => false,
			'title' => trans('t.contactErrorTitle'),
			'message' => trans('t.contactErrorMessage')
		);

		$requestAllowed = true;
		if (self::$requestType == 'ajax') {
			$requestAllowed = \Request::ajax() && \Request::get('getIgnore_isAjax');
		}

		if ($requestAllowed) {

			$validator = \Validator::make(\Request::all(), self::$rules);
			if ($validator->fails()) {
				$messages = $validator->getMessageBag()->toArray();
				$finalMessages = array();
				foreach ($messages as $field => $fieldMessages) {
					foreach ($fieldMessages as $fieldMessage) {
						$finalMessages[] = $fieldMessage;
					}
				}
				$message = implode("\n", $finalMessages);
				$data['message'] = $message;
				return self::returnData($data);
			} else {
				try {
					Mailer::sendMessage(\Request::all());
					$data['success'] = true;
					$data['title'] = trans('t.contactSuccessTitle');
					$data['message'] = trans('t.contactSuccessMessage');
				} catch (\Exception $e) {
					Dbar::error("Error while sending message: ".$e->getMessage());
				}
			}

			return self::returnData($data);
		}

		\App::abort(404);

	}

	protected static function returnData($data) {
		if (self::$requestType == 'ajax') {
			return \Response::json($data);
		} else {
			// Custom code

		}
	}

}