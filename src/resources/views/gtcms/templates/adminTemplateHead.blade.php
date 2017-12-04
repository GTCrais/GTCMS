<head data-csrf="{{csrf_token()}}" data-title="{{PageMetaManager::getAdminTitle()}}" data-cmsprefix="{{config('gtcms.cmsPrefix')}}">
	<title>{{PageMetaManager::getAdminTitle()}}</title>

	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href={{asset("/components/font-awesome/css/font-awesome.min.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/vendors.min.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/style.min.css")}} rel="stylesheet">

	<script src={{asset("/gtcms/js/vendors.min.js")}}></script>
	<script src={{asset("/gtcms/js/admin.min.js")}}></script>

	<link rel="shortcut icon" href="{{asset("img/favicon.png")}}">
</head>