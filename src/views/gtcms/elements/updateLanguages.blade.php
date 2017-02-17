@extends('gtcms.templates.admin')

@section('content')

	<div class="row">
		<div class="col-lg-12">
			<h3 class="page-header">Update languages</h3>
		</div>

		<div class="col-lg-6">
			<div class="globalMessages">
				<div class="alert alert-danger">
					It is recommended you backup your database first!
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-body">
					{{Form::open()}}
					{{Form::submit("Proceed", array('name' => 'updateLanguages', 'class' => 'btn btn-primary cBoth floatNone'))}}
					{{Form::submit("Cancel", array('name' => 'updateLanguages', 'class' => 'btn btn-default cBoth floatNone'))}}
					{{Form::close()}}
				</div>
			</div>
		</div>
	</div>

@endsection
