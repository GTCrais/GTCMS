<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	@include("gtcms.admin.templates.adminTemplateHead")
</head>
<body class="nav-{{AdminHelper::getNavigationSize()}}" data-csrf="{{csrf_token()}}">
	<div class="quickEditContainer"></div>

	@include("gtcms.admin.elements.navigation")

	<div id="page-wrapper">
		@yield('content')
	</div>

	@include("gtcms.admin.elements.modalDelete")

	<script src={{asset("/components/bootstrap/dist/js/bootstrap.min.js")}}></script>
	<script src={{asset("/gtcms/js/metis-menu.min.js")}}></script>
	<script src={{asset("/gtcms/js/template.js")}}></script>

</body>
</html>