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

			return view()->make('front.elements.homepage')->with($data);

		} else {
			if (config('gtcms.premium') && config('gtcmslang.siteIsMultilingual')) {
				$slugString = "slug_" . app()->getLocale();
			} else {
				$slugString = "slug";
			}

			$cPage = Page::where($slugString, $slug)->first();
			if ($cPage) {
				PageMetaManager::setPage($cPage);
				$data = [
					'cPage' => $cPage
				];

				return view()->make('front.elements.page')->with($data);
			}

			abort(404);
		}
	}

	public function compose(View $view)
	{
		$navPages = Page::where('depth', 1)->orderBy('position')->with(['pages'])->get();
		//$home = Page::where('model_key', 'home')->first();

		$view->with(compact('navPages'));
	}
}