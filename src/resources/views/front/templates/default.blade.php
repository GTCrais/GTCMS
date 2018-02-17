<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<title>{{PageMetaManager::getMetaTitle()}}</title>
		<meta name="description" content="{{PageMetaManager::getMetaDescription()}}" />
		<meta name="keywords" content="{{PageMetaManager::getMetaKeywords()}}" />

		<meta property="og:title" content="{{PageMetaManager::getMetaTitle()}}" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="{{request()->url()}}" />
		@if (config('gtcms.ogImage', false))
			<meta property="og:image" content="http://{{request()->server ("HTTP_HOST")}}/img/{{config('gtcms.ogImage')}}" />
		@endif
		<meta property="og:description" content="{{PageMetaManager::getMetaDescription()}}">

		<link rel="stylesheet" href="{{asset("css/reset.css")}}">
		<link rel="stylesheet" href="{{asset("css/style.min.css?v=" . config('assetversioning.front.css'))}}">

		<link rel="shortcut icon" href="{{asset("img/favicon.png?v=" . config('assetversioning.front.favicon'))}}">
	</head>
	<body>
		<div id="wrap">

			@include("front.elements.header")

			@yield('content')

			@include("front.elements.footer")

		</div>

		<script src="{{asset("/components/jquery/dist/jquery.min.js")}}"></script>
		<script src="{{asset("/js/scripts.js?v=" . config('assetversioning.front.js'))}}"></script>
	</body>
</html>