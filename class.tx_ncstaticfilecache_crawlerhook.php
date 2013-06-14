<?php
/***************************************************************
 *  Copyright notice
 *
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

t3lib_div::requireOnce(t3lib_extMgm::extPath('nc_staticfilecache') . 'class.tx_ncstaticfilecache.php');

/**
 * Static file cache extension crawlerhook
 *
 * @author		Daniel Poetzinger (dev@aoemedia.de)
 * @package		TYPO3
 * @subpackage	tx_ncstaticfilecache
 */
class tx_ncstaticfilecache_crawlerhook {
	/**
	 * @var	tx_ncstaticfilecache
	 */
	public $pubObj;

	public function __construct() {
		$this->pubObj = t3lib_div::makeInstance('tx_ncstaticfilecache');
	}

	/**
	 * Invoked by crawler this method should mark the cache as dirty
	 * (Hook-function called from TSFE, see ext_localconf.php for configuration)
	 *
	 * @param	array		$parameters: Parameters delived by TSFE
	 * @param	tslib_fe	$pObj: The calling parent object (TSFE)
	 * @return	void
	 */
	public function clearStaticFile(array $parameters, tslib_fe $pObj) {
			// Look for "crawler" extension activity:
			// Requirements are that the crawler is loaded, a crawler session is running and tx_ncstaticfilecache_markdirty requested as processing instruction:
		if (t3lib_extMgm::isLoaded('crawler')
			&& $pObj->applicationData['tx_crawler']['running']
			&& in_array('tx_ncstaticfilecache_clearstaticfile', $pObj->applicationData['tx_crawler']['parameters']['procInstructions'])) {

			$pageId = $GLOBALS['TSFE']->id;

			if (is_numeric($pageId)) {
				$clearStaticFileParameters = array('cacheCmd' => $pageId);
				$this->pubObj->clearStaticFile($clearStaticFileParameters);
				$pObj->applicationData['tx_crawler']['log'][] = 'EXT:nc_staticfilecache cleared static file';
			} else {
				$pObj->applicationData['tx_crawler']['log'][] = 'EXT:nc_staticfilecache skipped';
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/class.tx_ncstaticfilecache_crawlerhook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/class.tx_ncstaticfilecache_crawlerhook.php']);
}
?>