<?php

/**
 * Extension configuration
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Register with "crawler" extension:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_ncstaticfilecache_clearstaticfile'] = 'clear static cache file';
// Hook to process clearing static cached files if "crawler" extension is active:
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\Hook\\Crawler->clearStaticFile';

// Create cache
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\StaticFileCache';
// Catch Ctrl + Shift + reload (only works when backend user is logged in)
//	You need the be_typo_user cookie detection enabled in the rewrite rules
//	for this to work.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['tslib_fe-PostProc'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\StaticFileCache->headerNoCache';

// Log a cache miss if no_cache is true
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\StaticFileCache->logNoCache';

// Clear cache
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\StaticFileCache->clearCachePostProc';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\StaticFileCache->clearStaticFile';

// Set cookie when User logs in
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][$_EXTKEY] = 'SFC\\NcStaticfilecache\\StaticFileCache->setFeUserCookie';

// register command controller
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'SFC\\NcStaticfilecache\\Command\\CacheCommandController';