<div id="wrapper" data-csrf="{{csrf_token()}}">
	<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="{{AdminHelper::getCmsPrefix()}}">
				<img src="{{asset('img/gtcms-logo.png')}}" height="28" />
			</a>
		</div>

		<ul class="nav navbar-top-links navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle standardLink" data-toggle="dropdown" href="#">
					<i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
				</a>
				<ul class="dropdown-menu dropdown-user">
					@if (auth()->user()->is_superadmin)
						<li>
							<a href="{{url()->route('optimize')}}" class="standardLink"><i class="fa fa-wrench fa-fw"></i> {{trans('gtcms.optimization')}}</a>
						</li>
					@endif

					<li>
						<a href="{{AdminHelper::getCmsPrefix()}}logout" class="standardLink"><i class="fa fa-sign-out fa-fw"></i> {{trans('gtcms.logout')}}</a>
					</li>
				</ul>
			</li>
		</ul>

		<div class="navbar-default sidebar" role="navigation">
			<div class="sidebar-nav navbar-collapse">
				<ul class="nav" id="side-menu">
					<?php $userRole = auth()->user() ? auth()->user()->role : false; ?>
					@foreach (AdminHelper::modelConfigs() as $modelConfig)
						@if ($userRole && $modelConfig->standalone !== false &&
							!$modelConfig->hiddenInNavigation &&
							(!$modelConfig->restrictedAccess || $modelConfig->restrictedAccess->$userRole) &&
							(!$modelConfig->hiddenInNavigationForRoles || !$modelConfig->hiddenInNavigationForRoles->$userRole) &&
							(!$modelConfig->restrictedToSuperadmin || (auth()->check() && auth()->user()->is_superadmin)))
							<li>
								<a
									data-loadtype="{{count(request()->segments()) == 2 ? 'moveLeft' : 'moveRight'}}"
									class="{{$modelConfig->name == $active ? 'active ' : ''}} navigationLink model{{$modelConfig->name}}"
									href="{{AdminHelper::getCmsPrefix() . $modelConfig->name}}"
								>
									<i class="fa {{$modelConfig->faIcon}} fa-fw" {!! ModelConfig::colorStyleForModel($modelConfig) !!}></i> <span class="modelName">{{$modelConfig->hrNamePlural}}</span>
								</a>
							</li>
						@endif
					@endforeach
					<li class="navResizer">
						<a href="javascript:;" class="changeNavigationSize">
							<i class="fa fa-angle-double-left"></i>
							<i class="fa fa-angle-double-right"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
</div>