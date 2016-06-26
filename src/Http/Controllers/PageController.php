<?php

namespace App\Http\Controllers;

use App\Page;
use App\PageMetaManager;

class PageController extends Controller {

	public static function showPage($slug = "") {

		self::shareViews();

		if (!$slug) {
			//homepage
			$homepage = Page::where('depth', 0)->first();
			PageMetaManager::setPage($homepage);

			$data = array(
				'cPage' => $homepage
			);

			return \View::make('gtcms.front.elements.homepage')->with($data);

		} else {
			if (config('gtcms.premium') && config('gtcmslang.siteIsMultilingual')) {
				$slugString = "slug_" . \App::getLocale();
			} else {
				$slugString = "slug";
			}

			$cPage = Page::where($slugString, $slug)->first();
			if ($cPage) {
				PageMetaManager::setPage($cPage);
				$data = array(
					'cPage' => $cPage
				);
				return \View::make('gtcms.front.elements.page')->with($data);
			} else {
				\App::abort(404);
			}
		}

	}

	public static function shareViews() {
		$navPages = Page::where('depth', 1)->orderBy('position')->with(array('pages'))->get();
		//$home = Page::where('model_key', 'home')->first();

		\View::share('navPages', $navPages);
		//View::share('home', $home);
	}

	public static function show404() {
		\App::abort(404);
	}

}