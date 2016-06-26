@extends('gtcms.admin.templates.admin')

@section('content')
	@if ($indexType == 'Tree')
		@include('gtcms.admin.elements.indexTreeContent')
	@else
		@include('gtcms.admin.elements.indexContent')
	@endif
@endsection
