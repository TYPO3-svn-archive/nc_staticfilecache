<?php
/**
 * Cache commands
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Command;

use SFC\NcStaticfilecache\StaticFileCache;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
		$this->getStaticFileCache()
			->removeExpiredPages();

		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$cacheManager = $this->objectManager->get('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cache = $cacheManager->getCache('static_file_cache');
		$cache->collectGarbage();
	}

	/**
	 * Process dirty pages
	 *
	 * @param int $itemLimit
	 */
	public function processDirtyPagesCommand($itemLimit = 0) {
		$this->getStaticFileCache()
			->processDirtyPages(NULL, $itemLimit);
	}

	/**
	 * Get the static file cache object
	 *
	 * @return StaticFileCache
	 */
	protected function getStaticFileCache() {
		return GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\StaticFileCache');
	}
}
