<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "falsearch".
 *
 * Auto generated 02-03-2015 11:12
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'CAB FAL search',
	'description' => 'Adds a file search box that allows the user to search through the files/folders recursively.',
	'category' => 'be',
	'version' => '0.2.0',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearcacheonload' => 1,
	'author' => 'Nils Blattner, Tizian Schmidlin, Lavinia Negru',
	'author_email' => 'nb@cabag.ch, st@cabag.ch, ln@cabag.ch',
	'author_company' => '',
	'constraints' =>
	array (
		'depends' =>
		array (
			'typo3' => '7.6.0-7.6.99',
		),
		'conflicts' =>
		array (
		),
		'suggests' =>
		array (
		),
	),
	'_md5_values_when_last_written' => 'a:52:{s:9:"ChangeLog";s:4:"6353";s:35:"class.tx_falsearch_searchfiles.php";s:4:"da5a";s:28:"class.ux_tx_dam_tce_file.php";s:4:"11f8";s:21:"ext_conf_template.txt";s:4:"61fd";s:12:"ext_icon.gif";s:4:"e35d";s:17:"ext_localconf.php";s:4:"667f";s:13:"locallang.xml";s:4:"3a7f";s:10:"README.txt";s:4:"e5da";s:19:"doc/wizard_form.dat";s:4:"eca1";s:20:"doc/wizard_form.html";s:4:"1390";s:46:"hooks/class.tx_falsearch_searchfiles_find.php";s:4:"c3d3";s:49:"hooks/class.tx_falsearch_searchfiles_scandir.php";s:4:"92a5";s:46:"typo3_versions/4.5.0/class.ux_browse_links.php";s:4:"1e19";s:43:"typo3_versions/4.5.0/class.ux_file_list.php";s:4:"dcd9";s:51:"typo3_versions/4.5.0/class.ux_t3lib_extfilefunc.php";s:4:"ceef";s:61:"typo3_versions/4.5.0/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"d30f";s:40:"typo3_versions/4.5.0/ux_SC_file_list.php";s:4:"3a50";s:46:"typo3_versions/4.5.1/class.ux_browse_links.php";s:4:"1e19";s:43:"typo3_versions/4.5.1/class.ux_file_list.php";s:4:"dcd9";s:51:"typo3_versions/4.5.1/class.ux_t3lib_extfilefunc.php";s:4:"ceef";s:61:"typo3_versions/4.5.1/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"d30f";s:40:"typo3_versions/4.5.1/ux_SC_file_list.php";s:4:"3a50";s:47:"typo3_versions/4.5.16/class.ux_browse_links.php";s:4:"e1b0";s:44:"typo3_versions/4.5.16/class.ux_file_list.php";s:4:"6321";s:52:"typo3_versions/4.5.16/class.ux_t3lib_extfilefunc.php";s:4:"ae4a";s:62:"typo3_versions/4.5.16/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"6fd8";s:41:"typo3_versions/4.5.16/ux_SC_file_list.php";s:4:"7b47";s:46:"typo3_versions/4.5.2/class.ux_browse_links.php";s:4:"1e19";s:43:"typo3_versions/4.5.2/class.ux_file_list.php";s:4:"6321";s:51:"typo3_versions/4.5.2/class.ux_t3lib_extfilefunc.php";s:4:"ceef";s:61:"typo3_versions/4.5.2/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"d30f";s:40:"typo3_versions/4.5.2/ux_SC_file_list.php";s:4:"3a50";s:47:"typo3_versions/4.5.26/class.ux_browse_links.php";s:4:"e1b0";s:44:"typo3_versions/4.5.26/class.ux_file_list.php";s:4:"6321";s:52:"typo3_versions/4.5.26/class.ux_t3lib_extfilefunc.php";s:4:"3059";s:62:"typo3_versions/4.5.26/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"6fd8";s:41:"typo3_versions/4.5.26/ux_SC_file_list.php";s:4:"7b47";s:47:"typo3_versions/4.5.27/class.ux_browse_links.php";s:4:"e1b0";s:44:"typo3_versions/4.5.27/class.ux_file_list.php";s:4:"6321";s:52:"typo3_versions/4.5.27/class.ux_t3lib_extfilefunc.php";s:4:"3059";s:62:"typo3_versions/4.5.27/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"6fd8";s:41:"typo3_versions/4.5.27/ux_SC_file_list.php";s:4:"7b47";s:43:"typo3_versions/4.5.27/ux_SC_file_upload.php";s:4:"8f82";s:46:"typo3_versions/4.5.8/class.ux_browse_links.php";s:4:"1e19";s:43:"typo3_versions/4.5.8/class.ux_file_list.php";s:4:"6321";s:51:"typo3_versions/4.5.8/class.ux_t3lib_extfilefunc.php";s:4:"09bb";s:61:"typo3_versions/4.5.8/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"d30f";s:40:"typo3_versions/4.5.8/ux_SC_file_list.php";s:4:"2e31";s:44:"typo3_versions/old/class.ux_browse_links.php";s:4:"8211";s:41:"typo3_versions/old/class.ux_file_list.php";s:4:"ccd9";s:49:"typo3_versions/old/class.ux_t3lib_extfilefunc.php";s:4:"2e26";s:59:"typo3_versions/old/class.ux_tx_rtehtmlarea_browse_links.php";s:4:"4494";}',
);
