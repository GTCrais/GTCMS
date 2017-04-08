<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model as Eloquent;

use App\Classes\AdminEntityHandler;
use App\Classes\AdminHelper;
use App\Classes\Dbar;
use App\Classes\GtcmsPremium;
use App\Classes\ModelConfig;

class BaseModel extends \Eloquent
{
	public static $modelConfig = false;

	public function __get($key)
	{
		if (config('gtcms.premium')) {
			$fullEntity = get_called_class();
			$reflection = new \ReflectionClass($fullEntity);
			$entity = $reflection->getShortName();
			$data = GtcmsPremium::getLangDependentPropertyValue($this, $key, $entity);
			if ($data['valueFound']) {
				return $data['value'];
			}
		}

		return parent::__get($key);
	}

	public static function completeEntities(ModelConfig $modelConfig)
	{
		$m2mData = $modelConfig->getManyToManyRelationData();
		if (!empty($m2mData['relationData'])) {
			$groupBy = $m2mData['table'].".".$m2mData['idProperty'];
			$select = array(
				$m2mData['table'] . ".*",
				$groupBy
			);

			$joins = array();

			foreach ($m2mData['relationData'] as $relationData) {
				$select[] = $relationData['relationTable']
					. ".".$relationData['relatedModelId']
					. " AS ".$relationData['relatedModelId'];
				$joins[] = array(
					'table' => $relationData['relationTable'],
					'key1' => $m2mData['table'] . ".id",
					'key2' => $relationData['relationTable'] . "." . $relationData['relationId']
				);
			}

			$return = static::select($select);

			foreach ($joins as $leftJoin) {
				$return->leftJoin($leftJoin['table'], $leftJoin['key1'], "=", $leftJoin['key2']);
			}

			$return->groupBy($groupBy);
			return $return;
		}

		return static::select('*');
	}

	public static function completeEntity($modelConfig, $id, $field = 'id')
	{
		return static::completeEntities($modelConfig)->where($field, $id);
	}

	public static function searchResultsEntities(ModelConfig $modelConfig)
	{
		return static::completeEntities($modelConfig)->where(function($query) use ($modelConfig) {
			$searchDataArray = AdminHelper::getSearchData($modelConfig);
			if ($searchDataArray) {
				foreach ($searchDataArray as $searchData) {
					if ($searchData['searchConfig']['type'] == 'standard') {
						if ($searchData['searchConfig']['match'] == 'exact') {
							if (config('gtcms.premium') && isset($searchData['langDependent']) && $searchData['langDependent']) {
								$query->where(function($query2) use ($searchData){
									foreach (config('gtcmslang.languages') as $lang) {
										$query2->orWhere($searchData['dbProperty']."_".$lang, $searchData['value']);
									}
								});
							} else {
								$value = $modelConfig->getDatabasePropertyValue($searchData['property'], $searchData['value']);
								$eqSign = "=";
								if ($searchData['fieldFrom']) {
									$eqSign = ">=";
								} else if ($searchData['fieldTo']) {
									$eqSign = "<=";
								}
								$query->where($searchData['dbProperty'], $eqSign, $value);
							}
						} else if ($searchData['searchConfig']['match'] == 'pattern') {
							if (config('gtcms.premium') && isset($searchData['langDependent']) && $searchData['langDependent']) {
								$query->where(function($query2) use ($searchData){
									foreach (config('gtcmslang.languages') as $lang) {
										$query2->orWhere($searchData['dbProperty'] . "_" . $lang, 'LIKE', '%' . $searchData['value'] . '%');
									}
								});
							} else {
								$query->where($searchData['dbProperty'], 'LIKE', '%' . $searchData['value'] . '%');
							}
						}
					} else if ($searchData['searchConfig']['type'] == 'exception') {
						//custom code here
					}
				}
			}
		});
	}

	public function getIndexDateAttribute($format = NULL)
	{
		return self::formatDate($this->created_at, $format);
	}

	public static function formatDate($date, $format = NULL)
	{
		if (!$date) return "";

		if (is_null($format)) {
			$format = config('gtcms.defaultDateFormat');
		}

		return date($format, strtotime($date));
	}

