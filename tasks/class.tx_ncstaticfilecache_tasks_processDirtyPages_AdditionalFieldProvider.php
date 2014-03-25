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
class tx_ncstaticfilecache_tasks_processDirtyPages_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {

	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an email field
	 *
	 * @param	array					$taskInfo: reference to the array containing the info used in the add/edit form
	 * @param	object					$task: when editing, reference to the current task object. Null when adding.
	 * @param	tx_scheduler_Module		$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	array					Array containg all the information pertaining to the additional fields
	 *									The array is multidimensional, keyed to the task class name and each field's id
	 *									For each field it provides an associative sub-array with the following:
	 *										['code']		=> The HTML code for the field
	 *										['label']		=> The label of the field (possibly localized)
	 *										['cshKey']		=> The CSH key for the field
	 *										['cshLabel']	=> The code of the CSH label
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {
		$additionalFields = array();

		if (empty($taskInfo['itemLimit'])) {
                        if ($schedulerModule->CMD == 'add') {
                                $taskInfo['itemLimit'] = 0;
                        } else {
                                $taskInfo['itemLimit'] = $task->itemLimit;
                        }
		}
		$fieldID = 'task_itemLimit';
		$fieldCode  = '<input type="text" name="tx_scheduler[itemLimit]" id="' . $fieldID . '" value="' . $taskInfo['itemLimit'] . '" />';
		$additionalFields[$fieldID] = array(
                        'code'     => $fieldCode,
                        'label'    => 'LLL:EXT:nc_staticfilecache/locallang_db.xml:nc_staticfilecache_task_processDirtyPages.itemLimit'
                );
		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param	array					An array containing the data submitted by the add/edit task form
	 * @param	tx_scheduler_Module		Reference to the scheduler backend module
	 * @return	boolean					True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $schedulerModule) {

		$itemLimit = class_exists('t3lib_utility_Math') ?
			t3lib_utility_Math::convertToPositiveInteger($submittedData['itemLimit']) :
			t3lib_div::intval_positive($submittedData['itemLimit']);

		if ( $itemLimit > 0 ) {
			return true;
		} else {
			$schedulerModule->addMessage('no valid limit given (positive number expected)', t3lib_FlashMessage::ERROR);
			return false;
		}

	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param	array					An array containing the data submitted by the add/edit task form
	 * @param	tx_scheduler_Module		Reference to the scheduler backend module
	 * @return	void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->itemLimit = $submittedData['itemLimit'];
	}
}
?>
