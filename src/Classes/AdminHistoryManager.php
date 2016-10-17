<?php

namespace App;

class AdminHistoryManager {

	public static function getHistory($removeLast = true) {
		if (\Session::has('adminHistoryLinks')) {
			$links = \Session::get('adminHistoryLinks');
		} else {
			$links = array();
		}
		if ($removeLast && $links) {
			array_pop($links);
		}
		return $links ? $links : false;
	}

	public static function addHistoryLink($link = false, $modelName = false, $ignoreRequestForModelName = false, $sideTablePaginating = false) {
		if (\Session::has('adminHistoryLinks')) {
			$links = \Session::get('adminHistoryLinks');
		} else {
			$links = array();
		}

		if (!$link) {
			$url = explode('?', $_SERVER["REQUEST_URI"]);
			$link = (array_shift($url)) . Tools::getGets();
		}
		if (!$modelName) {
			$modelName = \Request::segment(2);
		}
		$linkSegments = explode("/", $link);
		$action = isset($linkSegments[3]) ? $linkSegments[3] : false;
		$addLink = isset($linkSegments[4]) && $linkSegments[4] == "new" ? true : false;

		$modelConfig = AdminHelper::modelExists($modelName);

		if ($ignoreRequestForModelName) {
			$returnModelName = $modelConfig ? $modelConfig->hrName : $modelName;
		} else {
			$returnModelName = $modelConfig ? (count(\Request::segments()) > 2 && \Request::segment(3) == 'edit' ? $modelConfig->hrName : $modelConfig->hrNamePlural) : $modelName;
		}

		$currentLinkData = array(
			'link' => $link,
			'action' => $action,
			'addLink' => $addLink,
			'modelConfigName' => $modelConfig ? $modelConfig->name : $modelName,
			'modelName' => $returnModelName,
			'modelIcon' => $modelConfig && $modelConfig->faIcon ? $modelConfig->faIcon : 'fa-circle'
		);

		$tempLinks = $links;
		if (count($links)) {
			$delete = false;
			$currentIndex = null;
			foreach ($links as $index => $cLink) {

				// If ModelName and Action already exist in history, it means we're
				// either going back or side-table paginating
				// therefore delete all history links AFTER the current history link
				// and replace current history with the newly modified one
				if ($cLink['modelConfigName'] == $modelName && $cLink['action'] == $action) {
					$delete = true;
					$currentIndex = $index;

					// Skip this iteration because this is the link we're going back to
					continue;
				}

				// Delete all links after the one we're going back to
				if ($delete) {
					unset($tempLinks[$index]);
				}
			}
			if ($delete) {
				if ($sideTablePaginating) {
					// If side-table paginating then replace last link with current link
					// so the $_GET side-table page parameter is added
					if (!is_null($currentIndex) && isset($tempLinks[$currentIndex])) {
						$tempLinks[$currentIndex] = $currentLinkData;
					}
				}

				\Session::put('adminHistoryLinks', $tempLinks);

				// There is no need to insert the current link because it already exists
				// so just return
				return;
			}
		}

		$links[] = $currentLinkData;

		\Session::put('adminHistoryLinks', $links);
	}

	public static function replaceAddLink($link, $modelName) {
		if (\Session::has('adminHistoryLinks')) {
			$links = \Session::get('adminHistoryLinks');
		} else {
			$links = array();
		}

		$setLinks = false;
		if (count($links)) {
			foreach ($links as $index => $cLink) {
				if ($cLink['modelConfigName'] == $modelName && $cLink['addLink']) {
					$links[$index]['link'] = $link;
					$links[$index]['addLink'] = false;
					$setLinks = true;
					break;
				}
			}
		}

		if ($setLinks) {
			\Session::put('adminHistoryLinks', $links);
		}
	}

	public static function clearHistory() {
		\Session::forget('adminHistoryLinks');
	}

}