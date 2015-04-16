<?php
/**
 * Cache backend for static file cache
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache backend for static file cache
 *
 * @author Tim Lochmüller
 * @todo   finish implementation
 */
class StaticFileBackend extends AbstractBackend {

	/**
	 * Cache directory
	 *
	 * @var string
	 */
	protected $cacheDirectory = 'typo3temp/tx_ncstaticfilecache/';

	/**
	 * Configuration
	 *
	 * @var \SFC\NcStaticfilecache\Configuration
	 */
	protected $configuration;

	/**
	 * Saves data in the cache.
	 *
	 * @param string  $entryIdentifier An identifier for this specific cache entry
	 * @param string  $data            The data to be stored
	 * @param array   $tags            Tags to associate with this cache entry. If the backend does not support tags, this option can be ignored.
	 * @param integer $lifetime        Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
	 *
	 * @return void
	 * @throws \TYPO3\CMS\Core\Cache\Exception if no cache frontend has been set.
	 * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException if the data is not a string
	 * @api
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		$cacheFolder = $this->getCacheFolder($entryIdentifier);
		if (!is_dir($cacheFolder)) {
			GeneralUtility::mkdir_deep($cacheFolder);
		}
		#DebuggerUtility::var_dump($cacheFolder);
		#die();

		#
		#		$this->writeHtAccessFile($cacheDir, $uri, $timeOutSeconds);
		#		GeneralUtility::writeFile(PATH_site . $cacheDir . $file, $content);
		#		$this->writeCompressedContent(PATH_site . $cacheDir . $file, $content);
		#		return TRUE;
		#
		#		DebuggerUtility::var_dump($entryIdentifier);
		#		DebuggerUtility::var_dump($data);
		#		die();
	}

	/**
	 * Get the cache folder for the given entry
	 *
	 * @param $entryIdentifier
	 *
	 * @return string
	 */
	protected function getCacheFolder($entryIdentifier) {
		$urlParts = parse_url($entryIdentifier);
		return GeneralUtility::getFileAbsFileName($this->cacheDirectory . $urlParts['host'] . '/' . trim($urlParts['path'], '/') . '/');
	}

	/**
	 * Loads data from the cache.
	 *
	 * @param string $entryIdentifier An identifier which describes the cache entry to load
	 *
	 * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
	 * @api
	 */
	public function get($entryIdentifier) {
		// TODO: Implement get() method.
	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 *
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 * @api
	 */
	public function has($entryIdentifier) {
		$cacheFolder = $this->getCacheFolder($entryIdentifier);
		// TODO: Implement has() method.
	}

	/**
	 * Removes all cache entries matching the specified identifier.
	 * Usually this only affects one entry but if - for what reason ever -
	 * old entries for the identifier still exist, they are removed as well.
	 *
	 * @param string $entryIdentifier Specifies the cache entry to remove
	 *
	 * @return boolean TRUE if (at least) an entry could be removed or FALSE if no entry was found
	 * @api
	 */
	public function remove($entryIdentifier) {
		// TODO: Implement remove() method.
	}

	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 * @api
	 */
	public function flush() {
		// TODO: Implement flush() method.
	}

	/**
	 * Does garbage collection
	 *
	 * @return void
	 * @api
	 */
	public function collectGarbage() {
		// TODO: Implement collectGarbage() method.
	}
}
