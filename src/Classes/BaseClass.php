<?php

namespace App\Classes;


class BaseClass extends \stdClass
{
	public function __get($property)
	{
		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return null;
	}
}