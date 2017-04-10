@extends('front.templates.default')

@section('content')

	<script>
		var linkElement = document.createElement("link");
		linkElement.rel = "stylesheet";
		linkElement.href = "{{asset("components/bootstrap/dist/css/bootstrap.min.css")}}";
		document.head.appendChild(linkElement);
	</script>

	<style>
		@import url(https://fonts.googleapis.com/css?family=Roboto:300,400);

		.container {
			max-width: 1200px;
			margin: 0 auto;
		}

		.auth-form-container {
			font-family: 'Roboto', sans-serif;
			font-size: 18px;
			font-weight: 300;
			max-width: 400px;
			margin: 50px auto 30px;
		}

		h2 {
			margin-bottom: 30px;
		}

		.auth-form-container a {
			text-decoration: none !important;
			color: #424242 !important;
			font-size: 14px;
			position: relative;
			top: 8px;
			float: right;
		}

		label {
			font-weight: 300;
		}

		.btn {
			font-weight: 300;
		}

		.footer {
			margin-top: 40px;
			border-top: 1px solid #ccc;
		}

		.footer a {
			text-transform: uppercase;
			color: #a3a3a3 !important;
			font-size: 13px;
		}
	</style>

	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="auth-form-container">
					<h2>Login</h2>

					@if ($passwordResetSuccess)
						<div class="alert alert-success" role="alert">
							{!! $passwordResetSuccess !!}
						</div>
					@elseif ($errorMessage)
						<div class="alert alert-warning" role="alert">
							{!! $errorMessage !!}
						</div>
					@endif

					{{Form::open(['method' => 'post', 'url' => url()->route('submitLogin')])}}

					<div class="form-group">
						{{Form::label('email', 'Email')}}
						{{Form::text('email', null, ['class' => 'form-control', 'id' => 'email'])}}
					</div>

					<div class="form-group">
						{{Form::label('password', 'Password')}}
						{{Form::password('password', ['class' => 'form-control', 'id' => 'password'])}}
					</div>

					{{Form::submit('SUBMIT', ['class' => 'btn btn-default'])}}

					<a href="{{url()->route('passwordReset')}}">Forgot password?</a>

					{{Form::close()}}

					<div class="footer">
						<a href="/">Home</a>
					</div>
				</div>
			</div>
		</div>
	</div>

@stop