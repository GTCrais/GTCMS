<?php

namespace App\Http\Controllers;

use App\Classes\AdminEntityHandler;
use App\Classes\AdminHelper;
use App\Classes\AdminHistoryManager;
use App\Classes\Dbar;
use App\Classes\Front;
use App\Classes\GtcmsPremium;
use App\Classes\ModelConfig;
use App\Classes\Tools;
use App\Models\GtcmsSetting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminEntityController extends Controller
{
	private static $modelConfig = NULL;
	private static $entity = NULL;

	public function handleAction(Request $request, $entity, $action = NULL, $id = NULL)
	{
		if (!$action) {
			$action = "index";
		}

		try {
			if (self::$modelConfig = AdminHelper::modelExists($entity)) {

				$user = auth()->user();
				$role = $user->role;

				if ((self::$modelConfig->restrictedAccess && $action != 'ajaxSearch' && !self::$modelConfig->restrictedAccess->$role) ||
					(self::$modelConfig->restrictedToSuperadmin && !$user->is_superadmin))
				{
					session(['accessDenied' => true]);

					return redirect()->route('restricted', ['getIgnore_isAjax' => $request->get('getIgnore_isAjax')]);
				}

				self::$entity = $entity;
				$loginRedirect = $request->get('getIgnore_loginRedirect') ? true : false;

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

				if (in_array($action, ['edit', 'view', 'delete'])) {
					return $this->$action($request, $id, false, $settings);
				} else if (in_array($action, ['add', 'index', 'ajaxMove'])) {
					return $this->$action($request, $loginRedirect);
				} else if (in_array($action, ['ajaxSearch'])) {
					return $this->$action($request);
				} else {
					throw new \Exception("Invalid action!");
				}

			} elseif ($action == 'ajaxSearch') {
				self::$entity = $entity;

				return $this->$action($request);
			} else {
				throw new \Exception("Model doesn't exist: " . $entity);
			}
		} catch (\Exception $e) {
			return AdminHelper::handleException($e);
		}
	}

	private function index(Request $request, $loginRedirect)
	{
		$startUrl = "";
		AdminHistoryManager::clearHistory();
		if ($loginRedirect) {
			$defaultModel = AdminController::getDefaultModelForUser();
			$startUrl = AdminHelper::getCmsPrefix() . $defaultModel;
			AdminHistoryManager::addHistoryLink($startUrl, $defaultModel);
		} else {
			AdminHistoryManager::addHistoryLink();
		}

		$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;
		$getSearchResults = isset($_GET['getIgnore_getSearchResults']) && $ajaxRequest ? true : false;
		$searchIsOpen = isset($_GET['getIgnore_searchIsOpen']) && $ajaxRequest ? true : false;

		/** @var \App\Models\BaseModel $entity */
		$entity = self::$entity;
		/** @var \App\Models\BaseModel $fullEntity */
		$fullEntity = self::$modelConfig->myFullEntityName();
		$indexType = self::$modelConfig->index ? ucfirst(self::$modelConfig->index) : '';
		$searchParams = [];
		$searchDataWithFieldValues = [];
		$ordering = Tools::getSearchAndOrderGets(false, false, false, true);

		if ($indexType == 'Tree') {
			$objects = $fullEntity::where('depth', 0)->orderBy('position', 'asc')->get();
		} else {
			$searchDataWithFieldValues = AdminHelper::getSearchData(self::$modelConfig, true);
			$input = [];
			AdminHelper::standaloneCheck(self::$modelConfig, 'index', $input);

			$objectsQuery = $fullEntity::searchResultsEntities(self::$modelConfig)
				->where(function ($query) {
					if (self::$modelConfig->name == 'User' && !auth()->user()->is_superadmin) {
						$query->where('is_superadmin', 0);
					}
				});

			$countQuery = clone $objectsQuery;
			$objectCount = $countQuery->count();

			$orderAndDirection = self::$modelConfig->getOrderParams();
			$page = filter_var($request->get('page'), FILTER_VALIDATE_INT) ?: 1;
			$limit = self::$modelConfig->perPage;
			$offset = ($page - 1) * $limit;

			$objects = $objectsQuery
				->orderBy($orderAndDirection['orderBy'], $orderAndDirection['direction'])
				->limit($limit)
				->offset($offset)
				->get();

			$objects = new LengthAwarePaginator($objects, $objectCount, $limit, $page, [
				'path' => $request->url()
			]);
		}

		$addEntity = true;
		if ($indexType == 'Tree' && self::$modelConfig->maxFirstLevelItems && ($fullEntity::where('depth', 0)->count() >= self::$modelConfig->maxFirstLevelItems)) {
			$addEntity = false;
		}

		$viewData = [
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
		];

		if ($ajaxRequest) {
			$view = view()->make('gtcms.elements.index' . $indexType . 'Content')->with($viewData);

			if ($loginRedirect) {
				$data = [
					'success' => true,
					'setUrl' => $startUrl,
					'view' => $view->render()
				];

				return response()->json($data);
			}

			$data = [
				'success' => true,
				'view' => $view->render(),
				'setHistoryLinks' => false,
				'modelConfigName' => self::$modelConfig->name,
				'indexView' => true,
				'setUrl' => AdminHelper::getCmsPrefix() . $entity . Tools::getGets(),
				'getParams' => Tools::getGets(),
				'entity' => $entity,
				'searchDataWithFieldValues' => $searchDataWithFieldValues || $ordering ? true : false
			];

			if ($indexType != 'Tree') {
				$data['paginationFrom'] = $objects->firstItem();
				$data['paginationTo'] = $objects->lastItem();
				$data['paginationTotal'] = $objects->total();
			}

			return response()->json($data);
		}

		return view()->make('gtcms.elements.index')->with($viewData);
	}

	private function add(Request $request)
	{
		$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;

		/** @var \App\Models\BaseModel $entity */
		$entity = self::$entity;

		if ($ajaxRequest) {
			return $this->edit($request, "new", AdminHelper::getCmsPrefix() . $entity . "/edit/new" . Tools::getGets());
		}

		abort(404);
	}

	private function edit(Request $request, $id, $historyLink = false, $settings = false)
	{
		if ($request->get('getIgnore_loadRelatedModels')) {
			return $this->loadRelatedModels($request, $id);
		}

		$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;

		/** @var \App\Models\BaseModel $entity */
		$entity = self::$entity;
		/** @var \App\Models\BaseModel $fullEntity */
		$fullEntity = self::$modelConfig->myFullEntityName();

		if ($entity == "GtcmsSetting") {
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
			session(['accessDenied' => true]);

			return redirect()->route('restricted', ['getIgnore_isAjax' => $request->get('getIgnore_isAjax')]);
		}

		if ($action == 'edit' && !$object->isEditable()) {
			session(['accessDenied' => true]);

			return redirect()->route('restricted', ['getIgnore_isAjax' => $request->get('getIgnore_isAjax')]);
		}

		$sideTablePaginationResults =
			$request->get('getIgnore_tableType') == 'sideTable' &&
			$request->get('getIgnore_modelName') &&
			$ajaxRequest &&
			$action == "edit"
				? true : false;

		if ($settings) {
			AdminHistoryManager::clearHistory();
		} else {
			AdminHistoryManager::addHistoryLink($historyLink, self::$entity, true, $sideTablePaginationResults);
		}

		if ($sideTablePaginationResults) {
			return $this->sideTablePaginationResults($request, $object);
		}

		if (!empty($_POST) && $ajaxRequest) {
			$validator = \Validator::make(
				$request->all(), AdminHelper::validationRules(self::$modelConfig, $object, $quickEdit)
			);
			if ($validator->fails()) {
				$message = trans('gtcms.validationFailed');
				$data = [
					'success' => false,
					'errors' => $validator->getMessageBag()->getMessages(),
					'errorMsg' => $message,
					'quickEdit' => $quickEdit
				];

				return response()->json($data);
			} else {
				if ($entity == "GtcmsSetting") {
					AdminEntityHandler::editSettings(self::$modelConfig);
				} else {
					$object = AdminEntityHandler::edit($object, self::$modelConfig);
				}

				return $this->ajaxRedirect($request, $object, $action, $quickEdit);
			}
		}

		$viewData = [
			'active' => $entity,
			'modelConfig' => self::$modelConfig,
			'object' => $object,
			'ajaxRequest' => $ajaxRequest,
			'action' => $action,
			'quickEdit' => $quickEdit
		];

		$setUrl = false;
		if (!$settings) {
			$setUrl = AdminHelper::getCmsPrefix() . self::$modelConfig->name . '/edit/' . ($object->id ? $object->id : 'new') . Tools::getGets();
		}

		if ($ajaxRequest) {
			$view = view()->make('gtcms.elements.editContent')->with($viewData);
			$data = [
				'success' => true,
				'view' => $view->render(),
				'setUrl' => $setUrl,
				'history' => AdminHistoryManager::getHistory(),
				'setHistoryLinks' => true,
				'modelConfigName' => self::$modelConfig->name,
				'replaceCurrentHistory' => false
			];

			return response()->json($data);
		}

		return view()->make('gtcms.elements.edit')->with($viewData);
	}

	private function sideTablePaginationResults(Request $request, $object)
	{
		$relatedModelName = $request->get('getIgnore_modelName');
		$relatedModelConfig = AdminHelper::modelExists($relatedModelName);
		/** @var \App\Models\BaseModel $object */
		$configInParent = $object->relatedModelConfiguration($relatedModelConfig->name);
		$method = $configInParent->method;

		$relatedObjects = $object->$method()->orderBy($configInParent->orderBy, $configInParent->direction)->paginate($configInParent->perPage, ['*'], $configInParent->name . "Page");;
		$objectsView = Front::drawObjectTable($relatedObjects, $relatedModelConfig, 'sideTable', [
			'parentIdProperty' => self::$modelConfig->id,
			'parentIdValue' => $object->id,
			'loadSideTablePaginationResults' => true
		]);
		$setUrl = AdminHelper::getCmsPrefix() . self::$modelConfig->name . '/edit/' . ($object->id ? $object->id : 'new') . Tools::getGets();

		$returnData = [
			'success' => true,
			'setUrl' => $setUrl,
			'view' => $objectsView,
			'sideTablePagination' => true
		];

		return response()->json($returnData);
	}

	public function loadRelatedModels(Request $request, $parentModelId)
	{
		$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;

		if ($ajaxRequest) {
			$data = [
				'success' => true,
				'view' => null
			];

			$parentModelClass = self::$entity;
			$relatedModelClass = $request->get('getIgnore_loadRelatedModel');

			try {
				$modelConfig = AdminHelper::modelExists($parentModelClass);
				$parentModel = ModelConfig::fullEntityName($parentModelClass);
				$object = $parentModel::find($parentModelId);

				if (!$object->isEditable()) {
					session(['accessDenied' => true]);

					return redirect()->route('restricted', ['getIgnore_isAjax' => $request->get('getIgnore_isAjax')]);
				}

				if (!$relatedModelClass) {
					$view = view('gtcms.elements.editContentRelatedModels', [
						'modelConfig' => $modelConfig,
						'displayModel' => true,
						'object' => $object,
						'action' => 'edit',
						'ignorePage' => true
					])->render();

					$data['view'] = $view;
				} else {
					$foundRelatedModelConfig = false;

					foreach ($modelConfig->relatedModels as $relatedModel) {
						if ($relatedModel->name == $relatedModelClass) {
							$foundRelatedModelConfig = $relatedModel;
							break;
						}
					}

					if ($foundRelatedModelConfig) {
						$view = view('gtcms.elements.editContentRelatedModel', [
							'modelConfig' => $modelConfig,
							'relatedModel' => $foundRelatedModelConfig,
							'displayModel' => true,
							'object' => $object,
							'action' => 'edit',
							'ignorePage' => true
						])->render();

						$data['view'] = $view;
					} else {
						$data['success'] = false;
					}
				}
			} catch (\Exception $e) {
				\Log::error($e);
				$data['success'] = false;
				$data['message'] = $e->getMessage();
			}

			if ($data['success'] && $modelConfig->relatedModels) {
				$ignorePages = [];
				foreach ($modelConfig->relatedModels as $relatedModelConfig) {
					$ignorePages[$relatedModelConfig->name . "Page"] = null;
				}

				$data['replaceUrl'] = $request->url() . Tools::getGets($ignorePages);
			}

			return response()->json($data);
		}

		return redirect()->to(AdminHelper::getCmsPrefix());
	}

	private function delete(Request $request, $id)
	{
		/** @var \App\Models\BaseModel $entity */
		$entity = self::$modelConfig->myFullEntityName();
		/** @var \App\Models\BaseModel $object */
		$object = $entity::find($id);

		if (!$object->isDeletable()) {
			session(['accessDenied' => true]);

			return redirect()->route('restricted', ['getIgnore_isAjax' => $request->get('getIgnore_isAjax')]);
		}

		$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;

		if ($ajaxRequest) {
			try {
				if (config('gtcms.allowDelete')) {
					$object->delete();
				}
				$data = [
					'success' => true
				];

				return response()->json($data);
			} catch (\Exception $e) {
				return AdminHelper::handleException($e);
			}
		}

		abort(404);
	}

	private function ajaxMove(Request $request)
	{
		$objectId = isset($_GET['objectId']) ? $_GET['objectId'] : false;
		/** @var \App\Models\BaseModel $entity */
		$entity = self::$modelConfig->myFullEntityName();
		$ajaxRequest = $request->ajax() && $request->get('getIgnore_isAjax') ? true : false;
		$message = false;
		$success = false;

		if ($ajaxRequest) {
			if ($objectId) {
				/** @var \App\Models\BaseModel $object */
				$object = $entity::find($objectId);
				if ($object) {
					if (isset($_GET['treeStructure']) && $_GET['treeStructure'] == 'true') {
						$params = [
							'modelConfig' => self::$modelConfig,
							'parentId' => isset($_GET['parentId']) ? ($_GET['parentId'] == 'false' ? false : $_GET['parentId']) : false,
							'position' => isset($_GET['position']) ? $_GET['position'] : false,
						];

						try {
							$success = $object->moveInTree($params);
						} catch (\Exception $e) {
							$success = false;
							$message = $e->getMessage();
						}


					} else {
						$params = [
							'modelConfig' => self::$modelConfig,
							'parentName' => isset($_GET['parentName']) ? $_GET['parentName'] : false,
							'aboveItemId' => isset($_GET['aboveItemId']) ? $_GET['aboveItemId'] : false,
							'belowItemId' => isset($_GET['belowItemId']) ? $_GET['belowItemId'] : false,
							'direction' => isset($_GET['direction']) ? $_GET['direction'] : false
						];

						try {
							$success = $object->move($params);
						} catch (\Exception $e) {
							$success = false;
							$message = $e->getMessage();
						}
					}

				}
			}

			$data = [
				'success' => $success,
				'message' => $message
			];

			return response()->json($data);
		}

		abort(404);
	}

	private function ajaxSearch(Request $request)
	{
		$return = null;
		if (config('gtcms.premium')) {
			$return = GtcmsPremium::ajaxSearch(self::$modelConfig);
		}

		if (!is_null($return)) {
			return $return;
		}

		abort(404);
	}

	private function ajaxRedirect(Request $request, $object = false, $action = false, $quickEdit = false)
	{
		$data = [
			'success' => true,
			'returnToParent' => false,
			'quickEdit' => $quickEdit,
			'objectRow' => false,
			'objectId' => false
		];

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
			$data['replaceCurrentHistory'] = [
				'modelName' => self::$modelConfig->hrName,
				'objectName' => $printProperty ? $object->$printProperty : false
			];

			$fullUrl = str_replace("/edit/new", "/edit/" . $object->id, \Tools::fullUrl());
			$data['replaceUrl'] = $fullUrl;
			$data['objectId'] = $object->id;

			AdminHistoryManager::replaceAddLink($fullUrl, self::$modelConfig->name);
		}

		if (self::$modelConfig->reloadRelatedModelsOnSave && !$quickEdit) {
			$data['reloadRelated'] = [
				'model' => self::$modelConfig->name,
				'id' => $object->id
			];
		}

		return response()->json($data);
	}
}