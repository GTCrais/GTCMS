<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

	@include("gtcms.templates.adminTemplateHead")

	<body class="loginBody nav-{{AdminHelper::getNavigationSize()}} skin-{{config('gtcms.skin', 'dark')}}">
		<div class="quickEditContainer"></div>

		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<div class="login-panel panel panel-default">
						<div class="panel-body">
							@if (config('gtcms.loginLogo'))
								<img src="/img/{{config('gtcms.loginLogo')}}" alt="Admin logo" class="login-logo"/>
							@endif

							{{Form::open(array('url' => AdminHelper::getCmsPrefix() . 'login', 'class' => 'entityForm loginForm'))}}
							<fieldset>
								<div class="form-group">
									{{Form::label('email', 'Email')}}
									@if (config('gtcms.showTestAdminLoginInfo'))
										<span class='info'>Test username: admin@site.com</span>
									@endif
									{{Form::text('email', NULL, array('class' => 'form-control', 'placeholder' => 'Username'))}}
								</div>
								<div class="form-group">
									{{Form::label('password', trans('gtcms.password'))}}
									@if (config('gtcms.showTestAdminLoginInfo'))
										<span class='info'>Test password: admin</span>
									@endif
									{{Form::password('password', array('class' => 'form-control', 'placeholder' => 'Password'))}}
								</div>
								{{Form::submit(trans('gtcms.login'), array('class' => 'btn btn-lg btn-primary btn-block'))}}
								<div class="loginSpinner"></div>
								<div class="errorMessage"></div>
							</fieldset>
							{{Form::close()}}
						</div>
					</div>
				</div>
			</div>
		</div>

		@include("gtcms.elements.modalDelete")

	</body>
</html>