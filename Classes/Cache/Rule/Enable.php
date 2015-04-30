<?php
/**
 * Enable
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use SFC\NcStaticfilecache\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Enable
 *
 * @author Tim Lochmüller
 */
class Enable {

	/**
	 * Enable
	 *
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param array                        $explanation
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function check($frontendController, $uri, $explanation, $skipProcessing) {
		/** @var Configuration $configuration */
		$configuration = GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\Configuration');
		if ((boolean)$configuration->get('disableCache') === TRUE) {
			$explanation[] = 'static cache disabled by TypoScript';
		}
		return array(
			'frontendController' => $frontendController,
			'uri'                => $uri,
			'explanation'        => $explanation,
			'skipProcessing'     => $skipProcessing,
		);
	}
}
