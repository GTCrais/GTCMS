<div class="col-lg-{{($modelConfig->editFormWidth ? (12 - $modelConfig->editFormWidth) : '6')}} relatedModelsContainer">

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

		?>
		@if ($displayModel)

			@include('gtcms.elements.editContentRelatedModel')

		@endif
	@endforeach
</div>