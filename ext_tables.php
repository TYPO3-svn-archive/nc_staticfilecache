<?php

/**
 * Extension backend registration
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$tmp = Array(
	'tx_ncstaticfilecache_cache' => Array(
		'exclude' => 0,
		'label'   => 'LLL:EXT:nc_staticfilecache/locallang_db.xml:nc_staticfilecache.field',
		'config'  => Array(
			'type'    => 'check',
			'default' => '1',
		),
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tmp);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_ncstaticfilecache_cache;;;;1-1-1');


if (TYPO3_MODE == 'BE') {
	// Add Web>Info module:
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction('web_info', 'tx_ncstaticfilecache_infomodule', NULL, 'LLL:EXT:nc_staticfilecache/locallang_db.php:moduleFunction.tx_ncstaticfilecache_infomodule');
}