<?php

namespace App\Http\Controllers;

use App\AdminHelper;
use App\Dbar;
use App\FileHandler;
use App\GtcmsPremium;
use App\ImageHandler;
use App\MessageManager;
use App\ModelConfig;
use App\Tools;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller {

	public static function index() {
		$defaultModel = self::getDefaultModelForUser();
		return \Redirect::to('/admin/' . $defaultModel);
	}

	public static function login() {
		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

		if (!empty($_POST) && $ajaxRequest) {
			try {
				$email = \Request::get('email');
				$password = \Request::get('password');

				if (\Auth::attempt(array('email' => $email, 'password' => $password))) {
					$allowedUserRoles = config('gtcms.allowedUserRoles');
					$user = \Auth::user();
					$userRole = $user->role;
					if (in_array($userRole, $allowedUserRoles)) {
						$defaultModel = self::getDefaultModelForUser($user);
						if ($defaultModel) {
							return \Redirect::to("/admin/" . $defaultModel . "?getIgnore_loginRedirect=true&getIgnore_isAjax=true");
						}
					} else {
						\Auth::logout();
					}
				}

				$data = array(
					'success' => false,
					'message' => trans('gtcms.incorrectUsernameOrPassword')
				);
				return \Response::json($data);
			} catch (\Exception $e) {
				return AdminHelper::handleException($e, trans('gtcms.errorHasOccurred') . ". " . trans('gtcms.pleaseRefresh') . ".");
			}
		}

		return \View::make('gtcms.admin.templates.adminLogin')->with(array('active' => false));
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
		return \Redirect::to("/admin/login");
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
			$modelConfig = new ModelConfig();
			return \View::make('gtcms.admin.elements.restricted')->with(array('active' => false, 'modelConfig' => $modelConfig));
		} else {
			return \Redirect::to("/admin");
		}
	}

	public static function handleFile($entity, $fileAction, $fileNameField, $id) {

		$data = array(
			'success' => false,
			'message' => trans('gtcms.errorHasOccurred') . ". " . trans('gtcms.pleaseTryAgain') . "."
		);

		try {
			/** @var \App\BaseModel $entity */
			$modelConfig = AdminHelper::modelExists($entity);
			/** @var \App\BaseModel $fullEntity */
			$fullEntity = $modelConfig->myFullEntityName();
			/** @var \App\BaseModel $object */

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
								$fileUrl = $object->$method('url', $fileData[0]['returnFolder']);
								$fileOriginalUrl = $object->$method('url', 'original');
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

				return \Redirect::to("/admin");
			}

			$data = array(
				'active' => false,
				'modelConfig' => new ModelConfig()
			);

			return \View::make("gtcms.admin.elements.updateLanguages")->with($data);
		} else {
			\Session::set('accessDenied', true);
			return self::restricted();
		}
	}

	public function optimize() {
		if (\Auth::user()->is_superadmin) {
			if (!empty($_POST)) {
				if (\Request::get('formSubmit') == "Proceed") {
					if (\Request::get('optimizationOption') == "clearCompiledAndOptimize") {

						Artisan::call('clear-compiled');
						Artisan::call('optimize', ['--force' => true]);

						MessageManager::setSuccess("Optimized class loader generated");
					} else if (\Request::get('optimizationOption') == "clearCompiled") {

						Artisan::call('clear-compiled');

						MessageManager::setSuccess("Compiled classes cleared");
					}
				}

				return \Redirect::to("/admin");
			}

			$data = array(
				'active' => false,
				'modelConfig' => new ModelConfig()
			);

			return \View::make("gtcms.admin.elements.optimizationOptions")->with($data);
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
				/** @var \App\BaseModel $fullClass */
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