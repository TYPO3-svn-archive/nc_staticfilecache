<?php
if(!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Register with "crawler" extension:
$TYPO3_CONF_VARS['EXTCONF']['crawler']['procInstructions']['tx_ncstaticfilecache_clearstaticfile'] = 'clear static cache file';
// Hook to process clearing static cached files if "crawler" extension is active:
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache_crawlerhook.php:&tx_ncstaticfilecache_crawlerhook->clearStaticFile';

// Create cache
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache';
// Catch Ctrl + Shift + reload (only works when backend user is logged in)
//	You need the be_typo_user cookie detection enabled in the rewrite rules
//	for this to work.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['tslib_fe-PostProc'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->headerNoCache';

// Log a cache miss if no_cache is true
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->logNoCache';

// Clear cache
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->clearCachePostProc';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->clearStaticFile';

// Set cookie when User logs in
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][$_EXTKEY] = 'EXT:nc_staticfilecache/class.tx_ncstaticfilecache.php:&tx_ncstaticfilecache->setFeUserCookie';

if (TYPO3_MODE=='BE') {
	// Setting up scripts that can be run from the cli_dispatch.phpsh script.
	$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
		'EXT:nc_staticfilecache/cli/cleaner.php',
		'_CLI_ncstaticfilecache'
	);

        // Setup for the scheduler
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_ncstaticfilecache_tasks_removeExpiredPages'] = array(
        'extension'        => $_EXTKEY,
        'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:nc_staticfilecache_task_removeExpiredPages.name',
        'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:nc_staticfilecache_task_removeExpiredPages.description',
        'additionalFields' => 'tx_ncstaticfilecache_tasks_removeExpiredPages_AdditionalFieldProvider'
    );
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_ncstaticfilecache_tasks_processDirtyPages'] = array(
        'extension'        => $_EXTKEY,
        'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:nc_staticfilecache_task_processDirtyPages.name',
        'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:nc_staticfilecache_task_processDirtyPages.description',
        'additionalFields' => 'tx_ncstaticfilecache_tasks_processDirtyPages_AdditionalFieldProvider'
    );
}
?>
