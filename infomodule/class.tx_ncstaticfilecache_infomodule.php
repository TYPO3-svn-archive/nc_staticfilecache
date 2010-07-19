<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Michiel Roos <extensions@netcreators.com>
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * class 'tx_ncstaticfilecache_infomodule' for the 'nc_staticfilecache' extension.
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_ncstaticfilecache_infomodule extends t3lib_extobjbase
 *   60:     function main()
 *  101:     function renderModule($tree)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_t3lib.'class.t3lib_browsetree.php');
require_once(PATH_t3lib.'class.t3lib_extobjbase.php');

/**
 * Static file cache extension
 *
 * @author	Michiel Roos <extensions@netcreators.com>
 * @package TYPO3
 * @subpackage tx_ncstaticfilecache
 */
class tx_ncstaticfilecache_infomodule extends t3lib_extobjbase {
	/**
	 * @var	tx_ncstaticfilecache
	 */
	protected $pubObj;

	/**
	 * @var	integer
	 */
	protected $pageId = 0;

	/**
	 * MAIN function for static publishing information
	 *
	 * @return	string		Output HTML for the module.
	 */
	function main()	{
		global $BACK_PATH,$LANG,$BE_USER;

		// Handle actions:
		$this->handleActions();

		$output = '';

		$this->backPath = $BACK_PATH;

		$this->pageId = intval($this->pObj->id);

		// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_browsetree');
		// Also store tree prefix markup:
		$tree->makeHTML = 2;
		$tree->init();
		// Set starting page Id of tree (overrides webmounts):
		if ($this->pageId > 0) {
			$tree->MOUNTS = array(0 => $this->pageId);
		}
		$tree->ext_IconMode = true;
		$tree->ext_showPageId = $BE_USER->getTSConfigVal('options.pageTree.showPageIdWithTitle');
		$tree->showDefaultTitleAttribute = true;
		$tree->thisScript = 'index.php';
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
	 * @param	array		The Page tree data
	 * @return	string		HTML for the information table.
	 */
	function renderModule($tree)	{
		global $LANG;

		$pubDir = $this->getStaticFileCacheInstance()->getCacheDirectory();

		// Traverse tree:
		$output = '';
		foreach($tree->tree as $row)	{

			// Fetch files:
			$filerecords = $this->getStaticFileCacheInstance()->getRecordForPageID($row['row']['uid']);
			$cellAttrib = ($row['row']['_CSSCLASS'] ? ' class="'.$row['row']['_CSSCLASS'].'"' : '');

			if (count($filerecords))	{
				foreach($filerecords as $k => $frec)	{
					$tCells = array();

					if (!$k)	{
						$tCells[] = '<td nowrap="nowrap"' . $cellAttrib . '>' . $row['HTML'] . t3lib_BEfunc::getRecordTitle('pages', $row['row'], TRUE) . '</td>';
					} else {
						$tCells[] = '<td nowrap="nowrap"' . $cellAttrib . '>' . $row['HTML_depthData'] . '</td>';
					}

					$tCells[] = '<td nowrap="nowrap"><span class="typo3-dimmed">'.($frec['tstamp']?t3lib_BEfunc::datetime($frec['tstamp']):'').'</span></td>';
					$timeout = ($frec['tstamp'] > 0) ? t3lib_BEfunc::calcAge(($frec['cache_timeout']),$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')) : '';
					$tCells[] = '<td nowrap="nowrap">'.$timeout.'</td>';
					$tCells[] = '<td>' . ($frec['isdirty'] ? 'yes' : 'no') . '</td>';
					$tCells[] = '<td nowrap="nowrap">'.($frec['explanation']?$frec['explanation']:'').'</td>';

				// Compile Row:
					$output .= $this->renderTableRow(
						$tCells,
						'valign="top" title="id='.$frec['pid'].' host='.$frec['host'].' uri='.$frec['uri'].'"',
						$frec
					);
				}
			} else {
				$tCells = array(
					'<td nowrap="nowrap" colspan="4"' . $cellAttrib . '>' . $row['HTML'] . t3lib_BEfunc::getRecordTitle('pages', $row['row'], TRUE) . '</td>',
					'<td><span class="typo3-dimmed">' . ($row['row']['uid'] == 0 ? '' : 'not hit') . '</span></td>',
				);

				// Compile Row:
				$output .= $this->renderTableRow(
					$tCells,
					'valign="top" class="bgColor4" title="id='.$row['row']['uid'].'"'
				);
			}
		}

		// Create header:
		$tCells = array();
		$tCells[]='<td>Page:</td>';
		$tCells[]='<td>Last modified:</td>';
		$tCells[]='<td>Cache Timeout:</td>';
		$tCells[]='<td>is Dirty:</td>';
		$tCells[]='<td>Explanation:</td>';

		$output = $this->renderTableHeaderRow($tCells, 'class="bgColor5 tableheader"') . $output;

		// Compile final table and return:
		$output = 
			$this->renderHeader() . '
			<table border="0" cellspacing="1" cellpadding="0" class="lrPadding">'.$output.'
			</table>';

		// Outputting refresh-link
		$output.= '
			<p class="c-refresh">
				<a href="'.htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')).'">'.
		'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'" alt="" />'.
		$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'</a>
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
	 * @param	array		$elements: The row elements to be rendered
	 * @param	string		$attributes: (optional) The attributes to be used on the table row
	 * @param	array		$cacheElement: (optional) The cache element row
	 * @return	string		The HTML representation of the table row
	 */
	protected function renderTableRow(array $elements, $attributes = '', array $cacheElement = NULL) {
		return '<tr' . ($attributes ? ' ' : '') . $attributes . '>' . implode('', $elements) . '</tr>';
	}

	/**
	 * Renders a table header row.
	 *
	 * @param	array		$elements: The row elements to be rendered
	 * @param	string		$attributes: (optional) The attributes to be used on the table row
	 * @return	string		The HTML representation of the table row
	 */
	protected function renderTableHeaderRow(array $elements, $attributes = '') {
		return '<tr' . ($attributes ? ' ' : '') . $attributes . '>' . implode('', $elements) . '</tr>';
	}

	/**
	 * Handles incoming actions (e.g. removing all expired pages).
	 *
	 * @return	void
	 */
	protected function handleActions() {
		$action = t3lib_div::_GP('ACTION');

		if (isset($action['removeExpiredPages'])) {
			$this->getStaticFileCacheInstance()->removeExpiredPages();
		} elseif (isset($action['processDirtyPages'])) {
			$this->getStaticFileCacheInstance()->processDirtyPages();
		}
	}

	/**
	 * Renders the header of the modile ("Static File Cache") and the accordant actions.
	 *
	 * @return	string		The HTML code of the header section
	 */
	protected function renderHeader() {
		return $this->pObj->doc->section(
			'Static File Cache',
			implode('', $this->getHeaderActionButtons()),
			false,
			true
		);
	}

	/**
	 * Gets the header actions buttons to be rendered in the header section.
	 *
	 * @return	array		Action buttons to be rendered in the header section
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
	 * @param	string		$elementName: Name attribute of the element
	 * @param	string		$elementLabel: Label of the action button
	 * @param	string		$confirmationText: (optional) Confirmation text - will not be used if empty
	 * @return	string		The HTML representation of an action button
	 */
	protected function renderActionButton($elementName, $elementLabel, $confirmationText = '') {
		return '<input type="submit" name="ACTION[' . htmlspecialchars($elementName) . ']" value="' . $elementLabel . '"' .
			($confirmationText ? ' onclick="return confirm(\'' . addslashes($confirmationText) . '\');"' : '') . ' />';
	}

	/**
	 * Gets the instance of the static file cache object to modify the cached information.
	 * 
	 * @return	tx_ncstaticfilecache
	 */
	protected function getStaticFileCacheInstance() {
		if (!isset($this->pubObj)) {
			t3lib_div::requireOnce(t3lib_extMgm::extPath('nc_staticfilecache') . 'class.tx_ncstaticfilecache.php');
			$this->pubObj = t3lib_div::makeInstance('tx_ncstaticfilecache');
		}
		return $this->pubObj;
	}

	/**
	 * Determines whether the extension configuration property 'markDirtyInsteadOfDeletion' is enabled.
	 *
	 * @return	boolean		Whether the extension configuration property 'markDirtyInsteadOfDeletion' is enabled
	 */
	protected function isMarkDirtyInsteadOfDeletionDefined() {
		return (bool)$this->getStaticFileCacheInstance()->getConfigurationProperty('markDirtyInsteadOfDeletion');
	}

	/**
	 * Processes the expand/collapse links and adds the Id of the current page in branch.
	 *
	 * Example:
	 * index.php?PM=0_0_23_staticfilecache#0_23 --> index.php?PM=0_0_23_staticfilecache&id=13#0_23
	 *
	 * @param	string		$content: Content to be processed
	 * @return	string		The processed and modified content
	 */
	protected function processExpandCollapseLinks($content) {
		if (strpos($content, '?PM=') !== false && $this->pageId > 0) {
			$content = preg_replace(
				'/(href=")([^"]+\?PM=[^"#]+)(#[^"]+)?(")/',
				'${1}${2}&id=' . $this->pageId . '${3}${4}',
				$content
			);
		}

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/infomodule/class.tx_ncstaticfilecache_infomodule.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/infomodule/class.tx_ncstaticfilecache_infomodule.php']);
}
?>
