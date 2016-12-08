<?php

namespace App\Classes;

use Collective\Html\FormFacade as Form;
use Illuminate\Database\Eloquent\Collection;

class Front {

	public static function drawObjectTree($objects, ModelConfig $modelConfig, $parentModelConfig, $depth = 0) {

		$tree = "";
		if ($depth == 0) {
			$tree = "
				<table
					class='table table-hover hasTreeStructure baseContainer sortContainer" . ($depth) . "'
					data-depth='" . ($depth) . "'
					data-modelname='" . ($modelConfig->name) . "'
				>
					<tbody>";
		}

		$addParentNameOriginal = $editParentNameOriginal = "";
		$parentId = "";
		$parentProperty = $objectChildren = false;
		$printProperty = $modelConfig->printProperty;
		if ($modelConfig->parent) {
			$addParentNameOriginal = "?".$modelConfig->parent->property;
			$parentProperty = $modelConfig->parent->property;
		}

		/** @var \App\Models\BaseModel $object */
		foreach ($objects as $object) {

			$addParentName = $editParentName = "";
			$addParentName .= $addParentNameOriginal ? $addParentNameOriginal . '=' . $object->id : '';

			$addDisabled = '';

			if ($parentProperty) {
				$parentId = $object->$parentProperty;
			}

			if ($modelConfig->children) {
				$objectChildren = $modelConfig->children;
				$childModelName = $objectChildren->name;
				$addUrl = AdminHelper::getCmsPrefix() . $childModelName . '/add' . $addParentName;
			} else {
				$addDisabled = "disabled";
				$addUrl = "javascript:;";
			}

			// if objects contains constraining objects, disallow adding children
			if ($modelConfig->constrainingModels) {
				foreach ($modelConfig->constrainingModels as $constraint) {
					if (method_exists($object, $constraint) && $object->$constraint->count()) {
						$addDisabled = "disabled";
						$addUrl = "javascript:;";
						break;
					}
				}
			}

			if (property_exists($modelConfig, 'maxDepth') && $object->depth == $modelConfig->maxDepth) {
				$addDisabled = "disabled";
				$addUrl = "javascript:;";
			}

			$hasChildren = false;
			if ($modelConfig->children) {
				$method = $objectChildren->method;
				/** @var Collection $children */
				$children = $object->$method;
				if ($children->count()) {
					$hasChildren = true;
				}
			}

			$linkProperty = $modelConfig->linkProperty;

			$tree .= "
				<tr class='childTableContainer isSortable isSortable" . $depth . " objectId" . $object->id . "' data-depth='" . ($depth) . "' data-objectid='" . ($object->id) . "'>
					<td colspan='3'>
						<table class='table table-hover hasTreeStructure depth" . ($depth) . "' data-depth='" . ($depth) . "'>
							<tbody>";

			$tree .= '
			<tr class="depth depth' . $depth . ($hasChildren ? ' hasChildren' : ' notSortable') . '" data-depth="' . $depth . '" data-parentid="' . $parentId . '" data-objectid="' . $object->id . '" >
				<td class="sortHandle">
					<div class="sortHandle">
						<i class="fa fa-ellipsis-v"></i>
					</div>
				</td>
				<td><a class="printPropertyValue" href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/edit/' . $object->id . '">' . \Html::entities($object->$linkProperty) . '</a></td>
				<td class="controlButtons controls3">';

			if ($modelConfig->getQuickEditFields('all')) {
				$tree .=
					'<a
						href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/edit/' . $object->id  . '"
						class="btn btn-default btn-xs quickEditButton treeQuickEdit"
						>
							<i class="fa fa-pencil-square-o"></i>
						</a>';
			}

			$tree .= '<a href="' . $addUrl . '" class="btn btn-default btn-xs addButton ' . $addDisabled . '"><i class="fa fa-plus"></i></a>';

			if ($object->isDeletable()) {
				$tree .= '
					<a
						href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/delete/' . $object->id . '"
						class="btn btn-default btn-xs deleteButton"
						data-modelname="' . $modelConfig->name . '"
						data-objectname="' . \Html::entities($object->$printProperty) . '"
					>
						<i class="fa fa-times"></i>
					</a>';
			} else {
				$tree .= '
					<a
						href="#"
						class="btn btn-default btn-xs deleteButton disabled"
					>
						<i class="fa fa-times"></i>
					</a>';
			}
			$tree .= '
				</td>
			</tr>';

			if ($modelConfig->children) {
				$tree .= "
					<tr class='childTableContainer'>
						<td colspan='3'>
							<table class='table table-hover hasTreeStructure sortContainer" . ($depth+1) . "' data-depth='" . ($depth + 1) . "'>
								<tbody class='transitionHeight depth" . ($depth + 1) . "'>";

				$method = $objectChildren->method;
				$children = $object->$method;
				if ($children->count()) {
					$reflect = new \ReflectionClass($children[0]);
					$childObjectName = $reflect->getShortName();
					$childObjectConfig = AdminHelper::modelExists($childObjectName);
					$tree .= self::drawObjectTree($children, $childObjectConfig, $parentModelConfig, $depth + 1);
				}

				$tree .= "
								</tbody>
							</table>
						</td>
					</tr>";
			}


			$tree .= "
							</tbody>
						</table>
					</td>
				</tr>";

		}

		if ($depth == 0) {
			$tree .= "
				</tbody>
			</table>";
		}

		return $tree;

	}

