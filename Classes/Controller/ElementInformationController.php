<?php
namespace Cabag\Falsearch\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Injects the option to get the sys_refindex for file id's and paths
 *
 * @author Nils Blattner <nb@cabag.ch>
 */
class ElementInformationController extends \TYPO3\CMS\Backend\Controller\ContentElement\ElementInformationController {
	
	/**
	 * Checks whether this object should render the given item.
	 *
	 * @param string $type The type of the item to be rendered.
	 * @param TYPO3\CMS\Backend\Controller\ContentElement\ElementInformationController $caller The calling controller.
	 * @return boolean Whether or not this will render the item.
	 */
	public function isValid($type, \TYPO3\CMS\Backend\Controller\ContentElement\ElementInformationController $caller) {
		return $type === 'file';
	}
	
	/**
	 * Renders the given item (established to be a file item by isValid).
	 *
	 * @param string $type The type of the item to be rendered.
	 * @param TYPO3\CMS\Backend\Controller\ContentElement\ElementInformationController $caller The calling controller.
	 * @return boolean Whether or not this will render the item.
	 */
	public function render($type, \TYPO3\CMS\Backend\Controller\ContentElement\ElementInformationController $caller) {
		$this->init();
		$content = $this->renderPageTitle();
		$content .= $this->renderPreview();
		$content .= $this->renderPropertiesAsTable();
		$content .= $this->renderReferences();
		
		return $content;
	}
	
	/**
	 * Make reference display
	 *
	 * @param string $table Table name
	 * @param string|\TYPO3\CMS\Core\Resource\File $ref Filename or uid
	 * @return string HTML
	 */
	protected function makeRef($table, $ref) {
		/* MODIFIED nb@cabag.ch start */
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_refindex',
			'((ref_table=\'sys_file\' AND ref_uid = ' . (int)$ref->getUid() . ') OR ref_table = \'_FILE\' AND ref_string = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(rawurldecode($ref->getPublicURL()), 'sys_refindex') . ') AND deleted=0', 'recuid, ref_uid'
		);
		/* MODIFIED nb@cabag.ch end */

		// Compile information for title tag:
		$infoData = array();
		if (count($rows)) {
			$infoDataHeader = '<tr>' . '<td>&nbsp;</td>' . '<td>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:show_item.php.table') . '</td>' . '<td>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:show_item.php.title') . '</td>' . '<td>[uid]</td>' . '<td>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:show_item.php.field') . '</td>' . '<td>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:show_item.php.flexpointer') . '</td>' . '<td>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:show_item.php.softrefKey') . '</td>' . '<td>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:show_item.php.sorting') . '</td>' . '</tr>';
		}
		foreach ($rows as $row) {
			if ($row['tablename'] === 'sys_file_reference') {
				$row = $this->transformFileReferenceToRecordReference($row);
			}
			$record = BackendUtility::getRecord($row['tablename'], $row['recuid']);
			$parentRecord = BackendUtility::getRecord('pages', $record['pid']);
			$actions = $this->getRecordActions($row['tablename'], $row['recuid']);
			$infoData[] = '<tr class="db_list_normal">' .
					'<td style="white-space:nowrap;">' . $actions . '</td>' .
					'<td>' . $GLOBALS['LANG']->sL($GLOBALS['TCA'][$row['tablename']]['ctrl']['title'], TRUE) . '</td>' .
					'<td>' . BackendUtility::getRecordTitle($row['tablename'], $record, TRUE) . '</td>' .
					'<td><span title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xlf:page') . ': ' .
							htmlspecialchars(BackendUtility::getRecordTitle('pages', $parentRecord)) . ' (uid=' . $record['pid'] . ')">' .
							$record['uid'] . '</span></td>' .
					'<td>' . htmlspecialchars($this->getLabelForTableColumn($row['tablename'], $row['field'])) . '</td>' .
					'<td>' . htmlspecialchars($row['flexpointer']) . '</td>' . '<td>' . htmlspecialchars($row['softref_key']) . '</td>' .
					'<td>' . htmlspecialchars($row['sorting']) . '</td>' .
					'</tr>';
		}
		$referenceLine = '';
		if (count($infoData)) {
			$referenceLine = '<table class="t3-table">' .
					'<thead>' . $infoDataHeader . '</thead>' .
					'<tbody>' .
					implode('', $infoData) .
					'</tbody></table>';
		}
		return $referenceLine;
	}
}
