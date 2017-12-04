<?php

namespace App\Classes;

use App\Models\Page;

class PageMetaManager
{
	public static $page = null;
	public static $item = null;

	public static function getMetaDescription()
	{
		if (self::$item) {
			// requires Item to have 'metaDescription' accessor
			return self::$item->metaDescription ?: self::getDefaultMeta('description');
		}

		if (self::$page) {
			return self::$page->meta_description ?: self::getDefaultMeta('description');
		}

		return self::getDefaultMeta('description');
	}

	public static function getMetaKeywords()
	{
		if (self::$item) {
			// requires Item to have 'metaKeywords' accessor
			return self::$item->metaKeywords ?: self::getDefaultMeta('keywords');
		}

		if (self::$page) {
			return self::$page->meta_keywords ? self::$page->meta_keywords : self::getDefaultMeta('keywords');
		}

		return self::getDefaultMeta('keywords');
	}

	public static function getMetaTitle()
	{
		$title = config('gtcms.siteName');

		if (self::$item) {
			// requires Item to have 'metaTitle' accessor
			return self::$item->metaTitle . " :: " . $title;
		}

		if (self::$page) {
			// return $title = self::$page->name . " :: " . $title;
			return $title = $title . " :: " . self::$page->name;
		}

		return $title;
	}

	public static function getAdminTitle()
	{
		$title = trans('gtcms.adminTitle');
		$siteName = config('gtcms.siteName');

		//return $title. " :: " . $siteName;
		return $siteName . " :: " . $title;
	}

	private static function getDefaultMeta($attr = 'description')
	{
		$homepage = Page::whereNull('page_id')->first();
		if ($attr == 'description') {
			return $homepage->meta_description;
		} else {
			return $homepage->meta_keywords;
		}
	}

	public static function setPage($page)
	{
		self::$page = $page;
	}

	public static function setItem($item)
	{
		self::$item = $item;
	}
}