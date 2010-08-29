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
$wgPieceOfCodeConfig['svn-binary']	= '/usr/bin/svn';
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
/** @} */
?>
