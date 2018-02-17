<?php

namespace App\Classes;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GtcmsPremium
{
	public static function oneToMany($modelConfig, &$input)
	{
		foreach ($modelConfig->formFields as $field) {
			if ($field->type == 'select' && $field->selectType->type == 'model' && $field->create) {
				/** @var BaseModel $modelName */
				$modelName = $field->selectType->modelName;
				/** @var BaseModel $fullModelName */
				$fullModelName = ModelConfig::fullEntityName($modelName);
				$createProperty = $field->createProperty;

				if (isset($input[$field->property]) && $input[$field->property]) {
					$value = $input[$field->property];
					if (Str::endsWith($value, '_gtcms_selectizejs_newitem')) {
						$value = explode("_gtcms_selectizejs_newitem", $value);
						$value = $value[0];
						$relatedObject = $fullModelName::where($createProperty, $value)->first();
						if (!$relatedObject) {
							if (strlen($value) > 50) {
								$message = "One-to-many - entity name too long: " . $value . " - Entity: " . $modelConfig->name;
								\Log::notice($message);
								throw new \Exception(trans('gtcms.createOnTheFlyTooLong'));
							}

							$relatedObject = $fullModelName::create([$createProperty => $value]);
							$relatedObject->save();
						}
						$input[$field->property] = $relatedObject->id;
					}
				}
			}
		}
	}

	public static function manyToManyCreate($modelConfig, &$relatedIds, $field, $input)
	{
		/** @var BaseModel $modelName */
		$modelName = $field->selectType->modelName;
		/** @var BaseModel $fullModelName */
		$fullModelName = ModelConfig::fullEntityName($modelName);
		/** @var BaseModel $fullModelName */
		$createProperty = $field->createProperty;

		if (isset($input[$field->property])) {
			$position = 0;
			foreach ($input[$field->property] as $propertyValue) {
				$position++;

				if (Str::endsWith($propertyValue, '_gtcms_selectizejs_newitem')) {
					$propertyValue = explode("_gtcms_selectizejs_newitem", $propertyValue);
					$propertyValue = $propertyValue[0];
					$relatedObject = $fullModelName::where($createProperty, $propertyValue)->first();
					if (!$relatedObject) {
						if (strlen($propertyValue) > 50) {
							$message = "Many-to-many - entity name too long: " . $propertyValue . " - Entity: " . $modelConfig->name;
							\Log::notice($message);
							throw new \Exception(trans('gtcms.createOnTheFlyTooLong'));
						}

						$relatedObject = $fullModelName::create([$createProperty => $propertyValue]);
						$relatedObject->save();
					}
				} else {
					$relatedObject = $fullModelName::where('id', $propertyValue)->first();
				}

				if ($relatedObject) {
					$relatedIds[$relatedObject->id] = ['position' => $position];
				}
			}
		}
	}

	public static function manyToManyPosition(&$relatedIds, $input, $field)
	{
		$position = 0;
		foreach ($input[$field->property] as $id) {
			if (is_numeric($id) && $id) {
				$position++;
				$relatedIds[$id] = ['position' => $position];
			}
		}
	}

	public static function sortMultiSelectList($selectedValues, &$list)
	{
		if (is_array($selectedValues) && !empty($selectedValues)) {
			$listAddon = [];
			foreach ($selectedValues as $value) {
				if (isset($list[$value])) {
					// list WITHOUT option to create new objects
					$listAddon[$value] = $list[$value];
					unset($list[$value]);
				} else {
					// list WITH option to create new objects
					$listAddon[$value] = $value;
				}
			}
			$list = $listAddon + $list;
		}
	}

	public static function ajaxSearch(ModelConfig $modelConfig)
	{
		/** @var \App\Models\BaseModel $fullEntity */
		$fullEntity = $modelConfig->myFullEntityName();
		$items = [];
		$data = [
			'success' => false
		];

		if (request()->ajax()) {
			try {
				$value = request()->get('value');
				$text = request()->get('text');
				$searchFields = explode("|", request()->get('searchFields'));
				$searchQuery = request()->get('query');
				$objects = $fullEntity::where(function ($query) use ($searchFields, $searchQuery) {
					foreach ($searchFields as $searchField) {
						$query->orWhere($searchField, "LIKE", "%" . $searchQuery . "%");
					}
				})->get();
				if ($objects->count()) {
					foreach ($objects as $object) {
						$items[] = [
							'value' => $object->$value,
							'text' => $object->$text
						];
					}
					$data['success'] = true;
				}
			} catch (\Exception $e) {
				Dbar::error($e->getMessage());
			}

			$data['items'] = $items;

			return response()->json($data);
		}

		return null;
	}

