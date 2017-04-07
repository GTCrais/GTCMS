<?php
if ($modelConfig->index == 'tree' && $object->id) {
	$parentIdProperty = $modelConfig->parent->property;
	//echo Form::hidden($parentIdProperty, $object->$parentIdProperty, array());
}

if ($quickEdit) {
	$formFields = $modelConfig->getQuickEditFields($fieldType);
	$modelConfig->form = false;
} else {
	$formFields = $modelConfig->getFormFields($fieldType);
}

foreach ($formFields as $field) {

	$continue = false;

	if (!$quickEdit && $modelConfig->tabs && $field->tab != $tab) {
		$continue = true;
	}

	if ($field->restrictedToSuperadmin && !Auth::user()->is_superadmin) {
		$continue = true;
	}

	$userRole = Auth::user()->role;
	if ($field->restrictedAccess && !$field->restrictedAccess->$userRole) {
		$continue = true;
	}

	if (config('gtcms.premium')) {
		GtcmsPremium::setContinueForModelsAndModelKeyPropertyOptions($field, $modelConfig, $object, $continue);
	}

	$add = false;
	$edit = true;

	if (!$object->id) {
		$add = true;
		$edit = false;
	}

	if ($field->hidden && $field->hidden->edit && $edit) {
		$continue = true;
	}

	if ($field->hidden && $field->hidden->add && $add) {
		$continue = true;
	}

	if ($continue) {
		continue;
	}

	$iterations = 1;
	$languages = $originalLabel = $originalProperty = false;

	if (config('gtcms.premium')) {
		GtcmsPremium::setEditFormLanguageVars($languages, $iterations, $originalLabel, $originalProperty, $field);
	}
	if ($ignoreLanguageIterations) {
		$iterations = 1;
	}

	for ($currentLanguage = 0; $currentLanguage < $iterations; $currentLanguage++) {

		$trueCurrentLanguage = $currentLanguage;
		if ($ignoreLanguageIterations) {
			$trueCurrentLanguage = $langContentIndex;
		}

		if ($field->subTab) {
			if ($field->subTab->startSubTabGroup) {
				echo '
					<div class="panel panel-default">
						<div class="panel-body">
							<ul class="nav nav-tabs">
				';

				foreach ($field->subTab->subTabList as $subTabIndex => $subTabName) {
					echo '
						<li class="'.($subTabIndex == 0 ? "active" : "").'">
							<a href="#tab-' . (\Illuminate\Support\Str::slug($subTabName)) . '" class="standardLink" data-toggle="tab" aria-expanded="' . ($subTabIndex == 0 ? "true" : "false") . '">' . ($subTabName) . '</a>
						</li>
					';
				}

				echo '
					</ul>
					<div class="tab-content">
				';
			}

			if ($field->subTab->startSubTab) {
				echo '
					<div class="tab-pane fade ' . ($field->subTab->open ? "active in" : "") . '" id="tab-' . (\Illuminate\Support\Str::slug($field->subTab->name)) . '">
				';
			}
		}

		$infoClass = "";
		$infoWidthClass = "";

		$labelClass = "";
		if ($modelConfig->form && $modelConfig->form->horizontal) {
			$labelClass = " control-label col-sm-" . $modelConfig->form->labelWidth;
			$infoWidthClass = " col-sm-offset-" . $modelConfig->form->labelWidth . " col-sm-" . $modelConfig->form->inputWidth;
		}

		$role = \Auth::user()->role;

		if ($field->viewField || ($field->viewFieldForRoles && $field->viewFieldForRoles->$role)) {

			try {
				$label = AdminHelper::getModelConfigFieldValue($modelConfig, clone $field, $object, $trueCurrentLanguage, true);
				$value = " - ";

				if ($edit) {
					$value = AdminHelper::getModelConfigFieldValue($modelConfig, clone $field, $object, $trueCurrentLanguage);
					if (!$value) $value = " - ";
				}
			} catch (Exception $e) {
				$value = " --- error --- ";
				Dbar::error("View Field Error (EditContentFormFields): " . $field->property);
			}

			echo '
				<div class="form-group disabledInput">
				<label for="disabledSelect" class="' . $labelClass . '">' . $label . '</label>';

			if ($modelConfig->form && $modelConfig->form->horizontal) {
				echo "<div class='col-sm-" . $modelConfig->form->inputWidth . "'>";
			}

			echo '<input class="form-control disabledInput readOnly " type="text" readonly="readonly" value="' . $value . '">';

			if ($modelConfig->form && $modelConfig->form->horizontal) {
				echo "</div>";
			}

			echo '</div>';

			if ($field->subTab) {
				if ($field->subTab->endSubTab) {
					echo "</div>";
				}
				if ($field->subTab->endSubTabGroup) {
					echo "
						</div>
					</div>
				</div>";
				}
			}

			continue;
		}

		$containerClass = $field->containerClass;

		if (config('gtcms.premium')) {
			GtcmsPremium::setEditFormLangLabelAndProperty($field, $languages, $trueCurrentLanguage, $originalLabel, $originalProperty);
		}

		$type = $field->type;

		if (in_array($type, array('checkbox'))) {
			$infoClass = "checkInfo";
		}

		$showInfo = false;
		if ((!$field->hiddenInfo ||
				($object->id && !$field->hiddenInfo->edit) ||
				(!$object->id && !$field->hiddenInfo->add)) &&
			$field->info
		) {
			$showInfo = true;
		}

		$infoSpans = "";

		//info
		if ($field->info || $field->specialInfo || $field->showDimensions) {
			if ($showInfo) {
				$infoSpans  .= "<span class='info " . $infoClass . $infoWidthClass . "'>" . $field->info . "</span>";
			}
			if ($type == 'image' && $modelConfig->name == 'ModelImage' && $dimensions = AdminHelper::modelImageMinDimensions()) {
				if ($dimensions[2] == 'resize') {
					$infoSpans .= "<span class='info " . $infoWidthClass . "'>" . trans('gtcms.minimumDimensions') . ": <strong>" . $dimensions[0] . "px width</strong> OR <strong>" . $dimensions[1] . "px height</strong></span>";
				} else if ($dimensions[2] == 'minWidth') {
					$infoSpans .= "<span class='info " . $infoWidthClass . "'>" . trans('gtcms.minimumDimensions') . ": <strong>" . $dimensions[0] . "px width</strong></span>";
				} else if ($dimensions[2] == 'minHeight') {
					$infoSpans .= "<span class='info " . $infoWidthClass . "'>" . trans('gtcms.minimumDimensions') . ": <strong>" . $dimensions[1] . "px height</strong></span>";
				} else {
					$infoSpans .= "<span class='info " . $infoWidthClass . "'>" . trans('gtcms.minimumDimensions') . ": <strong>" . $dimensions[0] . "px</strong> x <strong>" . $dimensions[1] . "px</strong></span>";
				}
			} else if ($type == 'image' && $modelConfig->name != 'ModelImage') {
				foreach ($field->sizes as $size) break;
				$dimensions = AdminHelper::objectToArray($size);
				if ($dimensions[2] == 'resize') {
					$infoSpans .= "<span class='info " . $infoWidthClass . "'>" . trans('gtcms.minimumDimensions') . ": <strong>" . (is_null($dimensions[0]) ? 1 : $dimensions[0]) . "px width</strong> OR <strong>" . (is_null($dimensions[1]) ? 1 : $dimensions[1]) . "px height</strong></span>";
				} else {

					$infoSpans .= "<span class='info " . $infoWidthClass . "'>" . trans('gtcms.minimumDimensions') . ": <strong>" . (is_null($dimensions[0]) ? 1 : $dimensions[0]) . "px</strong> x <strong>" . (is_null($dimensions[1]) ? 1 : $dimensions[1]) . "px</strong></span>";
				}
			}
		}

		if ($field->specialInfo) {
			$infoSpans .= "<span class='info specialInfo  " . $infoClass . " " . $infoWidthClass . "'>"  .  $field->specialInfo  .  "</span>";
		}

		$ctrlGroup = " " . $containerClass;

		$property = $field->property;

		if ($add && $field->default) {
			$originalValue = $field->default;
		} else {
			$originalValue = $object->$property;
		}

		if ($field->autofill === false) {
			$originalValue = "";
		}
		$options = array(
			'class' => ' form-control '
		);

		if ($field->options) {
			foreach ($field->options as $key => $value) {
				if ($key == 'class') {
					$options[$key] .= $value;
				} else {
					$options[$key] = $value;
				}
			}
		}

		$showEditIcon = false;
		$showEditIconClass = '';
		if (!in_array($type, array('checkbox', 'select', 'multiSelect', 'image', 'file'))) {
			$showEditIcon = true;
			$showEditIconClass = 'hasEditIcon';
		}

		$formGroupClass = $type == 'multiSelect' || $type == 'select' ? 'form-group isSelect' : 'form-group';

		echo "<div class='{$formGroupClass} {$ctrlGroup} {$showEditIconClass}'>";

		//label
		$fieldRules = ModelConfig::rulesToString($field->rules);
		if (strpos($fieldRules, 'required') !== false || (strpos($fieldRules, 'addRequired') !== false && !$object->id)) {
			$field->label .= " *";
		}

		if ($modelConfig->form && $modelConfig->form->horizontal) {
			echo $infoSpans;
		}

		if (!in_array($type, array('checkbox', 'hidden'))) {
			echo Form::label($field->property, $field->label, array('class' => $containerClass . $labelClass));
		}

		if (!($modelConfig->form && $modelConfig->form->horizontal)) {
			echo $infoSpans;
		}

		if ($modelConfig->form && $modelConfig->form->horizontal) {
			$class = "col-sm-".$modelConfig->form->inputWidth;
			if (in_array($type, array('checkbox', 'radio'))) {
				$class .= " col-sm-offset-".$modelConfig->form->labelWidth;
			}
			echo "<div class='".$class."'>";
		}

			// ----------- CHECKBOX ------------

		if (in_array($type, array('checkbox'))) {
			$fieldProperty = $field->property;
			echo '<input type="hidden" value="0" name="' . $field->property . '">';
			echo "<div class='checkbox'><label>";
			echo Form::$type($field->property, 1, $originalValue);
			echo " " . $field->label . "</label></div>";

			$showEditIcon = false;

			// ----------- SINGLE SELECT ------------

		} else if ($type == 'select') {
			$listMethod = $field->selectType->listMethod;
			$options['class'] = "";
			if ($field->selectType->type == 'model') {
				if ($field->selectType->ajax && config('gtcms.premium')) {
					$options['data-searchfields'] = $field->selectType->ajax->searchFields;
					$options['data-model'] = $field->selectType->modelName;
					$options['data-value'] = "id";
					$options['data-text'] = $field->selectType->ajax->valueProperty;
					$options['class'] .= " ajax ";
					$method = $field->selectType->method;
					$valueProperty = $field->selectType->ajax->valueProperty;
					$list = $object->$method()->get()->pluck($valueProperty, 'id');
				} else {
					if ($field->selectType->callMethodOnInstance) {
						$list = $object->$listMethod();
					} else {
						$selectModel = $field->selectType->modelName;
						$fullModel = ModelConfig::fullEntityName($selectModel);
						$list = $fullModel::$listMethod();
					}
				}
			} else if ($field->selectType->type == 'list') {
				$entity = $modelConfig->myFullEntityName();
				$list = $entity::$listMethod();
			}

			if (!is_array($list) && is_object($list)) {
				$reflection = new ReflectionClass($list);
				if ($reflection->getShortName() == "Collection") {
					/** @var \Illuminate\Support\Collection $list */
					$list = $list->toArray();
				}
			}

			if (isset($_GET[$field->property]) && !$object->id) {
				if (isset($list[$_GET[$field->property]])) {
					$options['readonly'] = true;
					$options['class'] .= " form-control ";
					echo Form::hidden($field->property, $_GET[$field->property]);
					echo Form::text('dummy', $list[$_GET[$field->property]], $options);
				} else {
					Throw new Exception("You are not allowed to add ".$modelConfig->hrNamePlural." for this model, or non-existing parent model.");
				}
			} else {
				$options['placeholder'] = is_string($field->selectablePlaceholder) ? $field->selectablePlaceholder : " - ";
				$options['class'] .= $field->create ? ' doSelectize selectizeCreate ' : ' doSelectize selectizeNoCreate ';
				$options['class'] .= $field->selectablePlaceholder ? " selectablePlaceholder " : '';
				echo Form::select($field->property, $list, \Request::old($field->property) ? \Request::old($field->property) : $originalValue, $options);
			}

			// ----------- MULTISELECT ------------

		} else if ($type == 'multiSelect') {
			$method = $field->selectType->method;
			$params = array();
			$originalValue = NULL;
			$originalValue = Tools::createItemList($object->$method()->withPivot('position')->orderBy('pivot_position', 'asc')->get(), $originalValue, $params);
			$originalValue = Tools::createMultiSelectList($originalValue);
			if ($field->autofill === false) {
				$originalValue = "";
			}

			$list = array();
			$options['class'] = $field->create ? ' doSelectize selectizeCreate required' : ' doSelectize selectizeNoCreate required';
			$options['multiple'] = 'multiple';

			$listMethod = $field->selectType->listMethod;
			if ($field->selectType->type == 'model') {
				if ($field->selectType->ajax && config('gtcms.premium')) {
					$options['data-searchfields'] = $field->selectType->ajax->searchFields;
					$options['data-model'] = $field->selectType->modelName;
					$options['data-value'] = "id";
					$options['data-text'] = $field->selectType->ajax->valueProperty;
					$options['class'] .= " ajax ";
					$method = $field->selectType->method;
					$valueProperty = $field->selectType->ajax->valueProperty;
					$list = $object->$method()->get()->pluck($valueProperty, 'id');
				} else {
					if ($field->selectType->callMethodOnInstance) {
						$list = $object->$listMethod();
					} else {
						$selectModel = $field->selectType->modelName;
						$fullModel = ModelConfig::fullEntityName($selectModel);
						$list = $fullModel::$listMethod();
					}

				}
			} else {
				throw new \Exception ("Error: selectType['type'] must be 'model'");
			}

			if (!is_array($list)) {
				if (is_object($list)) {
					$reflection = new ReflectionClass($list);
					if ($reflection->getShortName() == "Collection") {
						/** @var \Illuminate\Support\Collection $list */
						$list = $list->toArray();
					}
				} else {
					$list = array();
				}
			}

			$list = (array('gtcms_load_default' => 'gtcms_load_default'))+($list);
			$originalValue = (is_array($originalValue) ? $originalValue : array($originalValue)) + (array('gtcms_load_default' => 'gtcms_load_default'));

			$selectedValues = $originalValue;

			if (config('gtcms.premium')) {
				GtcmsPremium::sortMultiSelectList($selectedValues, $list);
			}

			$options['id'] = $field->property;
			echo Form::hidden($field->property . "_exists_in_gtcms_form", 1);
			echo Form::select($field->property . "[]", $list, $selectedValues, $options);

			// ----------- IMAGE ------------

		} else if($type == 'image') {
			$options['class'] = $options['class'] . " fileUpload";
			$property = $field->property;
			$imageFieldData = AdminHelper::getImageFieldRequirements($modelConfig, $field->property);
			$method = "image";
			if ($field->displayProperty && $field->displayProperty->method) {
				$method = $field->displayProperty->method;
			}

			?>
			@include("gtcms.elements.imageUpload")
			<?php

			// ----------- FILE ------------

		} else if($type == 'file') {
			$options['class'] = $options['class'] . " fileUpload";
			$property = $field->property;
			$method = "file";
			if ($field->displayProperty && $field->displayProperty->method) {
				$method = $field->displayProperty->method;
			}

			?>
			@include("gtcms.elements.fileUpload")
			<?php

			// ----------- DATE / DATETIME ------------

		} else if (in_array($type, array('date', 'dateTime'))) {
			$originalValue = $object->formatDate($originalValue, $field->dateFormat);
			if ($type == 'date') {
				$options['class'] .= ' datePicker ';
			} else if ($type == 'dateTime') {
				$options['class'] .= ' dateTimePicker ';
			}
			echo Form::text($field->property, \Request::old($field->property) ? \Request::old($field->property) : $originalValue, $options);

			// ----------- OTHER FIELDS ------------

		} else {
			if ($field->type == "textarea" &&
				$field->options &&
				$field->options->class &&
				strpos($field->options->class, "autosize") !== false &&
				!$field->options->rows)
			{
				$options['rows'] = 2;
			}
			echo Form::$type($field->property, \Request::old($field->property) ? \Request::old($field->property) : $originalValue, $options);
		}

		if ($showEditIcon) {
			echo '<i class="fa fa-pencil inputEdit"></i>';
		}

		if ($field->subTab) {
			if ($field->subTab->endSubTab) {
				echo "</div>";
			}
			if ($field->subTab->endSubTabGroup) {
				echo "
						</div>
					</div>
				</div>";
			}
		}

		if ($modelConfig->form && $modelConfig->form->horizontal) {
			echo "</div>";
		}

		echo "</div>";

	}

}

if ($quickEdit) {
	echo Form::hidden('getIgnore_quickEdit', true);
}

echo "<div class='cBoth'></div>";
if (!$hideSave) {
	echo Form::submit(trans('gtcms.save'), array('class' => 'btn btn-default cBoth floatNone'));
	echo "
		<div class='formSubmitMessage'>
			<div class='formSpinner'></div>
			<i class='fa fa-check'></i>
			<span class='errorMessage'></span>
		</div>
	";
}
?>