	public static function drawObjectTable($objects, ModelConfig $modelConfig, $tableType = 'table', $parent = "", $searchDataWithFieldValues = false, $ordering = false, $quickEdit = false, $loadSideTablePaginationResults = false) {

		$hasPositionInParent = false;
		$parentModelName = false;
		$hidePositionControls = $modelConfig->hidePositionControls;
		if ($tableType == 'sideTable' && $objects->count()) {
			/** @var \App\Models\BaseModel $firstObject */
			$firstObject = $objects[0];
			$configuration = $firstObject->getRelatedModelConfigurationInParentModel($modelConfig);
			$hasPositionInParent = $configuration['position'];
			$hidePositionControls = $configuration['hidePositionControls'];
			$parentModelName = $configuration['parentModelName'];
		}

		$objectsAreMovable = ((($modelConfig->position && $tableType == 'table') || $hasPositionInParent) && !$hidePositionControls && $objects->count() > 1);

		$tree = "";
		$ctrlNum = 1;

		$controls = true;

		// -------------- EXCEPTIONS ---------------

		// ------------ END EXCEPTIONS -------------

		if (!$quickEdit) {

			$tree .= '<div class="table-responsive ' . ($tableType == "sideTable" ? "objectsContainer " : " ") . ($loadSideTablePaginationResults ? "transparent " : " ") . '">';

			$tree .= '
				<table class="table table-striped table-hover table-type-' . $tableType . ($objectsAreMovable ? ' hasPositioning' : '') . '">
				<tbody class="' . ($searchDataWithFieldValues || $ordering ? ' searchDataPresent' : '') . '">
				<tr>';
			$counter = 0;
			foreach ($modelConfig->formFields as $field) {
				if ($field->restrictedToSuperadmin && !\Auth::user()->is_superadmin) {
					continue;
				}

				$userRole = \Auth::user()->role;
				if ($field->restrictedAccess && !$field->restrictedAccess->$userRole) {
					continue;
				}

				if ($field->$tableType) {
					$counter++;
					$tree .= '
						<th class="controlButtons ' . ($objectsAreMovable && $counter == 1 ? ' sortablePadding ' : '') .
						$field->responsiveClasses . '" ' .
						($objectsAreMovable && $counter == 1 ? 'colspan="2"' : '') . '><span>' . $field->label . '</span>';

					if ($field->order && $tableType == 'table') {
						$tree .= '
							<div class="sortLinks">
								<a class="btn btn-xs" href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '?&orderBy=' . $field->property . '&direction=asc&getIgnore_getSearchResults=true' . (Tools::getSearchAndOrderGets(false, true, true)) . '" class="sortLink" data-loadtype="fadeIn">
									<i class="fa fa-caret-up"></i>
								</a>
								<a class="btn btn-mini" href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '?orderBy=' . $field->property . '&direction=desc&getIgnore_getSearchResults=true' . (Tools::getSearchAndOrderGets(false, true, true)) . '" class="sortLink" data-loadtype="fadeIn">
									<i class="fa fa-caret-down"></i>
								</a>
							</div>
						';
					}

					$tree .= '</th>';
				}
			}

			if ($controls) {
				$tree .= '
			<th class="controls' . $ctrlNum . '"><span>' . trans('gtcms.controls') . '</span></th>';
			}

			$tree .= '
			</tr>';
		}

		/** @var \App\Models\BaseModel $object */
		foreach ($objects as $object) {

			$objectName = "";
			$gets = $parent ? $parent . (Tools::getGets() ? "&" . Tools::getGets(array(), false, false) : '') : Tools::getGets();

			$tree .= '
			<tr
				class="depth ' . ($objectsAreMovable ? 'isSortable' : '') . ' ' . ($quickEdit ? 'rowSelectize' : '') . '"
				data-objectid="' . ($object->id) . '"
				data-modelname="' . ($modelConfig->name) . '"
				data-parentname="' . ($parentModelName) . '"
			>';
			//regular fields
			$counter = 0;
			foreach ($modelConfig->formFields as $index => $field) {
				if ($field->restrictedToSuperadmin && !\Auth::user()->is_superadmin) {
					continue;
				}

				$userRole = \Auth::user()->role;
				if ($field->restrictedAccess && !$field->restrictedAccess->$userRole) {
					continue;
				}

				if ($field->$tableType) {
					$counter++;
					$image = false;
					$method = false;
					if ($field->displayProperty) {
						if ($field->displayProperty->type == 'accessor') {
							$property = $field->displayProperty->method;
						} else if ($field->displayProperty->type == 'image') {
							$method = $field->displayProperty->method;
							$image = true;
						} else if ($field->displayProperty->type == 'model') {
							$property = $field->displayProperty->property;
							$method = $field->displayProperty->method;
							$relatedProperty = $field->displayProperty->property;

							if ($field->displayProperty->multiple && $object->$method->count()) {
								if ($field->displayProperty->autoSort === false || !config('gtcms.premium')) {
									$relatedModels = $object->$method;
								} else {
									$relatedModels = $object->$method()->withPivot('position')->orderBy('pivot_position', 'asc')->get();
								}

								$value = "";
								foreach ($relatedModels as $relModel) {
									$value .= ($relModel->$relatedProperty).", ";
								}
								$value = rtrim($value, ", ");
							} else if (!$field->displayProperty->multiple && $object->$method) {
								$value = $object->$method->$relatedProperty;
							} else {
								$value = " - ";
							}
						} else {
							$property = $field->property;
						}
					} else {
						$property = $field->property;
					}

					if ($objectsAreMovable && $counter == 1) {
						$tree .= "
								<td class='sortHandle'>
									<div class='sortHandle'>
										<i class='fa fa-ellipsis-v'></i>
									</div>
								</td>";
					}

					$tree .= "<td class='" . ($field->responsiveClasses) . "'>";

					if (property_exists($field, $tableType.'Link') && !$image) {
						if ($field->displayProperty && $field->displayProperty->type == 'model') {
							$tree .= '<a href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/edit/' . $object->id . $gets . '">' . ($object->$method ? \Html::entities($object->$method->$property) : '- deleted -') . '</a>';
							if (!$objectName) {
								$objectName = $object->$method ? \Html::entities($object->$method->$property) : '';
							}
						} else {
							$tree .= '<a href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/edit/' . $object->id . $gets . '">' . \Html::entities($object->$property) . '</a>';
							if (!$objectName) {
								$objectName = \Html::entities($object->$property);
							}
						}
					} else if ($image) {
						if (!$method) {
							$method = $image;
						}
						$tree .= '
							<a href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/edit/' . $object->id . $gets . '">
								<img style="height: 60px;" src="' . $object->$method('url', 'gtcmsThumb') . '">
							</a>';
						if (!$objectName) {
							$objectName = "image";
						}
					} else if ($field->displayProperty && $field->displayProperty->type == 'model') {
						if ($field->displayProperty->multiple) {
							$tree .= \Html::entities($value);
						} else {
							$tree .= ($object->$method ? \Html::entities($object->$method->$property) : ' - ');
						}
					} else if (in_array($field->type, array('date', 'dateTime'))) {
						$tree .= $object->formatDate($object->$property, $field->displayProperty->dateFormat ? $field->displayProperty->dateFormat : $field->dateFormat);
					} else if ($field->type == 'checkbox') {
						$tree .= ($object->$property ? self::drawCheckboxIcon(true) : self::drawCheckboxIcon(false));
					} else if ($field->type == 'select' && $field->indexSelect) {
						$originalValue = $object->$property;
						$listMethod = $field->selectType->listMethod;
						$options['class'] = "";
						$list = array();
						if ($field->selectType->type == 'model') {
							$selectModel = $field->selectType->modelName;
							$fullModel = ModelConfig::fullEntityName($selectModel);
							$list = $fullModel::$listMethod();
						} else if ($field->selectType->type == 'list') {
							$entity = $modelConfig->myFullEntityName();
							$list = $entity::$listMethod();
						}

						if (!is_array($list) && is_object($list)) {
							$reflection = new \ReflectionClass($list);
							if ($reflection->getShortName() == "Collection") {
								/** @var \Illuminate\Support\Collection $list */
								$list = $list->toArray();
							}
						}

						if ($field->selectablePlaceholder) {
							$null = array('' => '-');
							$list = $null+$list;
						}

						$options['class'] .= ' ajaxSelectUpdate ';
						$options['data-classname'] = $modelConfig->name;
						$options['data-objectid'] = $object->id;
						$options['data-property'] = $property;
						$options['data-token'] = csrf_token();
						if ($field->indexClass) {
							$options['class'] .= ' ' . $field->indexClass . ' ';
						} else {
							$options['class'] .= ' standardSelectWidth ';
						}
						$tree .= \Form::select($field->property, $list, $originalValue, $options);

					} else {
						$tree .= \Html::entities($object->$property);
					}

					$tree .= "</td>";
				}
			}

			if ($controls) {
				$quickEditControl = "";
				if (config('gtcms.premium') && $modelConfig->getQuickEditFields('all')) {
					$quickEditControl = GtcmsPremium::getQuickEditControl($modelConfig, $object, $gets);
				}

				if ($object->isDeletable()) {
					$tree .= '<td class="controlButtons">';
					$tree .= $quickEditControl;
					$tree .=
						'<a
						href="' . AdminHelper::getCmsPrefix() . $modelConfig->name . '/delete/' . $object->id . $gets . '"
						class="btn btn-default btn-xs deleteButton"
						data-modelname="' . $modelConfig->hrName . '"
						data-objectname="' . \Html::entities($objectName) . '"
						>
							<i class="fa fa-times"></i>
						</a>
					</td>';
				} else {
					$tree .= '
					<td class="controlButtons">';
					$tree .= $quickEditControl;
					$tree .=
						'<a
						href="#"
						class="btn btn-default btn-xs deleteButton disabled"
						>
							<i class="fa fa-times"></i>
						</a>
					</td>';
				}
			}

			$tree .= '</tr>';
		}

		if (!$quickEdit) {
			$tree .= '
					</tbody>
				</table>';

			if (is_a($objects, "Illuminate\\Pagination\\LengthAwarePaginator") && $objects->hasPages()) {
				$tree .= '<div class="paginationContainer" data-tabletype="' . $tableType . '">' .
					$objects->appends(Tools::getGets(array(
						$objects->getPageName() => NULL,
						'getIgnore_getSearchResults' => 'true',
						'getIgnore_tableType' => $tableType,
						'getIgnore_modelName' => $modelConfig->name
					), TRUE))->links() .
					'</div>';
			}

			$tree .= "</div>";
		}

		return $tree;

	}

