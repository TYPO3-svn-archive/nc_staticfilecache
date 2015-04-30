<?php
/**
 * No _INT scripts
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No _INT scripts
 *
 * @author Tim Lochmüller
 */
class NoIntScripts {

	/**
	 * Check if there are no _INT scripts
	 *
	 * @param TypoScriptFrontendController $frontendController
	 * @param string                       $uri
	 * @param array                        $explanation
	 * @param bool                         $skipProcessing
	 *
	 * @return array
	 */
	public function check($frontendController, $uri, $explanation, $skipProcessing) {
		if ($frontendController->isINTincScript()) {

			$collect = array();
			foreach ($frontendController->config['INTincScript'] as $k => $v) {
				$info = array();
				if (isset($v['type'])) {
					$info[] = 'type: ' . $v['type'];
				}
				if (isset($v['conf']['userFunc'])) {
					$info[] = 'userFunc: ' . $v['conf']['userFunc'];
				}
				if (isset($v['conf']['includeLibs'])) {
					$info[] = 'includeLibs: ' . $v['conf']['includeLibs'];
				}
				if (isset($v['conf']['extensionName'])) {
					$info[] = 'extensionName: ' . $v['conf']['extensionName'];
				}
				if (isset($v['conf']['pluginName'])) {
					$info[] = 'pluginName: ' . $v['conf']['pluginName'];
				}

				$collect[] = implode(', ', $info);
			}
			$explanation[__CLASS__] = 'The page has INTincScript: (' . implode(' - ', $collect) . ')';
		}
		return array(
			'frontendController' => $frontendController,
			'uri'                => $uri,
			'explanation'        => $explanation,
			'skipProcessing'     => $skipProcessing,
		);
	}
}