	public static function create(array $data = [])
	{
		$fullEntity = get_called_class();
		$reflection = new \ReflectionClass($fullEntity);
		$entity = $reflection->getShortName();

		$modelConfig = AdminHelper::modelExists($entity);
		if (!$modelConfig) {
			throw new \Exception("Model config for entity " . $entity . " doesn't exist.");
		}

		if ($modelConfig->index == 'tree') {
			$depth = 0;
			$parentData = (new static)->getParentData();
			if ($parentData['parentObject']) {
				$depth = ($parentData['parentObject']->depth) + 1;
			}

			$maxPos = static::getMaxPositionInTree('position', $parentData);
			$data['depth'] = $depth;
			$data['position'] = $maxPos + 1;

			if ($parentData['parentIdProperty'] && $parentData['parentId']) {
				$data[$parentData['parentIdProperty']] = $parentData['parentId'];
			} else if ($depth > 0) {
				throw new \Exception('Tree structure view: $parentData["parentIdProperty"] OR $parentData["parentId"] is false!');
			}
		} else {
			if ($modelConfig->position) {
				$data['position'] = static::getNextTablePosition();
			}
		}

		return (new static)->newQuery()->create($data);
	}

	public static function getMaxPositionInTree($positionProperty = 'position', $parentData = NULL)
	{
		/** @var BaseModel $model */
		$model = get_called_class();
		$object = new static;
		$parentId = false;
		if (is_null($parentData)) {
			$parentData = $object->getParentData();
		}

		$parentIdProperty = NULL;
		if ($parentData) {
			$parentId = $parentData['parentId'];
			$parentIdProperty = $parentData['parentIdProperty'];
		}

		if ($parentIdProperty) {
			$object = $model::where($parentIdProperty, $parentId)->orderBy($positionProperty, 'desc')->first();
			if ($object) {
				return $object->$positionProperty;
			} else {
				return 0;
			}
		} else {
			$object = $model::where('id', '>', 0)->orderBy($positionProperty, 'desc')->first();
			if ($object) {
				return $object->$positionProperty;
			} else {
				return 0;
			}
		}
	}

	private static function getNextTablePosition()
	{
		return number_format(microtime(true), 3, "", "");
	}

	public static function getPositionPropertyAndValueFromParentData($parentData, $modelConfig)
	{
		$positionProperties = array();
		if ($parentData['allParents']) {
			foreach ($parentData['allParents'] as $parentIdProperty => $parentId) {
				$parentModelConfig = AdminHelper::modelExists($parentIdProperty, 'id');
				if ($parentModelConfig && $parentModelConfig->relatedModels) {
					foreach ($parentModelConfig->relatedModels as $relatedModel) {
						if ($relatedModel->name == $modelConfig->name) {
							if ($relatedModel->position) {
								$positionProperty = $relatedModel->positionProperty;
								$positionProperties[$positionProperty] = static::getNextTablePosition();
							}
							break;
						}
					}
				}
			}
		}

		return $positionProperties;
	}

	public function delete()
	{
		/** @var ModelConfig $modelConfig */
		$modelConfig = $this->modelConfig();

		\DB::transaction(function() use ($modelConfig) {
			if ($modelConfig->index == 'tree') {
				$parentIdProperty = $modelConfig->parent->property;
				\DB::table($this->table)->where($parentIdProperty, $this->$parentIdProperty)->where('position', '>', $this->position)->decrement('position');
			}

			return parent::delete();
		});
	}

