<?php
/**
 * Check if the doktype is valid
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the doktype is valid
 *
 * @author Tim Lochmüller
 */
class ValidDoktype {

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
	public function check($frontendController, $uri, $explanation, $skipProcessing) {
		$ignoreTypes = array(3);
		if (in_array($frontendController->page['doktype'], $ignoreTypes)) {
			$explanation[] = 'The Page doktype is one of the follwing not allowed numbers: ' . implode(', ', $ignoreTypes);
			$skipProcessing = TRUE;
		}
		return array(
			'frontendController' => $frontendController,
			'uri'                => $uri,
			'explanation'        => $explanation,
			'skipProcessing'     => $skipProcessing,
		);
	}
}
