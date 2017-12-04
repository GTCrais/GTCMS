<?php

$method = $relatedModel->method;
$relatedModelConfig = AdminHelper::modelExists($relatedModel->name);

$prependGets = "?" . $modelConfig->id . "=" . ($object->id ? $object->id : "new_gtcms_entry");
$gets = $prependGets . Tools::getGets([$modelConfig->id => null], false, "&");

$configInParent = $relatedModel;

if (config('gtcms.premium')) {
	GtcmsPremium::setDisplayRelatedModelBasedOnModelKey($configInParent, $object, $displayModel);
}

?>

<div class="panel panel-default relatedModel{{$relatedModelConfig->name}}">
	@if (in_array($relatedModelConfig->name, ['AppLog', 'DbBackup', 'AppLogEntry']))
		<div class="panel-heading">
			<h4 class="modelName">{{$relatedModelConfig->hrNamePlural}}</h4>
		</div>
	@endif


	<div class="panel-body">
		<?php
		// --------------- EXCEPTIONS ----------------
		$addObject = true;
		$showHeader = false;

		$relatedClassName = $relatedModelConfig->myFullEntityName();

		if (!(new $relatedClassName)->isAddable()) {
			$addObject = false;
		}

		if ($addObject || in_array($relatedModelConfig->name, ['AppLog', 'DbBackup'])) {
			$showHeader = true;
		}

		?>
		@if ($showHeader)
			<div class="indexTableHeader sideTableHeader">
				@if ($addObject)
					<a href="{{AdminHelper::getCmsPrefix() . $relatedModelConfig->name}}/add{{$gets}}&addToParent=true" class="btn btn-primary btn-sm addRelatedObject">
						<i class="fa fa-plus-circle"></i> {{$relatedModelConfig->hrName}}
					</a>
				@endif

				@if ($relatedModel->name == 'AppLog')
					<a href="{{url()->route('initialLogsRequest', ['applicationId' => $object->id ?: 'new_gtcms_entry'])}}"
					   class="btn btn-primary btn-sm appRequest makeInitialRequest {{$object->canMakeInitialLogsRequest() ? '' : 'hidden'}}"
					>
						<i class="fa fa-plus-circle"></i> Initial Logs check
						<div class="buttonSpinner"></div>
					</a>

					<a href="{{url()->route('scheduleNextLogsRequest', ['applicationId' => $object->id ?: 'new_gtcms_entry'])}}"
					   class="btn btn-primary btn-sm appRequest scheduleNextLogsCheck {{$object->canScheduleNextLogsRequest() ? '' : 'hidden'}}"
					>
						<i class="fa fa-plus-circle"></i> Schedule next Logs check
						<div class="buttonSpinner"></div>
					</a>
				@endif

				@if ($relatedModel->name == 'DbBackup')
					<a href="{{url()->route('initialDatabaseBackup', ['applicationId' => $object->id ?: 'new_gtcms_entry'])}}"
					   class="btn btn-primary btn-sm appRequest makeInitialRequest {{$object->canMakeInitialDbBackupRequest() ? '' : 'hidden'}}"
						>
						<i class="fa fa-plus-circle"></i> Initial Database backup
						<div class="buttonSpinner"></div>
					</a>

					<a href="{{url()->route('scheduleDatabaseBackup', ['applicationId' => $object->id ?: 'new_gtcms_entry'])}}"
					   class="btn btn-primary btn-sm appRequest scheduleNextLogsCheck {{$object->canScheduleNextDbBackupRequest() ? '' : 'hidden'}}"
						>
						<i class="fa fa-plus-circle"></i> Schedule next Database backup
						<div class="buttonSpinner"></div>
					</a>
				@endif
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