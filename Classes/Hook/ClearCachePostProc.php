<?php
/**
 * ClearCachePostProc
 *
 * @package SFC\NcStaticfilecache\Hook
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Hook;

use SFC\NcStaticfilecache\StaticFileCache;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ClearCachePostProc
 *
 * @author Tim Lochmüller
 */
class ClearCachePostProc {

	/**
	 * Clear cache post processor.
	 * The same structure as DataHandler::clear_cache
	 *
	 * @param    array       $params : parameter array
	 * @param    DataHandler $pObj   : partent object
	 *
	 * @return    void
	 */
	public function clearCachePostProc(array &$params, DataHandler &$pObj) {
		$staticFileCache = StaticFileCache::getInstance();

		if ($params['cacheCmd']) {
			$staticFileCache->clearStaticFile($params);
			return;
		}

		// Do not do anything when inside a workspace
		if ($pObj->BE_USER->workspace > 0) {
			return;
		}

		$uid = intval($params['uid']);
		$table = strval($params['table']);

		if ($uid <= 0) {
			return;
		}

		// Get Page TSconfig relevant:
		list($tscPID) = BackendUtility::getTSCpid($table, $uid, '');
		$TSConfig = $pObj->getTCEMAIN_TSconfig($tscPID);

		if (!$TSConfig['clearCache_disable']) {
			$list_cache = array();
			$databaseConnection = $this->getDatabaseConnection();

			// If table is "pages":
			if ($table == 'pages') {
				// Builds list of pages on the SAME level as this page (siblings)
				$rows_tmp = $databaseConnection->exec_SELECTgetRows('A.pid AS pid, B.uid AS uid', 'pages A, pages B', 'A.uid=' . intval($uid) . ' AND B.pid=A.pid AND B.deleted=0');
				$pid_tmp = 0;
				foreach ($rows_tmp as $row_tmp) {
					$list_cache[] = $row_tmp['uid'];
					$pid_tmp = $row_tmp['pid'];

					// Add children as well:
					if ($TSConfig['clearCache_pageSiblingChildren']) {
						$rows_tmp2 = $databaseConnection->exec_SELECTgetRows('uid', 'pages', 'pid=' . intval($row_tmp['uid']) . ' AND deleted=0');
						foreach ($rows_tmp2 as $row_tmp2) {
							$list_cache[] = $row_tmp2['uid'];
						}
					}
				}

				// Finally, add the parent page as well:
				$list_cache[] = $pid_tmp;

				// Add grand-parent as well:
				if ($TSConfig['clearCache_pageGrandParent']) {
					$rows_tmp = $databaseConnection->exec_SELECTgetRows('pid', 'pages', 'uid=' . intval($pid_tmp));
					foreach ($rows_tmp as $row_tmp) {
						$list_cache[] = $row_tmp['pid'];
					}
				}
			} else {
				// For other tables than "pages", delete cache for the records "parent page".
				$list_cache[] = $tscPID;
			}

			// Delete cache for selected pages:
			$ids = $databaseConnection->cleanIntArray($list_cache);
			foreach ($ids as $id) {
				$cmd = array('cacheCmd' => $id);
				$staticFileCache->clearStaticFile($cmd);
			}
		}

		// Clear cache for pages entered in TSconfig:
		if ($TSConfig['clearCacheCmd']) {
			$Commands = GeneralUtility::trimExplode(',', strtolower($TSConfig['clearCacheCmd']), TRUE);
			$Commands = array_unique($Commands);
			foreach ($Commands as $cmdPart) {
				$cmd = array('cacheCmd' => $cmdPart);
				$staticFileCache->clearStaticFile($cmd);
			}
		}
	}

	/**
	 * Get database connection
	 *
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
