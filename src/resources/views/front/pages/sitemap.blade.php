<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

	@foreach($pages as $page)

		@if (config('gtcmslang.siteIsMultilingual'))

			@foreach (config('gtcmslang.languages') as $language)
				<?php app()->setLocale($language) ?>
				<url>
					<loc>{{ $page->url }}</loc>
					<lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>

					@if ($page->depth == 0)
						@if (app()->getLocale() == config('gtcmslang.defaultLocale'))
							<priority>1.0</priority>
						@else
							<priority>0.9</priority>
						@endif
					@else
						<priority>0.5</priority>
					@endif

				</url>
			@endforeach

		@else

			<url>
				<loc>{{ $page->url }}</loc>
				<lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>

				@if ($page->depth == 0)
					<priority>1.0</priority>
				@else
					<priority>0.5</priority>
				@endif

			</url>

		@endif

	@endforeach

</urlset>