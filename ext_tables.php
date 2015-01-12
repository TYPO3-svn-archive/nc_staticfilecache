<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tmp = Array (
	'tx_ncstaticfilecache_cache' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:nc_staticfilecache/locallang_db.xml:nc_staticfilecache.field',
		'config' => Array (
			'type' => 'check',
			'default' => '1',
		),
	),
);


ExtensionManagementUtility::addTCAcolumns('pages', $tmp);
ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_ncstaticfilecache_cache;;;;1-1-1');


if (TYPO3_MODE=='BE')	{
	// Add Web>Info module:
    ExtensionManagementUtility::insertModuleFunction(
		'web_info',
		'tx_ncstaticfilecache_infomodule',
        null,
		'LLL:EXT:nc_staticfilecache/locallang_db.php:moduleFunction.tx_ncstaticfilecache_infomodule'
	);
}
?>