<?php

namespace App\Http\Controllers;

use App\Classes\AdminEntityHandler;
use App\Classes\AdminHelper;
use App\Classes\AdminHistoryManager;
use App\Classes\Dbar;
use App\Classes\Front;
use App\Classes\GtcmsPremium;
use App\Classes\Tools;
use App\Models\GtcmsSetting;

class AdminEntityController extends Controller {

	private static $modelConfig = NULL;
	private static $entity = NULL;

	public static function handleAction($entity, $action = NULL, $id = NULL) {

		if (!$action) {
			$action = "index";
		}

		try {
			if (self::$modelConfig = AdminHelper::modelExists($entity)) {

				$user = \Auth::user();
				$role = $user->role;

				if ((self::$modelConfig->restrictedAccess && $action != 'ajaxSearch' && !self::$modelConfig->restrictedAccess->$role) ||
					(self::$modelConfig->restrictedToSuperadmin && !$user->is_superadmin))
				{
					\Session::put('accessDenied', true);
					return \Redirect::route('restricted', ['getIgnore_isAjax' => \Request::get('getIgnore_isAjax')]);
				}

				self::$entity = $entity;
				$loginRedirect = \Request::get('getIgnore_loginRedirect') ? true : false;

				$settings = false;
				if ($entity == "GtcmsSetting") {
					if ($action != "index") {
						throw new \Exception("Invalid action for Settings!");
					}
					if (!is_null($id)) {
						throw new \Exception("You cannot pass ID for Settings!");
					}
					$action = "edit";
					$settings = true;
				}

				if (in_array($action, array('edit', 'view', 'delete'))) {
					return self::$action($id, false, $settings);
				} else if (in_array($action, array('add', 'index', 'ajaxMove'))) {
					return self::$action($loginRedirect);
				} else if (in_array($action, array('ajaxSearch'))) {
					return self::$action();
				} else {
					throw new \Exception ("Invalid action!");
				}

			} elseif ($action == 'ajaxSearch') {
				self::$entity = $entity;
				return self::$action();
			} else {
				throw new \Exception ("Model doesn't exist: " . $entity);
			}
		} catch (\Exception $e) {
			return AdminHelper::handleException($e);
		}
	}

	public static function index($loginRedirect) {

		$startUrl = "";
		AdminHistoryManager::clearHistory();
		if ($loginRedirect) {
			$defaultModel = AdminController::getDefaultModelForUser();
			$startUrl = AdminHelper::getCmsPrefix() . $defaultModel;
			AdminHistoryManager::addHistoryLink($startUrl, $defaultModel);
		} else {
			AdminHistoryManager::addHistoryLink();
		}

		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;
		$getSearchResults = isset($_GET['getIgnore_getSearchResults']) && $ajaxRequest ? true : false;
		$searchIsOpen = isset($_GET['getIgnore_searchIsOpen']) && $ajaxRequest ? true : false;

		/** @var \App\Models\BaseModel $entity */
		$entity = self::$entity;
		/** @var \App\Models\BaseModel $fullEntity */
		$fullEntity = self::$modelConfig->myFullEntityName();
		$indexType = self::$modelConfig->index ? ucfirst(self::$modelConfig->index) : '';
		$searchParams = array();
		$searchDataWithFieldValues = array();
		$ordering = Tools::getSearchAndOrderGets(false, false, false, true);

		if ($indexType == 'Tree') {
			$objects = $fullEntity::where('depth', 0)->orderBy('position', 'asc')->get();
		} else {
			$searchDataWithFieldValues = AdminHelper::getSearchData(self::$modelConfig, true);
			$input = array();
			AdminHelper::standaloneCheck(self::$modelConfig, 'index', $input);
			$orderAndDirection = AdminHelper::getOrderParams(self::$modelConfig);
			$objects = $fullEntity::searchResultsEntities(self::$modelConfig)
				->where(function($query) {
					if (self::$modelConfig->name == 'User' && !\Auth::user()->is_superadmin) {
						$query->where('is_superadmin', 0);
					}
				})->orderBy($orderAndDirection['orderBy'], $orderAndDirection['direction'])
				->paginate(self::$modelConfig->perPage);
		}

		$addEntity = true;
		if ($indexType == 'Tree' && self::$modelConfig->maxFirstLevelItems && ($fullEntity::where('depth', 0)->count() >= self::$modelConfig->maxFirstLevelItems)) {
			$addEntity = false;
		}

		$viewData = array(
			'active' => $entity,
			'modelConfig' => self::$modelConfig,
			'objects' => $objects,
			'addEntity' => $addEntity,
			'searchParams' => $searchParams,
			'searchDataWithFieldValues' => $searchDataWithFieldValues,
			'ordering' => $ordering,
			'ajaxRequest' => $ajaxRequest,
			'loginRedirect' => $loginRedirect,
			'getSearchResults' => $getSearchResults,
			'searchIsOpen' => $searchIsOpen,
			'indexType' => $indexType
		);

		if ($ajaxRequest) {
			$view = \View::make('gtcms.elements.index'.$indexType.'Content')->with($viewData);

			if ($loginRedirect) {
				$data = array(
					'success' => true,
					'setUrl' => $startUrl,
					'view' => $view->render()
				);
				return \Response::json($data);
			}

			$data = array(
				'success' => true,
				'view' => $view->render(),
				'setHistoryLinks' => false,
				'modelConfigName' => self::$modelConfig->name,
				'indexView' => true,
				'setUrl' => AdminHelper::getCmsPrefix() . $entity . Tools::getGets(),
				'getParams' => Tools::getGets(),
				'entity' => $entity,
				'searchDataWithFieldValues' => $searchDataWithFieldValues || $ordering ? true : false
			);

			return \Response::json($data);
		} else {
			return \View::make('gtcms.elements.index')->with($viewData);
		}

	}

