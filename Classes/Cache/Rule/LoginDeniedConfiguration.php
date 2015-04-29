<?php
/**
 * LoginDeniedConfiguration
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LoginDeniedConfiguration
 *
 * @author Tim Lochmüller
 */
class LoginDeniedConfiguration {

	/**
	 * Check LoginDeniedConfiguration
	 *
	 * @param array                        $explanation
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function check($explanation, $frontendController, $uri, $skipProcessing) {
		$loginDeniedCfg = (!$frontendController->config['config']['sendCacheHeaders_onlyWhenLoginDeniedInBranch'] || !$frontendController->loginAllowedInBranch);
		if (!$loginDeniedCfg) {
			$explanation[] = 'loginsDeniedCfg is true';
		}
		return array(
			'explanation'        => $explanation,
			'frontendController' => $frontendController,
			'uri'                => $uri,
			'skipProcessing'     => $skipProcessing,
		);
	}
}