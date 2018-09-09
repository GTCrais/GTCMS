<?php

$displayModel = true;
if ($modelConfig->reverseConstrainingModels) {
	foreach ($modelConfig->reverseConstrainingModels as $constraint) {
		if (method_exists($object, $constraint) && $object->$constraint->count()) {
			$displayModel = false;
			break;
		}
	}
}

$method = $relatedModel->method;
$relatedModelConfig = AdminHelper::modelExists($relatedModel->name);

$prependGets = "?" . $modelConfig->id . "=" . ($object->id ? $object->id : "new_gtcms_entry");
$gets = $prependGets . Tools::getGets([$modelConfig->id => null], false, "&");

$configInParent = $relatedModel;

if (config('gtcms.premium')) {
	GtcmsPremium::setDisplayRelatedModelBasedOnModelKey($configInParent, $object, $displayModel);
}

?>

@if ($displayModel)

	<div class="panel panel-default relatedModel{{$relatedModelConfig->name}}">

		<div class="panel-body">
			<?php
			// --------------- EXCEPTIONS ----------------
			$addObject = true;

			$relatedClassName = $relatedModelConfig->myFullEntityName();

			if (!(new $relatedClassName)->isAddable()) {
				$addObject = false;
			}

			?>

			@if ($addObject)
				<div class="indexTableHeader sideTableHeader">
					<a href="{{AdminHelper::getCmsPrefix() . $relatedModelConfig->name}}/add{{$gets}}&addToParent=true" class="btn btn-primary btn-sm addRelatedObject">
						<i class="fa fa-plus-circle"></i> {{$relatedModelConfig->hrName}}
					</a>
				</div>
			@endif

			<?php
				$relatedObjects = $object->$method()->orderBy($configInParent->orderBy, $configInParent->direction);

				if ($configInParent->paginate) {
					$countQuery = clone $relatedObjects;
					$objectCount = $countQuery->count();

					$pageName = $configInParent->name . "Page";

					$page = isset($ignorePage) ? 1 : (filter_var(request()->get($pageName), FILTER_VALIDATE_INT) ?: 1);

					$limit = $configInParent->perPage;
					$offset = ($page - 1) * $limit;

					$relatedObjects = $relatedObjects
						->limit($limit)
						->offset($offset)
						->get();

					$relatedObjects = new \Illuminate\Pagination\LengthAwarePaginator($relatedObjects, $objectCount, $limit, $page, [
						'path' => request()->url(),
						'pageName' => $pageName
					]);

				} else {
					$relatedObjects = $relatedObjects->get();
				}
			?>

			@if ($relatedObjects->count())
				{!! Front::drawObjectTable($relatedObjects, $relatedModelConfig, 'sideTable', ['parentIdProperty' => $modelConfig->id, 'parentIdValue' => $object->id]) !!}
			@else
				{!! trans('gtcms.noRelatedModels', array('modelName1' => $modelConfig->hrName, 'modelName2' => $relatedModelConfig->hrNamePlural)) !!}
			@endif

		</div>

		<div class="disableRelatedModel {{$action == 'add' ? '' : 'hidden'}}">
			<div class="disableTextContainer">
				<p>{!! trans('gtcms.enableRelatedModels', array('modelName1' => $modelConfig->hrName, 'modelName2' => $relatedModelConfig->hrNamePlural)) !!}</p>
			</div>
		</div>
	</div>

@endif