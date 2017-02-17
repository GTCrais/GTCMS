<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

	@include("gtcms.templates.adminTemplateHead")

<body class="nav-{{AdminHelper::getNavigationSize()}} skin-{{config('gtcms.skin', 'dark')}}">
	<div class="quickEditContainer"></div>

	@include("gtcms.elements.navigation")

	<div id="page-wrapper">
		@yield('content')
	</div>

	@include("gtcms.elements.modalDelete")

</body>
</html>