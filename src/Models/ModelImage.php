<?php

namespace App\Models;

class ModelImage extends BaseModel
{
	protected $table = 'model_images';
	protected $fillable = ['imagename', 'caption'];
}