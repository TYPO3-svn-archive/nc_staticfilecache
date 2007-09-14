<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Michiel Roos <extensions@netcreators.com>
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
/**
 * cache cleaner script for the 'nc_staticfilecache' extension.
 *
 */

define('TYPO3_cliMode', true);
define('PATH_thisScript', $_SERVER['argv'][0]);

require_once(dirname(PATH_thisScript).'/conf.php');
require_once(dirname(PATH_thisScript).'/'.$BACK_PATH.'init.php');
$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nc_staticfilecache']);
if($conf['debug']) {
	require_once(dirname(PATH_thisScript).'/../../class.tx_ncstaticfilecache.debug.php');
}
else {
	require_once(dirname(PATH_thisScript).'/../../class.tx_ncstaticfilecache.php');
}

class staticFileCacheCleaner extends tx_ncstaticfilecache {
	var $extKey = 'nc_staticfilecache';
	var $fileTable = 'tx_ncstaticfilecache_file';
	var $cacheDir = 'typo3temp/tx_ncstaticfilecache/';
	var $debug = false;

	function removeExpired () {
		if ($this->debug)	t3lib_div::devlog("checking for expired pages", $this->extKey, 1);
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'pid, host',
			$this->fileTable,
			'expires <='.$GLOBALS['EXEC_TIME']);

		if ($rows) {
			foreach ($rows as $row) {
				if ($this->debug)	t3lib_div::devlog("clearing expired pid: ".$row['pid'], $this->extKey, 1);
				$params = array('cacheCmd' => $row['pid'], 'host' => $row['host']);
				$this->clearStaticFile ($params);
			}
		}
		else {
			if ($this->debug) t3lib_div::devlog("No expired pages found.", $this->extKey, 1);
		}
	}
}

$cleaner = t3lib_div::makeInstance('staticFileCacheCleaner');
$cleaner->removeExpired();

?>