@extends('front.templates.default')

@section('content')

	<style>
		@import url(https://fonts.googleapis.com/css?family=Lato:100);
		@import url(https://fonts.googleapis.com/css?family=Roboto:300,400);

		.gtcms {
			position: absolute;
			font-family: Lato, sans-serif;
			font-weight: 100;
			font-size: 100px;
			text-align: center;
			left: 0;
			right: 0;
			top: calc(50% - 80px);
			padding: 0 15px;
		}

		@media screen and (max-width: 480px) {
			.gtcms {
				font-size: 80px;
			}
		}

		.gtcms .links {
			font-family: 'Roboto', sans-serif;
			font-size: 16px;
			padding-top: 10px;
			text-transform: uppercase;
			color: gray;
		}

		.gtcms a {
			text-decoration: none;
			color: #424242;
			display: inline-block;
			margin-bottom: 5px;
		}

		.gtcms a.cms {
			color: #0088cc;
		}
	</style>

	<div class="gtcms">
		GTCMS
		<div class="links">
			@if (auth()->check())
				<a href="{{url()->route('logout')}}">Logout</a> &nbsp;|&nbsp;
			@else
				<a href="{{url()->route('login')}}">Login</a> &nbsp;|&nbsp;
				<a href="{{url()->route('register')}}">Register</a> &nbsp;|&nbsp;
			@endif

			<a href="/contact">Contact</a> &nbsp;|&nbsp;

			<a class="cms" href="{{url()->to(AdminHelper::getCmsPrefix())}}">
				CMS
				@if (auth()->guest() || auth()->user()->role != 'admin')
					login
				@endif
			</a>
		</div>
	</div>

@stop