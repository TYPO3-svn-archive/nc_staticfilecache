<?php

########################################################################
# Extension Manager/Repository config file for ext "nc_staticfilecache".
#
# Auto generated 20-09-2010 20:03
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Static File Cache',
	'description' => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase response times for static pages by a factor of 230!',
	'category' => 'fe',
	'shy' => 0,
	'version' => '2.3.6',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'pages',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Michiel Roos, Tim Lochm�ller, Marc H�rsken',
	'author_email' => 'extensions@netcreators.com',
	'author_company' => 'Netcreators',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.11-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:24:{s:9:"Changelog";s:4:"21cc";s:10:"README.txt";s:4:"2972";s:30:"class.tx_ncstaticfilecache.php";s:4:"1f75";s:42:"class.tx_ncstaticfilecache_crawlerhook.php";s:4:"e98e";s:16:"ext_autoload.php";s:4:"eb7f";s:21:"ext_conf_template.txt";s:4:"9a63";s:12:"ext_icon.gif";s:4:"03df";s:17:"ext_localconf.php";s:4:"c470";s:14:"ext_tables.php";s:4:"378e";s:14:"ext_tables.sql";s:4:"8387";s:16:"locallang_db.xml";s:4:"3560";s:11:"patch.patch";s:4:"9ce5";s:15:"cli/cleaner.php";s:4:"45e1";s:25:"doc/gzip.realurl.htaccess";s:4:"1fe7";s:32:"doc/gzip.simulateStatic.htaccess";s:4:"dd32";s:14:"doc/manual.sxw";s:4:"7ec3";s:26:"doc/plain.realurl.htaccess";s:4:"9617";s:33:"doc/plain.simulateStatic.htaccess";s:4:"7a3d";s:52:"infomodule/class.tx_ncstaticfilecache_infomodule.php";s:4:"b8dc";s:24:"infomodule/locallang.php";s:4:"022c";s:60:"tasks/class.tx_ncstaticfilecache_tasks_processDirtyPages.php";s:4:"1917";s:84:"tasks/class.tx_ncstaticfilecache_tasks_processDirtyPages_AdditionalFieldProvider.php";s:4:"387a";s:61:"tasks/class.tx_ncstaticfilecache_tasks_removeExpiredPages.php";s:4:"5f45";s:85:"tasks/class.tx_ncstaticfilecache_tasks_removeExpiredPages_AdditionalFieldProvider.php";s:4:"f8f9";}',
	'suggests' => array(
	),
);

?>