<?php

namespace App;

class ModelImage extends BaseModel {

	protected $table = 'model_images';
	protected $fillable = array('imagename', 'caption');

}