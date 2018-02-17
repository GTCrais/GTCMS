<div class="col-lg-{{($modelConfig->form && $modelConfig->form->width ? (12 - $modelConfig->form->width) : '6')}} relatedModelsContainer">

	@foreach ($modelConfig->relatedModels as $relatedModel)

		@include('gtcms.elements.editContentRelatedModel')

	@endforeach

</div>