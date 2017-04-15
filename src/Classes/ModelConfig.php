<?php

namespace App\Classes;

use Illuminate\Support\Str;

class ModelConfig
{
	// All properties are public because if for any reason
	// a ModelConfig is turned into array and then back to object
	// the properties will remain

	public $formFieldsParsed = false;
	public $gtcmsModelParents = null;
	public $langDependentProperties = null;
	public $searchPropertiesExist = false;
	public $searchPropertiesData = [
		'properties' => [],
		'searchConfig' => []
	];
	public $regularFormFields = [];
	public $langDependentFormFields = [];
	public $searchFieldsWithLabels = [];
	public $propertyFieldArray = [];
	public $regularPropertyFieldArray = [];
	public $langDependentPropertyFieldArray = [];
	public $modifiedLangDependentPropertyFieldArray = [];
	public $propertiesTables = [];
	public $manyToManyRelationData = [];
	public $excelExportFields = [];
	public $excelExportFieldsCount = 0;
	public $quickEditFields = [
		'all' => [],
		'regular' => [],
		'langDependent' => []
	];
	public $imageFields = [];
	public $fileFields = [];
	public $orderAndDirection = [];

	protected static $faIconColors = null;

	public function __get($property)
	{
		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return null;
	}

	public function myFullEntityName()
	{
		$namespace = $this->namespace ? $this->namespace : config('gtcms.defaultNamespace');

		return $namespace . "\\Models\\" . $this->name;
	}

	public static function fullEntityName($entity, $namespace = false)
	{
		if ($namespace) {
			return $namespace . "\\Models\\" . $entity;
		} else {
			$modelConfig = AdminHelper::modelExists($entity);
			if ($modelConfig) {
				return $modelConfig->myFullEntityName();
			}
		}

		Dbar::error("ModelConfig for " . $entity . "doesn't exist!");

		return "";
	}

	public static function rulesToArray($rules)
	{
		if (is_object($rules)) {
			return AdminHelper::objectToArray($rules);
		}

		return explode("|", $rules);
	}

	public static function rulesToString($rules)
	{
		if (is_object($rules)) {
			$rules = AdminHelper::objectToArray($rules);
		}

		if (is_array($rules)) {
			return implode("|", $rules);
		}

		return $rules;
	}

	public static function colorStyleForModel($modelConfigOrModelName)
	{
		if (!is_a($modelConfigOrModelName, 'ModelConfig')) {
			$modelConfig = AdminHelper::modelExists($modelConfigOrModelName);
		} else {
			$modelConfig = $modelConfigOrModelName;
		}

		$iconColor = "";

		if ($modelConfig) {
			$iconColor = $modelConfig->faIconColor;

			if (!$iconColor && $configIconColors = config('gtcms.faIconColors')) {
				if (is_null(self::$faIconColors)) {
					$faIconColors = [];
					$counter = 0;
					foreach (AdminHelper::modelConfigs() as $cModelConfig) {
						if ($cModelConfig->standalone !== false &&
							!$cModelConfig->hiddenInNavigation &&
							isset($configIconColors[$counter]))
						{
							$faIconColors[$cModelConfig->name] = $configIconColors[$counter];
							$counter++;
						}
					}

					self::$faIconColors = $faIconColors;
				}

				if (isset(self::$faIconColors[$modelConfig->name])) {
					$iconColor = self::$faIconColors[$modelConfig->name];
				}
			}

			if ($iconColor && !Str::startsWith($iconColor, "#")) {
				$iconColor = "#" . $iconColor;
			}
		}

		if ($iconColor) {
			$iconColor = "style='color: " . $iconColor . ";'";
		}

		return $iconColor;
	}