	public function moveInTree($params)
	{
		$modelConfig = $params['modelConfig'];
		/** @var BaseModel $objectClass */
		/** @var ModelConfig $modelConfig */
		$objectClass = $modelConfig->myFullEntityName();
		$newParentId = $params['parentId'];
		$newPosition = $params['position'];
		$parentIdProperty = $modelConfig->parent->property;

		// Make sure the depth hasn't changed
		$oldDepth = $this->depth;
		$oldPosition = $this->position;
		$parentObjectMethod = $modelConfig->parent->method;

		if ($this->$parentObjectMethod) {
			$newDepth = $this->$parentObjectMethod->depth + 1;
			$oldParentId = $this->$parentObjectMethod->id;
		} else {
			$oldParentId = false;
			$newDepth = 0;
		}

		if ($oldDepth != $newDepth) {
			Dbar::error("Error: Depth change");
			return false;
		}

		/** @var BaseModel $object */
		$object = $this;
		\DB::transaction(function() use ($parentIdProperty, &$object, $objectClass, $oldParentId, $newParentId, $oldPosition, $newPosition, $modelConfig) {
			$object->position = $newPosition;
			if ($oldParentId != $newParentId) {
				// Parent changed
				if (!$newParentId) $newParentId = null;
				$object->$parentIdProperty = $newParentId;

				// In old parent, decrement positions of all objects
				// that had higher position than this one
				$objectClass::where($parentIdProperty, $oldParentId)
					->where('position', '>', $oldPosition)
					->decrement('position');

				// In new parent, increment positions of all objects
				// that had >= position than this one has now
				$objectClass::where($parentIdProperty, $newParentId)
					->where('position', '>=', $newPosition)
					->increment('position');

				// Generate slug
				AdminEntityHandler::generateSlug($modelConfig, $object, $newParentId, true);

			} else {
				// Parent is same
				if (!$newParentId) $newParentId = null;
				if ($newPosition > $oldPosition) {
					// Move down
					$objectClass::where('id', '<>', $object->id)
						->where($parentIdProperty, $newParentId)
						->where('position', '>', $oldPosition)
						->where('position', '<=', $newPosition)
						->decrement('position');
				} else {
					// Move up
					$objectClass::where('id', '<>', $object->id)
						->where($parentIdProperty, $newParentId)
						->where('position', '>=', $newPosition)
						->where('position', '<', $oldPosition)
						->increment('position');
				}
			}

			$object->save();
		});

		return true;
	}

	public function move($params)
	{
		$modelConfig = $params['modelConfig'];
		$objectClass = $modelConfig->name;
		$parentName = $params['parentName'];
		$aboveItemId = $params['aboveItemId'];
		$belowItemId = $params['belowItemId'];
		$direction = $params['direction'];

		/** @var BaseModel $this */
		/** @var BaseModel $objectClass */
		/** @var BaseModel $fullObjectClass */
		$fullObjectClass = ModelConfig::fullEntityName($objectClass);
		$belowItem = $belowItemId ? $fullObjectClass::find($belowItemId) : false;
		$aboveItem = $aboveItemId ? $fullObjectClass::find($aboveItemId) : false;

		if (!$belowItem && !$aboveItem) {
			Dbar::error("no below or above item");
			return false;
		}

		$positionProperty = 'position';
		$positionInParent = false;
		$parentIdProperty = false;
		if ($parentName) {
			$parentModelConfig = AdminHelper::modelExists($parentName);
			$parentIdProperty = $parentModelConfig->id;
			$positionInParent = true;
			$modelConfigInParent = $this->getRelatedModelConfigurationInParentModel($modelConfig, $parentName);
			$positionProperty = $modelConfigInParent['positionProperty'];
			$directionField = $modelConfigInParent['direction'];

			if ($directionField == 'desc') {
				if ($direction == 'move-down') {
					$direction = 'move-up';
				} else if ($direction == 'move-up') {
					$direction = 'move-down';
				}

				$tempItem = $belowItem;
				$belowItem = $aboveItem;
				$aboveItem = $tempItem;
			}

		} else {
			if ($modelConfig->direction == 'desc') {
				if ($direction == 'move-down') {
					$direction = 'move-up';
				} else if ($direction == 'move-up') {
					$direction = 'move-down';
				}

				$tempItem = $belowItem;
				$belowItem = $aboveItem;
				$aboveItem = $tempItem;
			}
		}

		$oldPosition = $this->$positionProperty;

		//moved down
		if ($aboveItem && $direction == 'move-down') {
			$newPosition = $aboveItem->$positionProperty;

		//moved up
		} else if ($belowItem && $direction == 'move-up') {
			$newPosition = $belowItem->$positionProperty;
		} else {
			return false;
		}

		$this->$positionProperty = $newPosition;

		if ($direction == 'move-down') {
			$this->$positionProperty = $newPosition;
			$object = &$this;
			\DB::transaction(function() use ($parentIdProperty, &$object, $positionInParent, $fullObjectClass, $positionProperty, $oldPosition, $newPosition) {
				$object->save();
				if ($positionInParent) {
					$fullObjectClass::where($parentIdProperty, $object->$parentIdProperty)
						->where('id', '<>', $object->id)
						->where($positionProperty, '>', $oldPosition)
						->where($positionProperty, '<=', $newPosition)
						->decrement($positionProperty);
				} else {
					$fullObjectClass::where('id', '<>', $object->id)
						->where('position', '>', $oldPosition)
						->where('position', '<=', $newPosition)
						->decrement('position');
				}
			});
		} else if ($direction == 'move-up') {
			$object = &$this;
			\DB::transaction(function() use ($parentIdProperty, &$object, $positionInParent, $fullObjectClass, $positionProperty, $oldPosition, $newPosition)  {
				$object->save();
				if ($positionInParent) {
					$fullObjectClass::where($parentIdProperty, $object->$parentIdProperty)
						->where('id', '<>', $object->id)
						->where($positionProperty, '>=', $newPosition)
						->where($positionProperty, '<', $oldPosition)
						->increment($positionProperty);
				} else {
					$fullObjectClass::where('id', '<>', $object->id)
						->where('position', '>=', $newPosition)
						->where('position', '<', $oldPosition)
						->increment('position');
				}
			});
		}

		return true;
	}

