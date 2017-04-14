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
			'title' => trans('front.contactErrorTitle'),
			'message' => trans('front.errorHasOccurred')
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
					$data['title'] = trans('front.contactSuccessTitle');
					$data['message'] = trans('front.contactSuccessMessage');
				} catch (\Exception $e) {
					Dbar::error("Error while sending message: " . $e->getMessage());

					if (app()->environment() != 'production') {
						$data['message'] .= "<br>Non-production environment detected. Try changing your email driver to 'log'.";
					}
				}
			}

			return $this->returnData($data);
		}

		abort(404);
	}

	public function testEmail(Request $request)
	{
		if (auth()->guest() || !auth()->user()->is_superadmin) {
			abort(404);
		}

		$body = view()->make('front.emails.test.testContent')->render();

		try {
		    Mailer::sendEmail($body);

			return "Test email sent successfully.";
		} catch (\Exception $e) {
			\Log::error($e);

			return "Error while sending test email: " . $e->getMessage();
		}
	}

	protected function returnData($data)
	{
		if (self::$requestType == 'ajax') {
			return response()->json($data);
		} else {
			return back()->with($data);
		}
	}
}