<?php
/**
 * Cache Utility
 *
 * @package NcStaticfilecache\Module
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Utility;

use SFC\NcStaticfilecache\Configuration;
use SFC\NcStaticfilecache\StaticFileCache;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Cache Utility
 *
 * @author Tim Lochmüller
 */
class CacheUtility {

	/**
	 * Get the static file cache
	 *
	 * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
	 */
	static public function getCache() {
		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$objectManager = new ObjectManager();
		$cacheManager = $objectManager->get('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		return $cacheManager->getCache('static_file_cache');
	}
}