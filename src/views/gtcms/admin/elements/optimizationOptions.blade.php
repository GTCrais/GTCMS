@extends('gtcms.admin.templates.admin')

@section('content')

	<div class="row">
		<div class="col-lg-12">
			<h3 class="page-header">Application optimization</h3>
		</div>

		<div class="col-lg-6">
			<div class="panel panel-default">
				<div class="panel-body">
					{{Form::open()}}
					{{Form::select('optimizationOption',
						array('clearCompiledAndOptimize' => 'Clear compiled and optimize', 'clearCompiled' => 'Clear compiled'),
						'clearCompiledAndOptimize',
						array('class' => 'doSelectize'))
					}}
					{{Form::submit("Proceed", array('name' => 'formSubmit', 'class' => 'btn btn-primary cBoth floatNone'))}}
					{{Form::submit("Cancel", array('name' => 'formSubmit', 'class' => 'btn btn-default cBoth floatNone'))}}
					{{Form::close()}}
				</div>
			</div>
		</div>
	</div>

@endsection
