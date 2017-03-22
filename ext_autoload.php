<?php

$extensionClassesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('falsearch') . 'Classes/';
return array(
	'cabag\\falsearch\\xclass\\filelist' => $extensionClassesPath . 'Xclass/FileList.php',
	'cabag\\falsearch\\xclass\\browselinks' => $extensionClassesPath . 'Xclass/BrowseLinks.php',
	'cabag\\falsearch\\xclass\\elementbrowser' => $extensionClassesPath . 'Xclass/ElementBrowser.php',
	'cabag\\falsearch\\xclass\\folder' => $extensionClassesPath . 'Xclass/Folder.php',
	'cabag\\falsearch\\utility\\categoryutility' => $extensionClassesPath . 'Utility/CategoryUtility.php',
	'cabag\\falsearch\\controller\\elementinformationcontroller' => $extensionClassesPath . 'Controller/ElementInformationController.php',
);
