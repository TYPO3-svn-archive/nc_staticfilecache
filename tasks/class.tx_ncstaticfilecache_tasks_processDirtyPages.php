<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 AOE media (dev@aoemedia.de)
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

require_once(t3lib_extMgm::extPath('nc_staticfilecache') . 'class.tx_ncstaticfilecache.php');

/**
 * {@inheritdoc}
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @version $Id$
 * @date $Date$
 * @since 08.01.2010 - 11:00:44
 * @package TYPO3
 * @subpackage tx_ncstaticfilecache
 * @access public
 */
class tx_ncstaticfilecache_tasks_processDirtyPages extends tx_scheduler_Task {

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
	 * @return boolean	Returns true on successful execution, false on error
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function execute() {
		/* @var $cleaner tx_ncstaticfilecache */
		$cleaner = t3lib_div::makeInstance('tx_ncstaticfilecache');
		$cleaner->processDirtyPages(NULL, $this->itemLimit);

		return true;
	}
}
?>
