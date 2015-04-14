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

/**
 * A folder that groups files in a storage. This may be a folder on the local
 * disk, a bucket in Amazon S3 or a user or a tag in Flickr.
 *
 * This object is not persisted in TYPO3 locally, but created on the fly by
 * storage drivers for the folders they "offer".
 *
 * Some folders serve as a physical container for files (e.g. folders on the
 * local disk, S3 buckets or Flickr users). Other folders just group files by a
 * certain criterion, e.g. a tag.
 * The way this is implemented depends on the storage driver.
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @author Ingmar Schlecht <ingmar@typo3.org>
 */
class Folder extends \TYPO3\CMS\Core\Resource\Folder {
	/**
	 * Override the recursion when getting files.
	 * @var boolean
	 */
	protected $overrideRecursion = false;

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
	 * Returns a list of files in this folder, optionally filtered. There are several filter modes available, see the
	 * FILTER_MODE_* constants for more information.
	 *
	 * For performance reasons the returned items can also be limited to a given range
	 *
	 * @param integer $start The item to start at
	 * @param integer $numberOfItems The number of items to return
	 * @param integer $filterMode The filter mode to use for the file list.
	 * @param boolean $recursive
	 * @return \TYPO3\CMS\Core\Resource\File[]
	 */
	public function getFiles($start = 0, $numberOfItems = 0, $filterMode = self::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS, $recursive = FALSE) {
		if ($this->overrideRecursion) {
			$files = array();
			
			foreach (parent::getFiles($start, $numberOfItems, $filterMode, true) as $file) {
				if (!($file instanceof \TYPO3\CMS\Core\Resource\ProcessedFile)) {
					$files[] = $file;
				}
			}
			return $files;
		}
		return parent::getFiles($start, $numberOfItems, $filterMode, $recursive);
	}
}
