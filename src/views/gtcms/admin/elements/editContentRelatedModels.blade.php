<div class="col-lg-6">

	@foreach ($modelConfig->relatedModels as $relatedModel)
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
		$gets = $prependGets . Tools::getGets([], false, "&");

		$configInParent = $object->relatedModelConfiguration($relatedModelConfig->name);
		GtcmsPremium::setDisplayRelatedModelBasedOnModelKey($configInParent, $object, $displayModel);

		?>
		@if ($displayModel)

			<div class="panel panel-default">
				<div class="panel-body">
					<?php
					// --------------- EXCEPTIONS ----------------
					$addObject = true;

					?>
					@if ($addObject)
						<div class="indexTableHeader">
							<a href="/admin/{{$relatedModelConfig->name}}/add{{$gets}}&addToParent=true" class="btn btn-primary btn-sm addRelatedObject">
								<i class="fa fa-plus-circle"></i> {{$relatedModelConfig->hrName}}
							</a>
						</div>
					@endif

					<?php
					$relatedObjects = $object->$method()->orderBy($configInParent->orderBy, $configInParent->direction)->get();
					?>

					@if ($relatedObjects->count())
						<div class="table-responsive">
							{!! Front::drawObjectTable($relatedObjects, $relatedModelConfig, 'sideTable', '?' . $modelConfig->id . '=' . $object->id) !!}
						</div>
					@else
						{!! trans('gtcms.noRelatedModels', array('modelName1' => $modelConfig->hrName, 'modelName2' => $relatedModelConfig->hrNamePlural)) !!}
					@endif
					<div class="disableRelatedModel {{$action == 'add' ? '' : 'hidden'}}">
						<div class="disableTextContainer">
							<p>{!! trans('gtcms.enableRelatedModels', array('modelName1' => $modelConfig->hrName, 'modelName2' => $relatedModelConfig->hrNamePlural)) !!}</p>
						</div>
					</div>
				</div>
			</div>
		@endif
	@endforeach
</div>