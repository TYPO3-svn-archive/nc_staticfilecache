<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "nc_staticfilecache".
 *
 * Auto generated 25-03-2014 07:22
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title'              => 'Static File Cache',
	'description'        => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase response times for static pages by a factor of 230!',
	'category'           => 'fe',
	'version'            => '3.2.0',
	'state'              => 'stable',
	'modify_tables'      => 'pages',
	'author'             => 'Static File Cache team',
	'author_email'       => 'extensions@netcreators.com',
	'author_company'     => 'Netcreators',
	'constraints'        => array(
		'depends' => array(
			'typo3' => '6.2.0-7.1.99',
		),
	),
);