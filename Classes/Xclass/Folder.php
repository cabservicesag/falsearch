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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A folder that allows searching by name and category.
 *
 * @author Nils Blattner <nb@cabag.ch>
 */
class Folder extends \TYPO3\CMS\Core\Resource\Folder {
	/**
	 * Override the recursion when getting files.
	 * @var boolean
	 */
	protected $overrideRecursion = false;

	/**
	 * The search words.
	 * @var string
	 */
	protected $searchWords = '';

	/**
	 * The search category.
	 * @var integer
	 */
	protected $searchCategory = 0;

	/**
	 * Returns whether the recursion should be overwritten to true.
	 *
	 * @return boolean
	 */
	public function getOverrideRecursion() {
		return $this->overrideRecursion;
	}

	/**
	 * Sets whether the recursion should be overwritten to true.
	 *
	 * @param boolean $overrideRecursion Whether or not the recursion should be set to true in getFiles().
	 */
	public function setOverrideRecursion($overrideRecursion) {
		$this->overrideRecursion = !!$overrideRecursion;
	}

	/**
	 * Returns the search words.
	 *
	 * @return string
	 */
	public function getSearchWords() {
		return $this->searchWords;
	}

	/**
	 * Sets the search words.
	 *
	 * @param string $searchWords The search words.
	 */
	public function setSearchWords($searchWords) {
		$this->searchWords = trim($searchWords);
	}

	/**
	 * Returns the search category.
	 *
	 * @return integer
	 */
	public function getSearchCategory() {
		return $this->searchCategory;
	}

	/**
	 * Sets the search category.
	 *
	 * @param integer $searchCategory The search category.
	 */
	public function setSearchCategory($searchCategory) {
		$this->searchCategory = intval($searchCategory);
	}

	/**
	 * Returns a list of files in this folder, optionally filtered. There are several filter modes available, see the
	 * FILTER_MODE_* constants for more information.
	 *
	 * For performance reasons the returned items can also be limited to a given range
	 *
	 * @param integer $start The item to start at
	 * @param integer $numberOfItems The number of items to return
	 * @param integer $filterMode The filter mode to use for the file list.
	 * @param boolean $recursive
	 * @param string $sort Property name used to sort the items.
	 *                     Among them may be: '' (empty, no sorting), name,
	 *                     fileext, size, tstamp and rw.
	 *                     If a driver does not support the given property, it
	 *                     should fall back to "name".
	 * @param bool $sortRev TRUE to indicate reverse sorting (last to first)
	 * @return \TYPO3\CMS\Core\Resource\File[]
	 */
	public function getFiles($start = 0, $numberOfItems = 0, $filterMode = self::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS, $recursive = FALSE, $sort = '', $sortRev = false) {
		if ($this->overrideRecursion) {
			$files = array();

			$resourceFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
			$driver = $resourceFactory->getDriverObject($this->getStorage()->getDriverType(), $this->getStorage()->getConfiguration());

			// Fallback for compatibility with the old method signature variable $useFilters that was used instead of $filterMode
			if ($filterMode === FALSE) {
				$useFilters = FALSE;
				$filters = array();
			} else {
				$useFilters = TRUE;
				$filters = $this->storage->getFileAndFolderNameFilters();
				foreach ($this->fileAndFolderNameFilters as $filter) {
					$filters[] = $filter;
				}
			}

			$words = $GLOBALS['TYPO3_DB']->fullQuoteArray(array_map(function ($value) { return '%' . $value . '%'; }, GeneralUtility::trimExplode(' ', $this->searchWords, true)), 'sys_file');
			$wordsMatch = '';
			if (count($words)) {
				$wordsMatch = ' AND (sys_file.name LIKE ' . implode(' AND sys_file.name LIKE ', $words) . ')';
			}

			$categoryUtility = GeneralUtility::makeInstance('Cabag\\Falsearch\\Utility\\CategoryUtility');
			$categories = array();
			if ($this->searchCategory > 0) {
				$categories = array_keys($categoryUtility->getIndexForCategory($this->searchCategory));
			}

			$resource = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'sys_file.*',
				'sys_file' . (count($categories) > 0 ? ' LEFT JOIN sys_file_metadata ON sys_file_metadata.file = sys_file.uid LEFT JOIN sys_category_record_mm ON sys_file_metadata.uid = sys_category_record_mm.uid_foreign' : ''),
				'sys_file.missing = 0 AND sys_file.storage = ' . $this->getStorage()->getUid() . ' AND sys_file.identifier LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getIdentifier() . '%', 'sys_file') . $wordsMatch . (count($categories) > 0 ? ' AND sys_category_record_mm.tablenames = \'sys_file_metadata\' AND sys_category_record_mm.fieldname = \'categories\' AND sys_category_record_mm.uid_local IN (' . implode(',', $categories) . ')' : ''),
				(count($categories) > 0 ? 'sys_file.uid' : ''),
				'sys_file.identifier ASC'
			);

