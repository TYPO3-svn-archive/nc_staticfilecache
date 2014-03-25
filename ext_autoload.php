<?php
$extensionPath = t3lib_extMgm::extPath('nc_staticfilecache');
return array(
	'tx_ncstaticfilecache_tasks_processdirtypages'                          => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_processDirtyPages.php',
	'tx_ncstaticfilecache_tasks_processdirtypages_additionalfieldprovider'  => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_processDirtyPages_AdditionalFieldProvider.php',
	'tx_ncstaticfilecache_tasks_removeexpiredpages'                         => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_removeExpiredPages.php',
	'tx_ncstaticfilecache_tasks_removeexpiredpages_additionalfieldprovider' => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_removeExpiredPages_AdditionalFieldProvider.php',
	'tx_ncstaticfilecache_infomodule'                                       => $extensionPath . 'infomodule/class.tx_ncstaticfilecache_infomodule.php',
	'tx_ncstaticfilecache'                                                  => $extensionPath . 'class.tx_ncstaticfilecache.php',
	'tx_ncstaticfilecache_crawlerhook'                                      => $extensionPath . 'class.tx_ncstaticfilecache_crawlerhook.php',
);