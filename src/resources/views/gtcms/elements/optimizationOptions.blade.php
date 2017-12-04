@extends('gtcms.templates.admin')

@section('content')

	<div class="row">
		<div class="col-lg-12">
			<h3 class="page-header">Application optimization</h3>
		</div>

		<div class="col-lg-6">
			<div class="globalMessages">
				{!! Front::showMessages() !!}
			</div>

			<div class="panel panel-default">
				<div class="panel-body">
					{{Form::open()}}

					<div class="form-group">
						<span class="info checkInfo">Clears cached configuration options</span>
						<div class="checkbox">
							<label>
								<input name="clearCache" type="checkbox" value="1"> Delete cached configuration
							</label>
						</div>
					</div>

					<div class="form-group">
						<span class="info checkInfo">Clears cached routes</span>
						<div class="checkbox">
							<label>
								<input name="clearRoutes" type="checkbox" value="1"> Delete cached routes
							</label>
						</div>
					</div>

					<div class="form-group">
						<hr />
					</div>

					<div class="form-group">
						<span class="info checkInfo">(Re)caches configuration options</span>
						<div class="checkbox">
							<label>
								<input name="cacheConfiguration" type="checkbox" value="1"> Cache configuration
							</label>
						</div>
					</div>

					<div class="form-group">
						<span class="info checkInfo">(Re)caches routes</span>
						<div class="checkbox">
							<label>
								<input name="cacheRoutes" type="checkbox" value="1"> Cache routes
							</label>
						</div>
					</div>

					{{Form::submit("Proceed", array('name' => 'formSubmit', 'class' => 'btn btn-primary cBoth floatNone'))}}
					{{Form::submit("Cancel", array('name' => 'formSubmit', 'class' => 'btn btn-default cBoth floatNone'))}}
					{{Form::close()}}
				</div>
			</div>
		</div>
	</div>

@endsection