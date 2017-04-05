@if (isset($loginRedirect) && $loginRedirect)
@include("gtcms.elements.navigation")
<div id="page-wrapper">
@endif

<div class="row {{$ajaxRequest && !$loginRedirect ? 'absoluteRow' : ''}}">
	<div class="col-lg-12">
		<h3 class="page-header">{{$modelConfig->hrNamePlural}}</h3>
	</div>

	<div class="col-lg-12">
		<div class="globalMessages">
			{!! Front::showMessages() !!}
		</div>

		@if (!$objects->count())
			<p>{{trans('gtcms.currentlyNoObjects', array('objects' => $modelConfig->hrNamePlural))}}</p>
		@else
			@if($addEntity)
				<div class="indexTableHeader">
					<a href="{{AdminHelper::getCmsPrefix() . $modelConfig->name}}/add" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle"></i> {{$modelConfig->hrName}}</a>
				</div>
			@endif

			<div class="table-responsive">
				{!! Front::drawObjectTree($objects, $modelConfig, $modelConfig) !!}
			</div>
		@endif
	</div>
</div>

@if (isset($loginRedirect) && $loginRedirect)
</div>
@endif