	public static function add() {
		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

		/** @var \App\Models\BaseModel $entity */
		$entity = self::$entity;

		if ($ajaxRequest) {
			return self::edit("new", AdminHelper::getCmsPrefix() . $entity . "/edit/new" . Tools::getGets());
		}

		\App::abort(404);
	}

	public static function edit($id, $historyLink = false, $settings = false) {

		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

		/** @var \App\Models\BaseModel $entity */
		$entity = self::$entity;
		/** @var \App\Models\BaseModel $fullEntity */
		$fullEntity = self::$modelConfig->myFullEntityName();

		if (config('gtcms.premium') && $entity == "GtcmsSetting") {
			$object = GtcmsSetting::createSettingsObject();
		} else {
			/** @var \App\Models\BaseModel $object */
			if ($id == "new") {
				$object = new $fullEntity();
			} else {
				$object = $fullEntity::find($id);
			}
		}

		$validator = NULL;
		$action = $object->id ? 'edit' : 'add';
		$quickEdit = false;
		if (config('gtcms.premium')) {
			$quickEdit = GtcmsPremium::getQuickEditVar();
		}

		if ($action == 'add' && !$object->isAddable()) {
			\Session::put('accessDenied', true);
			return \Redirect::route('restricted', ['getIgnore_isAjax' => \Request::get('getIgnore_isAjax')]);
		}

		if ($action == 'edit' && !$object->isEditable()) {
			\Session::put('accessDenied', true);
			return \Redirect::route('restricted', ['getIgnore_isAjax' => \Request::get('getIgnore_isAjax')]);
		}

		$sideTablePaginationResults =
			\Request::get('getIgnore_tableType') == 'sideTable' &&
			$ajaxRequest &&
			$action == "edit"
				? true : false;

		if ($settings) {
			AdminHistoryManager::clearHistory();
		} else {
			AdminHistoryManager::addHistoryLink($historyLink, self::$entity, true, $sideTablePaginationResults);
		}

		if ($sideTablePaginationResults) {
			return self::sideTablePaginationResults($object);
		}

		if (!empty($_POST) && $ajaxRequest) {
			$validator = \Validator::make(
				\Request::all(), AdminHelper::validationRules(self::$modelConfig, $object, $quickEdit)
			);
			if ($validator->fails()) {
				$message = trans('gtcms.validationFailed');
				$data = array(
					'success' => false,
					'errors' => $validator->getMessageBag()->getMessages(),
					'errorMsg' => $message,
					'quickEdit' => $quickEdit
				);
				return \Response::json($data);
			} else {
				if ($entity == "GtcmsSetting") {
					AdminEntityHandler::editSettings(self::$modelConfig);
				} else {
					$object = AdminEntityHandler::edit($object, self::$modelConfig);
				}

				return self::ajaxRedirect($object, $action, $quickEdit);
			}
		}

		$viewData = array(
			'active' => $entity,
			'modelConfig' => self::$modelConfig,
			'object' => $object,
			'ajaxRequest' => $ajaxRequest,
			'action' => $action,
			'quickEdit' => $quickEdit
		);

		$setUrl = false;
		if (!$settings) {
			$setUrl = AdminHelper::getCmsPrefix() . self::$modelConfig->name . '/edit/' . ($object->id ? $object->id : 'new') . Tools::getGets();
		}

		if ($ajaxRequest) {
			$view = \View::make('gtcms.elements.editContent')->with($viewData);
			$data = array(
				'success' => true,
				'view' => $view->render(),
				'setUrl' => $setUrl,
				'history' => AdminHistoryManager::getHistory(),
				'setHistoryLinks' => true,
				'modelConfigName' => self::$modelConfig->name,
				'replaceCurrentHistory' => false
			);

			return \Response::json($data);
		} else {
			return \View::make('gtcms.elements.edit')->with($viewData);
		}

	}

