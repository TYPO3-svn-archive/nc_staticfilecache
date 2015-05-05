<?php
/**
 * Init frontend user
 *
 * @package SFC\NcStaticfilecache\Hook
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Hook;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Init frontend user
 *
 * @author Tim Lochmüller
 */
class InitFrontendUser {

	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extKey = 'nc_staticfilecache';

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
		if ($pObj->fe_user->dontSetCookie) {
			// do not set any cookie
			return;
		}

		// @todo Do we need this check?
		#$configuredCookieName = trim($GLOBALS['TYPO3_CONF_VARS']['FE']['cookieName']);
		#if (empty($configuredCookieName)) {
		#	$configuredCookieName = 'fe_typo_user';
		#}
		#if (!isset($_COOKIE[$configuredCookieName])) {
		#	return;
		#}

		if (($pObj->fe_user->loginSessionStarted || $pObj->fe_user->forceSetCookie) && $pObj->fe_user->lifetime == 0) {
			// If new session and the cookie is a sessioncookie, we need to set it only once!
			// // isSetSessionCookie()
			$this->setCookie(0);
		} elseif (($pObj->fe_user->loginSessionStarted || isset($_COOKIE[$this->extKey])) && $pObj->fe_user->lifetime > 0) {
			// If it is NOT a session-cookie, we need to refresh it.
			// isRefreshTimeBasedCookie()
			$this->setCookie(time() + $pObj->fe_user->lifetime);
		}
	}

	/**
	 * Set the Cookie
	 *
	 * @param $lifetime
	 */
	protected function setCookie($lifetime) {
		$cookieDomain = $this->getCookieDomain();
		setcookie($this->extKey, 'fe_typo_user_logged_in', $lifetime, '/', $cookieDomain ? $cookieDomain : NULL);
	}

	/**
	 * Gets the domain to be used on setting cookies.
	 * The information is taken from the value in $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'].
	 *
	 * @return string The domain to be used on setting cookies
	 * @see AbstractUserAuthentication::getCookieDomain
	 */
	protected function getCookieDomain() {
		$result = '';
		$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
		// If a specific cookie domain is defined for a given TYPO3_MODE,
		// use that domain
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'])) {
			$cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'];
		}
		if ($cookieDomain) {
			if ($cookieDomain[0] == '/') {
				$match = array();
				$matchCnt = preg_match($cookieDomain, GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE) {
					GeneralUtility::sysLog('The regular expression for the cookie domain (' . $cookieDomain . ') contains errors. The session is not shared across sub-domains.', 'Core', GeneralUtility::SYSLOG_SEVERITY_ERROR);
				} elseif ($matchCnt) {
					$result = $match[0];
				}
			} else {
				$result = $cookieDomain;
			}
		}
		return $result;
	}
}