	public static function excelExport($modelName)
	{
		try {
			$modelConfig = AdminHelper::modelExists($modelName);
			if ($modelConfig) {
				/** @var \App\Models\BaseModel $entity */
				/** @var ModelConfig $modelConfig */
				$entity = $modelConfig->myFullEntityName();
				$orderAndDirection = $modelConfig->getOrderParams();
				$objects = $entity::searchResultsEntities($modelConfig)
					->orderBy($orderAndDirection['orderBy'], $orderAndDirection['direction'])
					->get();

				$searchData = AdminHelper::getSearchData($modelConfig, true);
				$fields = $modelConfig->getFormFields('excelExport');
				$fieldsCount = $modelConfig->getFormFields('excelExport', ['count' => true]);

				$spaces = [];
				for ($i = 0; $i < $fieldsCount; $i++) {
					$spaces[] = " ";
				}
				$searchCriteriaSpaces = $spaces;
				array_pop($searchCriteriaSpaces);

				$searchCriteriaValuesSpaces = $searchCriteriaSpaces;
				array_pop($searchCriteriaValuesSpaces);

				$languages = config('gtcmslang.languages');
				$tableHeader = [];
				foreach ($fields as $field) {
					if ($field->langDependent) {
						foreach ($languages as $lang) {
							$tableHeader[] = $field->label . " [$lang]";
						}
					} else {
						$tableHeader[] = $field->label;
					}
				}

				$writer = new \XLSXWriter();
				$table = [];
				$table[] = $spaces;

				if ($searchData) {
					$table[] = array_merge([trans('gtcms.searchCriteria')], $searchCriteriaSpaces);
					foreach ($searchData as $criteria) {
						$table[] = array_merge([$criteria['label'], $criteria['value']], $searchCriteriaValuesSpaces);
					}
					$table[] = [];
					$table[] = [];
				}

				$table[] = $tableHeader;

				if ($objects) {
					foreach ($objects as $object) {
						$values = [];
						foreach ($fields as $field) {
							$iterations = 1;
							if ($field->langDependent) {
								$iterations = count($languages);
							}
							for ($currentLanguage = 0; $currentLanguage < $iterations; $currentLanguage++) {
								$value = AdminHelper::getModelConfigFieldValue($modelConfig, $field, $object, $currentLanguage, false, true);
								$value = $value != "" ? $value : "-";
								$values[] = $value;
							}

						}
						$table[] = $values;
					}
				} else {
					return false;
				}

				$writer->writeSheet($table);

				$dirs = [
					resource_path("exports"),
				];

				foreach ($dirs as $dir) {
					if (!is_dir($dir)) {
						mkdir($dir, 0755);
					}
				}

				$fileName = "/exports/" . ($modelConfig->name) . "_export_" . date("Y_m_d__H_i_s") . ".xlsx";
				$filePath = resource_path() . $fileName;
				$writer->writeToFile($filePath);

				$response = response()->download($filePath, null, ['Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8']);
				ob_end_clean();

				return $response;
			} else {
				MessageManager::setException("Error: Model doesn't exist.");

				return redirect()->to(AdminHelper::getCmsPrefix());
			}
		} catch (\Exception $e) {
			return AdminHelper::handleException($e);
		}
	}

	public static function setEditFormLanguageVars(&$languages, &$iterations, &$originalLabel, &$originalProperty, $field)
	{
		if ($field->langDependent) {
			$languages = config('gtcmslang.languages');
			$iterations = count($languages);
			$originalLabel = $field->label;
			$originalProperty = $field->property;
		}
	}

