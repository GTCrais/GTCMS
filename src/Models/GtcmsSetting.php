<?php

namespace App\Models;

class GtcmsSetting extends BaseModel
{
	protected $table = 'gtcms_settings';
	protected $fillable = ['setting_value'];

	public static function createSettingsObject()
	{
		$settings = self::all();
		$object = new BaseModel();
		$object->id = null;

		foreach ($settings as $setting) {
			$key = $setting->setting_key;
			$object->$key = $setting->setting_value;
		}

		return $object;
	}

	public static function getValue($settingKey)
	{
		$setting = self::where('setting_key', $settingKey)->first();
		if ($setting) {
			return $setting->setting_value;
		}

		return null;
	}

	public static function setValue($settingKey, $settingValue)
	{
		self::where('setting_key', $settingKey)->update(['setting_value' => $settingValue]);
	}
}