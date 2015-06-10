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
 * Class for rendering of File>Filelist
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
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
}
