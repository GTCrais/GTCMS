<head data-csrf="{{csrf_token()}}" data-title="{{PageMetaManager::getAdminTitle()}}" data-cmsprefix="{{config('gtcms.cmsPrefix')}}">
	<title>{{PageMetaManager::getAdminTitle()}}</title>

	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href={{asset("/components/font-awesome/css/font-awesome.min.css")}} rel="stylesheet">
	<link href={{asset("/gtcms/css/vendors.min.css?v=" . config('assetversioning.gtcms.cssVendors'))}} rel="stylesheet">
	<link href={{asset("/gtcms/css/style.min.css?v=" . config('assetversioning.gtcms.css'))}} rel="stylesheet">

	<script src={{asset("/gtcms/js/vendors.min.js?v=" . config('assetversioning.gtcms.jsVendors'))}}></script>
	<script src={{asset("/gtcms/js/admin.min.js?v=" . config('assetversioning.gtcms.js'))}}></script>

	<link rel="shortcut icon" href="{{asset("img/favicon.png?v=" . config('assetversioning.gtcms.favicon'))}}">
</head>