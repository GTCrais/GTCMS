<?php

namespace App\Models;

class ModelFile extends BaseModel {

	protected $table = 'model_files';
	protected $fillable = array('filename', 'title');

}