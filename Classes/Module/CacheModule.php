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
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
		$output = '';
		foreach ($tree->tree as $row) {

			// Fetch files:
			$filerecords = StaticFileCache::getInstance()
				->getRecordForPageID($row['row']['uid']);
			$cellAttrib = ($row['row']['_CSSCLASS'] ? ' class="' . $row['row']['_CSSCLASS'] . '"' : '');

			if (count($filerecords)) {
				foreach ($filerecords as $k => $frec) {
					$tCells = array();

					if (!$k) {
						$tCells[] = '<td nowrap="nowrap"' . $cellAttrib . '>' . $row['HTML'] . BackendUtility::getRecordTitle('pages', $row['row'], TRUE) . '</td>';
					} else {
						$tCells[] = '<td nowrap="nowrap"' . $cellAttrib . '>' . $row['HTML_depthData'] . '</td>';
					}

					$tCells[] = '<td nowrap="nowrap"><span class="typo3-dimmed">' . ($frec['tstamp'] ? BackendUtility::datetime($frec['tstamp']) : '') . '</span></td>';
					$timeout = ($frec['tstamp'] > 0) ? BackendUtility::calcAge(($frec['cache_timeout']), $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')) : '';
					$tCells[] = '<td nowrap="nowrap">' . $timeout . '</td>';
					$tCells[] = '<td nowrap="nowrap">' . ($frec['explanation'] ? $frec['explanation'] : '') . '</td>';

					// Compile Row:
					$output .= $this->renderTableRow($tCells, 'valign="top" title="id=' . $frec['pid'] . ' host=' . $frec['host'] . ' uri=' . $frec['uri'] . '"', $frec);
				}
			} else {
				$tCells = array(
					'<td nowrap="nowrap" colspan="3"' . $cellAttrib . '>' . $row['HTML'] . BackendUtility::getRecordTitle('pages', $row['row'], TRUE) . '</td>',
					'<td><span class="typo3-dimmed">' . ($row['row']['uid'] == 0 ? '' : 'not hit') . '</span></td>',
				);

				// Compile Row:
				$output .= $this->renderTableRow($tCells, 'valign="top" class="bgColor4" title="id=' . $row['row']['uid'] . '"');
			}
		}

		/** @var StandaloneView $renderer */
		$renderer = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:nc_staticfilecache/Resources/Private/Templates/Module.html'));
		$renderer->assignMultiple(array(
			'headerActionButtons' => implode('', $this->getHeaderActionButtons()),
			'requestUri'          => GeneralUtility::getIndpEnv('REQUEST_URI'),
			'refreshLabel'        => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.refresh', 1),
			'table'               => '<tbody>' . $output . '</tbody>',
			'pageId'              => $this->pageId
		));

		return $renderer->render();
	}

	/**
	 * Renders a table row.
	 *
	 * @param    array  $elements     : The row elements to be rendered
	 * @param    string $attributes   : (optional) The attributes to be used on the table row
	 * @param    array  $cacheElement : (optional) The cache element row
	 *
	 * @return    string        The HTML representation of the table row
	 */
	protected function renderTableRow(array $elements, $attributes = '', array $cacheElement = NULL) {
		return '<tr' . ($attributes ? ' ' : '') . $attributes . '>' . implode('', $elements) . '</tr>';
	}

	/**
	 * Handles incoming actions (e.g. removing all expired pages).
	 *
	 * @return    void
	 */
	protected function handleActions() {
		$action = GeneralUtility::_GP('ACTION');

		if (isset($action['removeExpiredPages'])) {
			StaticFileCache::getInstance()
				->removeExpiredPages();
		}
	}

	/**
	 * Gets the header actions buttons to be rendered in the header section.
	 *
	 * @return    array        Action buttons to be rendered in the header section
	 */
	protected function getHeaderActionButtons() {
		$headerActionButtons = array(
			'removeExpiredPages' => $this->renderActionButton('removeExpiredPages', 'Remove all expired pages', 'Are you sure?'),
		);

		return $headerActionButtons;
	}

	/**
	 * Renders a single action button,
	 *
	 * @param    string $elementName      : Name attribute of the element
	 * @param    string $elementLabel     : Label of the action button
	 * @param    string $confirmationText : (optional) Confirmation text - will not be used if empty
	 *
	 * @return    string        The HTML representation of an action button
	 */
	protected function renderActionButton($elementName, $elementLabel, $confirmationText = '') {
		return '<input type="submit" name="ACTION[' . htmlspecialchars($elementName) . ']" value="' . $elementLabel . '"' . ($confirmationText ? ' onclick="return confirm(\'' . addslashes($confirmationText) . '\');"' : '') . ' />';
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