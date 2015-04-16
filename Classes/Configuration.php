<?php
/**
 * Handle extension and TS configuration
 *
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache;

/**
 * Handle extension and TS configuration
 *
 * @author Tim Lochmüller
 */
class Configuration {

	/**
	 * Current configuration
	 *
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * Build up the configuration
	 */
	public function __construct() {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nc_staticfilecache'])) {
			$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nc_staticfilecache']);
			if (is_array($extensionConfiguration)) {
				$this->configuration = array_merge($this->configuration, $extensionConfiguration);
			}
		}
		if (isset($GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.']) && is_array($GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.'])) {
			$this->configuration = array_merge($this->configuration, $GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.']);
		}
	}

	/**
	 * Get the configuration
	 *
	 * @param string $key
	 *
	 * @return null|mixed
	 */
	public function get($key) {
		$result = NULL;
		if (isset($this->configuration[$key])) {
			$result = $this->configuration[$key];
		}
		return $result;
	}
}