	public function image($urlOrName = 'url', $folder = 'original', $imageProperty = 'imagename', $filenameValue = false)
	{
		$filenameValue = $this->$imageProperty ? $this->$imageProperty : $filenameValue;

		if ($filenameValue) {
			$reflection = new \ReflectionClass($this);
			$entity = $reflection->getShortName();
			if ($urlOrName == 'url') {
				return request()->root() . "/img/modelImages/" . $entity . "/" . $folder . "/" . $filenameValue;
			} else if ($urlOrName == 'path') {
				return public_path() . "/img/modelImages/" . $entity . "/" . $folder . "/" . $filenameValue;
			} else if ($urlOrName == 'relative') {
				return "/img/modelImages/" . $entity . "/" . $folder . "/" . $filenameValue;
			} else {
				return $filenameValue;
			}
		}

		return null;
	}

	public function file($urlOrName = 'url', $filenameProperty = "filename", $filenameValue = false)
	{
		$filenameValue = $this->$filenameProperty ? $this->$filenameProperty : $filenameValue;

		if ($filenameValue) {
			$reflection = new \ReflectionClass($this);
			$entity = $reflection->getShortName();
			if ($urlOrName == 'url') {
				return request()->root() . "/file/modelFiles/" . $entity . "/" . $filenameValue;
			} else if ($urlOrName == 'path') {
				return public_path() . "/file/modelFiles/" . $entity . "/" . $filenameValue;
			} else {
				return $filenameValue;
			}
		}

		return null;
	}

	public function fileSize($filenameAttribute = 'filename')
	{
		$entity = get_class($this);
		$reflection = new \ReflectionClass($entity);
		$shortEntity = $reflection->getShortName();
		$path = public_path() . "/file/modelFiles/" . $shortEntity . "/" .  $this->$filenameAttribute;

		if (!file_exists($path)) {
			return " - ";
		}

		$size = filesize($path);

		$kb = round($size/1024);
		$mb = round($size/1024/1024);

		if ($mb < 1) {
			return $kb . " KB";
		}

		return $mb . " MB";
	}

	public function getParentData($modelConfig = NULL)
	{
		$parentIdProperty = NULL;
		$parentId = NULL;
		$parent = NULL;
		$parentObject = NULL;
		$parentFound = false;
		$allParents = NULL;

		$reflection = new \ReflectionClass($this);
		$objectClass = $reflection->getShortName();

		if (!$modelConfig) {
			$modelConfig = AdminHelper::modelExists($objectClass);
		}

		if ($modelConfig->getModelParents() || $modelConfig->parent) {
			$parentIdProperties = AdminHelper::objectToArray($modelConfig->getModelParents());

			if (!$parentIdProperties) {
				$parentIdProperties = array($modelConfig->parent->property);
			}

			if (!empty($_GET)) {
				foreach ($_GET as $parentIdProperty => $id) {
					if (in_array($parentIdProperty, $parentIdProperties) && is_numeric($id)) {
						/** @var ModelConfig $parentModelConfig */
						$parentModelConfig = AdminHelper::modelExists($parentIdProperty, 'id');
						/** @var BaseModel $parentModel */
						$parentModel = $parentModelConfig->myFullEntityName();
						$parentObject = $parentModel::find($id);
						if ($parentObject) {
							$parentId = $id;
							$parentFound = true;

							// We don't want to set $allParents for tree-index Models
							// because we don't want don't want AdminEntityHandler to set
							// position for tree-index objects when adding them,
							// so we check for Standalone, or presence of addToParent
							// which is not present for tree-index operations

							if ($modelConfig->standalone === false || isset($_GET['addToParent'])) {
								$allParents[$parentIdProperty] = $id;
							}
						} else {
							$parentIdProperty = NULL;
							$parentObject = NULL;
						}
					} else {
						$parentIdProperty = NULL;
					}

					// Break after first iteration because first $_GET must be the parent

					break;
				}
			}

			if (!empty($_POST)) {
				foreach ($_POST as $parentIdPropertyFromPost => $idFromPost) {
					if (in_array($parentIdPropertyFromPost, $parentIdProperties) && is_numeric($idFromPost)) {
						/** @var ModelConfig $parentModelConfig */
						$parentModelConfig = AdminHelper::modelExists($parentIdPropertyFromPost, 'id');
						$parentModel = $parentModelConfig->myFullEntityName();
						/** @var BaseModel $parentModel */
						$parentObject = $parentModel::find($idFromPost);
						if ($parentObject) {
							$allParents[$parentIdPropertyFromPost] = $idFromPost;
						}
					}
				}
			}
		}

		if (!$parentFound && $modelConfig->index == 'tree') {
			$parentIdProperty = $modelConfig->parent->property;
			$parentId = $this->$parentIdProperty;

			// Intentionally don't set $allParents

		}

		$data = array(
			'parentIdProperty' => $parentIdProperty,
			'parentId' => $parentId,
			'parent' => $parent,
			'parentObject' => $parentObject,
			'allParents' => $allParents
		);

		return $data;
	}

