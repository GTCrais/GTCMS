
@include("gtcms.elements.editContentFormFields", [
	'fieldType' => 'regular',
	'ignoreLanguageIterations' => false,
	'hideSave' => true
])

<ul class="nav nav-tabs">
	@foreach (config('gtcmslang.languages') as $langTabIndex => $langTab)
		<li class="{{$langTabIndex == 0 ? 'active' : ''}}">
			<a href="#subTab{{$index}}-lang{{$langTab}}" class="standardLink" data-toggle="tab" aria-expanded="{{$langTabIndex == 0 ? 'true' : 'false'}}">{{$langTab}}</a>
		</li>
	@endforeach
</ul>

<div class="tab-content">
	@foreach (config('gtcmslang.languages') as $langContentIndex => $language)
		<div class="tab-pane fade {{$langContentIndex == 0 ? 'active in' : ''}}" id="subTab{{$index}}-lang{{$language}}">
			@include("gtcms.elements.editContentFormFields", [
				'fieldType' => 'langDependent',
				'currentTabLanguage' => $language,
				'ignoreLanguageIterations' => true,
				'hideSave' => false
			])
		</div>
	@endforeach
</div>