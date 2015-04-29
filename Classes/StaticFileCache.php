<?php
/**
 * Static File Cache
 *
 * @package NcStaticfilecache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache;

use SFC\NcStaticfilecache\Cache\UriFrontend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Static File Cache
 *
 * @author Michiel Roos
 * @author Tim Lochmüller
 */
class StaticFileCache implements SingletonInterface {

	/**
	 * Configuration of the extension
	 *
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extKey = 'nc_staticfilecache';

	/**
	 * Cache
	 *
	 * @var UriFrontend
	 */
	protected $cache;

	/**
	 * Cache
	 *
	 * @var Dispatcher
	 */
	protected $signalDispatcher;

	/**
	 * Get the current object
	 *
	 * @return StaticFileCache
	 */
	public static function getInstance() {
		return GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\StaticFileCache');
	}

	/**
	 * Constructs this object.
	 */
	public function __construct() {
		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$cacheManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$this->cache = $cacheManager->getCache('static_file_cache');
		$this->signalDispatcher = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

		$this->configuration = GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\Configuration');
	}

	/**
	 * Clear static file
	 *
	 * @param    object $_params : array containing 'cacheCmd'
	 *
	 * @return    void
	 */
	public function clearStaticFile(&$_params) {
		if (isset($_params['cacheCmd']) && $_params['cacheCmd']) {
			$cacheCmd = $_params['cacheCmd'];
			switch ($cacheCmd) {
				case 'all':
				case 'pages':
					$directory = '';
					if ((boolean)$this->configuration->get('clearCacheForAllDomains') === FALSE) {
						if (isset($_params['host']) && $_params['host']) {
							$directory = $_params['host'];
						} else {
							$directory = GeneralUtility::getIndpEnv('HTTP_HOST');
						}
					}

					$this->cache->flush();
					break;
				case 'temp_CACHED':
					// Clear temp files, not frontend cache.
					break;
				default:
					$doClearCache = MathUtility::canBeInterpretedAsInteger($cacheCmd);

					if ($doClearCache) {
						$cacheEntries = array_keys($this->cache->getByTag('pageId_' . $cacheCmd));
						foreach ($cacheEntries as $cacheEntry) {
							$this->cache->remove($cacheEntry);
						}
					}
					break;
			}
		}
	}

	/**
	 * Check if the SFC should create the cache
	 *
	 * @param    TypoScriptFrontendController $pObj        : The parent object
	 * @param    string                       $timeOutTime : The timestamp when the page times out
	 *
	 * @return    void
	 */
	public function insertPageIncache(TypoScriptFrontendController &$pObj, &$timeOutTime) {
		$isStaticCached = FALSE;

		// Find host-name / IP, always in lowercase:
		$isHttp = (strpos(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), 'http://') === 0);
		$host = strtolower(GeneralUtility::getIndpEnv('HTTP_HOST'));
		$uri = GeneralUtility::getIndpEnv('REQUEST_URI');
		if ($this->configuration->get('recreateURI')) {
			$uri = $this->recreateURI();
		}
		$uri = urldecode($uri);
		$cacheUri = ($isHttp ? 'http://' : 'https://') . $host . $uri;

		$fieldValues = array();

		// Hook: Initialize variables before starting the processing.
		$initializeVariablesHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_initializeVariables'];
		if (is_array($initializeVariablesHooks)) {
			foreach ($initializeVariablesHooks as $hookFunction) {
				$hookParameters = array(
					'TSFE'        => $pObj,
					'host'        => &$host,
					'uri'         => &$uri,
					'isHttp'      => &$isHttp,
					'fieldValues' => &$fieldValues,
				);
				GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
			}
		}

		// cache rules
		$ruleArguments = array(
			'explanation'        => array(),
			'frontendController' => $pObj,
			'uri'                => $cacheUri,
			'skipProcessing'     => FALSE,
		);
		$ruleArguments = $this->signalDispatcher->dispatch(__CLASS__, 'cacheRule', $ruleArguments);
		$explanation = $ruleArguments['explanation'];

