<?php

namespace App\Classes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class Tools {

	public static function getGets(array $currentGets = array(), $array = false, $qMark = "?") {
		$gets = "";
		$getsArray = array();
		$added = array();
		if (isset($_GET)) {
			foreach($_GET as $key=>$value) {
				if (strpos($key, "getIgnore") === false) {
					if (!array_key_exists($key, $currentGets)) {
						$gets .= "&" . $key . "=" . $value;
						$getsArray[$key] = $value;
					} else if ($currentGets[$key] !== NULL) {
						$added[$key] = true;
						$gets .= "&" . $key . "=" . $currentGets[$key];
						$getsArray[$key] = $currentGets[$key];
					}
				}
			}
		}
		foreach ($currentGets as $key=>$getValue) {
			if (!isset($added[$key]) && $getValue !== NULL) {
				$gets .= "&" . $key . "=" . $getValue;
				$getsArray[$key] = $getValue;
			}
		}
		if ($gets != "") {
			$gets = ltrim ($gets,'&');
			if ($qMark) {
				$gets = $qMark . $gets;
			}
		}

		if ($array) {
			return $getsArray;
		}
		return $gets;
	}

	public static function getSearchAndOrderGets($qMark = true, $ampersand = false, $skipOrderGets = false, $skipSearchGets = false) {
		$gets = "";
		if (isset($_GET)) {
			foreach($_GET as $key=>$value) {
				if (in_array($key, array('orderBy', 'direction', 'page')) && !$skipOrderGets) {
					$gets .= "&" . $key . "=" . $value;
				}
				if (strpos($key, "search_") === 0 && $value && !$skipSearchGets) {
					$gets .= "&" . $key . "=" . $value;
				}
			}
		}
		if ($gets != "") {
			$gets = ltrim ($gets,'&');
			if ($qMark) {
				$gets = "?" . $gets;
			} else if ($ampersand) {
				$gets = "&" . $gets;
			}
		}
		return $gets;
	}

	public static function createItemList($itemTree = NULL, &$items, array $params = array()) {

		if (!isset($params['depth'])) $params['depth'] = 0;
		if (!isset($params['itemName'])) $params['itemName'] = 'name';
		if (!isset($params['subItemName'])) $params['subItemName'] = NULL;
		if (!isset($params['showSpaces'])) $params['showSpaces'] = TRUE;
		if (!isset($params['parent'])) $params['parent'] = FALSE;
		if (!isset($params['parents'])) $params['parents'] = array();
		if (!isset($params['key'])) $params['key'] = NULL;
		if (!isset($params['arrayKey'])) $params['arrayKey'] = 'id';

		$spaces = "";
		if ($params['depth'] > 0 && $params['showSpaces']) {
			for ($i = 0; $i < $params['depth']; $i++) {
				$spaces .= "&nbsp;&nbsp;";
			}
			$spaces .= " - ";
		}

		foreach ($itemTree as $item) {

			$originalParents = $params['parents'];
			$originalParent = $params['parent'];

			$keyValue = '';
			if ($params['depth'] > 0) {
				$key = '';
			} else if ($params['key']) {
				$key = $params['key'];
				$keyValue = $item->$key . " - ";
			}

			if ($params['parent'] && $params['parent'] !== TRUE && $params['depth'] > count($params['parents'])) {
				$params['parents'][] = $params['parent'];
			}

			$thisparents = $params['parents'];
			$thisparents = implode(", ",$thisparents);
			$thisparents = "(" . $thisparents . ") ";
			if ($thisparents == "() ") $thisparents = "";

			$items[$item->{$params['arrayKey']}] = $thisparents.$keyValue.$spaces.($item->{$params['itemName']});

			$subItems = new Collection();
			if ($params['subItemName']) {
				$subItems = $item->{$params['subItemName']};
			}
			if ($params['subItemName'] && ($subItems->count())) {
				$params['depth'] = $params['depth'] + 1;
				if ($params['parent']) {
					$params['parent'] = $item->{$params['itemName']};
				} else {
					$params['parents'] = array();
				}
				self::createItemList($subItems, $items, $params);
			}

			$params['parents'] = $originalParents;
			$params['parent'] = $originalParent;

		}

		return $items;
	}

	public static function createMultiSelectList($itemList) {
		if (is_array($itemList)) {
			$msItemList = array();
			foreach ($itemList as $key => $value) {
				$msItemList[] = $key;
			}
			return $msItemList;
		} else {
			return NULL;
		}
	}

	public static function price($price) {
		return number_format($price, 2, ",", ".");
	}

	public static function lorem($paragraphsNum = 1) {
		$paragraphs = array(
			"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse pharetra luctus mattis. Sed facilisis, enim eget blandit tincidunt, mi mi pharetra enim, rutrum dictum enim mi in turpis.",
			"Nam imperdiet magna sed luctus mattis. Aliquam faucibus luctus pellentesque. Donec quis nibh quis risus feugiat eleifend. Sed urna nibh, laoreet quis fringilla vel, tempus vel velit. Curabitur malesuada odio ut sapien sodales ullamcorper nec id purus.",
			"Proin egestas dolor eu dapibus tempor. Nunc in sem quis justo volutpat rutrum. Nam quis est tempus, malesuada ante id, tristique mi. Mauris quis aliquam urna."
		);
		$output = [];
		for ($i = 1; $i <= $paragraphsNum; $i++) {
			$output[] = $paragraphs[$i];
		}
		return implode(" ", $output);
	}

	static public function parseMediaUrl($mediaUrl) {
		$sourceKey = '';
		$originalId = '';

		$isYoutube = preg_match('/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $mediaUrl, $match);

		if ($isYoutube) {
			$sourceKey = 'youtube';
			$originalId = $match[1];
		}

		if ($sourceKey == '') {
			$isVimeo = preg_match("/.*(?:vimeo\.com\/)(?:(?:(?:channels\/[A-z]+\/)|(?:groups\/[A-z]+\/videos\/))|(?:video\/))?([0-9]+)/i", $mediaUrl, $match);

			if ($isVimeo) {
				$sourceKey = 'vimeo';
				$originalId = $match[1];
			}
		}

		if (empty($sourceKey)) {
			return FALSE;
		} else {
			return array(
				'sourceKey' => $sourceKey,
				'originalId' => $originalId
			);
		}
	}

	public static function appendToFilename($filename, $string, $glue = "-") {
		$parts = explode('.', $filename);
		$ext = $parts[count($parts) - 1];
		unset($parts[count($parts) - 1]);
		$filename = implode(".", $parts);

		$newFilename = $filename . $glue . $string . "." . $ext;
		return $newFilename;
	}

	public static function validateDate($date) {
		if (!$date) {
			return false;
		}

		$valid = true;
		try {
			Carbon::parse($date);
		} catch (\Exception $e) {
			$valid = false;
		}
		return $valid;
	}

	public static function fullUrl() {
		$queryString = $_SERVER['QUERY_STRING'];
		$fullUrl = \Request::url();

		if ($queryString) {
			$fullUrl .= "?" . $queryString;
		}

		return $fullUrl;
	}

}