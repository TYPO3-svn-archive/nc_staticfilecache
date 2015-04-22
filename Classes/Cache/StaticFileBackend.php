<?php
/**
 * Cache backend for static file cache
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Cache backend for static file cache
 *
 * This cache handle the file representation of the cache and handle
 * - CacheFileName
 * - CacheFileName.gz
 *
 * @author Tim Lochmüller
 */
class StaticFileBackend extends AbstractStaticDbBackend {

	/**
	 * The default compression level
	 */
	const DEFAULT_COMPRESSION_LEVEL = 3;

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
	 * Build up the object
	 */
	public function __construct() {
		$this->configuration = GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\Configuration');
	}

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
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		$fileName = $this->getCacheFilename($entryIdentifier);
		$cacheDir = pathinfo($fileName, PATHINFO_DIRNAME);
		if (!is_dir($cacheDir)) {
			GeneralUtility::mkdir_deep($cacheDir);
		}

		// normal
		GeneralUtility::writeFile($fileName, $data);

		// gz
		if ($this->configuration->get('enableStaticFileCompression')) {
			$level = isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel']) ? (int)$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] : self::DEFAULT_COMPRESSION_LEVEL;
			if (!MathUtility::isIntegerInRange($level, 1, 9)) {
				$level = self::DEFAULT_COMPRESSION_LEVEL;
			}
			$contentGzip = gzencode($data, $level);
			if ($contentGzip) {
				GeneralUtility::writeFile($fileName . '.gz', $contentGzip);
			}
		}

		parent::set($entryIdentifier, $data, $tags, $lifetime);
	}

	/**
	 * Get the cache folder for the given entry
	 *
	 * @param $entryIdentifier
	 *
	 * @return string
	 */
	protected function getCacheFilename($entryIdentifier) {
		$urlParts = parse_url($entryIdentifier);
		$cacheFilename = GeneralUtility::getFileAbsFileName($this->cacheDirectory . $urlParts['host'] . '/' . trim($urlParts['path'], '/'));
		$fileExtension = pathinfo(basename($cacheFilename), PATHINFO_EXTENSION);
		if (empty($fileExtension) || !GeneralUtility::inList($this->configuration->get('fileTypes'), $fileExtension)) {
			$cacheFilename .= '/index.html';
		}
		return $cacheFilename;
	}

	/**
	 * Loads data from the cache.
	 *
	 * @param string $entryIdentifier An identifier which describes the cache entry to load
	 *
	 * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
	 */
	public function get($entryIdentifier) {
		if (!$this->has($entryIdentifier)) {
			return NULL;
		}
		return GeneralUtility::getUrl($this->getCacheFilename($entryIdentifier));
	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 *
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 */
	public function has($entryIdentifier) {
		return is_file($this->getCacheFilename($entryIdentifier)) || parent::has($entryIdentifier);
	}

	/**
	 * Removes all cache entries matching the specified identifier.
	 * Usually this only affects one entry but if - for what reason ever -
	 * old entries for the identifier still exist, they are removed as well.
	 *
	 * @param string $entryIdentifier Specifies the cache entry to remove
	 *
	 * @return boolean TRUE if (at least) an entry could be removed or FALSE if no entry was found
	 */
	public function remove($entryIdentifier) {
		if (!$this->has($entryIdentifier)) {
			return FALSE;
		}
		$fileName = $this->getCacheFilename($entryIdentifier);
		unlink($fileName);
		if (is_file($fileName . '.gz')) {
			unlink($fileName . '.gz');
		}
		return parent::remove($entryIdentifier);
	}

	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 */
	public function flush() {
		$absoluteCacheDir = GeneralUtility::getFileAbsFileName($this->cacheDirectory);
		$tempAbsoluteCacheDir = rtrim($absoluteCacheDir, '/') . '_' . GeneralUtility::milliseconds(TRUE) . '/';
		rename($absoluteCacheDir, $tempAbsoluteCacheDir);
		parent::flush();
		GeneralUtility::rmdir($tempAbsoluteCacheDir, TRUE);
	}

	/**
	 * Does garbage collection
	 *
	 * @return void
	 */
	public function collectGarbage() {
	}

	/**
	 * Removes all cache entries of this cache which are tagged by the specified tag.
	 *
	 * @param string $tag The tag the entries must have
	 *
	 * @return void
	 * @api
	 */
	public function flushByTag($tag) {
	}

	/**
	 * Finds and returns all cache entry identifiers which are tagged by the
	 * specified tag
	 *
	 * @param string $tag The tag to search for
	 *
	 * @return array An array with identifiers of all matching entries. An empty array if no entries matched
	 * @api
	 */
	public function findIdentifiersByTag($tag) {
	}
}
