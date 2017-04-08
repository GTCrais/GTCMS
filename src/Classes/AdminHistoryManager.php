<?php

namespace App\Classes;

use Illuminate\Support\Str;
use Monolog\ErrorHandler;

class AdminHistoryManager
{
	public static function getHistory($removeLast = true)
	{
		if (session('adminHistoryLinks')) {
			$links = session('adminHistoryLinks');
		} else {
			$links = [];
		}

		if ($removeLast && $links) {
			array_pop($links);
		}

		return $links ?: false;
	}

	public static function addHistoryLink($link = false, $modelName = false, $ignoreRequestForModelName = false, $sideTablePaginating = false)
	{
		if (session('adminHistoryLinks')) {
			$links = session('adminHistoryLinks');
		} else {
			$links = [];
		}

		if (!$link) {
			$url = explode('?', $_SERVER["REQUEST_URI"]);
			$link = (array_shift($url)) . Tools::getGets();
		}

		$link = self::cleanLink($link);

		$subtract = config('gtcms.cmsPrefix') ? 0 : 1;

		if (!$modelName) {
			$modelName = request()->segment(2 - $subtract);
		}

		$linkSegments = explode("/", $link);
		$action = isset($linkSegments[3 - $subtract]) ? $linkSegments[3 - $subtract] : false;
		$addLink = isset($linkSegments[4 - $subtract]) && ($linkSegments[4 - $subtract] == "new" || Str::startsWith($linkSegments[4 - $subtract], "new?")) ? true : false;

		$modelConfig = AdminHelper::modelExists($modelName);

		if ($ignoreRequestForModelName) {
			$returnModelName = $modelConfig ? $modelConfig->hrName : $modelName;
		} else {
			$returnModelName = $modelConfig ? (count(request()->segments()) > (2 - $subtract) && request()->segment(3 - $subtract) == 'edit' ? $modelConfig->hrName : $modelConfig->hrNamePlural) : $modelName;
		}

		$currentLinkData = [
			'link' => $link,
			'action' => $action,
			'addLink' => $addLink,
			'modelConfigName' => $modelConfig ? $modelConfig->name : $modelName,
			'modelName' => $returnModelName,
			'modelIcon' => $modelConfig && $modelConfig->faIcon ? $modelConfig->faIcon : 'fa-circle'
		];

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

				session(['adminHistoryLinks' => $tempLinks]);

				// There is no need to insert the current link because it already exists
				// so just return
				return;
			}
		}

		$links[] = $currentLinkData;

		session(['adminHistoryLinks' => $links]);
	}

	public static function replaceAddLink($link, $modelName)
	{
		if (session('adminHistoryLinks')) {
			$links = session('adminHistoryLinks');
		} else {
			$links = [];
		}

		$link = self::cleanLink($link);

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
			session(['adminHistoryLinks' => $links]);
		}
	}

	public static function clearHistory()
	{
		\Session::forget('adminHistoryLinks');
	}

	private static function cleanLink($link)
	{
		$baseUrl = url()->to("/");

		if (Str::startsWith($link, $baseUrl)) {
			return substr($link, strlen($baseUrl));
		}

		return $link;
	}
}