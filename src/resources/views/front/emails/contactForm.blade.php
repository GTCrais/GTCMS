@extends('front.templates.email')

@section('content')

	<p>
		<strong>Name:</strong> {{ $name }}<br>
		<strong>Email:</strong> {{ $email }}<br>
		<strong>Subject:</strong> {{ $messageSubject }}<br>
		<strong>Message:</strong><br>{{ $messageContent }}
	</p>

@endsection