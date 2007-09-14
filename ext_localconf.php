<?php
if(!defined('TYPO3_MODE'))   die('Access denied.');

$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if($conf['debug']) {
	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_ncstaticfilecache.debug.php');

	// Create cache
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.debug.php:&tx_ncstaticfilecache';
	// Catch Ctrl + Shift + reload (only works when backend user is logged in)
	//	You need the be_typo_user cookie detection enabled in the rewrite rules
	//	for this to work.
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['tslib_fe-PostProc'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.debug.php:&tx_ncstaticfilecache->headerNoCache';

	// Log a cache miss if no_cache is true
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.debug.php:&tx_ncstaticfilecache->logNoCache';

	// Clear cache
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.debug.php:&tx_ncstaticfilecache->clearCachePostProc';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.debug.php:&tx_ncstaticfilecache->clearStaticFile';

	// Set cookie when User logs in
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.debug.php:&tx_ncstaticfilecache->setFeUserCookie';
}
else {
	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_ncstaticfilecache.php');

	// Create cache
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache';
	// Catch Ctrl + Shift + reload (only works when backend user is logged in)
	//	You need the be_typo_user cookie detection enabled in the rewrite rules
	//	for this to work.
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['tslib_fe-PostProc'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->headerNoCache';

	// Log a cache miss if no_cache is true
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->logNoCache';

	// Clear cache
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->clearCachePostProc';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->clearStaticFile';

	// Set cookie when User logs in
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->setFeUserCookie';
}

if (TYPO3_MODE=='BE')	{
	// Setting up scripts that can be run from the cli_dispatch.phpsh script.
	$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['nc_staticfilecache'] = array('EXT:nc_staticfilecache/cli/cleaner.php','_CLI_ncstaticfilecache');
}
?>