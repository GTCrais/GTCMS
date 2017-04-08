<?php

namespace App\Models;

class ModelImage extends BaseModel
{
	protected $table = 'model_images';
	protected $fillable = array('imagename', 'caption');
}