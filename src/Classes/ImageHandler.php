<?php

namespace App;

class ImageHandler {

	const DIM_ERROR = 99;

	public static function process($modelConfig, $imageFields, $parentProperty) {

		$imageData = array();
		$counter = 0;

		foreach ($imageFields as $imageField) {
			$imageData[$counter]['property'] = $imageField->property;
			if (\Request::hasFile($imageField->property)) {
				$ext = \Request::file($imageField->property)->getClientOriginalExtension();
				if (!in_array(strtolower($ext), array('jpg', 'jpeg', 'gif', 'png'))) {
					throw new \Exception ("File is not an image");
				} else {
					$basePath = public_path()."/img/modelImages/".$modelConfig->name."/";

					$dirs = [
						public_path("img"),
						public_path("img/modelImages"),
						public_path("img/modelImages/" . $modelConfig->name),
						public_path("img/modelImages/" . $modelConfig->name . "/original"),
						public_path("img/modelImages/" . $modelConfig->name . "/gtcmsThumb"),
					];

					foreach ($dirs as $dir) {
						if (!is_dir($dir)) {
							mkdir($dir, 0755);
						}
					}

					//here check if file exists
					do {
						$name = str_random(32).".".$ext;
					} while (file_exists($basePath."original/".$name));
					//end check

					//copy the image to the original folder
					\Image::make(\Request::file($imageField->property)->getRealPath())->save($basePath . "original/" . $name);
					$img = \Image::make($basePath . "original/" . $name);

					if ($parentProperty && $modelConfig->name == 'ModelImage') {
						$parentModelConfig = AdminHelper::modelExists($parentProperty, 'id');
						if (config('gtcms.premium') && $parentModelConfig->keyBasedSizes) {
							$sizes = GtcmsPremium::getKeyBasedImageSizes($parentModelConfig);
						} else {
							$sizes = $parentModelConfig->imageSizes;
						}
					} else {
						$sizes = $imageField->sizes;
					}

					if (!$sizes) {
						throw new \Exception("Sizes not found!");
					}

					// Get the first size, which has the min/max dimensions
					foreach ($sizes as $size) break;

					$size = AdminHelper::objectToArray($size);
					$minWidth = $size[0];
					$minHeight = $size[1];
					$transformMethod = $size[2];

					if (in_array($transformMethod, array('resizeCanvas', 'resize'))) {
						if ($img->width() < $minWidth && $img->height() < $minHeight) {
							throw new \Exception (trans('gtcms.imageTooSmall'), self::DIM_ERROR);
						}
					} if (in_array($transformMethod, array('minWidth'))) {
						if ($img->width() < $minWidth) {
							throw new \Exception (trans('gtcms.imageTooSmall'), self::DIM_ERROR);
						}
					} if (in_array($transformMethod, array('minHeight'))) {
						if ($img->height() < $minHeight) {
							throw new \Exception (trans('gtcms.imageTooSmall'), self::DIM_ERROR);
						}
					} else {
						if ($img->width() < $minWidth || $img->height() < $minHeight) {
							throw new \Exception (trans('gtcms.imageTooSmall'), self::DIM_ERROR);
						}
					}

					$returnFolder = 'gtcmsThumb';

					$gtcmsSize = array(
						80, 80, 'resizeCanvas', 'gtcmsThumb', 100
					);

					$sizes = AdminHelper::objectToArray($sizes);
					array_unshift($sizes, $gtcmsSize);

					foreach ($sizes as $size) {
						$newImg = clone($img);
						$size = AdminHelper::objectToArray($size);

						if (!is_dir($basePath . $size[3])) {
							mkdir($basePath . $size[3], 0755);
						}

						if ($size[2] == 'crop') {
							$newImg->fit($size[0], $size[1])->save($basePath . $size[3] . "/" . $name, $size[4]);
						} else if (in_array($size[2], array('resize', 'minWidth', 'minHeight'))){
							$newImg->resize($size[0], $size[1], function($constraint){
								$constraint->aspectRatio();
								$constraint->upsize();
							})->save($basePath . $size[3] . "/" . $name, $size[4]);
						} else if ($size[2] == 'resizeCanvas'){
							$newImg->resize($size[0], null, function ($constraint) {
								$constraint->aspectRatio();
								$constraint->upsize();
							});

							$newImg->resize(null, $size[1], function ($constraint) {
								$constraint->aspectRatio();
								$constraint->upsize();
							});

							if ($newImg->width() < $size[0] || $newImg->height() < $size[1]) {
								$newImg = \Image::canvas($size[0], $size[1], '#FFFFFF')->insert($newImg, 'center');
							}

							$newImg->save($basePath . $size[3] . "/" . $name, $size[4]);
						} else {
							$newImg->save($basePath . $size[3] . "/" . $name, $size[4]);
						}
					}

					$imageData[$counter]['filename'] = $name;
					$imageData[$counter]['returnFolder'] = $returnFolder;
					$counter++;
				}
			}
		}
		return $imageData;
	}

}