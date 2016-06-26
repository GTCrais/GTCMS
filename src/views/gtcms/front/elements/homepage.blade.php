@extends('gtcms.front.templates.default')

@section('content')

	<style>
		@import url(https://fonts.googleapis.com/css?family=Lato:100);
		.gtcms {
			position: absolute;
			font-family: Lato, sans-serif;
			font-weight: 100;
			font-size: 100px;
			text-align: center;
			left: 0;
			right: 0;
			top: calc(50% - 50px);
		}
	</style>

	<div class="gtcms">GTCMS</div>

@stop