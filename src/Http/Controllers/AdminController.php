<?php

namespace App\Http\Controllers;

use App\Classes\AdminHelper;
use App\Traits\RequestThrottler;
use App\Classes\Dbar;
use App\Classes\FileHandler;
use App\Classes\GtcmsPremium;
use App\Classes\ImageHandler;
use App\Classes\MessageManager;
use App\Classes\ModelConfig;
use App\Classes\Tools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller {

	use RequestThrottler;

	public static function index() {
		$defaultModel = self::getDefaultModelForUser();
		$append = "";
		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;
		if ($ajaxRequest) {
			$append = "?getIgnore_isAjax=true";
		}
		return \Redirect::to(AdminHelper::getCmsPrefix() . $defaultModel . $append);
	}

	public function login(Request $request) {
		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

		if (!empty($_POST)) {
			if ($ajaxRequest) {

				$maxAttempts = config('gtcms.maxLoginAttempts');
				$lockoutDuration = config('gtcms.loginLockoutDuration');
				$key = $this->throttleKey($request);

				try {
					$email = \Request::get('email');
					$password = \Request::get('password');

					if ($this->hasTooManyAttempts($request, $maxAttempts, $lockoutDuration)) {
						$data['message'] = trans('auth.throttle', ['seconds' => $this->availableIn($key)]);
						return \Response::json($data);
					}

					$this->incrementAttempts($key, $lockoutDuration);
					$attemptsLeft = $this->retriesLeft($key, $maxAttempts);

					if (!$attemptsLeft) {
						$attemptsLeftMessage = trans('auth.throttle', ['seconds' => $lockoutDuration * 60]);
					} else {
						$attemptsLeftMessage = trans_choice('auth.attemptsLeft', $attemptsLeft, ['attemptsLeft' => $attemptsLeft]);
					}

					// Trigger countdown here
					$this->hasTooManyAttempts($request, $maxAttempts, $lockoutDuration);

					if (\Auth::attempt(array('email' => $email, 'password' => $password))) {

						$this->clear($key);

						$allowedUserRoles = config('gtcms.allowedUserRoles');
						$user = \Auth::user();
						$userRole = $user->role;
						if (in_array($userRole, $allowedUserRoles)) {
							$defaultModel = self::getDefaultModelForUser($user);
							if ($defaultModel) {
								return \Redirect::to(AdminHelper::getCmsPrefix() . $defaultModel . "?getIgnore_loginRedirect=true&getIgnore_isAjax=true");
							}
						} else {
							\Auth::logout();
						}
					}

					$data = array(
						'success' => false,
						'message' => trans('gtcms.incorrectUsernameOrPassword') . ".<br>" . $attemptsLeftMessage
					);
					return \Response::json($data);
				} catch (\Exception $e) {

					$this->resetAttempts($key);

					return AdminHelper::handleException($e, trans('gtcms.errorHasOccurred') . ". " . trans('gtcms.pleaseRefresh') . ".", false, "message");
				}
			} else {
				return \Redirect::to(AdminHelper::getCmsPrefix());
			}
		}

		return \View::make('gtcms.templates.adminLogin')->with(array('active' => false));
	}

	public static function getDefaultModelForUser($user = null) {
		$defaultModel = config('gtcms.defaultModel');

		if (!$user) {
			$user = \Auth::user();
		}
		if ($user) {
			$userRole = $user->role;
			$modelConfig = AdminHelper::modelExists($defaultModel);
			if ($modelConfig->restrictedAccess && !$modelConfig->restrictedAccess->$userRole) {
				$modelConfigs = AdminHelper::modelConfigs();
				foreach ($modelConfigs as $cModelConfig) {
					if ($cModelConfig->standalone !== false && (!$cModelConfig->restrictedAccess || $cModelConfig->restrictedAccess->$userRole)) {
						return $cModelConfig->name;
					}
				}
			} else {
				return $defaultModel;
			}

			return false;
		} else {
			Dbar::error("User undefined!");
			return $defaultModel;
		}
	}

	public static function logout() {
		\Auth::logout();
		return \Redirect::to(AdminHelper::getCmsPrefix() . "login");
	}

	public static function redirectToAdmin($slug = "") {

		// Ignore all public folders, because we don't want to
		// redirect missing resources to Admin

		$publicFolders = [];
		$publicFilesAndFolders = scandir(public_path());
		foreach ($publicFilesAndFolders as $fileOrFolder) {
			if (is_dir($fileOrFolder) && $fileOrFolder != ".." && $fileOrFolder != ".") {
				$publicFolders[] = $fileOrFolder;
			}
		}

		if (in_array(\Request::segment(1), $publicFolders)) {
			\Log::error("Missing resource: " . $_SERVER["REQUEST_URI"]);
			return "";
		} else {
			\Log::notice("AdminController: Redirecting the following request back to admin: " . $_SERVER["REQUEST_URI"]);
		}

		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

		if ($ajaxRequest) {
			$data = array(
				'success' => false,
				'error' => '404 - Page not found. Please refresh the page and try again.'
			);

			return \Response::json($data);
		}

		return redirect(AdminHelper::getCmsPrefix());
	}

	public static function excelExport($modelName) {
		if (config('gtcms.premium')) {
			return GtcmsPremium::excelExport($modelName);
		} else {
			\Session::set('accessDenied', true);
			return self::restricted();
		}
	}

	public static function restricted() {
		if (\Session::get('accessDenied')) {
			\Session::forget('accessDenied');

			$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

			if ($ajaxRequest) {
				$data = [
					'success' => false,
					'message' => trans('gtcms.unauthorizedAccess')
				];

				return \Response::json($data);
			} else {
				$modelConfig = new ModelConfig();
				return \View::make('gtcms.elements.restricted')->with(array('active' => false, 'modelConfig' => $modelConfig));
			}
		} else {
			return \Redirect::to(AdminHelper::getCmsPrefix());
		}
	}

	public static function handleFile($entity, $fileAction, $fileNameField, $id) {

		$data = array(
			'success' => false,
			'message' => trans('gtcms.errorHasOccurred') . ". " . trans('gtcms.pleaseTryAgain') . "."
		);

		try {
			/** @var \App\Models\BaseModel $entity */
			$modelConfig = AdminHelper::modelExists($entity);
			/** @var \App\Models\BaseModel $fullEntity */
			$fullEntity = $modelConfig->myFullEntityName();
			/** @var \App\Models\BaseModel $object */

			// "new" when adding an image/file, "new_gtcms_entry" when deleting it, before the object is saved
			if ($id == "new" || $id == "new_gtcms_entry") {
				$object = new $fullEntity();
			} else {
				$object = $fullEntity::find($id);
			}

			$field = AdminHelper::getFieldsByParam($modelConfig, 'property', $fileNameField, true);

			if (\Request::ajax() && $modelConfig && $object && $field) {
				if (in_array($fileAction, array('uploadFile', 'uploadImage'))) {
					$fieldRules = $field->rules ? array($field->property => ModelConfig::rulesToArray($field->rules)) : array();
					$validator = \Validator::make(
						\Request::all(), $fieldRules
					);
					if ($validator->fails()) {
						$messages = $validator->getMessageBag()->getMessages();
						$message = $messages[$fileNameField][0];
						$data['message'] = $message;
					} else {
						$fileData = false;
						$action = 'add';
						if ($object->id) {
							$action = 'edit';
						}

						$input = array();
						$parentProperty = AdminHelper::standaloneCheck($modelConfig, $action, $input, $object);

						if ($fileAction == 'uploadFile' && $fileFields = AdminHelper::modelConfigHasFile($modelConfig)) {
							$fileData = FileHandler::process($modelConfig, $fileFields, $parentProperty);
						} else if ($fileAction == 'uploadImage' && $imageFields = AdminHelper::modelConfigHasImage($modelConfig)) {
							$fileData = ImageHandler::process($modelConfig, $imageFields, $parentProperty);
						}

						if (!empty($fileData[0])) {
							$object->$fileNameField = $fileData[0]['filename'];
							$method = false;
							if ($field->displayProperty && $field->displayProperty->method) {
								$method = $field->displayProperty->method;
							}
							if ($fileAction == 'uploadImage') {
								$method = $method ? $method : "image";
								$fileUrl = $object->$method('url', $fileData[0]['returnFolder'], $fileNameField);
								$fileOriginalUrl = $object->$method('url', 'original', $fileNameField);
							} else {
								$method = $method ? $method : "file";
								$fileUrl = $fileOriginalUrl = $object->$method('url', $fileNameField);
							}
							$data = array(
								'success' => true,
								'message' => false,
								'fileUrl' => $fileUrl,
								'fileOriginalUrl' => $fileOriginalUrl,
								'filename' => $fileData[0]['filename']
							);
						}
					}
				} else if ($fileAction == 'deleteFile') {
					$data['success'] = true;
					$method = "file";
					$file = true;
					if (\Request::get('imageFile')) {
						$method = "image";
						$file = false;
					}

					$fileNameValue = \Request::get('fileNameValue');

					if ($file) {
						$filePath = $object->$method('path', $fileNameField, $fileNameValue);
						if (file_exists($filePath)) {
							try {
								unlink($filePath);
							} catch (\Exception $e) {
								Dbar::error("File couldn't be deleted: " . $e->getMessage());
							}
						}
					} else {
						$folders = array();
						$modelImagesPath = public_path("img/modelImages/" . $entity);
						$scannedFolders = scandir($modelImagesPath);
						if ($scannedFolders) {
							foreach($scannedFolders as $scannedFolder) {
								if (!in_array($scannedFolder, array('.', '..'))) {
									$folders[] = $scannedFolder;
								}
							}
						}

						foreach($folders as $folder) {
							$filePath = $object->$method('path', $folder, $fileNameField, $fileNameValue);
							if (file_exists($filePath)) {
								try {
									unlink($filePath);
								} catch (\Exception $e) {
									Dbar::error("File couldn't be deleted: " . $e->getMessage());
								}
							}
						}
					}

					if ($object->id) {
						$object->$fileNameField = null;
						$object->save();
					}
				}
			}
		} catch (\Exception $e) {
			$preventException = false;
			if (in_array($e->getCode(), array(ImageHandler::DIM_ERROR, FileHandler::INVALID_FILE_ERROR))) {
				$preventException = true;
			}
			AdminHelper::handleException($e, null, $preventException);
			$data['message'] = $e->getMessage();
			$data['success'] = false;
		}

		return \Response::json($data);

	}

	public static function updateLanguages() {
		if (config('gtcms.premium') && \Auth::user()->is_superadmin) {
			if (!empty($_POST)) {
				if (\Request::get('updateLanguages') == "Proceed") {
					foreach (AdminHelper::modelConfigs() as $modelConfig) {
						GtcmsPremium::updateLanguages($modelConfig);
					}
					MessageManager::setSuccess("Languages updated");
				}

				return \Redirect::to(AdminHelper::getCmsPrefix());
			}

			$data = array(
				'active' => false,
				'modelConfig' => new ModelConfig()
			);

			return \View::make("gtcms.elements.updateLanguages")->with($data);
		} else {
			\Session::set('accessDenied', true);
			return self::restricted();
		}
	}

	public function optimize() {

		if (\Auth::user()->is_superadmin) {

			$requestData = \Request::all();

			if (!empty($_POST)) {

				$redirectUrl = \Redirect::to(AdminHelper::getCmsPrefix() . "optimize")->getTargetUrl();

				if ($requestData['formSubmit'] == "Proceed") {

					$messages = [];
					$additionalRedirectRequired = false;

					if (isset($requestData['clearCompiled'])) {
						\Artisan::call('clear-compiled');
						$messages[] = "Compiled classes cleared";
					}

					if (isset($requestData['clearCache'])) {
						\Artisan::call('config:clear');
						$messages[] = "Cached configuration options cleared";
					}

					if (isset($requestData['clearRoutes'])) {
						\Artisan::call('route:clear');
						$messages[] = "Cached routes cleared";
					}

					if (isset($requestData['optimize'])) {
						\Artisan::call('optimize', ['--force' => true]);
						$messages[] = "Optimized class loader generated";
					}

					// This one is special because it messes up the Session of the current request
					// so it will require an additional redirect to pick up all required data

					if (isset($requestData['cacheConfiguration'])) {
						\Artisan::call('config:cache');
						$messages[] = "Configuration cached";
						$additionalRedirectRequired = true;
					}

					if (isset($requestData['cacheRoutes'])) {
						\Artisan::call('route:cache');
						$messages[] = "Routes cached";
						$additionalRedirectRequired = true;
					}

					if ($messages) {
						$messages = implode("<br>", $messages);

						if ($additionalRedirectRequired) {
							$redirectUrl .= "?optimizationMessages=" . $messages;
						}

						MessageManager::setSuccess($messages);
					}
				}

				return \Redirect::to($redirectUrl);
			}

			if (isset($requestData['optimizationMessages'])) {
				MessageManager::setSuccess($requestData['optimizationMessages']);
				return \Redirect::to(AdminHelper::getCmsPrefix() . 'optimize');
			}

			$data = array(
				'active' => false,
				'modelConfig' => new ModelConfig()
			);

			return \View::make("gtcms.elements.optimizationOptions")->with($data);
		} else {
			\Session::set('accessDenied', true);
			return self::restricted();
		}
	}

	public function setNavigationSize() {
		AdminHelper::setNavigationSize(\Request::get('navigationSize'));
	}

	public function ajaxUpdate() {
		if (\Request::ajax() && \Request::get('getIgnore_isAjax')) {
			$data = array(
				'success' => false,
				'message' => false
			);
			try {
				$class = \Request::get('className');
				/** @var \App\Models\BaseModel $fullClass */
				$fullClass = ModelConfig::fullEntityName($class);
				$objectId = \Request::get('objectId');
				$property = \Request::get('property');
				$value = \Request::get('value');

				$object = $fullClass::where('id', $objectId)->first();
				if ($object) {
					$deleteRow = false;
					/*

					Custom code for when row should be deleted

					 */

					$data['deleteRow'] = $deleteRow;

					if (!$value) {
						$value = null;
					}
					$object->$property = $value;
					$object->save();
					$data['success'] = true;
				} else {
					$data['message'] = "Object of type " . $fullClass . " with ID " . $objectId . " could not be found.";
				}

				return \Response::json($data);
			} catch (\Exception $e) {
				return AdminHelper::handleException($e);
			}
		}

		\App::abort(404);
	}

}