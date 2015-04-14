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
	 * The search words (AND'd).
	 * @var array
	 */
	protected $searchWords = array();

	/**
	 * Returns a table with directories and files listed.
	 *
	 * @param array $rowlist Array of files from path
	 * @return string HTML-table
	 * @todo Define visibility
	 */
	public function getTable($rowlist) {
		$searchWord = trim(GeneralUtility::_GP('searchWord'));
		$backupFilters = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['defaultFilterCallbacks'];
		
		$content .= '<input type="text" name="searchWord" value="' . $searchWord . '" /><input type="submit" value="' . $GLOBALS['LANG']->sL('LLL:EXT:falsearch/Resources/Private/Language/locallang.xlf:search') . '" />';
		
		if (!empty($searchWord)) {
			$this->searchWords = GeneralUtility::trimExplode(' ', $searchWord);
			
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['defaultFilterCallbacks'][] = array($this, 'filterFiles');
			$this->folderObject->setOverrideRecursion(true);
		}
		
		$content .= parent::getTable($rowlist);
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['defaultFilterCallbacks'] = $backupFilters;
		
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
		
		if (count($this->searchWords) > 0) {
			$code = htmlspecialchars(rawurldecode(substr($fileObject->getParentFolder()->getPublicUrl(), strlen($this->folderObject->getPublicUrl())))) . $code;
		}
		
		return $code;
	}
	
	/**
	 * Filter the given files/folders.
	 *
	 * @param string $itemName The name.
	 * @param string $itemIdentifier The identifier.
	 * @param string $parentIdentifier The parent identifier.
	 * @param array $ignored Ignored array.
	 * @param \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver The driver object.
	 * @return mixed TRUE if the item should be displayed, -1 otherwise.
	 */
	public function filterFiles($itemName, $itemIdentifier, $parentIdentifier, array $ignored = array(), \TYPO3\CMS\Core\Resource\Driver\DriverInterface $driver) {
		$result = TRUE;
		
		foreach ($this->searchWords as $word) {
			if (stripos($itemName, $word) === FALSE) {
				$result = -1;
				break;
			}
		}
		
		return $result;
	}
}
