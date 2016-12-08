<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

	@include("gtcms.admin.templates.adminTemplateHead")

<body class="nav-{{AdminHelper::getNavigationSize()}}">
	<div class="quickEditContainer"></div>

	@include("gtcms.admin.elements.navigation")

	<div id="page-wrapper">
		@yield('content')
	</div>

	@include("gtcms.admin.elements.modalDelete")

</body>
</html>