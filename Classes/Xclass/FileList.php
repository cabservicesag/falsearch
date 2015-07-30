<?php
namespace Cabag\Falsearch\Xclass;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * XClass for filelist
 *
 * @author Nils Blattner <nb@cabag.ch>
 */
class FileList extends \TYPO3\CMS\Filelist\FileList {

	/**
	 * Returns a table with directories and files listed.
	 *
	 * @param array $rowlist Array of files from path
	 * @return string HTML-table
	 * @todo Define visibility
	 */
	public function getTable($rowlist) {
		$categoryUtility = GeneralUtility::makeInstance('Cabag\\Falsearch\\Utility\\CategoryUtility');
		
		$searchCategory = intval(GeneralUtility::_GP('searchCategory'));
		$searchWord = trim(GeneralUtility::_GP('searchWord'));
		
		$content .= '<input type="text" name="searchWord" value="' . $searchWord . '" />';
		$content .= $categoryUtility->getCategorySelect(array('name' => 'searchCategory'), $searchCategory);
		$content .= '<input type="submit" value="' . $GLOBALS['LANG']->sL('LLL:EXT:falsearch/Resources/Private/Language/locallang.xlf:search') . '" />';
		
		if (!empty($searchWord) || $searchCategory > 0) {
			$this->folderObject->setSearchWords($searchWord);
			$this->folderObject->setSearchCategory($searchCategory);
			
			$this->folderObject->setOverrideRecursion(true);
		}
		
		$content .= parent::getTable($rowlist);
		
		return $content;
	}

	/**
	 * Wraps filenames in links which opens them in a window IF they are in web-path.
	 *
	 * @param string $code String to be wrapped in links
	 * @param \TYPO3\CMS\Core\Resource\File $fileObject File to be linked
	 * @return string HTML
	 * @todo Define visibility
	 */
	public function linkWrapFile($code, \TYPO3\CMS\Core\Resource\File $fileObject) {
		$code = parent::linkWrapFile($code, $fileObject);
		
		if ($this->folderObject->getOverrideRecursion()) {
			$code = htmlspecialchars(rawurldecode(substr($fileObject->getParentFolder()->getPublicUrl(), strlen($this->folderObject->getPublicUrl())))) . $code;
		}
		
		return $code;
	}

	/**
	 * Make reference count
	 *
	 * @param \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\Folder $fileOrFolderObject Array with information about the file/directory for which to make the clipboard panel for the listing.
	 * @return string HTML
	 * @todo Define visibility
	 */
	public function makeRef($fileOrFolderObject) {
		if ($fileOrFolderObject instanceof \TYPO3\CMS\Core\Resource\FolderInterface) {
			return '-';
		}
		// Look up the file in the sys_refindex.
		// Exclude sys_file_metadata records as these are no use references
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT *', 'sys_refindex', '((ref_table=\'sys_file\' AND ref_uid = ' . (int)$fileOrFolderObject->getUid() . ') OR ref_table = \'_FILE\' AND ref_string = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(rawurldecode($fileOrFolderObject->getPublicURL()), 'sys_refindex') . ') AND deleted=0 AND tablename != "sys_file_metadata"', 'ref_uid, recuid');
		return $this->generateReferenceToolTip($rows, '\'_FILE\', ' . GeneralUtility::quoteJSvalue($fileOrFolderObject->getCombinedIdentifier()));
	}

	/**
	 * Do not show folders when searching.
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder[] $folders Folders of \TYPO3\CMS\Core\Resource\Folder
	 * @return string HTML table rows.
	 * @todo Define visibility
	 */
	public function formatDirList(array $folders) {
		if ($this->folderObject->getOverrideRecursion()) {
			return '';
		}
		
		return parent::formatDirList($folders);
	}
}
