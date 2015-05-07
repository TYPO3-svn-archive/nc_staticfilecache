<?php
/**
 * Check if the Protocol is valid
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the Protocol is valid
 *
 * @author Tim Lochmüller
 */
class ValidProtocol extends AbstractRule {

	/**
	 * Check if the Protocol is valid
	 *
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param array                        $explanation
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing) {
		$scheme = strtolower(parse_url($uri, PHP_URL_SCHEME));
		$configuration = GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\Configuration');
		$allowHttps = $configuration->get('enableHttpsCaching');
		$cached = ($scheme === 'http' || ($scheme === 'https' && $allowHttps));

		if (!$cached) {
			$explanation[__CLASS__] = 'The current protocol is not allowed by configuration: ' . $scheme;
			$skipProcessing = TRUE;
		}
	}
}
