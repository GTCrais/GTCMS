@extends('gtcms.templates.admin')

@section('content')
	@if ($indexType == 'Tree')
		@include('gtcms.elements.indexTreeContent')
	@else
		@include('gtcms.elements.indexContent')
	@endif
@endsection