			$indexer = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Index\\Indexer', $this->getStorage());
			$count = 0;
			$resultCount = 0;
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) {
				$ok = $useFilters && $this->applyFilterMethodsToDirectoryItem($filters, $row['name'], $row['identifier'], $driver);
				if (!$ok) {
					$badFilters++;
					continue;
				}

				if ($start <= $count) {
					if ($numberOfItems !== 0 && $numberOfItems >= $resultCount) {
						break;
					}
					$file = $resourceFactory->getFileObject($row['uid'], $row);

					// make sure the missing information is up to date
					try {
						$indexer->updateIndexEntry($file);
					} catch (\Exception $e) {
						// file missing
						$file->setMissing(true);
						\TYPO3\CMS\Core\Resource\Index\FileIndexRepository::getInstance()->markFileAsMissing($file->getUid());
					}
					if ($file->isMissing()) {
						continue;
					}

					$files[] = $file;
					$resultCount++;
				}
				$count++;
			}

			return $files;
		}
		return parent::getFiles($start, $numberOfItems, $filterMode, $recursive, $sort, $sortRev);
	}

	/**
	 * Applies a set of filter methods to a file name to find out if it should be used or not. This is e.g. used by
	 * directory listings.
	 *
	 * @param array $filterMethods The filter methods to use
	 * @param string $itemName
	 * @param string $itemIdentifier
	 * @param string $parentIdentifier
	 * @throws \RuntimeException
	 * @return boolean
	 */
	protected function applyFilterMethodsToDirectoryItem(array $filterMethods, $itemName, $itemIdentifier, $driver) {
		foreach ($filterMethods as $filter) {
			if (is_array($filter)) {
				$result = call_user_func($filter, $itemName, $itemIdentifier, \TYPO3\CMS\Core\Utility\PathUtility::dirname($identifier) . '/', array(), $driver);
				// We have to use -1 as the „don't include“ return value, as call_user_func() will return FALSE
				// If calling the method succeeded and thus we can't use that as a return value.
				if ($result === -1) {
					return FALSE;
				} elseif ($result === FALSE) {
					continue;
				}
			}
		}
		return TRUE;
	}

	/**
	 * Returns the full path of this folder, from the root.
	 *
	 * This method only overrides the method if TYPO3 is < 6.2.22
	 *
	 * @param string $rootId ID of the root folder, NULL to auto-detect
	 *
	 * @return string
	 */
	public function getReadablePath($rootId = NULL) {
		if(version_compare(TYPO3_version, '6.2.22', '<')) {
			if ($rootId === NULL) {
				// Find first matching filemount and use that as root
				foreach ($this->storage->getFileMounts() as $fileMount) {
					if ($this->storage->isWithinFolder($fileMount['folder'], $this)) {
						$rootId = $fileMount['folder']->getIdentifier();
						break;
					}
				}
				if ($rootId === null) {
					$rootId = $this->storage->getRootLevelFolder()->getIdentifier();
				}
			}
			$readablePath = '/';
			if ($this->identifier !== $rootId) {
				try {
					$readablePath = $this->getParentFolder()->getReadablePath($rootId);
				} catch (\TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException $e) {
					// May no access to parent folder (e.g. because of mount point)
					$readablePath = '/';
				}
			}
			return $readablePath . $this->name . '/';
		} else {
			return parent::getReadablePath($rootId);
		}
	}
}
