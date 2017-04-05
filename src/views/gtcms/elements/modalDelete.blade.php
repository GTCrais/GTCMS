<div id="modalDelete">
	<div id="confirmWindow">
		<p>
			{!! trans('gtcms.doYouReallyWantToDelete') !!}<br>
			<span class="objectData"></span>
		</p>
		<div id="confirmButtons">
			<span class="btn btn-primary btn-confirm">
				<i class="fa fa-check"></i>
				{!! trans('gtcms.yes') !!}
			</span>
			<span class="btn btn-warning btn-cancel">
				<i class="fa fa-times"></i>
				{!! trans('gtcms.no') !!}
			</span>
		</div>
		<div id="confirmSpinner"></div>
		<div id="successCheckmark"><i class='fa fa-check'></i></div>
		<div id="errorMsg">{!! trans('gtcms.errorHasOccurred') !!}.<br>{!! trans('gtcms.pleaseRefresh') !!}.</div>
	</div>
</div>