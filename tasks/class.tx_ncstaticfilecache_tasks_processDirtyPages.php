<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE GmbH (dev@aoe.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * {@inheritdoc}
 *
 * @author     Michael Klapper <michael.klapper@aoe.com>
 * @copyright  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @since      08.01.2010 - 11:00:44
 * @package    TYPO3
 * @subpackage tx_ncstaticfilecache
 * @access     public
 * @deprecated Remove in Version 3.0.0
 */
class tx_ncstaticfilecache_tasks_processDirtyPages extends AbstractTask {

	/**
	 * @var integer
	 */
	public $itemLimit = 0;

	/**
	 * This is the main method that is called when a task is executed
	 * It MUST be implemented by all classes inheriting from this one
	 * Note that there is no error handling, errors and failures are expected
	 * to be handled and logged by the client implementations.
	 * Should return true on successful execution, false on error.
	 *
	 * @access public
	 * @return boolean    Returns true on successful execution, false on error
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @deprecated Remove in Version 3.0.0
	 */
	public function execute() {
		GeneralUtility::logDeprecatedFunction();
		/* @var $cleaner tx_ncstaticfilecache */
		$cleaner = GeneralUtility::makeInstance('tx_ncstaticfilecache');
		$cleaner->processDirtyPages(NULL, $this->itemLimit);

		return TRUE;
	}
}