<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Tim Lochmueller (tim@fruit-lab.de)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * class 'tx_ncstaticfilecache' for the 'nc_staticfilecache' extension.
 *
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_ncstaticfilecache
 *   71:     function clearCachePostProc (&$params, &$pObj)
 *  165:     function clearStaticFile (&$_params)
 *  216:     function getRecordForPageID($pid)
 *  234:     function headerNoCache (&$params, $parent)
 *  250:     function insertPageIncache (&$pObj, &$timeOutTime)
 *  385:     function logNoCache (&$params)
 *  405:     function mkdir_deep($destination,$deepDir)
 *  425:     function removeExpiredPages (&$pObj)
 *  459:     function setFeUserCookie (&$params, &$pObj)
 *  507:     function rm ($dir)
 *
 * TOTAL FUNCTIONS: 10
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Static file cache extension
 *
 * @author	Michiel Roos <extensions@netcreators.com>
 * @package TYPO3
 * @subpackage tx_ncstaticfilecache
 */
class tx_ncstaticfilecache {
	protected $extKey = 'nc_staticfilecache';
	protected $fileTable = 'tx_ncstaticfilecache_file';
	protected $cacheDir = 'typo3temp/tx_ncstaticfilecache/';
	protected $isDebugEnabled = false;
	protected $configuration = array();
	protected $setup = array();

	/**
	 * @var boolean
	 */
	protected $isClearCacheProcessingEnabled = TRUE;

