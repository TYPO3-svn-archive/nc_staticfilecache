<?php
/**
 * Cache backend for static db cache
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;

/**
 * Cache backend for static db cache
 *
 * The backend handle the database representation of the cache and is
 * backend of the StaticFileBackend
 *
 * @author Tim Lochmüller
 */
abstract class AbstractStaticDbBackend extends AbstractBackend implements TaggableBackendInterface {

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

		// @todo implement

	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 *
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 */
	public function has($entryIdentifier) {

		// @todo implement

		return FALSE;
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

		// @todo remove the DB representation

		return TRUE;
	}


	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 */
	public function flush() {
		// @todo implement
	}
}