	public static function sideTablePaginationResults($object) {

		$relatedModelName = \Request::get('getIgnore_modelName');
		$relatedModelConfig = AdminHelper::modelExists($relatedModelName);
		/** @var \App\Models\BaseModel $object */
		$configInParent = $object->relatedModelConfiguration($relatedModelConfig->name);
		$method = $configInParent->method;

		$relatedObjects = $object->$method()->orderBy($configInParent->orderBy, $configInParent->direction)->paginate($configInParent->perPage, ['*'], $configInParent->name . "Page");;
		$objectsView = Front::drawObjectTable($relatedObjects, $relatedModelConfig, 'sideTable', '?' . self::$modelConfig->id . '=' . $object->id, false, false, false, true);
		$setUrl = AdminHelper::getCmsPrefix() . self::$modelConfig->name . '/edit/' . ($object->id ? $object->id : 'new') . Tools::getGets();

		$returnData = array(
			'success' => true,
			'setUrl' => $setUrl,
			'view' => $objectsView,
			'sideTablePagination' => true
		);

		return \Response::json($returnData);
	}

	public static function delete($id) {
		/** @var \App\Models\BaseModel $entity */
		$entity = self::$modelConfig->myFullEntityName();
		/** @var \App\Models\BaseModel $object */
		$object = $entity::find($id);

		if (!$object->isDeletable()) {
			\Session::put('accessDenied', true);
			return \Redirect::route('restricted', ['getIgnore_isAjax' => \Request::get('getIgnore_isAjax')]);
		}

		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;

		if ($ajaxRequest) {
			try {
				if (config('gtcms.allowDelete')) {
					$object->delete();
				}
				$data = array(
					'success' => true
				);
				return \Response::json($data);
			} catch (\Exception $e) {
				return AdminHelper::handleException($e);
			}
		}

		\App::abort(404);
	}

	public static function ajaxMove() {

		$objectId = isset($_GET['objectId']) ? $_GET['objectId'] : false;
		/** @var \App\Models\BaseModel $entity */
		$entity = self::$modelConfig->myFullEntityName();
		$ajaxRequest = \Request::ajax() && \Request::get('getIgnore_isAjax') ? true : false;
		$message = false;
		$success = false;

		if ($ajaxRequest) {
			if ($objectId) {
				/** @var \App\Models\BaseModel $object */
				$object = $entity::find($objectId);
				if ($object) {
					if (isset($_GET['treeStructure']) && $_GET['treeStructure'] == 'true') {
						$params = array(
							'modelConfig' => self::$modelConfig,
							'parentId' => isset($_GET['parentId']) ? ($_GET['parentId'] == 'false' ? false : $_GET['parentId']) : false,
							'position' => isset($_GET['position']) ? $_GET['position'] : false,
						);

						try {
							$success = $object->moveInTree($params);
						} catch (\Exception $e) {
							$success = false;
							$message = $e->getMessage();
						}


					} else {
						$params = array(
							'modelConfig' => self::$modelConfig,
							'parentName' => isset($_GET['parentName']) ? $_GET['parentName'] : false,
							'aboveItemId' => isset($_GET['aboveItemId']) ? $_GET['aboveItemId'] : false,
							'belowItemId' => isset($_GET['belowItemId']) ? $_GET['belowItemId'] : false,
							'direction' => isset($_GET['direction']) ? $_GET['direction'] : false
						);

						try {
							$success = $object->move($params);
						} catch (\Exception $e) {
							$success = false;
							$message = $e->getMessage();
						}
					}

				}
			}

			$data = array(
				'success' => $success,
				'message' => $message
			);

			return \Response::json($data);
		}

		\App::abort(404);
	}

	public static function ajaxSearch() {

		$return = null;
		if (config('gtcms.premium')) {
			$return = GtcmsPremium::ajaxSearch(self::$modelConfig);
		}

		if (!is_null($return)) {
			return $return;
		}

		\App::abort(404);
	}

	private static function ajaxRedirect($object = false, $action = false, $quickEdit = false) {

		$data = array(
			'success' => true,
			'returnToParent' => false,
			'quickEdit' => $quickEdit,
			'objectRow' => false,
			'objectId' => false
		);

		if (!self::$modelConfig->relatedModels) {
			$data['returnToParent'] = true;
		}

		if (config('gtcms.preventRedirectOnSave') || $quickEdit) {
			$data['returnToParent'] = false;
		}

		/** @var \App\Models\BaseModel $object */

		if (config('gtcms.premium') && $quickEdit) {
			GtcmsPremium::setQuickEditReturnData($data, $object, self::$modelConfig);
		}

		// If object has just been successfully added
		if ($action == 'add' && !$data['returnToParent'] && self::$modelConfig->name != "GtcmsSetting") {
			$printProperty = self::$modelConfig->printProperty;
			$data['replaceCurrentHistory'] = array(
				'modelName' => self::$modelConfig->hrName,
				'objectName' => $printProperty ? $object->$printProperty : false
			);

			$fullUrl = str_replace("/edit/new", "/edit/" . $object->id, \Tools::fullUrl());
			$data['replaceUrl'] = $fullUrl;
			$data['objectId'] = $object->id;

			AdminHistoryManager::replaceAddLink($fullUrl, self::$modelConfig->name);
		}

		return \Response::json($data);
	}

}