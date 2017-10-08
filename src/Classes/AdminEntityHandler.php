<?php

namespace App\Classes;

use App\Models\BaseModel;
use App\Models\GtcmsSetting;
use Illuminate\Support\Str;

class AdminEntityHandler
{
	public static function edit($object, ModelConfig $modelConfig)
	{
		/** @var BaseModel $object */
		\DB::transaction(function () use (&$object, $modelConfig) {
			$originalObject = clone $object;

			if ($object->id) {
				$action = 'edit';
			} else {
				$action = 'add';
				/** @var BaseModel $fullEntityName */
				$fullEntityName = $modelConfig->myFullEntityName();
				$object = $fullEntityName::create();
			}

			$input = AdminHelper::input($modelConfig, $action);

			AdminHelper::standaloneCheck($modelConfig, $action, $input, $object);
			if (config('gtcms.premium')) {
				AdminEntityHandler::oneToMany($modelConfig, $input);
			}

			$object->update($input);

			AdminEntityHandler::generateSlug($modelConfig, $object, null, false, $originalObject, 0, $action);
			AdminEntityHandler::manyToMany($modelConfig, $input, $object);

			$object->runMutators($action);

			// Set correct position_in_parent values when adding
			// an entry or changing entry's parent(s).
			// This will only apply to table-index because for tree-index
			// $parentData['allParents'] will be empty, as intended

			$parentData = (new BaseModel())->getParentData($modelConfig);
			/** @var BaseModel $entity */
			/** @var ModelConfig $modelConfig */
			$entity = $modelConfig->myFullEntityName();
			$positionProperties = $entity::getPositionPropertyDataFromParentData($parentData, $modelConfig);
			if ($positionProperties) {
				foreach ($positionProperties as $positionProperty => $data) {
					$value = $data['value'];
					$parentIdProperty = $data['parentIdProperty'];

					// If new parentId is NULL, the old position will remain,
					// but that doesn't really matter

					if ($action == 'add' || $object->$parentIdProperty != $originalObject->$parentIdProperty) {
						$object->$positionProperty = $value;
					}
				}
			}

			$object->save();
		});

		return $object;
	}

	public static function editSettings($modelConfig)
	{
		$input = AdminHelper::input($modelConfig, 'edit');

		\DB::transaction(function () use ($input, $modelConfig) {
			foreach ($input as $settingKey => $settingValue) {
				if ($settingKey != "_token") {
					$setting = GtcmsSetting::where('setting_key', $settingKey)->first();
					if ($setting) {
						$setting->setting_value = $settingValue;
						$setting->save();
					}
				}
			}
		});
	}

	public static function manyToMany($modelConfig, &$input, &$object)
	{
		foreach ($modelConfig->formFields as $field) {
			if ($field->type == 'multiSelect' && $field->selectType->type == 'model') {
				$method = $field->selectType->method;
				$relatedIds = [];

				if (config('gtcms.premium') && $field->create) {
					GtcmsPremium::manyToManyCreate($modelConfig, $relatedIds, $field, $input);
				} else {
					if (isset($input[$field->property])) {
						if (config('gtcms.premium')) {
							GtcmsPremium::manyToManyPosition($relatedIds, $input, $field);
						} else {
							$relatedIds = $input[$field->property];
						}
					}
				}

				if (request()->get($field->property . "_exists_in_gtcms_form")) {
					$object->$method()->sync($relatedIds);
				}
			}
		}
	}

	public static function oneToMany($modelConfig, &$input)
	{
		GtcmsPremium::oneToMany($modelConfig, $input);
	}

	public static function generateSlug($modelConfig, &$object, $parentId = null, $recursiveGeneration = false, $originalObject = null, $depth = 0, $action = 'edit')
	{
		if ($modelConfig->generateSlug) {

			if ($modelConfig->skipFirstLevelSlug && $object->depth == 0) {
				return false;
			}

			if ($modelConfig->index == 'tree') {
				$recursiveGeneration = true;
			}

			$iterations = 1;
			$languages = [];
			if (config('gtcms.premium') && $modelConfig->langDependentSlug) {
				$languages = config('gtcmslang.languages');
				$iterations = count($languages);
			}

			$saveObject = false;
			for ($currentLanguage = 0; $currentLanguage < $iterations; $currentLanguage++) {

				$slugProperty = "slug";
				$modelConfigSlugProperty = $modelConfig->slugProperty;

				if (config('gtcms.premium')) {
					GtcmsPremium::setSlugProperty($modelConfig, $languages, $currentLanguage, $slugProperty, $modelConfigSlugProperty);
				}

				$parent = NULL;

				if ($modelConfig->parent) {
					$parentIdProperty = $modelConfig->parent->property;
					/** @var BaseModel $parentName */
					$parentName = ModelConfig::fullEntityName($modelConfig->parent->name);

					if ($parentId) {
						$parent = $parentName::find($parentId);
					} else if (request()->filled($parentIdProperty)) {
						$parent = $parentName::find(request()->get($parentIdProperty));
					}
				}

				$slug = $parent && $parent->$slugProperty ? $parent->$slugProperty : "";

				if (request()->get($modelConfigSlugProperty) && $depth == 0) {
					$slugAddon = (Str::slug(request()->get($modelConfigSlugProperty)));
				} else {
					$slugAddon = Str::slug($object->$modelConfigSlugProperty);
				}

				$slug .= $slug ? "/" . $slugAddon : $slugAddon;
				$finalSlug = $slug;

				/** @var BaseModel $modelName */
				/** @var ModelConfig $modelConfig */
				$modelName = $modelConfig->myFullEntityName();

				$counter = 1;

				// Add
				if ($action == 'add') {
					while ($modelName::where($slugProperty, $finalSlug)->count()) {
						$finalSlug = $slug . "-" . $counter;
						$counter++;
					}

					// Edit
				} else {
					while ($modelName::where($slugProperty, $finalSlug)->where('id', '!=', $object->id)->count()) {
						$finalSlug = $slug . "-" . $counter;
						$counter++;
					}
				}

				if (!is_null($originalObject) && $originalObject->$slugProperty != $finalSlug) {
					$saveObject = true;
				}

				$object->$slugProperty = $finalSlug;

			}

			if ($saveObject) {
				/** @var BaseModel $object */
				$object->save();

				if ($recursiveGeneration) {
					$childrenMethod = $modelConfig->children->method;
					if ($object->$childrenMethod->count()) {
						foreach ($object->$childrenMethod as $childObject) {
							self::generateSlug($modelConfig, $childObject, $object->id, true, $childObject, $depth + 1, 'edit');
						}
					}
				}
			}
		}

		return false;
	}
}