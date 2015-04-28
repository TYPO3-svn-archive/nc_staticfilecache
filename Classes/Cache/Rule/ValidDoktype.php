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
	 * @param array                        $explanation
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function check($explanation, $frontendController, $uri, $skipProcessing) {
		$ignoreTypes = array(3);
		if (in_array($frontendController->page['doktype'], $ignoreTypes)) {
			$explanation[] = 'The Page doktype is one of the follwing not allowed numbers: ' . implode(', ', $ignoreTypes);
			$skipProcessing = TRUE;
		}
		return array(
			'explanation'        => $explanation,
			'frontendController' => $frontendController,
			'uri'                => $uri,
			'skipProcessing'     => $skipProcessing,
		);
	}
}
