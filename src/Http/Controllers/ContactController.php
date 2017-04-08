<?php

namespace App\Http\Controllers;

use App\Classes\Dbar;
use App\Classes\Mailer;
use Illuminate\Http\Request;

class ContactController extends Controller
{
	protected static $requestType = 'ajax';
	protected static $rules = [
		'name' => 'required',
		'email' => 'required|email',
		'subject' => 'required',
		'message' => 'required'
	];

	public function handler(Request $request)
	{
		$data = [
			'success' => false,
			'title' => trans('t.contactErrorTitle'),
			'message' => trans('t.contactErrorMessage')
		];

		$requestAllowed = true;
		if (self::$requestType == 'ajax') {
			$requestAllowed = $request->ajax() && $request->get('getIgnore_isAjax');
		}

		if ($requestAllowed) {

			$validator = \Validator::make($request->all(), self::$rules);
			if ($validator->fails()) {
				$messages = $validator->getMessageBag()->toArray();
				$finalMessages = [];
				foreach ($messages as $field => $fieldMessages) {
					foreach ($fieldMessages as $fieldMessage) {
						$finalMessages[] = $fieldMessage;
					}
				}
				$message = implode("\n", $finalMessages);
				$data['message'] = $message;

				return $this->returnData($data);
			} else {
				try {
					Mailer::sendMessage($request->all());
					$data['success'] = true;
					$data['title'] = trans('t.contactSuccessTitle');
					$data['message'] = trans('t.contactSuccessMessage');
				} catch (\Exception $e) {
					Dbar::error("Error while sending message: " . $e->getMessage());
				}
			}

			return $this->returnData($data);
		}

		abort(404);
	}

	protected function returnData($data)
	{
		if (self::$requestType == 'ajax') {
			return response()->json($data);
		} else {
			// Custom code
		}
	}
}