	public function parseFormFields()
	{
		if ($this->formFieldsParsed) {
			return;
		}

		$premium = config('gtcms.premium');
		$model = $this->name;
		$fullModel = $this->myFullEntityName();
		$table = (new $fullModel)->getTable();
		$this->manyToManyRelationData = [
			'table' => $table,
			'modelName' => strtolower($model),
			'idProperty' => 'id',
			'relationData' => []
		];
		$requestOrderBy = request()->get('orderBy');
		$requestOrderByAcceptable = false;

		foreach ($this->formFields as $field) {

			// Parse FromTo Option

			if ($field->fromTo && $field->search) {
				$newField = AdminHelper::objectToArray($field);

				$fromField = $newField;
				$fromField['property'] = $field->property . "_fieldFrom";
				$fromField['label'] = $newField['label'] . " " . trans('gtcms.from');
				if (isset($fromField['search']['label'])) {
					$fromField['search']['label'] = $fromField['search']['label'] . " " . trans('gtcms.from');
				}
				$fromField['search']['fieldFrom'] = true;
				$fromField = AdminHelper::arrayToObject($fromField);

				$toField = $newField;
				$toField['property'] = $field->property . "_fieldTo";
				$toField['label'] = $newField['label'] . " " . trans('gtcms.to');
				if (isset($toField['search']['label'])) {
					$toField['search']['label'] = $toField['search']['label'] . " " . trans('gtcms.to');
				}
				$toField['search']['fieldTo'] = true;
				$toField = AdminHelper::arrayToObject($toField);

				$field->fromToFields = new BaseClass();
				$field->fromToFields->fromField = $fromField;
				$field->fromToFields->toField = $toField;
			}

			// Search Properties Data

			if ($field->search) {
				if ($field->fromTo) {
					$this->searchPropertiesData['properties'][] = $field->fromToFields->fromField->property;
					$this->searchPropertiesData['searchConfig'][$field->fromToFields->fromField->property] = AdminHelper::objectToArray($field->fromToFields->fromField->search);
					$this->searchFieldsWithLabels[$field->fromToFields->fromField->property] = $field->fromToFields->fromField->label;

					$this->searchPropertiesData['properties'][] = $field->fromToFields->toField->property;
					$this->searchPropertiesData['searchConfig'][$field->fromToFields->toField->property] = AdminHelper::objectToArray($field->fromToFields->toField->search);
					$this->searchFieldsWithLabels[$field->fromToFields->toField->property] = $field->fromToFields->toField->label;
				} else {
					$this->searchPropertiesData['properties'][] = $field->property;
					$this->searchPropertiesData['searchConfig'][$field->property] = AdminHelper::objectToArray($field->search);
					$this->searchFieldsWithLabels[$field->property] = $field->label;
				}

				$this->searchPropertiesExist = true;
			}

			// Regular and Lang Dependent Fields

			if (!$field->langDependent) {
				$this->regularFormFields[] = $field;
			} else if ($premium && $field->langDependent) {
				$this->langDependentFormFields[] = $field;
			}

			// (Lang Dependent) Property Field Array

			$this->propertyFieldArray[$field->property] = $field;

			if ($premium && $field->langDependent) {
				foreach (config('gtcmslang.languages') as $language) {
					$this->modifiedLangDependentPropertyFieldArray[$field->property . "_" . $language] = $field;
				}
				$this->langDependentPropertyFieldArray[$field->property] = $field;
			} else if (!$field->langDependent) {
				$this->regularPropertyFieldArray[$field->property] = $field;
			}

			// Properties Tables

			if ($field->type == 'multiSelect' && $field->selectType->type == 'model') {
				$relatedModel = $field->selectType->modelName;
				$tableNames = [snake_case($model), snake_case($relatedModel)];
				sort($tableNames, SORT_STRING);
				$tableName = implode('_', $tableNames);
				$this->propertiesTables[$field->property] = $tableName;
			} else {
				$fullModel = $this->myFullEntityName();
				$this->propertiesTables[$field->property] = (new $fullModel)->getTable();
			}

			// Many-To-Many Relation Data

			if ($field->type == 'multiSelect' &&
				$field->selectType->type == 'model' &&
				$field->search
			) {
				$relatedModel = $field->selectType->modelName;
				$tableNames = [snake_case($model), snake_case($relatedModel)];
				sort($tableNames, SORT_STRING);
				$tableName = implode('_', $tableNames);
				$relationId = snake_case($model) . '_id';
				$relatedModelId = snake_case($relatedModel) . "_id";

				$this->manyToManyRelationData['relationData'][] = [
					'relationTable' => $tableName,
					'relationId' => $relationId,
					'relatedModelId' => $relatedModelId
				];
			}

			// Excel Export Fields

			if ($field->excelExport) {
				$this->excelExportFields[] = $field;
				$this->excelExportFieldsCount++;
				if ($premium && $field->langDependent) {
					$this->excelExportFieldsCount += count(config('gtcmslang.languages')) - 1;
				}
			}

			// Quick Edit Fields

			if ($premium && $field->quickEdit) {
				$this->quickEditFields['all'][] = $field;
				if ($field->langDependent) {
					$this->quickEditFields['langDependent'][] = $field;
				} else {
					$this->quickEditFields['regular'][] = $field;
				}
			}

			// Image and File Fields

			if ($field->type == 'image') {
				$this->imageFields[] = $field;
			}

			if ($field->type == 'file') {
				$this->fileFields[] = $field;
			}

			// Order By

			if (!isset($this->orderAndDirection['orderBy']) && $requestOrderBy) {
				if ($field->property == $requestOrderBy && $field->order) {
					$requestOrderByAcceptable = true;
					if ($premium && $field->langDependent) {
						$requestOrderBy = $requestOrderBy . "_" . (app()->getLocale());
					}
				}

				if ($requestOrderByAcceptable) {
					$this->orderAndDirection['orderBy'] = $requestOrderBy;
				}
			}
		}

		if (!isset($this->orderAndDirection['orderBy'])) {
			$this->orderAndDirection['orderBy'] = $this->orderBy;
		}

		if (request()->has('direction')) {
			$direction = request()->get('direction');
			if (!in_array($direction, ['asc', 'desc'])) {
				$direction = $this->direction;
			}
		} else {
			$direction = $this->direction;
		}

		$this->orderAndDirection['direction'] = $direction;

		$this->formFieldsParsed = true;
	}

