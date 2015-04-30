<?php
/**
 * Clear the cache if there is a no cache header
 *
 * @package SFC\NcStaticfilecache\Hook
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Hook;

use SFC\NcStaticfilecache\Utility\CacheUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Clear the cache if there is a no cache header
 *
 * @author Tim Lochmüller
 */
class HeaderNoCache {

	/**
	 * Detecting if shift-reload has been clicked. Will not be called if re-
	 * generation of page happens by other reasons (for instance that the page
	 * is not in cache yet!) Also, a backend user MUST be logged in for the
	 * shift-reload to be detected due to DoS-attack-security reasons.
	 *
	 * @param    array                        $params : array containing pObj among other things
	 * @param    TypoScriptFrontendController $parent : The calling parent object
	 *
	 * @return    void
	 */
	public function headerNoCache(array &$params, TypoScriptFrontendController $parent) {
		$header = array(
			strtolower($_SERVER['HTTP_CACHE_CONTROL']),
			strtolower($_SERVER['HTTP_PRAGMA'])
		);
		if (in_array('no-cache', $header) && $parent->beUserLogin) {
			CacheUtility::clearByPageId($parent->id);
		}
	}
}
