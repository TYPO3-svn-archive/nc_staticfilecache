<?php
/**
 * Cache commands
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Command;

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
	 * @return \tx_ncstaticfilecache
	 */
	protected function getStaticFileCache() {
		return GeneralUtility::makeInstance('tx_ncstaticfilecache');
	}
}
