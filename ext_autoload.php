<?php
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('nc_staticfilecache');
return array(
	'tx_ncstaticfilecache_tasks_processdirtypages'                          => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_processDirtyPages.php',
	'tx_ncstaticfilecache_tasks_processdirtypages_additionalfieldprovider'  => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_processDirtyPages_AdditionalFieldProvider.php',
	'tx_ncstaticfilecache_tasks_removeexpiredpages'                         => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_removeExpiredPages.php',
	'tx_ncstaticfilecache_tasks_removeexpiredpages_additionalfieldprovider' => $extensionPath . 'tasks/class.tx_ncstaticfilecache_tasks_removeExpiredPages_AdditionalFieldProvider.php',
);