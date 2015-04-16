<?php
/**
 * Static file cache info module
 *
 * @package Hdnet
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Module;

use SFC\NcStaticfilecache\StaticFileCache;
use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Tree\View\BrowseTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Static file cache info module
 *
 * @author Tim Lochmüller
 * @author Michiel Roos
 */
class CacheModule extends AbstractFunctionModule {

	/**
	 * @var StaticFileCache
	 */
	protected $pubObj;

	/**
	 * @var    integer
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

		$output = '';

		$this->backPath = $GLOBALS['BACK_PATH'];

		$this->pageId = intval($this->pObj->id);

		// Initialize tree object:
		/* @var $tree BrowseTreeView */
		$tree = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Tree\\View\\BrowseTreeView');
		// Also store tree prefix markup:
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
		$output .= $this->processExpandCollapseLinks($this->renderModule($tree));

		return $output;
	}

	/**
	 * Rendering the information
	 *
	 * @param    BrowseTreeView $tree The Page tree data
	 *
	 * @return    string        HTML for the information table.
	 */
	protected function renderModule(BrowseTreeView $tree) {
		$pubDir = $this->getStaticFileCacheInstance()
			->getCacheDirectory();

		// Traverse tree:
		$output = '';
		foreach ($tree->tree as $row) {

			// Fetch files:
			$filerecords = $this->getStaticFileCacheInstance()
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
					$tCells[] = '<td>' . ($frec['isdirty'] ? 'yes' : 'no') . '</td>';
					$tCells[] = '<td nowrap="nowrap">' . ($frec['explanation'] ? $frec['explanation'] : '') . '</td>';

					// Compile Row:
					$output .= $this->renderTableRow($tCells, 'valign="top" title="id=' . $frec['pid'] . ' host=' . $frec['host'] . ' uri=' . $frec['uri'] . '"', $frec);
				}
			} else {
				$tCells = array(
					'<td nowrap="nowrap" colspan="4"' . $cellAttrib . '>' . $row['HTML'] . BackendUtility::getRecordTitle('pages', $row['row'], TRUE) . '</td>',
					'<td><span class="typo3-dimmed">' . ($row['row']['uid'] == 0 ? '' : 'not hit') . '</span></td>',
				);

				// Compile Row:
				$output .= $this->renderTableRow($tCells, 'valign="top" class="bgColor4" title="id=' . $row['row']['uid'] . '"');
			}
		}

		// Create header:
		$tCells = array();
		$tCells[] = '<td>Page:</td>';
		$tCells[] = '<td>Last modified:</td>';
		$tCells[] = '<td>Cache Timeout:</td>';
		$tCells[] = '<td>is Dirty:</td>';
		$tCells[] = '<td>Explanation:</td>';

		$output = $this->renderTableHeaderRow($tCells, 'class="bgColor5 tableheader"') . $output;

		// Compile final table and return:
		$output = $this->renderHeader() . '
			<table border="0" cellspacing="1" cellpadding="0" class="lrPadding">' . $output . '
			</table>';

		// Outputting refresh-link
		$output .= '
			<p class="c-refresh">
				<a href="' . htmlspecialchars(GeneralUtility::getIndpEnv('REQUEST_URI')) . '">' . '<img' . IconUtility::skinImg($this->backPath, 'gfx/refresh_n.gif', 'width="14" height="14"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.refresh', 1) . '" alt="" />' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.refresh', 1) . '</a>
			</p>';

		// Set the current page Id:
		if ($this->pageId > 0) {
			$output .= '<input type="hidden" name="id" value="' . $this->pageId . '" />';
		}

		return $output;
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
	 * Renders a table header row.
	 *
	 * @param    array  $elements   : The row elements to be rendered
	 * @param    string $attributes : (optional) The attributes to be used on the table row
	 *
	 * @return    string        The HTML representation of the table row
	 */
	protected function renderTableHeaderRow(array $elements, $attributes = '') {
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
			$this->getStaticFileCacheInstance()
				->removeExpiredPages();
		} elseif (isset($action['processDirtyPages'])) {
			$this->getStaticFileCacheInstance()
				->processDirtyPages();
		}
	}

	/**
	 * Renders the header of the modile ("Static File Cache") and the accordant actions.
	 *
	 * @return    string        The HTML code of the header section
	 */
	protected function renderHeader() {
		return $this->pObj->doc->section('Static File Cache', implode('', $this->getHeaderActionButtons()), FALSE, TRUE);
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

		if ($this->isMarkDirtyInsteadOfDeletionDefined()) {
			$headerActionButtons['processDirtyPages'] = $this->renderActionButton('processDirtyPages', 'Process all dirty pages', 'Are you sure?');
		}

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
	 * Gets the instance of the static file cache object to modify the cached information.
	 *
	 * @return StaticFileCache
	 */
	protected function getStaticFileCacheInstance() {
		if (!isset($this->pubObj)) {
			$this->pubObj = GeneralUtility::makeInstance('SFC\\NcStaticfilecache\\StaticFileCache');
		}
		return $this->pubObj;
	}

	/**
	 * Determines whether the extension configuration property 'markDirtyInsteadOfDeletion' is enabled.
	 *
	 * @return    boolean        Whether the extension configuration property 'markDirtyInsteadOfDeletion' is enabled
	 */
	protected function isMarkDirtyInsteadOfDeletionDefined() {
		return (bool)$this->getStaticFileCacheInstance()
			->getConfigurationProperty('markDirtyInsteadOfDeletion');
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