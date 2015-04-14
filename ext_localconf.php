<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Filelist\\FileList'] = array(
	'className' => 'Cabag\\Falsearch\\Xclass\\FileList'
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Recordlist\\Browser\\ElementBrowser'] = array(
	'className' => 'Cabag\\Falsearch\\Xclass\\ElementBrowser'
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Rtehtmlarea\\BrowseLinks'] = array(
	'className' => 'Cabag\\Falsearch\\Xclass\\BrowseLinks'
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Folder'] = array(
	'className' => 'Cabag\\Falsearch\\Xclass\\Folder'
);
