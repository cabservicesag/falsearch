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
 * Class for rendering of File>Filelist
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */
class ElementBrowser extends \TYPO3\CMS\Recordlist\Browser\ElementBrowser {

	/**
	 * For TYPO3 Element Browser: Expand folder of files.
	 *
	 * @param Folder $folder The folder path to expand
	 * @param string $extensionList List of fileextensions to show
	 * @param boolean $noThumbs Whether to show thumbnails or not. If set, no thumbnails are shown.
	 * @return string HTML output
	 * @todo Define visibility
	 */
	public function TBE_expandFolder(Folder $folder, $extensionList = '', $noThumbs = FALSE) {
		$content = $this->generateSearchFormAndInitializeFilter($folder);
		
		$content .= parent::TBE_expandFolder($folder, $extensionList, $noThumbs);
		
		return $content;
	}

	/**
	 * For TYPO3 Element Browser: Expand folder of files.
	 *
	 * @param Folder $folder The folder path to expand
	 * @param string $extensionList List of fileextensions to show
	 * @param boolean $noThumbs Whether to show thumbnails or not. If set, no thumbnails are shown.
	 * @return string HTML output
	 * @todo Define visibility
	 */
	public function expandFolder(Folder $folder, $extensionList = '') {
		$out = $this->generateSearchFormAndInitializeFilter($folder, false);
		
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
	 * Generate the search form and initalize the filters.
	 *
	 * @param Folder $folder The folder path to search in.
	 * @return string The form.
	 */
	protected function generateSearchFormAndInitializeFilter(Folder $folder, $tbe = true) {
		$categoryUtility = GeneralUtility::makeInstance('Cabag\\Falsearch\\Utility\\CategoryUtility');
		$searchCategory = intval(GeneralUtility::_GP('searchCategory'));
		$searchWord = trim(GeneralUtility::_GP('searchWord'));
		
		$content = '<form action="' . $this->getThisScript() . 'act=' . $this->act . '&mode=' . $this->mode
			. '&expandFolder=' . rawurlencode($folder->getCombinedIdentifier())
			. '&bparams=' . rawurlencode($this->bparams) . '" method="post" name="dblistForm">';
		$content .= '<input type="text" name="searchWord" value="' . $searchWord . '" />';
		$content .= $categoryUtility->getCategorySelect(array('name' => 'searchCategory'), $searchCategory);
		$content .= '<input type="submit" value="' . $GLOBALS['LANG']->sL('LLL:EXT:falsearch/Resources/Private/Language/locallang.xlf:search') . '" />';
		
		if (!$tbe) {
			$P = GeneralUtility::_GP('P');
			if (is_array($P)) {
				foreach ($P as $key => $value) {
					if (is_array($value)) {
						foreach ($value as $subKey => $subValue) {
							$content .= '<input type="hidden" name="P[' . htmlspecialchars($key) . '][' . htmlspecialchars($subKey) . ']" value="' . htmlspecialchars($subValue) . '" />';
						}
					} else {
						$content .= '<input type="hidden" name="P[' . htmlspecialchars($key) . ']" value="' . htmlspecialchars($value) . '" />';
					}
				}
			}
		}
		
		$content .= '<input type="hidden" name="cmd" /></form>';
		
		if (!empty($searchWord) || $searchCategory > 0) {
			$folder->setSearchWords($searchWord);
			$folder->setSearchCategory($searchCategory);
			
			$folder->setOverrideRecursion(true);
			$this->filteringRecursive = true;
		}
		
		return $content;
	}

	/**
	 * Render list of files.
	 *
	 * @param File[] $files List of files
	 * @param Folder $folder If set a header with a folder icon and folder name are shown
	 * @param boolean $noThumbs Whether to show thumbnails or not. If set, no thumbnails are shown.
	 * @return string HTML output
	 */
	protected function fileList(array $files, Folder $folder = NULL, $noThumbs = FALSE) {
		$out = '';

		$lines = array();
		// Create headline (showing number of files):
		$filesCount = count($files);
		$out .= $this->barheader(sprintf($GLOBALS['LANG']->getLL('files') . ' (%s):', $filesCount));
		$out .= '<div id="filelist">';
		$out .= $this->getBulkSelector($filesCount);
		$titleLen = (int)$GLOBALS['BE_USER']->uc['titleLen'];
		// Create the header of current folder:
		if ($folder) {
			$folderIcon = IconUtility::getSpriteIconForResource($folder);
			$lines[] = '<tr class="t3-row-header">
				<td colspan="4">' . $folderIcon
				. htmlspecialchars(GeneralUtility::fixed_lgd_cs($folder->getIdentifier(), $titleLen)) . '</td>
			</tr>';
		}
		if ($filesCount == 0) {
			$lines[] = '
				<tr class="file_list_normal">
					<td colspan="4">No files found.</td>
				</tr>';
		}
		// Traverse the file list:
		/** @var $fileObject \TYPO3\CMS\Core\Resource\File */
		foreach ($files as $fileObject) {
			$fileExtension = $fileObject->getExtension();
			// Thumbnail/size generation:
			$imgInfo = array();
			if (GeneralUtility::inList(strtolower($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']), strtolower($fileExtension)) && !$noThumbs) {
				$imageUrl = $fileObject->process(
					\TYPO3\CMS\Core\Resource\ProcessedFile::CONTEXT_IMAGEPREVIEW,
					array('width' => 64, 'height' => 64)
				)->getPublicUrl(TRUE);
				$imgInfo = array(
					$fileObject->getProperty('width'),
					$fileObject->getProperty('height')
				);
				$pDim = $imgInfo[0] . 'x' . $imgInfo[1] . ' pixels';
				$clickIcon = '<img src="' . $imageUrl . '" hspace="5" vspace="5" border="1" />';
			} else {
				$clickIcon = '';
				$pDim = '';
			}
			// Create file icon:
			$size = ' (' . GeneralUtility::formatSize($fileObject->getSize()) . 'bytes' . ($pDim ? ', ' . $pDim : '') . ')';
			$icon = IconUtility::getSpriteIconForResource($fileObject, array('title' => $fileObject->getName() . $size));
			// Create links for adding the file:
			$filesIndex = count($this->elements);
			$this->elements['file_' . $filesIndex] = array(
				'type' => 'file',
				'table' => 'sys_file',
				'uid' => $fileObject->getUid(),
				'fileName' => $fileObject->getName(),
				'filePath' => $fileObject->getUid(),
				'fileExt' => $fileExtension,
				'fileIcon' => $icon
			);
			if ($this->fileIsSelectableInFileList($fileObject, $imgInfo)) {
				$ATag = '<a href="#" onclick="return BrowseLinks.File.insertElement(\'file_' . $filesIndex . '\');">';
				$ATag_alt = substr($ATag, 0, -4) . ',1);">';
				$bulkCheckBox = '<input type="checkbox" class="typo3-bulk-item" name="file_' . $filesIndex . '" value="0" /> ';
				$ATag_e = '</a>';
			} else {
				$ATag = '';
				$ATag_alt = '';
				$ATag_e = '';
				$bulkCheckBox = '';
			}
			// Create link to showing details about the file in a window:
			$Ahref = $GLOBALS['BACK_PATH'] . 'show_item.php?type=file&table=_FILE&uid='
				. rawurlencode($fileObject->getCombinedIdentifier())
				. '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI'));
			$ATag2 = '<a href="' . htmlspecialchars($Ahref) . '">';
			$ATag2_e = '</a>';
			// Combine the stuff:
			$filenameAndIcon = $bulkCheckBox . $ATag_alt . $icon
				/* MODIFIED nb@cabag.ch start */
				. ($folder->getOverrideRecursion() ? htmlspecialchars(rawurldecode(substr($fileObject->getParentFolder()->getPublicUrl(), strlen($folder->getPublicUrl())))) : '')
				/* MODIFIED nb@cabag.ch end */
				. htmlspecialchars(GeneralUtility::fixed_lgd_cs($fileObject->getName(), $titleLen)) . $ATag_e;
			// Show element:
			if ($pDim) {
				// Image...
				$lines[] = '
					<tr class="file_list_normal">
						<td nowrap="nowrap">' . $filenameAndIcon . '&nbsp;</td>
						<td>' . ($ATag . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/plusbullet2.gif',
							'width="18" height="16"') . ' title="' . $GLOBALS['LANG']->getLL('addToList', TRUE)
							. '" alt="" />' . $ATag_e) . '</td>
						<td nowrap="nowrap">' . ($ATag2 . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'],
							'gfx/zoom2.gif', 'width="12" height="12"') . ' title="'
							. $GLOBALS['LANG']->getLL('info', TRUE) . '" alt="" /> '
							. $GLOBALS['LANG']->getLL('info', TRUE) . $ATag2_e) . '</td>
						<td nowrap="nowrap">&nbsp;' . $pDim . '</td>
					</tr>';
				$lines[] = '
					<tr>
						<td class="filelistThumbnail" colspan="4">' . $ATag_alt . $clickIcon . $ATag_e . '</td>
					</tr>';
			} else {
				$lines[] = '
					<tr class="file_list_normal">
						<td nowrap="nowrap">' . $filenameAndIcon . '&nbsp;</td>
						<td>' . ($ATag . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/plusbullet2.gif',
							'width="18" height="16"') . ' title="' . $GLOBALS['LANG']->getLL('addToList', TRUE)
							. '" alt="" />' . $ATag_e) . '</td>
						<td nowrap="nowrap">' . ($ATag2 . '<img' . IconUtility::skinImg($GLOBALS['BACK_PATH'],
							'gfx/zoom2.gif', 'width="12" height="12"') . ' title="'
							. $GLOBALS['LANG']->getLL('info', TRUE) . '" alt="" /> '
						. $GLOBALS['LANG']->getLL('info', TRUE) . $ATag2_e) . '</td>
						<td>&nbsp;</td>
					</tr>';
			}
		}
		// Wrap all the rows in table tags:
		$out .= '

	<!--
		File listing
	-->
			<table cellpadding="0" cellspacing="0" id="typo3-filelist">
				' . implode('', $lines) . '
			</table>';
		// Return accumulated content for file listing:
		$out .= '</div>';
		return $out;
	}
}
