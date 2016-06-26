<?php

namespace App;


class BaseClass extends \stdClass {

	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		} else {
			return NULL;
		}
	}

}