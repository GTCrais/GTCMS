<head data-csrf="{{csrf_token()}}" data-title="{{PageMetaManager::getAdminTitle()}}" data-cmsprefix="{{config('gtcms.cmsPrefix')}}">
	<title>{{PageMetaManager::getAdminTitle()}}</title>

	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href={{asset("/components/bootstrap/dist/css/bootstrap.min.css")}} rel="stylesheet">
	<link href={{asset("/components/font-awesome/css/font-awesome.min.css")}} rel="stylesheet">
	<link href={{asset("/components/selectize/dist/css/selectize.default.css")}} rel="stylesheet">
	<link href={{asset("/components/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.css")}} rel="stylesheet">
	<link href={{asset("/components/blueimp-file-upload/css/jquery.fileupload.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/metis-menu.min.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/theme.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/gtcms-datepicker.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/style.min.css")}} rel="stylesheet">

	<script src={{asset("/components/jquery/dist/jquery.min.js")}}></script>
	<script src={{asset("/components/history.js/scripts/bundled/html4+html5/jquery.history.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/core.min.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/widget.min.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/mouse.min.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/sortable.min.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/draggable.min.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/slider.min.js")}}></script>
	<script src={{asset("/components/jquery-ui/ui/minified/datepicker.min.js")}}></script>
	<script src={{asset("/components/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js")}}></script>
	<script src={{asset("/components/ckeditor/ckeditor.js")}}></script>
	<script src={{asset("/components/ckeditor/adapters/jquery.js")}}></script>
	<script src={{asset("/components/spin.js/spin.js")}}></script>
	<script src={{asset("/components/spin.js/jquery.spin.js")}}></script>
	<script src={{asset("/components/autosize/dist/autosize.min.js")}}></script>
	<script src={{asset("/components/blueimp-load-image/js/load-image.all.min.js")}}></script>
	<script src={{asset("/components/blueimp-canvas-to-blob/js/canvas-to-blob.min.js")}}></script>
	<script src={{asset("/components/blueimp-file-upload/js/jquery.iframe-transport.js")}}></script>
	<script src={{asset("/components/blueimp-file-upload/js/jquery.fileupload.js")}}></script>
	<script src={{asset("/components/blueimp-file-upload/js/jquery.fileupload-process.js")}}></script>
	<script src={{asset("/components/blueimp-file-upload/js/jquery.fileupload-image.js")}}></script>
	<script src={{asset("/components/bootstrap/dist/js/bootstrap.min.js")}}></script>
	<script src={{asset("/gtcms/js/selectize.js")}}></script>
	<script src={{asset("/gtcms/js/metis-menu.min.js")}}></script>
	<script src={{asset("/gtcms/js/template.js")}}></script>
	<script src={{asset("/gtcms/js/jquery.ui.touch-punch.min.js")}}></script>
	<script src={{asset("/gtcms/js/jquery.numeric.min.js")}}></script>
	@if (config('gtcms.premium'))
		<script src={{asset("/gtcms/js/gtcmspremium.js")}}></script>
	@endif
	<script src={{asset("/gtcms/js/admin.js")}}></script>

	<link rel="shortcut icon" href="{{asset("img/favicon.png")}}">
</head>