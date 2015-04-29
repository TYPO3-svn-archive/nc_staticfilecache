<?php
/**
 * Check if the current page is static cachable in TSFE context
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cachable in TSFE context
 *
 * @author Tim Lochmüller
 */
class StaticCacheable {

	/**
	 * Check if the page is static cachable
	 *
	 * @param array                        $explanation
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function check($explanation, $frontendController, $uri, $skipProcessing) {
		if (!$frontendController->isStaticCacheble()) {
			$explanation[] = 'The page is not static chachable via TSFE';
		}
		return array(
			'explanation'        => $explanation,
			'frontendController' => $frontendController,
			'uri'                => $uri,
			'skipProcessing'     => $skipProcessing,
		);
	}
}
