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

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

require_once(PATH_t3lib.'class.t3lib_cli.php');
require_once(t3lib_extMgm::extPath('nc_staticfilecache') . 'class.tx_ncstaticfilecache.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   46: class tx_ncstaticfilecache_cli extends t3lib_cli
 *   66:     function cli_main($argv)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_ncstaticfilecache_cli extends t3lib_cli {
	function tx_ncstaticfilecache_cli () {

		// Running parent class constructor
		parent::t3lib_cli();

		// Setting help texts:
		$this->cli_help['name'] = 'static file cache cleaner -- Removes expired pages from static file cache.';
		$this->cli_help['synopsis'] = 'removeExpiredPages|processDirtyPages ###OPTIONS###';
		$this->cli_help['description'] =
			'removeExpiredPages: Remove expired pages from the static file cache.' . PHP_EOL .
			'processDirtyPages:  Recaches pages that are marked as dirty.';
		$this->cli_help['examples'] = "/.../cli_dispatch.phpsh nc_staticfilecache removeExpiredPages\nThis will remove expired pages from the static file cache.";
		$this->cli_help['author'] = "Michiel Roos, (c) 2007";
	}

	/**
	 * CLI engine
	 *
	 * @param	array		Command line arguments
	 * @return	string
	 */
	function cli_main($argv) {
		// Print help
		$task = (string)$this->cli_args['_DEFAULT'][1];
		if (!$task)	{
			$this->cli_validateArgs();
			$this->cli_help();
			exit;
		}

		if ($task == 'removeExpiredPages') {
			$this->cli_echo("Looking for expired pages.\n");
			/* @var $cleaner tx_ncstaticfilecache */
			$cleaner = t3lib_div::makeInstance('tx_ncstaticfilecache');
			$cleaner->removeExpiredPages($this);
		} elseif ($task == 'processDirtyPages') {
			$this->cli_echo('Looking for dirty pages.' . PHP_EOL);
			/* @var $cleaner tx_ncstaticfilecache */
			$cleaner = t3lib_div::makeInstance('tx_ncstaticfilecache');
			$cleaner->processDirtyPages($this);
		}
	}
}

// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_ncstaticfilecache_cli');
$cleanerObj->cli_main($_SERVER["argv"]);
?>