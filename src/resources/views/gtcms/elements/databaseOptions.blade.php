@extends('gtcms.templates.admin')

@section('content')

	<div class="row">
		<div class="col-lg-12">
			<h3 class="page-header">Database operations</h3>
		</div>

		<div class="col-lg-6">
			<div class="globalMessages">
				{!! Front::showMessages() !!}
			</div>

			<div class="panel panel-default">
				<div class="panel-body">
					{{Form::open()}}

					<div class="form-group">
						<span class="info checkInfo clearTopMargin">Runs migrations</span>
						<div class="checkbox">
							<label>
								<input name="migrate" type="checkbox" value="1"> Migrate
							</label>
						</div>
					</div>

					<div class="form-group">
						<span class="info checkInfo">Adds missing language fields based on gtcmsmodels.php config file</span>
						<div class="checkbox">
							<label>
								<input name="updateLanguages" type="checkbox" value="1"> Update languages
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