<?php

namespace App\Classes;

use Illuminate\Support\Str;

class FileHandler {

	const INVALID_FILE_ERROR = 99;

	public static function process($modelConfig, $fileFields, $parentProperty) {

		$fileData = array();
		$counter = 0;
		foreach ($fileFields as $fileField) {
			$inputProperties = array();
			if (config('gtcms.premium')) {
				GtcmsPremium::setFileHandlerInputProperties($inputProperties, $fileField);
			} else {
				$inputProperties[] = $fileField->property;
			}

			$dirs = [
				public_path("file"),
				public_path("file/modelFiles"),
				public_path("file/modelFiles/" . $modelConfig->name),
			];

			foreach ($dirs as $dir) {
				if (!is_dir($dir)) {
					mkdir($dir, 0755);
				}
			}

			foreach ($inputProperties as $inputProperty) {
				$fileData[$counter]['property'] = $inputProperty;
				if (\Request::hasFile($inputProperty)) {
					$file = \Request::file($inputProperty);
					if ($file->isValid()) {
						$ext = $file->getClientOriginalExtension();
						$basePath = public_path()."/file/modelFiles/".$modelConfig->name."/";
						$targetName = Str::slug($file->getClientOriginalName());
						$targetName = preg_replace('/'.$ext.'$/', '', $targetName);
						$originalTargetName = $targetName;

						// Check if file exists
						$nameCounter = 0;
						while (file_exists($basePath.$targetName . "." . $ext)) {
							$nameCounter++;
							$targetName = $originalTargetName . "-" . $nameCounter;
						}
						$targetName .= "." . $ext;
						// End check

						$file->move($basePath, $targetName);

					} else {
						throw new \Exception ("File is invalid", self::INVALID_FILE_ERROR);
					}

					$fileData[$counter]['filename'] = $targetName;
					$counter++;
				}
			}
		}
		return $fileData;
	}

}