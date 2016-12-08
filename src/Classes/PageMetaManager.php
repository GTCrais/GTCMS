<?php

namespace App\Classes;

class PageMetaManager {

	public static $page = null;
	public static $category = null;
	public static $product = null;
	public static $post = null;

	public static function getMetaDescription() {

		if (self::$post) {
			return strip_tags(self::$post->excerpt);
		}

		if (self::$category) {
			return self::$category->meta_description ? self::$category->meta_description : self::getDefaultMeta('description');
		}

		if (self::$product) {
			return self::$product->description;
		}

		if (self::$page) {
			return self::$page->meta_description ? self::$page->meta_description : self::getDefaultMeta('description');
		}

		return self::getDefaultMeta('description');

	}

	public static function getMetaKeywords() {

		if (self::$category) {
			return self::$category->meta_keywords ? self::$category->meta_keywords : self::getDefaultMeta('keywords');
		}

		if (self::$product) {
			return "Default_String " . self::$product->name;
		}

		if (self::$page) {
			return self::$page->meta_keywords ? self::$page->meta_keywords : self::getDefaultMeta('keywords');
		}

		return self::getDefaultMeta('keywords');

	}

	public static function getMetaTitle() {

		$title = config('gtcms.siteName');
		if (self::$page) {
			//$title = self::$page->name . " :: " . $title;
			$title = $title . " :: " . self::$page->name;
		}
		if (self::$post) {
			$title = self::$post->title . " :: " . $title;
		}
		return $title;

	}

	public static function getAdminTitle() {
		$title = trans('gtcms.adminTitle');
		$siteName = config('gtcms.siteName');

		//return $title. " :: " . $siteName;
		return $siteName . " :: " . $title;
	}

	private static function getDefaultMeta($attr = 'description') {
		$homepage = Page::whereNull('page_id')->first();
		if ($attr == 'description') {
			return $homepage->meta_description;
		} else {
			return $homepage->meta_keywords;
		}
	}

	public static function setPage($page) {
		self::$page = $page;
	}

	public static function setCategory($category) {
		self::$category = $category;
	}

	public static function setProduct($product) {
		self::$product = $product;
	}

	public static function setPost($post) {
		self::$post = $post;
	}

}