	/**
	 * Constructs this object.
	 */
	public function __construct() {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey])) {
			$this->setConfiguration(
				unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey])
			);
		}
		if (isset($GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.'])) {
			$this->setSetup(
				$GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.']
			);
		}
	}

	/**
	 * Sets the extension configuration (can be modified by admins in extension manager).
	 *
	 * @param	array		$configuration: The extension configuration
	 * @return	void
	 */
	public function setConfiguration(array $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Gets the whole extension configuration (modified in extension manager).
	 *
	 * @return	array		The extension configuration
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * Sets the TypoScript setup.
	 *
	 * @param	array		$setup: The TypoScript setup
	 * @return	void
	 */
	public function setSetup(array $setup) {
		$this->setup = $setup;
	}

	/**
	 * Gets the TypoScript setup.
	 *
	 * @return	array		The TypoScript setup
	 */
	public function getSetup() {
		return $this->setup;
	}

	/**
	 * Gets a specific property of the extension configuration.
	 *
	 * @param	string		$property: Property to get configuration from
	 * @return	mixed		The configuration of a property
	 */
	public function getConfigurationProperty($property) {
		$result = NULL;

		if (isset($this->configuration[$property])) {
			$result = $this->configuration[$property];
		}

		return $result;
	}

	/**
	 * Gets the directory used for storing the cached files.
	 *
	 * @return	string		The directory used for storing the cached files
	 */
	public function getCacheDirectory() {
		return $this->cacheDir;
	}

	/**
	 * Enables the clear cache processing.
	 *
	 * @return void
	 * @see clearCachePostProc
	 */
	public function enableClearCacheProcessing() {
		$this->isClearCacheProcessingEnabled = TRUE;
	}

	/**
	 * Disables the clear cache processing.
	 *
	 * @return void
	 * @see clearCachePostProc
	 */
	public function disableClearCacheProcessing() {
		$this->isClearCacheProcessingEnabled = FALSE;
	}

	/**
	 * Clear cache post processor.
	 * The same structure as t3lib_TCEmain::clear_cache
	 *
	 * @param	object		$_params: parameter array
	 * @param	object		$pObj: partent object
	 * @return	void
	 */
	public function clearCachePostProc(&$params, &$pObj) {
		if ($this->isClearCacheProcessingEnabled === FALSE) {
			return NULL;
		}

		if($params['cacheCmd']) {
			$this->clearStaticFile($params);
		} else {
			$uid = intval($params['uid']);
			$table = strval($params['table']);

			if ($uid > 0) {
				// Get Page TSconfig relavant:
				list($tscPID) = t3lib_BEfunc::getTSCpid($table, $uid, '');
				$TSConfig = $pObj->getTCEMAIN_TSconfig($tscPID);

				if (!$TSConfig['clearCache_disable']) {
					// If table is "pages":
					if (t3lib_extMgm::isLoaded('cms')) {
						$list_cache = array();
						if ($table == 'pages') {

							// Builds list of pages on the SAME level as this page (siblings)
							$res_tmp = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
											'A.pid AS pid, B.uid AS uid',
											'pages A, pages B',
											'A.uid=' . intval($uid) . ' AND B.pid=A.pid AND B.deleted=0'
										);

							$pid_tmp = 0;
							while ($row_tmp = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_tmp)) {
								$list_cache[] = $row_tmp['uid'];
								$pid_tmp = $row_tmp['pid'];

								// Add children as well:
								if ($TSConfig['clearCache_pageSiblingChildren']) {
									$res_tmp2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
										'uid',
										'pages',
										'pid='.intval($row_tmp['uid']).' AND deleted=0'
									);
									while ($row_tmp2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_tmp2)) {
										$list_cache[] = $row_tmp2['uid'];
									}
									$GLOBALS['TYPO3_DB']->sql_free_result($res_tmp2);
								}
							}
							$GLOBALS['TYPO3_DB']->sql_free_result($res_tmp);

							// Finally, add the parent page as well:
							$list_cache[] = $pid_tmp;

							// Add grand-parent as well:
							if ($TSConfig['clearCache_pageGrandParent']) {
								$res_tmp = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'pid',
									'pages',
									'uid=' . intval($pid_tmp)
								);
								if ($row_tmp = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_tmp)) {
									$list_cache[] = $row_tmp['pid'];
								}
								$GLOBALS['TYPO3_DB']->sql_free_result($res_tmp);
							}
						} else {
							// For other tables than "pages", delete cache for the records "parent page".
							$list_cache[] = intval($pObj->getPID($table, $uid));
						}

						// Delete cache for selected pages:
						if (is_array($list_cache)) {
							$ids = $GLOBALS['TYPO3_DB']->cleanIntArray($list_cache);
							foreach ($ids as $id) {
								$cmd = array ('cacheCmd' => $id);
								$this->clearStaticFile($cmd);
							}
						}
					}
				}

				// Clear cache for pages entered in TSconfig:
				if ($TSConfig['clearCacheCmd']) {
					$Commands = t3lib_div::trimExplode(',', strtolower($TSConfig['clearCacheCmd']), true);
					$Commands = array_unique($Commands);
					foreach($Commands as $cmdPart) {
						$cmd = array ('cacheCmd' => $cmdPart);
						$this->clearStaticFile($cmd);
					}
				}
			}
		}
	}

	/**
	 * Clear static file
	 *
	 * @param	object		$_params: array containing 'cacheCmd'
	 * @return	void
	 */
	public function clearStaticFile(&$_params) {
		if (isset($_params['cacheCmd']) && $_params['cacheCmd']) {
			$cacheCmd = $_params['cacheCmd'];
			switch ($cacheCmd) {
				case 'all':
				case 'pages':
					$directory = '';
					if (!$this->configuration['clearCacheForAllDomains']) {
						if (isset($_params['host']) && $_params['host']) {
							$directory = $_params['host'];
						} else {
							$directory = t3lib_div::getIndpEnv('HTTP_HOST');
						}
					}

					$this->debug('clearing all static cache');
					$this->deleteStaticCache(0, $directory);
					break;
				case 'temp_CACHED':
					// Clear temp files, not frontend cache.
					break;
				default:
					if (t3lib_div::testInt($cacheCmd)) {
						$this->debug('clearing cache for pid: ' . $cacheCmd);
						$this->deleteStaticCache($cacheCmd);
					} else {
						$this->debug('Expected integer on clearing static cache', 1, $cacheCmd);
					}
					break;
			}
		}
	}

	/**
	 * Returns records for a page id
	 *
	 * @param	integer		Page id
	 * @return	array		Array of records
	 */
	public function getRecordForPageID($pid) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_ncstaticfilecache_file',
			'pid=' . intval($pid)
		);
	}

	/**
	 * Detecting if shift-reload has been clicked. Will not be called if re-
	 * generation of page happens by other reasons (for instance that the page
	 * is not in cache yet!) Also, a backend user MUST be logged in for the
	 * shift-reload to be detected due to DoS-attack-security reasons.
	 *
	 * @param	object		$_params: array containing pObj among other things
	 * @param	object		$parent: The calling parent object (tslib_fe)
	 * @return	void
	 */
	public function headerNoCache(&$params, $parent) {
		if (strtolower($_SERVER['HTTP_CACHE_CONTROL']) === 'no-cache' || strtolower($_SERVER['HTTP_PRAGMA']) === 'no-cache') {
			if ($parent->beUserLogin) {
				$this->debug('no-cache header found');
				$cmd = array('cacheCmd' => $parent->id);
				$this->clearStaticFile($cmd);
			}
		}
	}

	/**
	 * Write the static file and .htaccess
	 *
	 * @param	object		$pObj: The parent object
	 * @param	string		$timeOutTime: The timestamp when the page times out
	 * @return	void
	 */
	public function insertPageIncache(&$pObj, &$timeOutTime) {
		$isStaticCached = FALSE;
		$this->debug('insertPageIncache');

		// Find host-name / IP, always in lowercase:
		$host = strtolower(t3lib_div::getIndpEnv('HTTP_HOST'));
		$uri = t3lib_div::getIndpEnv('REQUEST_URI');

		$cacheDir = $this->cacheDir . $host;

		$isHttp = (strpos(t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST'), 'http://') === 0);
		$loginsDeniedCfg = (!$pObj->config['config']['sendCacheHeaders_onlyWhenLoginDeniedInBranch'] || !$pObj->loginAllowedInBranch);
		$staticCacheable = $pObj->isStaticCacheble();

		$fieldValues = array();
		$additionalHash = '';

			// Hook: Initialize variables before starting the processing.
			// $TYPO3_CONF_VARS['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_initializeVariables']
		$initializeVariablesHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_initializeVariables'];
		if (is_array($initializeVariablesHooks)) {
			foreach ($initializeVariablesHooks as $hookFunction) {
				$hookParameters = array(
					'TSFE' => $pObj,
					'host' => &$host,
					'uri' => &$uri,
					'isHttp' => &$isHttp,
					'cacheDir' => &$cacheDir,
					'fieldValues' => &$fieldValues,
					'loginDenied' => &$loginsDeniedCfg,
					'additionalHash' => &$additionalHash,
					'staticCacheable' => &$staticCacheable,
				);
				t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}

			// Only process if there are not query arguements, no link to external page (doktype=3) and not called over https:
		if (strpos($uri, '?') === false && $pObj->page['doktype'] != 3 && $isHttp) {
			if ($this->configuration['recreateURI']) {
				$uri = $this->recreateURI();
			}

			// Workspaces have been introduced with TYPO3 4.0.0:
			$workspacePreview = (t3lib_div::int_from_ver(TYPO3_version) >= 4000000 && $pObj->doWorkspacePreview());

			$file = $uri . '/index.html';
			$file = preg_replace('#//#', '/', $file);

			// This is supposed to have "&& !$pObj->beUserLogin" in there as well
			// This fsck's up the ctrl-shift-reload hack, so I pulled it out.
			if ($pObj->page['tx_ncstaticfilecache_cache']
				&& !(isset($this->setup['disableCache']) && $this->setup['disableCache'])
				&& $staticCacheable
				&& !$workspacePreview
				&& $loginsDeniedCfg) {

				$content = $pObj->content;
				t3lib_div::mkdir_deep(PATH_site, $cacheDir . $uri);

				if ($this->configuration['showGenerationSignature']) {
					$content .= "\n<!-- ".strftime (
						$this->configuration['strftime'],
						$GLOBALS['EXEC_TIME']
					) . ' -->';
				}

				$this->debug('writing cache for pid: ' . $pObj->id);

					// If page has a endtime before the current timeOutTime, use it instead:
				if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
					$timeOutTime = $pObj->page['endtime'];
				}
				$timeOutSeconds = $timeOutTime - $GLOBALS['EXEC_TIME'];

				if ($this->configuration['sendCacheControlHeader']) {
					$this->debug('writing .htaccess with timeout: ' . $timeOutSeconds);
					$htaccess = $uri . '/.htaccess';
					$htaccess = preg_replace('#//#', '/', $htaccess);
					$htaccessContent = '<IfModule mod_expires.c>
	ExpiresActive on
	ExpiresByType text/html A' . $timeOutSeconds . '
</IfModule>';
					t3lib_div::writeFile(PATH_site . $cacheDir . $htaccess, $htaccessContent);
				}

					// Hook: Process content before writing to static cached file:
					// $TYPO3_CONF_VARS['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_processContent']
				$processContentHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_processContent'];
				if (is_array($processContentHooks)) {
					foreach ($processContentHooks as $hookFunction) {
						$hookParameters = array(
							'TSFE' => $pObj,
							'content' => $content,
							'fieldValues' => &$fieldValues,
							'directory' => PATH_site . $cacheDir,
							'file' => $file,
							'host' => $host,
							'uri' => $uri,
						);
						$content = t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
					}
				}

				t3lib_div::writeFile(PATH_site . $cacheDir . $file, $content);
				$this->writeCompressedContent(PATH_site . $cacheDir . $file, $content);

				// Check for existing entries with the same uid and file, if a
				// record exists, update timestamp, otherwise create a new record.
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid',
					$this->fileTable,
					'pid=' . $pObj->page['uid'] .
						' AND host = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($host, $this->fileTable) .
						' AND file=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($file, $this->fileTable) .
						' AND additionalhash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($additionalHash, $this->fileTable)
				);

				if ($rows[0]['uid']) {
					$fieldValues['tstamp'] = $GLOBALS['EXEC_TIME'];
					$fieldValues['cache_timeout'] = $timeOutSeconds;
					$fieldValues['isdirty'] = 0;
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, 'uid=' . $rows[0]['uid'], $fieldValues);
				} else {
					$fieldValues = array_merge(
						$fieldValues,
						array(
							'crdate' => $GLOBALS['EXEC_TIME'],
							'tstamp' => $GLOBALS['EXEC_TIME'],
							'cache_timeout' => $timeOutSeconds,
							'file' => $file,
							'pid' => $pObj->page['uid'],
							'reg1' => $pObj->page_cache_reg1,
							'host' => $host,
							'uri' => $uri,
							'additionalhash' => $additionalHash,
						)
					);
					$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->fileTable, $fieldValues);
				}

				$isStaticCached = TRUE;

			} else {

					// This is an 'explode' of the function isStaticCacheble()
				if (!$pObj->page['tx_ncstaticfilecache_cache']) {
					$this->debug('insertPageIncache: static cache disabled by user');
					$explanation = 'static cache disabled on page';
				}
				if (isset($this->setup['disableCache']) && $this->setup['disableCache']) {
					$this->debug('insertPageIncache: static cache disabled by TypoScript "tx_ncstaticfilecache.disableCache"');
					$explanation = 'static cache disabled by TypoScript';
				}
				if ($pObj->no_cache) {
					$this->debug('insertPageIncache: no_cache setting is true');
					$explanation = 'config.no_cache is true';
				}
				if ($pObj->isINTincScript()) {
					$this->debug('insertPageIncache: page has INTincScript');
					$userFunc = array();
					$includeLibs = array();
					foreach($pObj->config['INTincScript'] as $k => $v) {
						$userFunc[] = $v['conf']['userFunc'];
						$includeLibs[] = $v['conf']['includeLibs'];
					}
					$userFunc = array_unique($userFunc);
					$includeLibs = array_unique($includeLibs);
					$explanation = 'page has INTincScript: <ul><li>'.implode('</li><li>', $userFunc).''.implode('</li><li>', $includeLibs).'</li></ul>';
					unset($includeLibs);
					unset($userFunc);
				}
				if ($pObj->isEXTincScript()) {
					$this->debug('insertPageIncache: page has EXTincScript');
					$explanation = 'page has EXTincScript';
				}
				if ($pObj->isUserOrGroupSet() && $this->isDebugEnabled) {
					$this->debug('insertPageIncache: page has user or group set');
					// This is actually ok, we do not need to create cache nor an entry in the files table
					//$explanation = "page has user or group set";
				}
				if ($workspacePreview) {
					$this->debug('insertPageIncache: workspace preview');
					$explanation = 'workspace preview';
				}
				if (!$loginsDeniedCfg) {
					$this->debug('insertPageIncache: loginsDeniedCfg is true');
					$explanation = 'loginsDeniedCfg is true';
				}


				// Check for existing entries with the same uid and file, if a
				// record exists, update timestamp, otherwise create a new record.
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid',
					$this->fileTable,
					'pid=' . $pObj->page['uid'] . 
						' AND host = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($host, $this->fileTable) .
						' AND file=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($file, $this->fileTable) .
						(!$additionalHash ? ' AND additionalhash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($additionalHash, $this->fileTable) : '')
				);
				if ($rows[0]['uid']) {
					$fieldValues['explanation'] = $explanation;
					$fieldValues['isdirty'] = 0;
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, 'uid=' . $rows[0]['uid'], $fieldValues);
				} else {
					$fieldValues = array_merge(
						$fieldValues,
						array(
							'explanation' => $explanation,
							'file' => $file,
							'pid' => $pObj->page['uid'],
							'host' => $host,
							'uri' => $uri,
							'additionalhash' => $additionalHash,
						)
					);
					$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->fileTable, $fieldValues);
				}

				$this->debug('insertPageIncache: ... this page is not cached!');
			}
		}

			// Hook: Post process (no matter whether content was cached statically)
			// $TYPO3_CONF_VARS['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['insertPageIncache_postProcess']
		$postProcessHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['insertPageIncache_postProcess'];
		if (is_array($postProcessHooks)) {
			foreach ($postProcessHooks as $hookFunction) {
				$hookParameters = array(
					'TSFE' => $pObj,
					'host' => $host,
					'uri' => $uri,
					'isStaticCached' => $isStaticCached,
				);
				$content = t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}
	}

	/**
	 * Log cache miss if no_cache is true
	 *
	 * @param	array		$params: Parameters delivered by the calling object (tslib_fe)
	 * @param	object		$parent: The calling parent object (tslib_fe)
	 * @return	void
	 */
	public function logNoCache(&$params, $parent) {
		if($params['pObj']) {
			if($params['pObj']->no_cache) {
				$timeOutTime = 0;
				$this->insertPageInCache($params['pObj'], $timeOutTime);
			}
		}
	}

	/**
	 * Remove expired pages. Call from cli script.
	 *
	 * @param	t3lib_cli		$parent: The calling parent object
	 * @return	void
	 */
	public function removeExpiredPages(t3lib_cli $parent = NULL) {
		$clearedPages = array();

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'file, host, pid, (' . $GLOBALS['EXEC_TIME'].' - crdate - cache_timeout) as seconds',
			$this->fileTable,
			'(cache_timeout + crdate) <= '.$GLOBALS['EXEC_TIME'] . ' AND crdate > 0'
		);

		if ($rows) {
			/* @var $tce t3lib_TCEmain */
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->start(array(), array());

			foreach ($rows as $row) {
				$pageId = $row['pid'];

					// Marks an expired page as dirty without removing it:
				if ($this->configuration['markDirtyInsteadOfDeletion']) {
					if (isset($parent)) {
						$parent->cli_echo("Marked pid as dirty: " . $pageId . "\t" . $row['host'] . $row['file'].", expired by " . $row['seconds'] . " seconds.\n");
					}

					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, 'pid=' . $pageId, array('isdirty' => 1));

					// Really removes an expired page:
				} else {
					if (isset($parent)) {
						$parent->cli_echo("Removed pid: " . $pageId . "\t" . $row['host'] . $row['file'].", expired by " . $row['seconds'] . " seconds.\n");
					}

					// Check whether page was already cleared:
					if (!isset($clearedPages[$pageId])) {
						$tce->clear_cacheCmd($pageId);
						$clearedPages[$pageId] = TRUE;
					}
				}
			}
		} elseif (isset($parent)) {
			$parent->cli_echo("No expired pages found.\n");
		}
	}

	/**
	 * Processes elements that have been marked as dirty.
	 *
	 * @param	t3lib_cli		$parent: The calling parent object
	 * @return	void
	 */
	public function processDirtyPages(t3lib_cli $parent = NULL, $limit = 0) {
		$dirtyElements = $this->getDirtyElements($limit );

		foreach ($dirtyElements as $dirtyElement) {
			$this->processDirtyPagesElement($dirtyElement, $parent);
		}
	}

	/**
	 * Processes one single dirty element - removes data from file system and database.
	 *
	 * @param	array		$dirtyElement: The dirty element record
	 * @param	t3lib_cli	$parent: (optional) The calling parent object
	 * @return	void
	 */
	public function processDirtyPagesElement(array $dirtyElement, t3lib_cli $parent = NULL) {
		$cancelExecution = FALSE;
		$cacheDirectory = $dirtyElement['host'] . dirname($dirtyElement['file']);

		$processDirtyPagesHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['processDirtyPages'];
		if (is_array($processDirtyPagesHooks)) {
			foreach ($processDirtyPagesHooks as $hookFunction) {
				$hookParameters = array(
					'dirtyElement' => $dirtyElement,
					'cacheDirectory' => &$cacheDirectory,
					'cancelExecution' => &$cancelExecution,
				);
				if (isset($parent)) {
					$hookParameters['cliDispatcher'] = $parent;
				}
				t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}

		if (!$cancelExecution) {
			$result = $this->deleteStaticCacheDirectory($cacheDirectory);

			if ($result !== FALSE) {
				$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->fileTable, 'uid=' . $dirtyElement['uid']);
			} elseif ($this->getConfigurationProperty('enableDevelopmentMode')) {
				$this->debug('Could not delete static cache directory "' . $cacheDirectory . '"', 2);
			}

			if (isset($parent)) {
				if (!isset($result)) {
					$resultMessage = 'NOT FOUND';
				} elseif ($result) {
					$resultMessage = 'OK';
				} else {
					$resultMessage = 'FAILED';
				}
				$parent->cli_echo('Removing directory ' . $cacheDirectory . '... ' . $resultMessage . PHP_EOL);
			}
		}
	}

	/**
	 * Set a cookie if a user logs in or refresh it
	 *
	 * This function is needed because TYPO3 always sets the fe_typo_user cookie,
	 * even if the user never logs in. We want to be able to check against logged
	 * in frontend users from mod_rewrite. So we need to set our own cookie (when
	 * a user actually logs in).
	 *
	 * Checking code taken from class.t3lib_userauth.php
	 *
	 * @param	object		$params: parameter array
	 * @param	object		$pObj: partent object
	 * @return	void
	 */
	public function setFeUserCookie(&$params, &$pObj) {
		global $TYPO3_CONF_VARS;

			// Setting cookies
		if ($TYPO3_CONF_VARS['SYS']['cookieDomain']) {
			if ($TYPO3_CONF_VARS['SYS']['cookieDomain']{0} == '/')	{
				$matchCnt = @preg_match($TYPO3_CONF_VARS['SYS']['cookieDomain'], t3lib_div::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE)	{
					t3lib_div::sysLog('The regular expression of $TYPO3_CONF_VARS[SYS][cookieDomain] contains errors. The session is not shared across sub-domains.', 'Core', 3);
				} elseif ($matchCnt) {
					$cookieDomain = $match[0];
				}
			} else {
				$cookieDomain = $TYPO3_CONF_VARS['SYS']['cookieDomain'];
			}
		}

			// If new session and the cookie is a sessioncookie, we need to set it only once!
		if (($pObj->fe_user->loginSessionStarted || $pObj->fe_user->forceSetCookie) && $pObj->fe_user->lifetime == 0) { // isSetSessionCookie()
			if (!$pObj->fe_user->dontSetCookie)	{
				if ($cookieDomain)	{
					SetCookie($this->extKey, 'fe_typo_user_logged_in', 0, '/', $cookieDomain);
				} else {
					SetCookie($this->extKey, 'fe_typo_user_logged_in', 0, '/');
				}
			}
		}

			// If it is NOT a session-cookie, we need to refresh it.
		if ($pObj->fe_user->lifetime > 0) { // isRefreshTimeBasedCookie()
			if ($pObj->fe_user->loginSessionStarted || isset($_COOKIE[$this->extKey])) {
				if (!$pObj->fe_user->dontSetCookie)	{
					if ($cookieDomain)	{
						SetCookie($this->extKey, 'fe_typo_user_logged_in', time() + $pObj->fe_user->lifetime, '/', $cookieDomain);
					} else {
						SetCookie($this->extKey, 'fe_typo_user_logged_in', time() + $pObj->fe_user->lifetime, '/');
					}
				}
			}
		}
	}


	/**
	 * Puts a message to the devlog.
	 *
	 * @param	string		$message: The message to log
	 * @param	integer		$severity: The severity value from warning to fatal error (default: 1)
	 * @return	void
	 */
	protected function debug($message, $severity = 1, $additionalData = false) {
		if (isset($this->configuration['debug']) && $this->configuration['debug']) {
			t3lib_div::devlog(
				trim($message),
				$this->extKey,
				$severity,
				$additionalData
			);
		}
	}

	/**
	 * Recreates the URI of the current request.
	 *
	 * Especially in simulateStaticDocument context, the different URIs lead to the same result
	 * and static file caching would store the wrong URI that was used in the first request to
	 * the website (e.g. "TheGoodURI.13.0.html" is as well accepted as "TheFakeURI.13.0.html")
	 *
	 * @return	string		The recreated URI of the current request
	 */
	protected function recreateURI() {
		$typoLinkConfiguration = array(
			'parameter' => $GLOBALS['TSFE']->id . ' ' . $GLOBALS['TSFE']->type,
		);
		$uri = t3lib_div::getIndpEnv('TYPO3_SITE_PATH') . $this->getContentObject()->typoLink_URL($typoLinkConfiguration);

		return $uri;
	}

	/**
	 * Gets the content object (cObj) of TSFE.
	 *
	 * @return	tslib_cObj		The content object (cObj) of TSFE
	 */
	protected function getContentObject() {
		if (!isset($GLOBALS['TSFE']->cObj)) {
			$GLOBALS['TSFE']->newCObj();
		}
		return $GLOBALS['TSFE']->cObj;
	}

	/**
	 * Deletes the static cache in database and filesystem.
	 * If the extension configuration 'markDirtyInsteadOfDeletion' is set,
	 * the database elements only get tagged a "dirty".
	 *
	 * @param	integer		$pid: (optional) Id of the page perform this action
	 * @param	string		$directory: (optional) The directory to use on deletion
	 *						below the static file directory
	 * @return	void
	 */
	protected function deleteStaticCache($pid = 0, $directory = '') {
		$pid = intval($pid);
		$pidCondition = ($pid ? 'pid=' . $pid : '');
			// Mark specific pages as dirty (does not macht clear all or pages cache):
		if ($pid && $this->configuration['markDirtyInsteadOfDeletion']) {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, $pidCondition, array('isdirty' => 1));
			// Clearing cache in filesystem and database:
		} else {
			if ($pid) {
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->fileTable, $pidCondition);
				foreach ($rows as $row) {
					$cacheDirectory = $row['host'] . dirname($row['file']);
					$result = $this->deleteStaticCacheDirectory($cacheDirectory);

					if ($result !== FALSE) {
						$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->fileTable, 'uid=' . $row['uid']);
					} else {
						$this->debug('Could not delete static cache directory "' . $cacheDirectory . '"', 2);
					}
				}
			} else {
				t3lib_div::rmdir(PATH_site . $this->cacheDir . $directory, true);
				$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->fileTable, $pidCondition);
			}
		}
	}

	/**
	 * Deletes contents of a static cache directory in filesystem, but omit the subfolders
	 *
	 * @param	string		$directory: The directory to use on deletion below the static cache directory
	 * @return	mixed		Whether the action was successful (if directory was not found, NULL is returned)
	 */
	public function deleteStaticCacheDirectory($directory) {
		$result = NULL;

		$directory = trim($directory);
		$cacheDirectory = PATH_site . $this->cacheDir . $directory;

		$this->debug('Removing files of directory "' . $cacheDirectory . '"');
		if (!empty($directory) && is_dir($cacheDirectory)) {
			$directoryHandle = @opendir($cacheDirectory);

			if ($directoryHandle === FALSE) {
				return $result;
			}

			while (($element = readdir($directoryHandle))) {
				if ($element == '.' || $element == '..') {
					continue;
				}

				if (is_file($cacheDirectory . '/' . $element)) {
						//keep false if one file cannot be deleted -> entries marked dirty will not be deleted from DB
					if (FALSE === unlink($cacheDirectory . '/' . $element)) {
						$result = FALSE;
					} elseif ($result !== FALSE) {
						$result = TRUE;
					}
				}
			}

			closedir($directoryHandle);
			@rmdir($cacheDirectory);
		}
		return $result;
	}
	
	/**
	 * Gets all dirty elements from database.
	 *
	 * @param	integer		$limit: (optional) Defines a limit for results to look up
	 * @return	array		All dirty elements from database
	 */
	protected function getDirtyElements($limit = 0) {
		$limit = intval($limit);
		$elements = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$this->fileTable,
			'isdirty=1',
			'',
			'uri ASC',
			($limit ? $limit : '')
		);

		return $elements;
	}

	/**
	 * Writes compressed content to the file system.
	 *
	 * @param string $filePath Name and path to the file containing the original content
	 * @param string $content Content data to be compressed
	 * @return void
	 */
	protected function writeCompressedContent($filePath, $content) {
		if ($this->configuration['enableStaticFileCompression']) {
			$level = is_int($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel']) ? $GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] : 3;
			$contentGzip = gzencode($content, $level);
			if ($contentGzip) {
				t3lib_div::writeFile($filePath . '.gz', $contentGzip);
			}
		}
	}

	/**
	 * Gets the name of the database table holding all cached files.
	 *
	 * @return	string		Name of the database holding all cached files
	 */
	public function getFileTable() {
		return $this->fileTable;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/class.tx_ncstaticfilecache.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/class.tx_ncstaticfilecache.php']);
}
?>
