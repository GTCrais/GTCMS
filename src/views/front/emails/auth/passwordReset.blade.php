@extends('front.templates.email')

@section('content')
	<h2>Password reset request</h2>

	<p>
		Hi {{$user->first_name}},
	</p>

	<p>
		Password reset has been requested for this email address.
	</p>

	<div class="with-bm">
		<a class="button" href="{{URL::route('passwordResetToken', ['token' => $token])}}">Reset password</a>
	</div>

	<p>
		Can't click the button? Here's the link:<br>
		<a href="{{url()->route('passwordResetToken', ['token' => $token])}}">
			{{url()->route('passwordResetToken', ['token' => $token])}}
		</a>
	</p>
@endsection