@if (isset($loginRedirect) && $loginRedirect)
@include("gtcms.admin.elements.navigation")
<div id="page-wrapper">
@endif

@if (!$getSearchResults)
<div class="row {{$ajaxRequest && !$loginRedirect ? 'absoluteRow' : ''}}">
	<div class="col-lg-12">
		<h3 class="page-header">{{$modelConfig->hrNamePlural}}</h3>
	</div>


	<div class="col-md-12 {{$searchIsOpen ? 'col-lg-9' : 'col-lg-12'}} transitionWidth hasBottomMargin searchResultObjects">
		<div class="globalMessages">
			{!! Front::showMessages() !!}
		</div>

		<div class="indexTableHeader">
			<a href="{{AdminHelper::getCmsPrefix() . $modelConfig->name}}/add" class="btn btn-primary btn-sm addButton"><i class="fa fa-plus-circle"></i> {{$modelConfig->hrName}}</a>

			@if ($modelConfig->searchPropertiesExist())
			<button class="btn btn-default btn-sm openSearch {{$searchIsOpen ? 'searchIsOpen' : ''}}" type="button">
				<i class="fa fa-search"></i>
			</button>
			@endif

			@if (config('gtcms.premium') && $modelConfig->getExcelExportFields(true) && $objects->count())
			<a href="{{AdminHelper::getCmsPrefix()}}excelExport/{{$modelConfig->name . Tools::getGets()}}" class="btn btn-primary btn-sm excelExport standardLink"><i class="fa fa-download"></i> {{trans('gtcms.excelExport')}}</a>
			@endif
		</div>
@endif

		<div class="objectsContainer {{$getSearchResults ? 'transparent' : ''}}">
			@if ($searchDataWithFieldValues)
			<div class="searchData">
				{!! Front::drawSearchCriteria($searchDataWithFieldValues) !!}
				<span class="clearSearchResults">
					<i class="fa fa-times"></i> {{trans('gtcms.clearSearch')}}
				</span>
			</div>
			@endif

			@if ($objects->count())
			<div class="table-responsive objectsContainer">
				{!! Front::drawObjectTable($objects, $modelConfig, 'table', "", $searchDataWithFieldValues, $ordering) !!}
			</div>
			@else
				<p>{{trans('gtcms.currentlyNoObjects', array('objects' => $modelConfig->hrNamePlural))}}</p>
			@endif
		</div>

@if (!$getSearchResults)
	</div>

	@if ($modelConfig->searchPropertiesExist())
	<div class="searchContainer col-md-12 col-lg-3 transitionWidth zeroWidth">
		<h4 class="page-header">{{trans('gtcms.search')}}</h4>
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12">
					{!! Front::drawSearch($modelConfig, $searchParams) !!}
					</div>
				</div>
			</div>
		</div>
	</div>
	@endif
</div>
@endif

@if (isset($loginRedirect) && $loginRedirect)
</div>
@endif