	public static function setEditFormLangLabelAndProperty(&$field, $languages, $trueCurrentLanguage, $originalLabel, $originalProperty)
	{
		if ($field->langDependent) {
			$lang = $languages[$trueCurrentLanguage];
			$field->label = $originalLabel . " [$lang]";
			$field->property = $originalProperty . "_" . $lang;
		}
	}

	public static function setFileHandlerInputProperties(&$inputProperties, $fileField)
	{
		if ($fileField->langDependent) {
			foreach (config('gtcmslang.languages') as $lang) {
				$inputProperties[] = $fileField->property . "_" . $lang;
			}
		} else {
			$inputProperties[] = $fileField->property;
		}
	}

	public static function getLangDependentPropertyValue($object, $key, $entity)
	{
		$data = [
			'valueFound' => false,
			'value' => null
		];

		if (config('gtcmslang.siteIsMultilingual')) {
			if ($entity != "BaseModel") {
				if (isset(BaseModel::$modelConfigs[$entity])) {
					$modelConfig = BaseModel::$modelConfigs[$entity];
				} else {
					$modelConfig = AdminHelper::modelExists($entity);

					if (!$modelConfig) {
						Dbar::log("No Model config for entity: " . $entity);
					}

					BaseModel::$modelConfigs[$modelConfig->name] = $modelConfig;
				}

				if (!$modelConfig) {
					Dbar::log("No Model config for entity: " . $entity);
				} else {
					$modelConfig->parseFormFields();
					$langDependentPropertyFieldArray = $modelConfig->langDependentPropertyFieldArray;

					if ($modelConfig->generateSlug && $modelConfig->langDependentSlug) {
						$langDependentPropertyFieldArray['slug'] = [];
					}

					if (array_key_exists($key, $langDependentPropertyFieldArray)) {
						$property = $key . "_" . app()->getLocale();
						$data = [
							'valueFound' => true,
							'value' => $object->$property
						];
					}
				}
			}
		}

		return $data;
	}

	public static function setSlugProperty($modelConfig, $languages, $currentLanguage, &$slugProperty, &$modelConfigSlugProperty)
	{
		if ($modelConfig->langDependentSlug) {
			$lang = $languages[$currentLanguage];
			$slugProperty = "slug_" . $lang;
			$modelConfigSlugProperty = $modelConfigSlugProperty . "_" . $lang;
		}
	}

	public static function updateLanguages($modelConfig)
	{
		/** @var ModelConfig $modelConfig */
		$fullModelName = $modelConfig->myFullEntityName();
		$table = (new $fullModelName)->getTable();
		$prefix = \DB::getTablePrefix();
		$defaultLocale = config('gtcmslang.defaultAdminLocale');

		\DB::beginTransaction();

		try {
			if ($modelConfig->generateSlug && $modelConfig->langDependentSlug) {
				$formFields = AdminHelper::objectToArray($modelConfig->formFields);
				$slug = [
					'property' => 'slug',
					'langDependent' => true
				];
				$formFields[] = $slug;
				$formFields = AdminHelper::arrayToObject($formFields, false);
				$modelConfig->formFields = $formFields;
			}

			foreach ($modelConfig->formFields as $formField) {
				if ($formField->langDependent) {
					$column = $formField->property;
					$columnData = false;
					if (\Schema::hasColumn($table, $column)) {
						$columnData = \DB::select(\DB::raw("DESCRIBE " . $prefix . $table . " " . $column));
					} else if (\Schema::hasColumn($table, $column . "_" . $defaultLocale)) {
						$columnData = \DB::select(\DB::raw("DESCRIBE " . $prefix . $table . " " . $column . "_" . $defaultLocale));
					}

					if ($columnData) {
						foreach (config('gtcmslang.languages') as $language) {
							if ($language == $defaultLocale) {
								if (\Schema::hasColumn($table, $column)) {
									if (\Schema::hasColumn($table, $column . "_" . $language)) {
										//delete column
										\Schema::table($table, function ($table) use ($column) {
											$table->dropColumn([$column]);
										});
									} else {
										//rename column
										\Schema::table($table, function ($table) use ($column, $language) {
											$table->renameColumn($column, $column . "_" . $language);
										});
									}
								} else if (!\Schema::hasColumn($table, $column . "_" . $language)) {
									//create column
									$type = $columnData[0]->Type;
									$statement = "ALTER TABLE " . $prefix . $table . " ADD " . $column . "_" . $language . " " . $type . " NULL";
									\DB::statement($statement);
								}
							} else if (!\Schema::hasColumn($table, $column . "_" . $language)) {
								//create column
								$type = $columnData[0]->Type;
								$statement = "ALTER TABLE " . $prefix . $table . " ADD " . $column . "_" . $language . " " . $type . " NULL";

								if (\Schema::hasColumn($table, $column . "_" . $defaultLocale)) {
									$statement .= " AFTER " . $column . "_" . $defaultLocale;
								}

								\DB::statement($statement);
							}
						}
					}
				}
			}

			\DB::commit();

		} catch (\Exception $e) {
			Dbar::error($e->getMessage());
			\DB::rollBack();
		}
	}

