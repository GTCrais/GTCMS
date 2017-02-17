<div
	class="fileUploadContainer"
	data-entityname="{{$modelConfig->name}}"
	data-entityid="{{$object->id ? $object->id : 'new'}}"
	data-filenamefield="{{$field->property}}"
	data-filenamevalue="{{$object->$property ? $object->$property : ''}}"
	>

	<div class="fileUploadForm {{($object->$property ? 'hidden' : '')}}">
				<span class="btn btn-primary fileinput-button">
					<i class="fa fa-plus-circle"></i>
					<span>{{trans('gtcms.addFile', array('label' => rtrim($field->label, '*')))}}</span>
					{{Form::file($field->property, $options)}}
					{{Form::hidden($property, $object->$property, array("id" => "hidden" . $property))}}
				</span>
		<div class="progress">
			<div class="progress-bar"></div>
		</div>
		<div class="uploadError">
			{{trans('gtcms.errorHasOccurred')}}.
		</div>
	</div>

	<div class="fileDownloadContainer {{($object->$property ? '' : 'hidden')}}">
		<a target="_blank" class="btn btn-primary standardLink fileDownloadLink" href="{{$object->$method('url', $property)}}">
			<i class="fa fa-download"></i>
			<span>{{trans('gtcms.downloadFile', array('label' => rtrim($field->label, '*')))}}</span>
		</a>
		<div class="controlButtons">
			<a class="btn btn-default btn-xs deleteButton deleteUploadedFile"
			   data-objectname=""
			   data-modelname="{{rtrim($field->label, '*')}}"
			   href="{{AdminHelper::getCmsPrefix() . $modelConfig->name}}/deleteFile/{{$field->property}}/{{$object->id ? $object->id : 'new_gtcms_entry'}}"
				>
				<i class="fa fa-times"></i>
			</a>
		</div>
	</div>

</div>