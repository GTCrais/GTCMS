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

		label {
			font-weight: 300;
		}

		.btn {
			font-weight: 300;
		}
	</style>

	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="auth-form-container">
					@if (isset($errorMessage) && $errorMessage)
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

					{{Form::close()}}
				</div>
			</div>
		</div>
	</div>

@stop