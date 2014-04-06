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
	 * Sets the TypoScript setup.
	 *
	 * @param	array		$setup: The TypoScript setup
	 * @return	void
	 */
	public function setSetup(array $setup) {
		$this->setup = $setup;
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
	 * Gets a specific property of the setup.
	 *
	 * @param	string		$property: Property to get setup from
	 * @return	mixed		The setup of a property
	 */
	public function getSetupProperty($property) {
		$result = NULL;

		if (isset($this->setup[$property])) {
			$result = $this->setup[$property];
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
			return;
		}

		$uid = intval($params['uid']);
		$table = strval($params['table']);

		if($uid <= 0) {
			return;
		}

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
					if ((boolean) $this->getConfigurationProperty('clearCacheForAllDomains') === FALSE) {
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
					$doClearCache = class_exists('TYPO3\CMS\Core\Utility\MathUtility')
						? \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($cacheCmd)
						: (class_exists('t3lib_utility_Math') ? t3lib_utility_Math::canBeInterpretedAsInteger($cacheCmd) : t3lib_div::testInt($cacheCmd));



					if ($doClearCache) {
						$this->debug('clearing cache for pid: ' . $cacheCmd);
						$this->deleteStaticCache($cacheCmd);
					} else {
						$this->debug('Expected integer on clearing static cache', LOG_WARNING, $cacheCmd);
					}
					break;
			}
		}
	}

	/**
	 * Returns records for a page id
	 *
	 * @param	integer	$pid	Page id
	 * @return	array		Array of records
	 */
	public function getRecordForPageID($pid) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$this->fileTable,
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
				$this->debug('no-cache header found', LOG_INFO);
				$cmd = array('cacheCmd' => $parent->id);
				$this->clearStaticFile($cmd);
			}
		}
	}

	/**
	 * Write the static file and .htaccess
	 *
	 * @param	tslib_fe	$pObj: The parent object
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

			// Only process if there are not query arguments, no link to external page (doktype=3) and not called over https:
		if (strpos($uri, '?') === false && $pObj->page['doktype'] != 3 && $isHttp) {
			if ($this->getConfigurationProperty('recreateURI')) {
				$uri = $this->recreateURI();
			}

			// Workspaces have been introduced with TYPO3 4.0.0:
			$version = class_exists('TYPO3\CMS\Core\Utility\VersionNumberUtility')
				? \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version)
				: t3lib_div::int_from_ver(TYPO3_version);
			$workspacePreview = ($version >= 4000000 && $pObj->doWorkspacePreview());


			// check the allowed file types
			$basename = basename($uri);
			$fileExtension = pathinfo($basename, PATHINFO_EXTENSION);
			$fileTypes = explode(',', $this->configuration['fileTypes']);

			$file = $uri;
			if (!in_array($fileExtension, $fileTypes)) {
				$file .= '/index.html';
			} else {
				$uri = dirname($uri);
			}

			$file = preg_replace('#//#', '/', $file);

			// This is supposed to have "&& !$pObj->beUserLogin" in there as well
			// This fsck's up the ctrl-shift-reload hack, so I pulled it out.
			if ($pObj->page['tx_ncstaticfilecache_cache']
				&& (boolean)$this->getSetupProperty('disableCache') === FALSE
				&& $staticCacheable
				&& !$workspacePreview
				&& $loginsDeniedCfg) {

				$content = $pObj->content;
				if ($this->getConfigurationProperty('showGenerationSignature')) {
					$content .= "\n<!-- ".strftime (
						$this->configuration['strftime'],
						$GLOBALS['EXEC_TIME']
					) . ' -->';
				}

				$this->debug('writing cache for pid: ' . $pObj->id);

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

				// If page has a endtime before the current timeOutTime, use it instead:
				if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
					$timeOutTime = $pObj->page['endtime'];
				}
				
				// write DB-record and staticCache-files, after DB-record was successful updated or created
				$timeOutSeconds  = $timeOutTime - $GLOBALS['EXEC_TIME'];
				$recordIsWritten = $this->writeStaticCacheRecord($pObj, $fieldValues, $host, $uri, $file, $additionalHash, $timeOutSeconds, '' );
				if($recordIsWritten === TRUE) {
					$isStaticCached  = $this->writeStaticCacheFile($cacheDir, $uri, $file, $timeOutSeconds, $content);
				}
			} else {
					// This is an 'explode' of the function isStaticCacheable()
				if (!$pObj->page['tx_ncstaticfilecache_cache']) {
					$this->debug('insertPageIncache: static cache disabled by user', LOG_INFO);
					$explanation = 'static cache disabled on page';
				}
				if ((boolean)$this->getSetupProperty('disableCache') === TRUE) {
					$this->debug('insertPageIncache: static cache disabled by TypoScript "tx_ncstaticfilecache.disableCache"', LOG_INFO);
					$explanation = 'static cache disabled by TypoScript';
				}
				if ($pObj->no_cache) {
					$this->debug('insertPageIncache: no_cache setting is true', LOG_INFO);
					$explanation = 'config.no_cache is true';
				}
				if ($pObj->isINTincScript()) {
					$this->debug('insertPageIncache: page has INTincScript', LOG_INFO);

					$INTincScripts = array();
					foreach($pObj->config['INTincScript'] as $k => $v) {
						$infos = array();
						if(isset($v['type']))
							$infos[] = 'type: '.$v['type'];
						if(isset($v['conf']['userFunc']))
							$infos[] = 'userFunc: '.$v['conf']['userFunc'];
						if(isset($v['conf']['includeLibs']))
							$infos[] = 'includeLibs: '.$v['conf']['includeLibs'];
						if(isset($v['conf']['extensionName']))
							$infos[] = 'extensionName: '.$v['conf']['extensionName'];
						if(isset($v['conf']['pluginName']))
							$infos[] = 'pluginName: '.$v['conf']['pluginName'];

						$INTincScripts[] = implode(',', $infos);
					}
					$explanation = 'page has INTincScript: <ul><li>'.implode('</li><li>', $INTincScripts).'</li></ul>';
					unset($INTincScripts);

				}
				if ($pObj->isUserOrGroupSet() && $this->isDebugEnabled) {
					$this->debug('insertPageIncache: page has user or group set', LOG_INFO);
					// This is actually ok, we do not need to create cache nor an entry in the files table
					//$explanation = "page has user or group set";
				}
				if ($workspacePreview) {
					$this->debug('insertPageIncache: workspace preview', LOG_INFO);
					$explanation = 'workspace preview';
				}
				if (!$loginsDeniedCfg) {
					$this->debug('insertPageIncache: loginsDeniedCfg is true', LOG_INFO);
					$explanation = 'loginsDeniedCfg is true';
				}


				$this->writeStaticCacheRecord($pObj, $fieldValues, $host, $uri, $file, $additionalHash, 0, $explanation );
				$this->debug('insertPageIncache: ... this page is not cached!', LOG_INFO);
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
				if ($this->getConfigurationProperty('markDirtyInsteadOfDeletion')) {
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
		foreach ($this->getDirtyElements($limit) as $dirtyElement) {
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

		if ($cancelExecution === TRUE) {
			return;
		}

		if (TRUE === $this->deleteStaticCacheDirectory($cacheDirectory)) {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->fileTable, 'uid=' . $dirtyElement['uid']);
			if (isset($parent)) {
				$parent->cli_echo('Removing directory ' . $cacheDirectory . '... OK' . PHP_EOL);
			}
		} else {
			$this->debug('Could not delete static cache directory "' . $cacheDirectory . '"', LOG_CRIT);
			if (isset($parent)) {
				$parent->cli_echo('Removing directory ' . $cacheDirectory . '... FAILED' . PHP_EOL);
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
		$cookieDomain = NULL;
			// Setting cookies
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']) {
			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']{0} == '/')	{
				$matchCnt = @preg_match($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'], t3lib_div::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE)	{
					t3lib_div::sysLog('The regular expression of $TYPO3_CONF_VARS[SYS][cookieDomain] contains errors. The session is not shared across sub-domains.', 'Core', 3);
				} elseif ($matchCnt) {
					$cookieDomain = $match[0];
				}
			} else {
				$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
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
	 * @param string $message  The message to log
	 * @param int    $severity The severity value from warning to fatal error (default: 1)
	 * @param bool   $additionalData
	 *
	 * @return    void
	 */
	protected function debug($message, $severity = LOG_NOTICE, $additionalData = FALSE) {
		if ($this->getConfigurationProperty('debug') || $severity <= LOG_CRIT) {

			// map PHP or nc_staticfilecache error levels to
			// t3lib_div::devLog() severity level
			$arMapping = array(
				LOG_EMERG   => 3,
				LOG_ALERT   => 3,
				LOG_CRIT    => 3,
				LOG_ERR     => 3,
				LOG_WARNING => 2,
				LOG_NOTICE  => 1,
				LOG_INFO    => -1,
				LOG_DEBUG   => 0,
			);

			t3lib_div::devlog(
				trim($message),
				$this->extKey,
				isset($arMapping[$severity]) ? $arMapping[$severity] : 1,
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


		if ($pid > 0 && $this->getConfigurationProperty('markDirtyInsteadOfDeletion')) {
			// Mark specific page as dirty
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, 'pid=' . $pid, array('isdirty' => 1));
			return;
		}


		if ($pid > 0) {
			// Cache of a single page shall be removed
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->fileTable, 'pid=' . $pid);
			foreach ($rows as $row) {
				$cacheDirectory = $row['host'] . dirname($row['file']);
				if (TRUE === $this->deleteStaticCacheDirectory($cacheDirectory)) {
					$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->fileTable, 'uid=' . $row['uid']);
				} else {
					$this->debug('Could not delete static cache directory "' . $cacheDirectory . '"', LOG_CRIT);
				}
			}
			return;
		}


		// Cache of all pages shall be removed (clearCacheCmd "all" or "pages")
		try {
			// 1. marked DB-records which should be deleted
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, '', array('ismarkedtodelete' => 1));
			$this->removeCacheDirectory($directory);
			// 3. delete marked DB-records
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->fileTable, 'ismarkedtodelete=1');
		} catch (Exception $e) {
			$this->debug($e->getMessage(), LOG_CRIT);
		}
	}
	

	/**
	 * Deletes contents of a static cache directory in filesystem, but omit the subfolders
	 *
	 * @param	string		$directory: The directory to use on deletion below the static cache directory
	 * @return	mixed		Whether the action was successful (if directory was not found, NULL is returned)
	 */
	public function deleteStaticCacheDirectory($directory) {
		$directory = trim($directory);
		$cacheDirectory = PATH_site . $this->cacheDir . $directory;

		$this->debug('Removing files of directory "' . $cacheDirectory . '"', LOG_INFO);
		if (empty($directory) === TRUE || is_dir($cacheDirectory) === FALSE) {
			// directory is not existing, so we don't must delete anything
			return TRUE;
		}


		$directoryHandle = @opendir($cacheDirectory);
		if ($directoryHandle === FALSE) {
			// we have no handle to delete the directory
			return FALSE;
		}


		$result = TRUE;
		while (($element = readdir($directoryHandle))) {
			if ($element == '.' || $element == '..') {
				continue;
			}
			if (is_file($cacheDirectory . '/' . $element)) {
				// keep false if one file cannot be deleted -> entries marked dirty will not be deleted from DB
				if (FALSE === unlink($cacheDirectory . '/' . $element)) {
					$result = FALSE;
				}
			}
		}
		closedir($directoryHandle);
		@rmdir($cacheDirectory);
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
		if(is_array($elements) === FALSE) {
			$elements = array();
		}
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
		if ($this->getConfigurationProperty('enableStaticFileCompression')) {
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

	/**
	 * create directory and return boolean, if directory could be created
	 *
	 * @param string $destination
	 * @param string $deepDir
	 * @return boolean
	 */
	protected function mkdirDeep($destination,$deepDir) {
		$result = t3lib_div::mkdir_deep($destination,$deepDir);
		if(stristr($result, 'error')) {
			$this->debug($result, LOG_CRIT);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @param string $cacheDir
	 * @param string $uri
	 * @param string $timeOutSeconds
	 */
	protected function writeHtAccessFile($cacheDir, $uri, $timeOutSeconds) {
		if ($this->getConfigurationProperty('sendCacheControlHeader')) {
			$this->debug('writing .htaccess with timeout: ' . $timeOutSeconds, LOG_INFO);
			$htaccess = $uri . '/.htaccess';

			$htaccess = preg_replace('#//#', '/', $htaccess);
			$htaccessContent = '<IfModule mod_expires.c>
	ExpiresActive on
	ExpiresByType text/html A' . $timeOutSeconds . '
</IfModule>
';
			if($this->getConfigurationProperty('sendCacheControlHeaderRedirectAfterCacheTimeout')) {
				$invalidTime = date("YmdHis", time()+(int)$timeOutSeconds);
				$htaccessContent .= '
				<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{TIME} >' . $invalidTime . '
RewriteRule ^.*$ /index.php
</IfModule>';
			}

			t3lib_div::writeFile(PATH_site . $cacheDir . $htaccess, $htaccessContent);
		}
	}

	/**
	 * 	Check for existing entries with the same uid and file, if a record exists, update timestamp, otherwise create a new record.
	 *
	 * @param object $pObj
	 * @param array $fieldValues
	 * @param string $host
	 * @param string $uri
	 * @param string $file
	 * @param string $additionalHash
	 * @param integer $timeOutSeconds
	 * @param string $explanation
	 * @return boolean
	 */
	private function writeStaticCacheRecord($pObj, array $fieldValues, $host, $uri, $file, $additionalHash, $timeOutSeconds, $explanation ) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid',
			$this->fileTable,
			'pid=' . $pObj->page['uid'] .
			' AND host = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($host, $this->fileTable) .
			' AND file=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($file, $this->fileTable) .
			' AND additionalhash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($additionalHash, $this->fileTable)
		);

		// update DB-record
		if (is_array($rows) === TRUE && count($rows) > 0) {
			$fieldValues['tstamp'] = $GLOBALS['EXEC_TIME'];
			$fieldValues['cache_timeout'] = $timeOutSeconds;
			$fieldValues['explanation'] = $explanation;
			$fieldValues['isdirty'] = 0;
			$fieldValues['ismarkedtodelete'] = 0;
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->fileTable, 'uid=' . $rows[0]['uid'], $fieldValues);
			if($result === TRUE) {
				return TRUE;
			}
		}

		// create DB-record
		$fieldValues = array_merge(
			$fieldValues,
			array(
				'crdate' => $GLOBALS['EXEC_TIME'],
				'tstamp' => $GLOBALS['EXEC_TIME'],
				'cache_timeout' => $timeOutSeconds,
				'explanation' => $explanation,
				'file' => $file,
				'pid' => $pObj->page['uid'],
				'reg1' => $pObj->page_cache_reg1,
				'host' => $host,
				'uri' => $uri,
				'additionalhash' => $additionalHash,
			)
		);
		return $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->fileTable, $fieldValues);
	}
	/**
	 * write static-cache-file
	 *
	 * @param string $cacheDir
	 * @param string $uri
	 * @param string $file
	 * @param string $timeOutSeconds
	 * @param string $content
	 * @return boolean
	 */
	private function writeStaticCacheFile($cacheDir, $uri, $file, $timeOutSeconds, $content) {
		if($this->mkdirDeep(PATH_site, $cacheDir . $uri) === FALSE) {
			return FALSE;
		}

		$this->writeHtAccessFile($cacheDir, $uri, $timeOutSeconds);
		t3lib_div::writeFile(PATH_site . $cacheDir . $file, $content);
		$this->writeCompressedContent(PATH_site . $cacheDir . $file, $content);
		return TRUE;
	}
	/**
	 *  move directory and delete it after movement (if directory exists)
	 *  @param string $directory
	 *  @throws Exception
	 */
	private function removeCacheDirectory($directory){
		try{
			$srcDir = PATH_site . $this->cacheDir . $directory;
			if(substr($srcDir, strlen($srcDir)-1, 1) === '/') {
				$tmpDir = substr($srcDir, 0, strlen($srcDir)-1).'_ismarkedtodelete/';
			} else {
				$tmpDir = PATH_site . $this->cacheDir . $directory.'_ismarkedtodelete/';
			}
			if(is_dir($srcDir) === TRUE) {
				if (is_dir($tmpDir)) {
					$this->debug('Temp Directory for Delete is allready present!', LOG_ERR);
					if(FALSE === t3lib_div::rmdir($tmpDir, true)) {
						throw new Exception('Could not delete already existing temp static cache directory "' . $tmpDir . '"');
					}
				}
	
				if(FALSE === rename($srcDir, $tmpDir)) {
					throw new Exception('Could not rename static cache directory "' . $srcDir . '"');
				}
				// delete moved directory
				if(FALSE === t3lib_div::rmdir($tmpDir, true)) {
					throw new RuntimeException('Could not delete temp static cache directory "' . $tmpDir . '"');
				}
			}
		}catch(RuntimeException $e){
			$this->debug($e->getMessage(), LOG_CRIT);
		}
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/class.tx_ncstaticfilecache.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/class.tx_ncstaticfilecache.php']);
}
?>