<?php
/**
 * Cache frontend for static file cache
 *
 * @package NcStaticfilecache\Cache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\StringFrontend;

/**
 * Cache frontend for static file cache
 *
 * @author Tim Lochmüller
 */
class UriFrontend extends StringFrontend {

	/**
	 * Check if the identifier is a valid URI incl. host and path
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
	 * Finds and returns all cache entries which are tagged by the specified tag.
	 *
	 * @param string $tag The tag to search for
	 *
	 * @return array An array with the content of all matching entries. An empty array if no entries matched
	 */
	public function getByTag($tag) {
		if (!$this->isValidTag($tag)) {
			throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233057772);
		}
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
