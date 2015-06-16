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
use TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * XClass to render the search form.
 *
 * @author Nils Blattner <nb@cabag.ch>
 */
class BrowseLinks extends \TYPO3\CMS\Rtehtmlarea\BrowseLinks {

	/**
	 * For RTE: This displays all files from folder. No thumbnails shown
	 *
	 * @param Folder $folder The folder path to expand
	 * @param string $extensionList List of file extensions to show
	 * @return string HTML output
	 * @todo Define visibility
	 */
	public function expandFolder(Folder $folder, $extensionList = '') {
		/* MODIFIED nb@cabag.ch start */
		$categoryUtility = GeneralUtility::makeInstance('Cabag\\Falsearch\\Utility\\CategoryUtility');
		
		$searchCategory = intval(GeneralUtility::_GP('searchCategory'));
		$searchWord = trim(GeneralUtility::_GP('searchWord'));
		
		$out = '<form action="' . $this->getThisScript() . 'act=' . $this->act . '&mode=' . $this->mode
			. '&expandFolder=' . rawurlencode($folder->getCombinedIdentifier())
			. '&bparams=' . rawurlencode($this->bparams) . '" method="post" name="dblistForm">';
		$out .= '<input type="text" name="searchWord" value="' . $searchWord . '" />';
		
		$out .= $categoryUtility->getCategorySelect(array('name' => 'searchCategory'), $searchCategory);
		$out .= '<input type="submit" value="' . $GLOBALS['LANG']->sL('LLL:EXT:falsearch/Resources/Private/Language/locallang.xlf:search') . '" />';
		$out .= '<input type="hidden" name="cmd" /></form>';
		
		if (!empty($searchWord) || $searchCategory > 0) {
			$folder->setSearchWords($searchWord);
			$folder->setSearchCategory($searchCategory);
			
			$folder->setOverrideRecursion(true);
			$this->filteringRecursive = true;
		}
		
		/* MODIFIED nb@cabag.ch end */
		$renderFolders = $this->act === 'folder';
		if ($folder->checkActionPermission('read')) {
			// Create header for file listing:
			$out .= $this->barheader($GLOBALS['LANG']->getLL('files') . ':');
			// Prepare current path value for comparison (showing red arrow)
			$currentIdentifier = '';
			if ($this->curUrlInfo['value']) {
				$currentIdentifier = $this->curUrlInfo['info'];
			}
			// Create header element; The folder from which files are listed.
			$titleLen = (int)$GLOBALS['BE_USER']->uc['titleLen'];
			$folderIcon = IconUtility::getSpriteIconForResource($folder);
			$folderIcon .= htmlspecialchars(GeneralUtility::fixed_lgd_cs($folder->getIdentifier(), $titleLen));
			$picon = '<a href="#" onclick="return link_folder(\'file:' . $folder->getCombinedIdentifier() . '\');">'
				. $folderIcon . '</a>';
			if ($this->curUrlInfo['act'] == 'folder' && $currentIdentifier == $folder->getCombinedIdentifier()) {
				$out .= '<img'
					. IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/blinkarrow_left.gif', 'width="5" height="9"')
					. ' class="c-blinkArrowL" alt="" />';
			}
			$out .= $picon . '<br />';
			// Get files from the folder:
			if ($renderFolders) {
				$items = $folder->getSubfolders();
			} else {
				$items = $this->getFilesInFolder($folder, $extensionList);
			}
			$c = 0;
			$totalItems = count($items);
			foreach ($items as $fileOrFolderObject) {
				$c++;
				if ($renderFolders) {
					$fileIdentifier = $fileOrFolderObject->getCombinedIdentifier();
					$overlays = array();
					if ($fileOrFolderObject instanceof \TYPO3\CMS\Core\Resource\InaccessibleFolder) {
						$overlays = array('status-overlay-locked' => array());
					}
					$icon = IconUtility::getSpriteIcon(
						IconUtility::mapFileExtensionToSpriteIconName('folder'),
						array('title' => $fileOrFolderObject->getName()),
						$overlays);
					$itemUid = 'file:' . $fileIdentifier;
				} else {
					$fileIdentifier = $fileOrFolderObject->getUid();
					// File icon:
					$fileExtension = $fileOrFolderObject->getExtension();
					// Get size and icon:
					$size = ' (' . GeneralUtility::formatSize($fileOrFolderObject->getSize()) . 'bytes)';
					$icon = IconUtility::getSpriteIconForResource($fileOrFolderObject, array('title' => $fileOrFolderObject->getName() . $size));
					$itemUid = 'file:' . $fileIdentifier;
				}
				// If the listed file turns out to be the CURRENT file, then show blinking arrow:
				if (($this->curUrlInfo['act'] == 'file' || $this->curUrlInfo['act'] == 'folder')
					&& $currentIdentifier == $fileIdentifier
				) {
					$arrCol = '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/blinkarrow_left.gif',
							'width="5" height="9"') . ' class="c-blinkArrowL" alt="" />';
				} else {
					$arrCol = '';
				}
				// Put it all together for the file element:
				$out .=
					'<img' .
						IconUtility::skinImg(
							$GLOBALS['BACK_PATH'],
							('gfx/ol/join' . ($c == $totalItems ? 'bottom' : '') . '.gif'),
							'width="18" height="16"'
						) . ' alt="" />' . $arrCol .
					'<a href="#" onclick="return link_folder(\'' . $itemUid . '\');">' .
						$icon .
						/* MODIFIED nb@cabag.ch start */
						($folder->getOverrideRecursion() ? htmlspecialchars(rawurldecode(substr($fileOrFolderObject->getParentFolder()->getPublicUrl(), strlen($folder->getPublicUrl())))) : '') .
						/* MODIFIED nb@cabag.ch end */
						htmlspecialchars(GeneralUtility::fixed_lgd_cs($fileOrFolderObject->getName(), $titleLen)) .
					'</a><br />';
			}
		}
		return $out;
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
		//debug($this->folderObject->getPublicUrl(), $fileObject->getPublicUrl());
		$code = parent::linkWrapFile($code, $fileObject);
		
		if ($this->folderObject->getOverrideRecursion()) {
			$code = substr($fileObject->getParentFolder()->getPublicUrl(), strlen($this->folderObject->getPublicUrl())) . $code;
		}
		
		return $code;
	}
}