	public static function setContinueForModelsAndModelKeyPropertyOptions($field, $modelConfig, $object, &$continue)
	{
		if ($field->modelKey) {
			$allowedKeys = AdminHelper::objectToArray($field->modelKey);
			if (!$object->model_key || !in_array($object->model_key, $allowedKeys)) {
				$continue = true;
			}
		}

		if ($field->models) {
			$allowedModels = AdminHelper::objectToArray($field->models);
			$parentIdProperty = AdminHelper::firstParamIsParent($modelConfig);
			if ($parentIdProperty) {
				$parentModel = AdminHelper::modelExists($parentIdProperty, 'id');
				if ($parentModel) {
					if (!in_array($parentModel->name, $allowedModels)) {
						$continue = true;
					}
				} else {
					Dbar::error("Edit content form fields error: Parent ID property exists, but Parent Model Config does not.");
				}
			}
		}
	}

	public static function setDisplayRelatedModelBasedOnModelKey($configInParent, $object, &$displayModel)
	{
		if ($configInParent->modelKey) {
			$allowedKeys = AdminHelper::objectToArray($configInParent->modelKey);
			if (!in_array($object->model_key, $allowedKeys)) {
				$displayModel = false;
			}
		}
	}

	public static function getKeyBasedImageSizes($parentModelConfig)
	{
		/** @var ModelConfig $parentModelConfig */
		$objectId = AdminHelper::firstParamIsParent($parentModelConfig, true);
		/** @var BaseModel $modelName */
		$modelName = $parentModelConfig->myFullEntityName();
		$object = $modelName::find($objectId);
		$modelKey = $object->model_key;
		$sizes = $parentModelConfig->imageSizes->$modelKey;

		if (!$sizes) {
			throw new \Exception("Sizes couldn't be found for model key " . $modelKey);
		}

		return $sizes;
	}

	public static function getQuickEditVar()
	{
		return request()->get('getIgnore_quickEdit') ? true : false;
	}

	public static function setQuickEditReturnData(&$data, $object, $modelConfig)
	{
		$data['objectId'] = $object->id;
		if ($modelConfig->index != "tree") {
			$objects = new Collection();
			$objects->push($object);
			$tableType = in_array(request()->get('quickEditTableType'), ['table', 'sideTable']) ? request()->get('quickEditTableType') : 'table';

			$parentName = "";
			$parentId = AdminHelper::firstParamIsParent($modelConfig);
			if ($parentId) {
				$parentModelConfig = AdminHelper::modelExists($parentId, 'id');
				$parentName = $parentModelConfig->name;
			}

			$objectRow = Front::drawObjectTable($objects, $modelConfig, $tableType, ['quickEdit' => true, 'parentName' => $parentName]);
			$data['objectRowHtml'] = $objectRow;
			$data['modelName'] = $modelConfig->name;
		} else {
			$printProperty = $modelConfig->printProperty;
			$data['printPropertyValue'] = $object->$printProperty;
		}
	}

	public static function getQuickEditControl($modelConfig, $object, $gets, $tableType)
	{
		$prepend = $gets ? '&' : '?';
		$gets .= $prepend . 'quickEditTableType=' . $tableType;

		return
			'<a
			href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/edit/' . $object->id . $gets . '"
			class="btn btn-default btn-xs quickEditButton"
			>
				<i class="fa fa-pencil-square-o"></i>
			</a>';
	}
}