	public static function getHistoryLinks() {
		$links = AdminHistoryManager::getHistory();
		$returnLinks = "";
		if ($links) {
			foreach ($links as $link) {
				$returnLinks .=
					'<a data-loadtype="moveRight" href="' . $link['link'] . '"><i class="fa ' . $link['modelIcon'] . '"></i> ' . $link['modelName'] . '</a> <i class="fa fa-caret-right"></i>';
			}
			return $returnLinks;
		} else {
			return "";
		}
	}

	public static function drawSearch(ModelConfig $modelConfig, $searchParams = false) {

		$html = \Form::open(
			array(
				'method' => 'get',
				'url' => AdminHelper::getCmsPrefix() . ($modelConfig->name),
				'class' => 'searchForm model' . $modelConfig->name . " " . (($modelConfig->form && $modelConfig->form->horizontal) ? ' form-horizontal' : '')
			)
		);

		$labelClass = "";
		if ($modelConfig->searchForm && $modelConfig->searchForm->horizontal) {
			$labelClass = " control-label col-sm-".$modelConfig->searchForm->labelWidth;
		}

		foreach ($modelConfig->getFormFields('all', true) as $field) {
			if ($field->restrictedToSuperadmin && !\Auth::user()->is_superadmin) {
				continue;
			}

			$userRole = \Auth::user()->role;
			if ($field->restrictedAccess && !$field->restrictedAccess->$userRole) {
				continue;
			}

			if ($field->search) {
				$html .= "<div class='form-group'>";
				$options = array(
					'class' => ' form-control '
				);

				if ($field->options) {
					foreach ($field->options as $key => $value) {
						if ($key == 'class') {
							$options[$key] .= $value;
						} else if ($key == 'readonly') {
							$options[$key] = 'readonly';
						} else {
							//ignore other options for search
						}
					}
				}

				if ($field->search->type == 'standard') {
					$label = $field->search->label ? $field->search->label : $field->label;

					if (!in_array($field->type, array('checkbox', 'radio', 'hidden'))) {
						$html .= \Form::label("search_" . $field->property, $label, array('class' => $labelClass));
					}

					if ($modelConfig->searchForm && $modelConfig->searchForm->horizontal) {
						$class = "col-sm-".$modelConfig->searchForm->inputWidth;
						if (in_array($field->type, array('checkbox', 'radio'))) {
							$class .= " col-sm-offset-" . $modelConfig->searchForm->labelWidth;
						}
						$html .= "<div class='" . $class . "'>";
					}

					if ($field->type == 'text' || $field->type == 'textarea') {
						$html .= \Form::text("search_" . $field->property, \Request::get("search_" . $field->property), $options);
					} else if ($field->type == 'checkbox') {
						$html .= "<div class='checkbox'><label>";
						$html .= \Form::checkbox("search_" . $field->property, 1, \Request::get("search_" . $field->property));
						$html .= " " . $label . "</label></div>";
					} else if (in_array($field->type, array('select', 'multiSelect'))) {
						$listMethod = $field->selectType->listMethod;
						$options['class'] = '';
						$list = array();
						if ($field->selectType->type == 'model') {
							/** @var \App\Models\BaseModel $selectModel */
							$selectModel = ModelConfig::fullEntityName($field->selectType->modelName);
							if ($field->selectType->ajax) {
								$list = array();
								if ($value = \Request::get('search_' . $field->property)) {
									$valueProperty = $field->selectType->ajax->valueProperty;
									$list = $selectModel::where('id', $value)->get()->pluck($valueProperty, 'id');
								}

								$options['data-searchfields'] = $field->selectType->ajax->searchFields;
								$options['data-model'] = $field->selectType->modelName;
								$options['data-value'] = "id";
								$options['data-text'] = $field->selectType->ajax->valueProperty;
								$options['class'] .= " ajax ";
							} else {

								// Even if 'callMethodOnInstance' is declared we need a static method
								// of the same name which will return the list of ALL selectable items
								// instead of just the ones a particular object would return
								// This method must be declared in Related Model Class

								$list = $selectModel::$listMethod();
							}
						} else if ($field->selectType->type == 'list') {
							$entity = $modelConfig->myFullEntityName();
							$list = $entity::$listMethod();
						}

						if (!is_array($list) && is_object($list)) {
							$reflection = new \ReflectionClass($list);
							if ($reflection->getShortName() == "Collection") {
								/** @var \Illuminate\Support\Collection $list */
								$list = $list->toArray();
							}
						}

						$options['placeholder'] = " - ";
						$options['class'] .= ' selectizeNoCreate doSelectize';
						$html .= \Form::select("search_".$field->property, $list, \Request::get("search_".$field->property), $options);
					} else if (in_array($field->type, array('date', 'dateTime'))) {
						if ($field->type == 'date') {
							$options['class'] .= ' datePicker ';
						} else if ($field->type == 'dateTime') {
							$options['class'] .= ' dateTimePicker ';
						}
						$html .= \Form::text("search_" . $field->property, \Request::get("search_".$field->property), $options);
					}
				} else if ($field->search->type == 'exception') {
					//custom code here

				}

				if ($modelConfig->searchForm && $modelConfig->searchForm->horizontal) {
					$html .= "</div>";
				}

				$html .= "</div>";
			}
		}

		$html .= \Form::submit(trans('gtcms.search'), array('class' => 'btn btn-default cBoth floatNone'));
		$html .= "
			<div class='formSubmitMessage'>
				<div class='formSpinner'></div>
				<span class='errorMessage'></span>
			</div>
		";
		$html .= \Form::close();

		return $html;
	}

