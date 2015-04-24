<?php
/**
 * Cache commands
 *
 * @package NcStaticfilecache\Command
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Command;

use SFC\NcStaticfilecache\StaticFileCache;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Cache commands
 *
 * @author Tim Lochmüller
 */
class CacheCommandController extends CommandController {

	/**
	 * Remove the expired pages
	 */
	public function removeExpiredPagesCommand() {
		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$cacheManager = $this->objectManager->get('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cache = $cacheManager->getCache('static_file_cache');
		$cache->collectGarbage();
	}
}
