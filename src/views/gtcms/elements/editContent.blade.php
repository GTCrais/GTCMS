<?php $printProperty = $modelConfig->printProperty; ?>
<div class="row {{$ajaxRequest && !$quickEdit ? 'absoluteRow' : ''}}">
	<div class="col-lg-12 page-header-container">
		<h3 class="page-header">
			@if (!$ajaxRequest)
				{!! Front::getHistoryLinks() !!}
			@endif

			<span class="currentHistory">
			@if (!$object->id && $modelConfig->name != "GtcmsSetting")
				@if ($modelConfig->addLegend)
					{{$modelConfig->addLegend}}
				@else
					<strong>{{trans('gtcms.newEntry')}}</strong>
				@endif
			@else
				@if ($modelConfig->editlegend)
					{{$modelConfig->editlegend}}
				@else
					{{$modelConfig->hrName}}
				@endif

				@if ($printProperty)
					<strong>{{$object->$printProperty}}</strong>
				@endif
			@endif
			</span>

			@if ($quickEdit)
				<a href="javascript:;" class="quickEditButton close">
					<i class="fa fa-times"></i>
				</a>
			@endif

		</h3>
	</div>

	<div class="col-lg-{{$quickEdit ? '12' : '6'}}">
		<div class="globalMessages"></div>

		<?php
			if ($modelConfig->name == "GtcmsSetting") {
				$submitUrl = AdminHelper::getCmsPrefix() . 'GtcmsSetting'.Tools::getGets();
			} else {
				$submitUrl = AdminHelper::getCmsPrefix() . ($modelConfig->name) . '/edit/' . ($object->id ? $object->id : 'new') . Tools::getGets();
			}
		?>

		{{Form::model(
			$object,
			array(
				'url' => $submitUrl,
				'files' => true,
				'class' => 'entityForm ' . ($object->id || $modelConfig->name == 'GtcmsSetting' ? 'editForm' : 'addForm') . (!$quickEdit && $modelConfig->form && $modelConfig->form->horizontal ? ' form-horizontal' : ''),
				'data-infooffset' => $modelConfig->form && $modelConfig->form->horizontal ? $modelConfig->form->labelWidth : false,
				'data-infowidth' => $modelConfig->form && $modelConfig->form->horizontal ? $modelConfig->form->inputWidth : false
			)
		)}}

		<div class="panel panel-default">
			<div class="panel-body">


				<?php $originalModelConfig = AdminHelper::modelExists($modelConfig->name); ?>

				@if ($modelConfig->tabs && !$quickEdit)
					<ul class="nav nav-tabs">
						@foreach ($modelConfig->tabs as $index => $tab)
							<li class="{{$index == 0 ? 'active' : ''}}">
								<a href="#tab-{{\Illuminate\Support\Str::slug($tab)}}" class="standardLink" data-toggle="tab" aria-expanded="{{$index == 0 ? 'true' : 'false'}}">{{$tab}}</a>
							</li>
						@endforeach
					</ul>

					<div class="tab-content">
						@foreach ($modelConfig->tabs as $index => $tab)
							<div class="tab-pane fade {{$index == 0 ? 'active in' : ''}}" id="tab-{{\Illuminate\Support\Str::slug($tab)}}">
								@if (config('gtcms.premium') && $modelConfig->tabbedLanguageFields)
									@include('gtcms.elements.tabbedLanguageFields')
								@else
									<?php
									$fieldType = 'all';
									$ignoreLanguageIterations = false;
									$hideSave = false;
									$modelConfig = AdminHelper::modelExists($modelConfig->name);
									?>
									@include("gtcms.elements.editContentFormFields")
								@endif
							</div>
						@endforeach
					</div>
				@else
					<?php $index = 0; ?>
					@if (config('gtcms.premium') && $modelConfig->tabbedLanguageFields)
						@include('gtcms.elements.tabbedLanguageFields')
					@else
						<?php
						$fieldType = 'all';
						$ignoreLanguageIterations = false;
						$hideSave = false;
						$modelConfig = AdminHelper::modelExists($modelConfig->name);
						?>
						@include("gtcms.elements.editContentFormFields")
					@endif
				@endif

			</div>
		</div>

		{{Form::close()}}
	</div>

	@if (!$quickEdit && !empty($modelConfig->relatedModels))

		@include("gtcms.elements.editContentRelatedModels")

	@endif
</div>