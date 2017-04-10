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

		label {
			font-weight: 300;
		}

		.btn {
			font-weight: 300;
		}

		.auth-form-container a {
			text-decoration: none !important;
			position: relative;
			top: 8px;
			float: right;
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

		.help-block {
			margin-top: -7px;
			margin-bottom: 2px;
			font-size: 13px;
			color: red;
		}

		.form-message {
			margin-bottom: 12px;
			font-size: 16px;
		}

		.form-message.is-error {
			color: red;
		}

		.soft-hidden {
			display: none;
		}
	</style>

	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="auth-form-container">
					<h2>Example Contact</h2>

					{{Form::open(['method' => 'post', 'url' => url()->route('sendQuery'), 'id' => 'contact-form'])}}

					<div class="form-group">
						{{Form::label('name', trans('front.name'))}}
						<p class="help-block soft-hidden contact-error name-error" data-emptyfield="{{trans('front.emptyField')}}"></p>
						{{Form::text('name', null, ['class' => 'form-control', 'id' => 'name'])}}
					</div>

					<div class="form-group">
						{{Form::label('email', trans('front.email'))}}
						<p class="help-block soft-hidden contact-error email-error" data-incorrectemailformat="{{trans('front.incorrectEmailFormat')}}"></p>
						{{Form::text('email', null, ['class' => 'form-control', 'id' => 'email'])}}
					</div>

					<div class="form-group">
						{{Form::label('subject', trans('front.subject'))}}
						<p class="help-block soft-hidden contact-error subject-error"></p>
						{{Form::text('subject', null, ['class' => 'form-control', 'id' => 'subject'])}}
					</div>

					<div class="form-group">
						{{Form::label('contact-message', trans('front.message'))}}
						<p class="help-block soft-hidden contact-error message-error"></p>
						{{Form::textarea('message', null, ['class' => 'form-control', 'id' => 'contact-message'])}}
					</div>

					<div class="form-message soft-hidden"></div>

					{{Form::submit('SUBMIT', ['class' => 'btn btn-default', 'id' => 'submit'])}}

					{{Form::close()}}

					<div class="footer">
						<a href="/">Home</a>
					</div>
				</div>
			</div>
		</div>
	</div>

@stop