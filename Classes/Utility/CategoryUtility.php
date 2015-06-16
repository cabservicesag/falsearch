<?php
namespace Cabag\Falsearch\Utility;

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
 * Utility class to help with search by the categories.
 *
 * @author Nils Blattner <nb@cabag.ch>
 */
class CategoryUtility implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * The category tree.
	 * @var array
	 */
	protected $tree = false;
	
	/**
	 * The category index.
	 * @var array
	 */
	protected $index = array();
	
	/**
	 * Returns a select box with all the sys_categories included.
	 * 
	 * @param array $attributes An array of name => value attributes to be rendered into the <select>.
	 * @param integer $selected The selected sys_category uid.
	 * @return string The rendered select box.
	 */
	public function getCategorySelect(array $attributes = array(), $selected = 0) {
		$tree = $this->getCategoryTree();
		
		$content = '<select';
		if (count($attributes) > 0) {
			foreach ($attributes as $key => $value) {
				$content .= ' ' . preg_replace('/[^a-z0-9_\-]/i', '', $key) . '="' . htmlspecialchars($value) . '"';
			}
		}
		$content .= '><option value="0">' . $GLOBALS['LANG']->sL('LLL:EXT:falsearch/Resources/Private/Language/locallang.xlf:allCategories') . '</option>';
		
		foreach ($tree as $child) {
			$content .= $this->getCategoryOption($child, $selected);
		}
		
		return $content . '</select>&nbsp;';
	}
	
	/**
	 * Render the category options.
	 *
	 * @param array $node The current node to render.
	 * @param int $selected The uid of the currently selected category.
	 * @return string The rendered option tag.
	 */
	protected function getCategoryOption($node, $selected) {
		$content = '<option value="' . $node['uid'] . '"' . ($selected == $node['uid'] ? ' selected="selected"' : '') . '>' . $node['title'] . '</option>';
		
		if (is_array($node['children']) && count($node['children']) > 0) {
			$content .= '<optgroup>';
			foreach ($node['children'] as $child) {
				$content .= $this->getCategoryOption($child, $selected);
			}
			$content .= '</optgroup>';
		}
		
		return $content;
	}
	
	/**
	 * Returns the full category tree.
	 *
	 * @return array The category tree, where each row contains 'children' with the array of direct children.
	 */
	public function getCategoryTree() {
		if ($this->tree === false) {
			$this->tree = array();
			
			$resource = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_category', 'sys_language_uid = 0 ' . \TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause('sys_category'), '', 'title ASC');
			
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) {
				if (isset($this->index[$row['uid']]) && is_array($this->index[$row['uid']]) && isset($this->index[$row['uid']]['children'])) {
					$row['children'] = &$this->index[$row['uid']]['children'];
				} else {
					$row['children'] = array();
				}
				$this->index[$row['uid']] = &$row;
				$row['parent'] = intval($row['parent']);
				
				if ($row['parent'] > 0) {
					$this->index[$row['parent']]['children'][$row['uid']] = &$row;
				} else {
					$this->tree[$row['uid']] = &$row;
				}
				unset($row);
			}
		}
		
		return $this->tree;
	}
	
	/**
	 * Return an array with all the categories that have the given uid as parent (including the current uid).
	 *
	 * @param int $category The category uid.
	 * @return array The list of contained categories.
	 */
	public function getIndexForCategory($category, &$index = array()) {
		$category = intval($category);
		
		if (isset($this->index[$category]) && !isset($index[$category])) {
			$index[$category] = &$this->index[$category];
			
			foreach ($index[$category]['children'] as &$child) {
				$this->getIndexForCategory($child['uid'], $index);
			}
		}
		
		return $index;
	}
}
