<?php
/**
 * Check if the URI is valid
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the URI is valid
 *
 * @author Tim Lochmüller
 */
class ValidUri {

	/**
	 * Check if the URI is valid
	 *
	 * @param array                        $explanation
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 *
	 * @return array
	 */
	public function check($explanation, $frontendController, $uri) {
		if (strpos($uri, '?') !== FALSE) {
			$explanation[] = 'The URI contain a "?" that is not allowed for static file cache';
		}
		return array(
			'explanation'        => $explanation,
			'frontendController' => $frontendController,
			'uri'                => $uri,
		);
	}
}