	public static function drawSearchCriteria($searchData) {
		$html = "";
		if ($searchData) {
			$html .= "
				<ul class='searchCriteria'>
					<li class='searchCriteriaTitle'>" . trans('gtcms.searchResultsForCriteria') . ":</li>
			";

			foreach ($searchData as $criteria) {
				$html .= "
					<li><strong>" . $criteria['label'] . ":</strong> " . \Html::entities($criteria['value']) . "
				";
			}

			$html .= "
				</ul>
			";
		}
		return $html;
	}

	public static function showMessages() {
		$messages = "";
		if ($msg = MessageManager::getException()) {
			$messages .=
				'<div class="alert alert-danger alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					' . $msg . '
				</div>';
		}
		if ($msg = MessageManager::getError()) {
			$messages .=
				'<div class="alert alert-warning alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					' . $msg . '
				</div>';
		}
		if ($msg = MessageManager::getSuccess()) {
			$messages .=
				'<div class="alert alert-info alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					' . $msg . '
				</div>';
		}

		return $messages;
	}

	public static function embedVideo($url, $params = array()) {
		$data = Tools::parseMediaUrl($url);

		$class = isset($params['class']) ? $params['class'] : "";
		$width = isset($params['width']) ? 'width="' . $params['width'] . '"' : "";
		$height = isset($params['height']) ? 'height="' . $params['height'] . '"' : "";

		$embedCode = "";

		if ($data) {
			if ($data['sourceKey'] == 'youtube') {
				$embedCode = '
					<div class="videoContainer ' . $class . '">
						<iframe ' . $width . ' ' . $height . ' src="//www.youtube.com/embed/' . $data['originalId'] . '" frameborder="0" allowfullscreen></iframe>
					</div>
				';
			} else {
				$embedCode = '
					<div class="videoContainer ' . $class . '">
						<iframe src="//player.vimeo.com/video/' . $data['originalId'] . '"
							' . $width . '
							' . $height . '
							frameborder="0"
							webkitallowfullscreen
							mozallowfullscreen allowfullscreen>
						</iframe>
					</div>
				';
			}
		}

		return $embedCode;
	}

	public static function drawCheckboxIcon($success = true, $return = true) {
		if ($success) {
			$icon = '<i class="fa fa-check adminBlue"></i>';
		} else {
			$icon = '<i class="fa fa-times adminOrange"></i>';
		}

		if ($return) {
			return $icon;
		} else {
			echo $icon;
		}
	}

}