	public function getSideTableParentModelData()
	{
		$subtract = config('gtcms.cmsPrefix') ? 0 : 1;

		$parentModel = request()->segment(2 - $subtract);
		$parentModelConfig = AdminHelper::modelExists($parentModel);
		$parentIdProperty = $parentModelConfig->id;
		$parentId = request()->segment(4 - $subtract);

		return array(
			'parentIdProperty' => $parentIdProperty,
			'parentId' => $parentId,
			'parentModelConfig' => $parentModelConfig
		);
	}

	public function relatedModelConfiguration($relatedModelName, $parentModelConfig = NULL, $returnFalseIfNotFound = false)
	{
		$parentModelConfig = $parentModelConfig ? $parentModelConfig : $this->modelConfig();
		foreach ($parentModelConfig->relatedModels as $cRelatedModel) {
			if ($cRelatedModel->name == $relatedModelName) {
				return $cRelatedModel;
			}
		}

		if ($returnFalseIfNotFound) {
			return false;
		}

		throw new \Exception("Related model configuration doesn't exist!");
	}

	public function getRelatedModelConfigurationInParentModel($modelConfig = NULL, $parentModelName = false)
	{
		if (!$modelConfig) {
			$fullEntity = get_called_class();
			$reflection = new \ReflectionClass($fullEntity);
			$entity = $reflection->getShortName();
			$modelConfig = AdminHelper::modelExists($entity);
		}

		if ($parentModelName) {
			$parentModelConfig = AdminHelper::modelExists($parentModelName);
			if (!$parentModelConfig) {
				throw new \Exception ("Parent model doesn't exist.");
			}
			$parentModel = $parentModelConfig->name;
		} else {
			$sideTableData = $this->getSideTableParentModelData();
			$parentModel = $sideTableData['parentModelConfig']->name;
			$parentModelConfig = $sideTableData['parentModelConfig'];
		}

		/** @var BaseModel $parentModel */
		$parentModel = ModelConfig::fullEntityName($parentModel);
		$relatedModelConfiguration = (new $parentModel)->relatedModelConfiguration($modelConfig->name, $parentModelConfig);

		return (
			array(
				'direction' => $relatedModelConfiguration->direction,
				'positionProperty' => $relatedModelConfiguration->positionProperty,
				'position' => $relatedModelConfiguration->position,
				'hidePositionControls' => $relatedModelConfiguration->hidePositionControls,
				'parentModelName' => $parentModelConfig->name
			)
		);
	}

	public function modelConfig()
	{
		$reflection = new \ReflectionClass($this);
		$class = $reflection->getShortName();
		$modelConfig = AdminHelper::modelExists($class);
		return $modelConfig;
	}

	public function isAddable()
	{
		return true;
	}

	public function isEditable()
	{
		return true;
	}

	public function isDeletable()
	{
		return true;
	}

	public function runMutators($action)
	{
		// Implement this method per model, if needed
	}
}