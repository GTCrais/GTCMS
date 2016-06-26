<?php

namespace App;

class ModelConfig {

	protected $gtcmsModelParents = null;
	public    $quickEditFields = null;
	protected $langDependentProperties = null;
	protected $excelExportFields = null;
	protected $excelExportFieldsCount = null;
	protected $searchPropertiesExist = null;

	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		} else {
			return NULL;
		}
	}

	public function myFullEntityName() {
		$namespace = $this->namespace ? $this->namespace : config('gtcms.defaultNamespace');
		return $namespace . "\\" . $this->name;
	}

	public static function fullEntityName($entity, $namespace = false) {
		if ($namespace) {
			return $namespace . "\\" . $entity;
		} else {
			$modelConfig = AdminHelper::modelExists($entity);
			if ($modelConfig) {
				return $modelConfig->myFullEntityName();
			}
		}

		Dbar::error("ModelConfig for " . $entity . "doesn't exist!");
		return "";
	}

	public static function rulesToArray($rules) {
		if (is_object($rules)) {
			return AdminHelper::objectToArray($rules);
		} else {
			return explode("|", $rules);
		}
	}

	public static function rulesToString($rules) {
		if (is_object($rules)) {
			$rules = AdminHelper::objectToArray($rules);
		}
		if (is_array($rules)) {
			return implode("|", $rules);
		}

		return $rules;
	}

	public function getSearchPropertiesData() {
		$properties = array();
		$searchConfig = array();
		foreach ($this->getFormFields('all', true) as $field) {
			if ($field->search) {
				$properties[] = $field->property;
				$searchConfig[$field->property] = AdminHelper::objectToArray($field->search);
			}
		}
		$data = array(
			'properties' => $properties,
			'searchConfig' => $searchConfig
		);
		return $data;
	}

	public function searchPropertiesExist() {
		if (!is_null($this->searchPropertiesExist)) {
			return $this->searchPropertiesExist;
		}

		$searchPropertiesData = $this->getSearchPropertiesData();
		if ($searchPropertiesData['properties']) {
			$this->searchPropertiesExist = true;
			return true;
		}

		$this->searchPropertiesExist = false;
		return false;
	}

	public function getFieldsWithLabels($search = false) {
		$fieldsWithLabels = array();
		foreach ($this->formFields as $field) {
			if ($search) {
				if ($field->search) {
					$fieldsWithLabels[$field->property] = $field->label;
				}
			} else {
				$fieldsWithLabels[$field->property] = $field->label;
			}
		}
		return $fieldsWithLabels;
	}

	public function getPropertyValue($property, $value) {
		$returnValue = "Undefined";
		$list = array();
		foreach ($this->formFields as $field) {
			if ($field->property == $property) {
				if (in_array($field->type, array('select', 'multiSelect'))) {
					$listMethod = $field->selectType->listMethod;
					if ($field->selectType->type == 'model') {
						$selectModel = ModelConfig::fullEntityName($field->selectType->modelName);
						if ($field->selectType->callMethodOnInstance) {
							$list = array();
							/** @var BaseModel $modelName */
							$modelName = ModelConfig::fullEntityName($field->selectType->modelName);
							$idField = $field->selectType->callMethodOnInstance->id;
							$valueField = $field->selectType->callMethodOnInstance->value;
							$object = $modelName::where($idField, $value)->first();
							if ($object) {
								$list[$value] = $object->$valueField;
							}
						} else {
							//id is passed to the list method so automatic Search can work
							$list = $selectModel::$listMethod('id');
						}
					} else if ($field->selectType->type == 'list') {
						$entity = ModelConfig::fullEntityName($this->name);
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
				break;
			}
		}
		return $returnValue;
	}

	public function getDatabasePropertyValue($property, $value) {
		foreach ($this->formFields as $field) {
			if ($field->property == $property) {
				if (in_array($field->type, array('date', 'dateTime'))) {
					if ($field->type == "date") {
						$value = date("Y-m-d", strtotime($value));
					} else if ($field->type == "dateTime") {
						$value = date("Y-m-d H:i:s", strtotime($value));
					} else {
						$value = "";
					}
				}
			}
		}
		return $value;
	}

	public function getPropertiesTables() {
		$propertiesTables = array();
		$model = $this->name;
		foreach ($this->formFields as $field) {
			if ($field->type == 'multiSelect' && $field->selectType->type == 'model') {
				$relatedModel = $field->selectType->modelName;
				$tableNames = array(snake_case($model), snake_case($relatedModel));
				sort($tableNames, SORT_STRING);
				$tableName = implode('_', $tableNames);
				$propertiesTables[$field->property] = $tableName;
			} else {
				$fullModel = $this->myFullEntityName();
				$propertiesTables[$field->property] = (new $fullModel)->getTable();
			}
		}
		return $propertiesTables;
	}

	public function getManyToManyRelationData() {
		$data = array();
		$model = $this->name;
		$fullModel = $this->myFullEntityName();
		$table = (new $fullModel)->getTable();
		$data['table'] = $table;
		$data['modelName'] = strtolower($model);
		$data['idProperty'] = 'id';
		$data['relationData'] = array();

		foreach ($this->formFields as $field) {
			if ($field->type == 'multiSelect' &&
				$field->selectType->type == 'model' &&
				$field->search
			) {
				$relatedModel = $field->selectType->modelName;
				$tableNames = array(snake_case($model), snake_case($relatedModel));
				sort($tableNames, SORT_STRING);
				$tableName = implode('_', $tableNames);
				$relationId = snake_case($model).'_id';
				$relatedModelId = snake_case($relatedModel)."_id";
				$data['relationData'][] = array('relationTable' => $tableName,
												'relationId' => $relationId,
												'relatedModelId' => $relatedModelId);
			}
		}
		return $data;
	}

	public function getLangDependentProperties() {
		if (!is_null($this->langDependentProperties)) {
			return $this->langDependentProperties;
		}

		$properties = array();
		foreach ($this->formFields as $field) {
			if (config('gtcms.premium') && $field->langDependent) {
				$properties[] = $field->property;
			}
		}

		$this->langDependentProperties = $properties;
		return $properties;
	}

	public function getExcelExportFields($returnCount = false) {
		if (!is_null($this->excelExportFields)) {
			if ($returnCount) {
				return $this->excelExportFieldsCount;
			}
			return $this->excelExportFields;
		}

		$count = 0;
		$fields = array();
		foreach ($this->formFields as $field) {
			if ($field->excelExport) {
				$fields[] = $field;
				$count++;
				if (config('gtcms.premium') && $field->langDependent) {
					$count += count(config('gtcmslang.languages')) - 1;
				}
			}
		}

		$this->excelExportFields = $fields;
		$this->excelExportFieldsCount = $count;
		if ($returnCount) {
			return $count;
		}
		return $fields;
	}

	public function getFormFields($fieldType, $parseFromTo = false) {
		if ($parseFromTo && !$this->fromToParsed) {
			$formFields = array();
			foreach ($this->formFields as $field) {
				if ($field->fromTo && $field->search) {
					$newField = AdminHelper::objectToArray($field);
					$newField['hidden'] = array(
						'add' => true, 'edit' => true, 'view' => true
					);
					$fromField = $newField;
					$fromField['property'] = $field->property."_fieldFrom";
					$fromField['label'] = $newField['label'] . " " . trans('gtcms.from');
					if (isset($fromField['search']['label'])) {
						$fromField['search']['label'] = $fromField['search']['label'] . " " . trans('gtcms.from');
					}
					$fromField['search']['fieldFrom'] = true;
					$fromField['table'] = false;
					$fromField['sideTable'] = false;
					$fromField = AdminHelper::arrayToObject($fromField);

					$toField = $newField;
					$toField['property'] = $field->property."_fieldTo";
					$toField['label'] = $newField['label'] . " " . trans('gtcms.to');
					if (isset($toField['search']['label'])) {
						$toField['search']['label'] = $toField['search']['label'] . " " . trans('gtcms.to');
					}
					$toField['search']['fieldTo'] = true;
					$toField['table'] = false;
					$toField['sideTable'] = false;
					$toField = AdminHelper::arrayToObject($toField);

					$formFields[] = $fromField;
					$formFields[] = $toField;

					$field->search = false;
					$formFields[] = $field;
				} else {
					$formFields[] = $field;
				}
			}
			$this->fromToParsed = true;
			$this->formFields = $formFields;
		}

		if ($fieldType == 'all') {
			return $this->formFields;
		} else if ($fieldType == 'regular') {
			$fields = array();
			foreach ($this->formFields as $field) {
				if (!$field->langDependent) {
					$fields[] = $field;
				}
			}
			return $fields;
		} else if ($fieldType == 'langDependent') {
			$fields = array();
			foreach ($this->formFields as $field) {
				if (config('gtcms.premium') && $field->langDependent) {
					$fields[] = $field;
				}
			}
			return $fields;
		} else {
			Dbar::error("ModelConfig - getFormFields: fieldType is incorrect! - " . $fieldType);
		}

		return false;
	}

	public function getQuickEditFields($fieldType) {
		$quickEditFields = array();

		if (config('gtcms.premium')) {
			return \GtcmsPremium::getQuickEditFields($this, $fieldType);
		}

		return $quickEditFields;
	}

	public function getModelParents() {
		if (!is_null($this->gtcmsModelParents)) {
			return $this->gtcmsModelParents;
		}

		$parents = array();
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