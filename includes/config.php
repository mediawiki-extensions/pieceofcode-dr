<?php
/**
 * @file config.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-08-28
 */

/**
 * Arrays.
 */
if(!isset($wgPieceOfCodeSVNConnections)) {
	$wgPieceOfCodeSVNConnections = array();
}

/**
 * Configuration.
 * @{
 */
if(!isset($wgPieceOfCodeConfig) || !is_array($wgPieceOfCodeConfig)) {
	$wgPieceOfCodeConfig = array();
}
/**
 * Allows to enable/disable internal CSS-file inclution.
 * @var bool
 */
$wgPieceOfCodeConfig['autocss']	= true;
/**
 * Subversion.
 * 	@{
 */
/**
 * Specifies SVN command full-path.
 * @var string
 */
$wgPieceOfCodeConfig['svn-binary']	= '/usr/bin/svn';
/**	@} */
/**
 * Uploading codes.
 * 	@{
 */
$wgPieceOfCodeConfig['uploaddirectory']	= $wgUploadDirectory.DIRECTORY_SEPARATOR.'PieceOfCode';
$wgPieceOfCodeConfig['enableuploads']	= $wgEnableUploads;
/** 	@} */
/**
 * Database.
 * 	@{
 */
$wgPieceOfCodeConfig['db-tablename']	= 'poc_codes';
/** 	@} */
/**
 * Font-code types.
 * 	@{
 */
$wgPieceOfCodeConfig['fontcodes']		= array();
$wgPieceOfCodeConfig['fontcodes']['bash']	= array('sh', 'mk', 'mak');
$wgPieceOfCodeConfig['fontcodes']['cpp']	= array('cpp', 'h', 'hpp', 'c');
$wgPieceOfCodeConfig['fontcodes']['php']	= array('php', 'inc', 'php3');
$wgPieceOfCodeConfig['fontcodes']['text']	= array('txt', 'log');
$wgPieceOfCodeConfig['fontcodes']['sql']	= array('sql');
$wgPieceOfCodeConfig['fontcodes']['xml']	= array('xml', 'xsl', 'xslt', 'mxml');
$wgPieceOfCodeConfig['fontcodes']['java']	= array('java');
$wgPieceOfCodeConfig['fontcodes']['dos']	= array('bat');
$wgPieceOfCodeConfig['fontcodes']['asm']	= array('asm');
$wgPieceOfCodeConfig['fontcodes']['html4strict']= array('html', 'htm');
$wgPieceOfCodeConfig['fontcodes']['python']	= array('py');
$wgPieceOfCodeConfig['fontcodes-forbidden']	= array('exe', 'so', 'zip', 'rar', '7z', 'gz', 'jar', 'class', 'a', 'o', 'tar', 'bz2');
$wgPieceOfCodeConfig['fontcodes-allowempty']	= false;
/** 	@} */
/**
 * @todo doc.
 * 	@{
 */
$wgPieceOfCodeConfig['showinstalldir']		= true;
/** 	@} */
/**
 * Others.
 * 	@{
 */
$wgPieceOfCodeConfig['maxsize']			= array();
$wgPieceOfCodeConfig['maxsize']['highlighting']	= 30720;	// 30KB
$wgPieceOfCodeConfig['maxsize']['showing']	= 51200;	// 50KB
/** 	@} */
/** @} */
?>
