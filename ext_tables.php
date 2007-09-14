<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

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

t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages', $tmp, 1);
t3lib_extMgm::addToAllTCAtypes('pages', 'tx_ncstaticfilecache_cache;;;;1-1-1');

if (TYPO3_MODE=='BE')	{

	// Add Web>Info module:
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_ncstaticfilecache_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_ncstaticfilecache_modfunc1.php',
		'LLL:EXT:nc_staticfilecache/locallang_db.php:moduleFunction.tx_ncstaticfilecache_modfunc1'
	);
}
?>