<?php

namespace App\Classes;
use Barryvdh\Debugbar\Facade as Debugbar;


class Dbar extends Debugbar
{
	public static function emergency()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::emergency", func_get_args());
	}

	public static function alert()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::alert", func_get_args());
	}

	public static function critical()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::critical", func_get_args());
	}

	public static function error()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::error", func_get_args());
	}

	public static function warning()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::warning", func_get_args());
	}

	public static function notice()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::notice", func_get_args());
	}

	public static function info()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::info", func_get_args());
	}

	public static function debug()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::debug", func_get_args());
	}

	public static function log()
	{
		call_user_func_array("\\Barryvdh\\Debugbar\\Facade::log", func_get_args());
	}
}