<?php

namespace App\Models;

class Page extends BaseModel
{
	protected $table = 'pages';
	protected $fillable = ['name', 'page_id', 'model_key', 'depth', 'slug', 'position', 'title', 'content', 'meta_description', 'meta_keywords'];

	public function parentPage()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}

	public function pages()
	{
		return $this->hasMany(Page::class, 'page_id')->orderBy('position', 'asc');
	}

	public function getUrlAttribute()
	{
		$defaultLocale = config('gtcmslang.defaultLocale');
		$propertyName = config('gtcms.premium') && config('gtcmslang.siteIsMultilingual') ? "slug_" . app()->getLocale() : "slug";
		$langPrefix = app()->getLocale() == $defaultLocale ? '' : "/" . app()->getLocale();

		return request()->root() . $langPrefix . "/" . $this->$propertyName;
	}

	public static function getPageKeyList()
	{
		return [
			'standard' => 'Standard'
		];
	}
}