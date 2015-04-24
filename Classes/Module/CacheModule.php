<?php
/**
 * Static file cache info module
 *
 * @package NcStaticfilecache\Module
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Module;

use SFC\NcStaticfilecache\Configuration;
use SFC\NcStaticfilecache\StaticFileCache;
use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Tree\View\BrowseTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Static file cache info module
 *
 * @author Tim Lochmüller
 * @author Michiel Roos
 */
class CacheModule extends AbstractFunctionModule {

	/**
	 * Page ID
	 *
	 * @var integer
	 */
	protected $pageId = 0;

	/**
	 * Back path
	 *
	 * @var string
	 */
	protected $backPath = '';

	/**
	 * MAIN function for static publishing information
	 *
	 * @return    string        Output HTML for the module.
	 */
	public function main() {
		// Handle actions:
		$this->handleActions();

		$this->backPath = $GLOBALS['BACK_PATH'];
		$this->pageId = intval($this->pObj->id);

		// Initialize tree object:
		/* @var $tree BrowseTreeView */
		$tree = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Tree\\View\\BrowseTreeView');
		$tree->makeHTML = 2;
		$tree->init();

		// Set starting page Id of tree (overrides webmounts):
		if ($this->pageId > 0) {
			$tree->MOUNTS = array(0 => $this->pageId);
		}

		$tree->ext_IconMode = TRUE;
		$tree->showDefaultTitleAttribute = TRUE;
		$tree->thisScript = BackendUtility::getModuleUrl(GeneralUtility::_GP('M'));
		$tree->setTreeName('staticfilecache');

		// Creating top icon; the current page
		$tree->getBrowsableTree();

		// Render information table:
		return $this->processExpandCollapseLinks($this->renderModule($tree));
	}

	/**
	 * Rendering the information
	 *
	 * @param    BrowseTreeView $tree The Page tree data
	 *
	 * @return    string        HTML for the information table.
	 */
	protected function renderModule(BrowseTreeView $tree) {
		$rows = array();

		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$cacheManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cache = $cacheManager->getCache('static_file_cache');

		foreach ($tree->tree as $row) {

			$cacheEntries = $cache->getByTag('pageId_' . $row['row']['uid']);

			if ($cacheEntries) {
				$isFirst = TRUE;
				foreach ($cacheEntries as $identifier => $info) {
					$tCells = array();

					if ($isFirst) {
						$tCells[] = '<td nowrap="nowrap">' . $row['HTML'] . BackendUtility::getRecordTitle('pages', $row['row'], TRUE) . '</td>';
						$isFirst = FALSE;
					} else {
						$tCells[] = '<td nowrap="nowrap">' . $row['HTML_depthData'] . '</td>';
					}

					$tCells[] = '<td nowrap="nowrap">' . $identifier . '</td>';
					if (strpos($info, '|')) {
						$times = GeneralUtility::trimExplode('|', $info);
						$tCells[] = '<td nowrap="nowrap">' . strftime('%d-%m-%y %H:%M', $times[0]) . '</td>';
						$tCells[] = '<td nowrap="nowrap">' . strftime('%d-%m-%y %H:%M', $times[1]) . '</td>';
						$tCells[] = '<td>' . IconUtility::getSpriteIcon('status-status-permission-granted') . '</td>';
					} else {
						$tCells[] = '<td nowrap="nowrap">' . IconUtility::getSpriteIcon('status-status-permission-denied') . '</td>';
						$tCells[] = '<td nowrap="nowrap">' . IconUtility::getSpriteIcon('status-status-permission-denied') . '</td>';
						$tCells[] = '<td>' . $info . '</td>';
					}

					$rows[] = implode('', $tCells);
				}
			} else {
				// empty entry
				$tCells = array(
					'<td nowrap="nowrap" colspan="4">' . $row['HTML'] . BackendUtility::getRecordTitle('pages', $row['row'], TRUE) . '</td>',
					'<td><span class="typo3-dimmed">' . ($row['row']['uid'] == 0 ? '' : 'not hit') . '</span></td>',
				);
				$rows[] = implode('', $tCells);
			}
		}

		/** @var StandaloneView $renderer */
		$renderer = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:nc_staticfilecache/Resources/Private/Templates/Module.html'));
		$renderer->assignMultiple(array(
			'requestUri'   => GeneralUtility::getIndpEnv('REQUEST_URI'),
			'rows'         => $rows,
			'pageId'       => $this->pageId
		));

		return $renderer->render();
	}

	/**
	 * Handles incoming actions (e.g. removing all expired pages).
	 *
	 * @return    void
	 */
	protected function handleActions() {
		$action = GeneralUtility::_GP('ACTION');

		if (isset($action['removeExpiredPages'])) {
			/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
			$objectManager = new ObjectManager();
			$cacheManager = $objectManager->get('TYPO3\\CMS\\Core\\Cache\\CacheManager');
			$cache = $cacheManager->getCache('static_file_cache');
			$cache->collectGarbage();
		}
	}

	/**
	 * Processes the expand/collapse links and adds the Id of the current page in branch.
	 *
	 * Example:
	 * index.php?PM=0_0_23_staticfilecache#0_23 --> index.php?PM=0_0_23_staticfilecache&id=13#0_23
	 *
	 * @param    string $content : Content to be processed
	 *
	 * @return    string        The processed and modified content
	 */
	protected function processExpandCollapseLinks($content) {
		if (strpos($content, 'PM=') !== FALSE && $this->pageId > 0) {
			$content = preg_replace('/(href=")([^"]+PM=[^"#]+)(#[^"]+)?(")/', '${1}${2}&id=' . $this->pageId . '${3}${4}', $content);
		}
		return $content;
	}
}