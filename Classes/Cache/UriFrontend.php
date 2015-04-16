<?php
/**
 * Cache frontend for static file cache
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;

/**
 * Cache frontend for static file cache
 *
 * @author Tim Lochmüller
 */
class UriFrontend extends AbstractFrontend {

	/**
	 * Check if the identifier is a valid URI
	 *
	 * @param string $identifier
	 *
	 * @return bool
	 */
	public function isValidEntryIdentifier($identifier) {
		if (filter_var($identifier, FILTER_VALIDATE_URL) === FALSE) {
			return FALSE;
		}
		$urlParts = parse_url($identifier);
		return isset($urlParts['host']) && strlen($urlParts['host']) && isset($urlParts['path']) && strlen($urlParts['path']);
	}

	/**
	 * Saves data in the cache.
	 *
	 * @param string  $entryIdentifier Something which identifies the data - depends on concrete cache
	 * @param mixed   $data            The data to cache - also depends on the concrete cache implementation
	 * @param array   $tags            Tags to associate with this cache entry
	 * @param integer $lifetime        Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
	 *
	 * @return void
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		$this->backend->set($entryIdentifier, $data, $tags, $lifetime);
	}

	/**
	 * Finds and returns data from the cache.
	 *
	 * @param string $entryIdentifier Something which identifies the cache entry - depends on concrete cache
	 *
	 * @return mixed
	 */
	public function get($entryIdentifier) {
		$this->backend->get($entryIdentifier);
	}

	/**
	 * Finds and returns all cache entries which are tagged by the specified tag.
	 *
	 * @param string $tag The tag to search for
	 *
	 * @return array An array with the content of all matching entries. An empty array if no entries matched
	 */
	public function getByTag($tag) {
		if (!($this->backend instanceof TaggableBackendInterface)) {
			return array();
		}
		$identifiers = $this->backend->findIdentifiersByTag($tag);
		$return = array();
		foreach ($identifiers as $identifier) {
			$return[$identifier] = $this->get($identifier);
		}
		return $return;
	}
}