	public function getSearchPropertiesData()
	{
		$this->parseFormFields();

		return $this->searchPropertiesData;
	}

	public function searchPropertiesExist()
	{
		$this->parseFormFields();

		return $this->searchPropertiesExist;
	}

	public function getSearchFieldsWithLabels()
	{
		$this->parseFormFields();

		return $this->searchFieldsWithLabels;
	}

	public function getPropertyFieldArray()
	{
		$this->parseFormFields();

		return $this->propertyFieldArray;
	}

	public function getPropertyValue($property, $value)
	{
		$this->parseFormFields();

		$returnValue = "Undefined";
		$list = [];

		if (isset($this->propertyFieldArray[$property])) {
			$field = $this->propertyFieldArray[$property];

			if (in_array($field->type, ['select', 'multiSelect'])) {
				$listMethod = $field->selectType->listMethod;
				if ($field->selectType->type == 'model') {
					/** @var \App\Models\BaseModel $selectModel */
					$selectModel = ModelConfig::fullEntityName($field->selectType->modelName);
					if ($field->selectType->ajax && config('gtcms.premium')) {
						$valueProperty = $field->selectType->ajax->valueProperty;
						$list = $selectModel::where('id', $value)->get()->pluck($valueProperty, 'id');
					} else {

						// Even if 'callMethodOnInstance' is declared we need a static method
						// of the same name which will return the list of ALL selectable items
						// instead of just the ones a particular object would return
						// This method must be declared in Related Model Class

						$list = $selectModel::$listMethod();
					}
				} else if ($field->selectType->type == 'list') {
					$entity = $this->myFullEntityName();
					$list = $entity::$listMethod();
				}

				foreach ($list as $actualValue => $frontValue) {
					if ($actualValue == $value) {
						$returnValue = $frontValue;
						break;
					}
				}
			} else if ($field->type == 'checkbox') {
				$returnValue = $value ? Front::drawCheckboxIcon(true) : Front::drawCheckboxIcon(false);
			} else {
				$returnValue = $value;
			}

			return $returnValue;
		}

		return $returnValue;
	}

