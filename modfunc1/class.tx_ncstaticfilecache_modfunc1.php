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
 * class 'tx_ncstaticfilecache_modfunc1' for the 'nc_staticfilecache' extension.
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_ncstaticfilecache_modfunc1 extends t3lib_extobjbase
 *   60:     function main()
 *  101:     function renderModule($tree)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_t3lib.'class.t3lib_browsetree.php');
require_once(PATH_t3lib.'class.t3lib_extobjbase.php');

$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nc_staticfilecache']);
if($conf['debug']) {
	require_once(t3lib_extMgm::extPath('nc_staticfilecache').'class.tx_ncstaticfilecache.debug.php');
}
else {
	require_once(t3lib_extMgm::extPath('nc_staticfilecache').'class.tx_ncstaticfilecache.php');
}

/**
 * Static file cache extension
 *
 * @author	Michiel Roos <extensions@netcreators.com>
 * @package TYPO3
 * @subpackage tx_ncstaticfilecache
 */
class tx_ncstaticfilecache_modfunc1 extends t3lib_extobjbase {

	/**
	 * MAIN function for static publishing information
	 *
	 * @return	string		Output HTML for the module.
	 */
	function main()	{
		global $BACK_PATH,$LANG,$BE_USER;

		$output = '';

		$this->backPath = $BACK_PATH;

		//$treeStartingPoint = intval($this->pObj->id);
		$treeStartingPoint = t3lib_div::_GP('id');
		$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint);

		// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_browsetree');
		$tree->init('AND pages.doktype < 199 AND '.$GLOBALS['BE_USER']->getPagePermsClause(1).' AND pages.deleted = 0'); //.' AND pages.hidden = "0"');
		$tree->ext_IconMode = true;
		$tree->ext_showPageId = $BE_USER->getTSConfigVal('options.pageTree.showPageIdWithTitle');
		$tree->showDefaultTitleAttribute = true;
		$tree->thisScript = 'index.php';
		$tree->setTreeName('staticfilecache');
		//$tree->MOUNTS = array('lip' => $treeStartingRecord);

		// Creating top icon; the current page
		$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
		$tree->tree[] = array(
		'row' => $treeStartingRecord,
		'HTML' => $HTML
		);
		$tree->getBrowsableTree();

		// Render information table:
		$output.= $this->renderModule($tree);

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

		// Init static publishing object:
		$this->pubObj = t3lib_div::makeInstance('tx_ncstaticfilecache');
		$pubDir = $this->pubObj->cacheDir;

		// Traverse tree:
		$output = '';
		foreach($tree->tree as $row)	{

			// Fetch files:
			$filerecords = $this->pubObj->getRecordForPageID($row['row']['uid']);
			$cellAttrib = ($row['row']['_CSSCLASS'] ? ' class="'.$row['row']['_CSSCLASS'].'"' : '');

			if (count($filerecords))	{
				foreach($filerecords as $k => $frec)	{
					$tCells = array();

					if (!$k)	{
						$tCells[] = '<td nowrap="nowrap" valign="top" rowspan="'.count($filerecords).'"'.$cellAttrib.'>'.$row['HTML'].t3lib_BEfunc::getRecordTitle('pages',$row['row'],TRUE).'</td>';
					}

					$tCells[] = '<td nowrap="nowrap"><span class="typo3-dimmed">'.($frec['crdate']?t3lib_BEfunc::datetime($frec['crdate']):'').'</span></td>';
					$timeout = ($frec['crdate'] > 0) ? t3lib_BEfunc::calcAge(($frec['cache_timeout']),$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')) : '';
					$tCells[] = '<td nowrap="nowrap">'.$timeout.'</td>';
					$tCells[] = '<td nowrap="nowrap">'.($frec['explanation']?$frec['explanation']:'').'</td>';

					// Compile Row:
					$output.= '
						<tr class="bgColor4" title="id='.$frec['pid'].' host='.$frec['host'].' file='.$frec['file'].'">
							'.implode('
							',$tCells).'
						</tr>';
				}
			} else {
				// Compile Row:
				$output.= '
					<tr class="bgColor4" title="id='.$row['row']['uid'].'">
						<td nowrap="nowrap" colspan="3"'.$cellAttrib.'>'.$row['HTML'].t3lib_BEfunc::getRecordTitle('pages',$row['row'],TRUE).'</td>
						<td><span class="typo3-dimmed">'.($row['row']['uid'] == 0 ? '' : 'not hit').'</span></td>
					</tr>';
			}
		}

		// Create header:
		$tCells = array();
		$tCells[]='<td>Page:</td>';
		$tCells[]='<td>Created:</td>';
		$tCells[]='<td>Cache Timeout:</td>';
		$tCells[]='<td>Explanation:</td>';
		$output = '
			<tr class="bgColor5 tableheader">
				'.implode('
				',$tCells).'
			</tr>'.$output;

		// Compile final table and return:
		$output = '
			<table border="0" cellspacing="1" cellpadding="0" class="lrPadding">'.$output.'
			</table>';

		// Outputting refresh-link
		$output.= '
			<p class="c-refresh">
				<a href="'.htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')).'">'.
		'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'" alt="" />'.
		$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'</a>
			</p>';

		return $output;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/modfunc1/class.tx_ncstaticfilecache_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nc_staticfilecache/modfunc1/class.tx_ncstaticfilecache_modfunc1.php']);
}
?>