		// Only process if there are not query arguments, no link to external page (doktype=3) and not called over https:
		if (!$ruleArguments['skipProcessing'] && ($isHttp || $this->configuration->get('enableHttpsCaching'))) {

			$cacheTags = array(
				'pageId_' . $pObj->page['uid'],
			);

			// This is supposed to have "&& !$pObj->beUserLogin" in there as well
			// This fsck's up the ctrl-shift-reload hack, so I pulled it out.
			if (sizeof($explanation) === 0 && (boolean)$this->configuration->get('disableCache') === FALSE) {

				// If page has a endtime before the current timeOutTime, use it instead:
				if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
					$timeOutTime = $pObj->page['endtime'];
				}

				// write DB-record and staticCache-files, after DB-record was successful updated or created
				$timeOutSeconds = $timeOutTime - $GLOBALS['EXEC_TIME'];

				$content = $pObj->content;
				if ($this->configuration->get('showGenerationSignature')) {
					$content .= "\n<!-- cached statically on: " . strftime($this->configuration->get('strftime'), $GLOBALS['EXEC_TIME']) . ' -->';
					$content .= "\n<!-- expires on: " . strftime($this->configuration->get('strftime'), $GLOBALS['EXEC_TIME'] + $timeOutSeconds) . ' -->';
				}

				// Hook: Process content before writing to static cached file:
				$processContentHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['createFile_processContent'];
				if (is_array($processContentHooks)) {
					foreach ($processContentHooks as $hookFunction) {
						$hookParameters = array(
							'TSFE'        => $pObj,
							'content'     => $content,
							'fieldValues' => &$fieldValues,
							'host'        => $host,
							'uri'         => $cacheUri,
						);
						$content = GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
					}
				}
			} else {
				if ((boolean)$this->configuration->get('disableCache') === TRUE) {
					$explanation[] = 'static cache disabled by TypoScript';
				}
				if ($pObj->no_cache) {
					$explanation[] = 'config.no_cache is true';
				}
				// new cache
				$cacheTags[] = 'explanation';
				$content = implode(' - ', $explanation);
				$timeOutSeconds = 0;
			}

			// create cache entry
			$this->cache->set($cacheUri, $content, $cacheTags, $timeOutSeconds);
		}

		// Hook: Post process (no matter whether content was cached statically)
		$postProcessHooks =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nc_staticfilecache/class.tx_ncstaticfilecache.php']['insertPageIncache_postProcess'];
		if (is_array($postProcessHooks)) {
			foreach ($postProcessHooks as $hookFunction) {
				$hookParameters = array(
					'TSFE'           => $pObj,
					'host'           => $host,
					'uri'            => $cacheUri,
					'isStaticCached' => $isStaticCached,
				);
				GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
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
	 * @param    object $params : parameter array
	 * @param    object $pObj   : partent object
	 *
	 * @return    void
	 */
	public function setFeUserCookie(&$params, &$pObj) {
		$cookieDomain = NULL;
		// Setting cookies
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']) {
			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain']{0} == '/') {
				$matchCnt = @preg_match($GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'], GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE) {
					GeneralUtility::sysLog('The regular expression of $GLOBALS[TYPO3_CONF_VARS][SYS][cookieDomain] contains errors. The session is not shared across sub-domains.', 'Core', 3);
				} elseif ($matchCnt) {
					$cookieDomain = $match[0];
				}
			} else {
				$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
			}
		}

		// If new session and the cookie is a sessioncookie, we need to set it only once!
		if (($pObj->fe_user->loginSessionStarted || $pObj->fe_user->forceSetCookie) && $pObj->fe_user->lifetime == 0) { // isSetSessionCookie()
			if (!$pObj->fe_user->dontSetCookie) {
				if ($cookieDomain) {
					SetCookie($this->extKey, 'fe_typo_user_logged_in', 0, '/', $cookieDomain);
				} else {
					SetCookie($this->extKey, 'fe_typo_user_logged_in', 0, '/');
				}
			}
		}

		// If it is NOT a session-cookie, we need to refresh it.
		if ($pObj->fe_user->lifetime > 0) { // isRefreshTimeBasedCookie()
			if ($pObj->fe_user->loginSessionStarted || isset($_COOKIE[$this->extKey])) {
				if (!$pObj->fe_user->dontSetCookie) {
					if ($cookieDomain) {
						SetCookie($this->extKey, 'fe_typo_user_logged_in', time() + $pObj->fe_user->lifetime, '/', $cookieDomain);
					} else {
						SetCookie($this->extKey, 'fe_typo_user_logged_in', time() + $pObj->fe_user->lifetime, '/');
					}
				}
			}
		}
	}

	/**
	 * Recreates the URI of the current request.
	 *
	 * Especially in simulateStaticDocument context, the different URIs lead to the same result
	 * and static file caching would store the wrong URI that was used in the first request to
	 * the website (e.g. "TheGoodURI.13.0.html" is as well accepted as "TheFakeURI.13.0.html")
	 *
	 * @return    string        The recreated URI of the current request
	 */
	protected function recreateURI() {
		$objectManager = new ObjectManager();
		/** @var UriBuilder $uriBuilder */
		$uriBuilder = $objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
		return $uriBuilder->reset()
			->setAddQueryString(TRUE)
			->build();
	}
}
