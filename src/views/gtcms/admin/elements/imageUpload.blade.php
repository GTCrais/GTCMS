<div
	class="fileUploadContainer imageUpload"
	data-entityname="{{$modelConfig->name}}"
	data-entityid="{{$object->id ? $object->id : 'new'}}"
	data-filenamefield="{{$field->property}}"
	>

	<div class="fileUploadForm {{($object->$property ? 'hidden' : '')}}">
		<div class="imagePreview">

		</div>
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

	<div class="fileDownloadContainer imageDownloadContainer {{($object->$property ? '' : 'hidden')}}">
		<div class="uploadedImagePreview">
			<a target="_blank" class="btn btn-primary standardLink fileDownloadLink imageDownloadLink" href="{{$object->$method('url', 'original', $field->property)}}">
				<img class="theImage" src="{{$object->$method('url', 'gtcmsThumb', $field->property)}}" />
			</a>
		</div>
		<div class="controlButtons">
			<a class="btn btn-default btn-xs deleteButton deleteUploadedFile deleteImageFile"
			   data-objectname=""
			   data-modelname="{{rtrim($field->label, '*')}}"
			   href="{{AdminHelper::getCmsPrefix() . $modelConfig->name}}/deleteFile/{{$field->property}}/{{$object->id ? $object->id : 'new_gtcms_entry'}}"
				>
				<i class="fa fa-times"></i>
			</a>
		</div>
	</div>

</div>