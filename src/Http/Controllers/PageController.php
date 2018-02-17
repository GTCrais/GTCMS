<?php

namespace App\Http\Controllers;

use App\Classes\AdminHelper;
use App\Classes\Dbar;
use App\Models\Page;
use App\Classes\PageMetaManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
	public function showPage(Request $request, $slug = "")
	{
		if (!$slug) {
			$homepage = Page::where('depth', 0)->first();
			PageMetaManager::setPage($homepage);

			$data = [
				'cPage' => $homepage
			];

			return view()->make('front.pages.homepage')->with($data);

		} else {
			if (config('gtcms.premium') && config('gtcmslang.siteIsMultilingual')) {
				$slugString = "slug_" . app()->getLocale();
			} else {
				$slugString = "slug";
			}

			// Example Contact Page
			if ($slug == 'contact') {
				return view()->make('front.pages.contact');
			}
			// End example

			$cPage = Page::where($slugString, $slug)->first();
			if ($cPage) {
				PageMetaManager::setPage($cPage);
				$data = [
					'cPage' => $cPage
				];

				return view()->make('front.pages.page')->with($data);
			}

			abort(404);
		}
	}

	public function show404()
	{
		abort(404);
	}

	public function sitemap(Request $request)
	{
		$pages = Page::orderBy('depth')->orderBy('position')->get();
		$content = view()->make('front.pages.sitemap')->with(compact('pages'));

		return response()->make($content)->header('Content-Type', 'text/xml');
	}

	public function compose(View $view)
	{
		$navPages = Page::where('depth', 1)->orderBy('position')->with(['pages'])->get();
		//$home = Page::where('model_key', 'home')->first();

		$view->with(compact('navPages'));
	}
}