	public function getDatabasePropertyValue($property, $value)
	{
		$this->parseFormFields();

		if (isset($this->propertyFieldArray[$property])) {
			$field = $this->propertyFieldArray[$property];

			if (in_array($field->type, ['date', 'dateTime'])) {
				if ($field->type == "date") {
					$value = date("Y-m-d", strtotime($value));
				} else if ($field->type == "dateTime") {
					$value = date("Y-m-d H:i:s", strtotime($value));
				} else {
					$value = "";
				}
			}
		}

		return $value;
	}

	public function getPropertiesTables()
	{
		$this->parseFormFields();

		return $this->propertiesTables;
	}

	public function getManyToManyRelationData()
	{
		$this->parseFormFields();

		return $this->manyToManyRelationData;
	}

	public function hasImage()
	{
		$this->parseFormFields();

		return $this->imageFields;
	}

	public function hasFile()
	{
		if (!$this->formFieldsParsed) {
			$this->parseFormFields();
		}

		return $this->fileFields;
	}

	public function getOrderParams()
	{
		$this->parseFormFields();

		return $this->orderAndDirection;
	}

	public function getFieldByPropertyParam($paramValue)
	{
		$this->parseFormFields();

		if (config('gtcms.premium')) {
			if (isset($this->modifiedLangDependentPropertyFieldArray[$paramValue])) {
				return $this->modifiedLangDependentPropertyFieldArray[$paramValue];
			}
		}

		if (isset($this->propertyFieldArray[$paramValue])) {
			return $this->propertyFieldArray[$paramValue];
		}

		return false;
	}

	public function getFormFields($fieldType = 'all', $options = [])
	{
		$this->parseFormFields();

		if ($fieldType == 'all') {
			return $this->formFields;
		} else if ($fieldType == 'regular') {
			return $this->regularFormFields;
		} else if ($fieldType == 'langDependent') {
			return $this->langDependentFormFields;
		} else if ($fieldType == 'quickEdit') {
			$quickEditType = 'all';

			if (isset($options['quickEditType'])) {
				if (in_array($options['quickEditType'], ['all', 'regular', 'langDependent'])) {
					$quickEditType = $options['quickEditType'];
				} else {
					Dbar::error("ModelConfig - getFormFields: fieldType is incorrect! - " . $fieldType);

					return [];
				}
			}

			return $this->quickEditFields[$quickEditType];
		} else if ($fieldType == 'excelExport') {
			if (isset($options['count']) && $options['count']) {
				return $this->excelExportFieldsCount;
			}

			return $this->excelExportFields;
		} else {
			Dbar::error("ModelConfig - getFormFields: fieldType is incorrect! - " . $fieldType);
		}

		return false;
	}

	public function getModelParents()
	{
		if (!is_null($this->gtcmsModelParents)) {
			return $this->gtcmsModelParents;
		}

		$parents = [];
		foreach (\AdminHelper::modelConfigs() as $modelConfig) {
			if ($modelConfig->name != $this->name && $modelConfig->relatedModels) {
				foreach ($modelConfig->relatedModels as $relatedModel) {
					if ($relatedModel->name == $this->name) {
						$parents[] = $modelConfig->id;
					}
				}
			}
		}

		$this->gtcmsModelParents = $parents;

		return $parents;
	}
}