<?php

namespace App;

class Page extends BaseModel {

	protected $table = 'pages';
	protected $fillable = array('name', 'page_id', 'model_key', 'depth', 'slug', 'position', 'title', 'content', 'meta_description', 'meta_keywords');

	public function parentPage() {
		return $this->belongsTo('App\Page', 'page_id');
	}

	public function pages() {
		return $this->hasMany('App\Page', 'page_id')->orderBy('position', 'asc');
	}

	public function getUrlAttribute() {
		$defaultLocale = config('gtcmslang.defaultLocale');
		$propertyName = config('gtcms.premium') && config('gtcmslang.siteIsMultilingual') ? "slug_" . \App::getLocale() : "slug";
		$langPrefix = \App::getLocale() == $defaultLocale ? '' : "/" . \App::getLocale();
		return \Request::root() . $langPrefix . "/" . $this->$propertyName;
	}

	public static function getPageKeyList() {
		return array(
			'standard' => 'Standard'
		);
	}

}