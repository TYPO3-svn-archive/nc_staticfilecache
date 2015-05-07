<?php
/**
 * Check if the URI is valid
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the URI is valid
 *
 * @author Tim Lochmüller
 */
class ValidUri extends AbstractRule {

	/**
	 * Check if the URI is valid
	 *
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param array                        $explanation
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing) {
		if (strpos($uri, '?') !== FALSE) {
			$explanation[__CLASS__] = 'The URI contain a "?" that is not allowed for static file cache';
			$skipProcessing = TRUE;
		}
	}
}
