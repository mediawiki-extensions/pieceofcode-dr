<?php
/**
 * @file PieceOfCode-dr.i18n.php
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

$messages = array();
$messages['en'] = array(
	'pieceofcode'		=> 'PieceOfCode',
	'poc-anonymous'		=> 'anonymous',
	'poc-disabled'		=> 'disabled',
	'poc-enabled'		=> 'enabled',
	'poc-not-present'	=> 'not present',
	'poc-open'		=> 'open',
	'poc-pieceofcode-desc'	=> 'This extension allows to connect several SVN repositories, import font-codes from them and paste pieces of those codes inside articles.<br/>Provides <tt>&lt;pieceofcode&gt;</tt> and <tt>&lt;/pieceofcode&gt;</tt> tags.<sup>[[Special:PieceOfCode|more]]</sup>',
	'poc-present'		=> 'present',

	'poc-errmsg-empty-tcode'	=> 'Files without extension are not allowed',
	'poc-errmsg-forbidden-tcode'	=> 'Forbidden font-code type (extension: *.$1)',
	'poc-errmsg-large-highlight'	=> 'Specific highlightning disable. Code is larger than $1 bytes',
	'poc-errmsg-large-show'		=> 'Preview was truncated. Code is larger than $1 bytes',
	'poc-errmsg-large-showall'	=> 'Preview without styles. Code is larger than $1 bytes',
	'poc-errmsg-invalid-connection'	=> 'Invalid connection',
	'poc-errmsg-no-connection'	=> 'No connection specified',
	'poc-errmsg-no-fileinfo'	=> 'Unable to retrieve file information ($1>$2:$3)',
	'poc-errmsg-no-insert'		=> 'Unable to perform the INSERT on database',
	'poc-errmsg-no-svn-file'	=> 'Unable to retrieve SVN file ($1>$2:$3)',
	'poc-errmsg-no-upload-rights'	=> 'Current user is not allowed to upload',
	'poc-errmsg-svn-no-file'	=> 'File does not exists (path: \'$1\')',
	'poc-errmsg-svn-file-exist'	=> 'This file already exists on the system (path: \'$1\')',
	'poc-errmsg-unknown-dbtype'	=> 'Unknown database type \'$1\'',
	'poc-errmsg-unknown-tcode'	=> 'Unknown font-code type (extension: *.$1)',

	'poc-sinfo-installation-directory'	=> 'Installation Directory',
	'poc-sinfo-author'			=> 'Author',
	'poc-sinfo-description'			=> 'Description',
	'poc-sinfo-extension-information'	=> 'Extension Information',
	'poc-sinfo-links'			=> 'Links',
	'poc-sinfo-name'			=> 'Name',
	'poc-sinfo-pieceofcode-desc'		=> 'PieceOfCode special page. Visit [[Special:PieceOfCode]].',
	'poc-sinfo-stored-codes-code'		=> 'Code',
	'poc-sinfo-stored-codes-conn'		=> 'Connection',
	'poc-sinfo-stored-codes-date'		=> 'Date',
	'poc-sinfo-stored-codes-lang'		=> 'Language',
	'poc-sinfo-stored-codes-path'		=> 'Path',
	'poc-sinfo-stored-codes-rev'		=> 'Revision',
	'poc-sinfo-stored-codes'		=> 'Stored Codes',
	'poc-sinfo-stored-codes-user'		=> 'User',
	'poc-sinfo-svn-connections'		=> 'SVN Connections',
	'poc-sinfo-svnconn-password'		=> 'Password',
	'poc-sinfo-svnconn-url'			=> 'URL',
	'poc-sinfo-svnconn-username'		=> 'Username',
	'poc-sinfo-svn-date'			=> 'Last changed date',
	'poc-sinfo-svn-revision'		=> 'Last changed revision',
	'poc-sinfo-svn'				=> 'Subversion',
	'poc-sinfo-url'				=> 'URL',
	'poc-sinfo-version'			=> 'Version',
);
$messages['es'] = array(
	'pieceofcode'		=> 'PieceOfCode',
	'poc-anonymous'		=> 'anónimo',
	'poc-disabled'		=> 'deshabilitado',
	'poc-enabled'		=> 'habilitado',
	'poc-not-present'	=> 'ausente',
	'poc-open'		=> 'abrir',
	'poc-pieceofcode-desc'	=> 'Esta extensión permite conectar varios repositorios SVN, importar fuentes desde ellos y pegar porciones de esos fuentes dentro de los artículos.<br/>Provee las etiquetas <tt>&lt;pieceofcode&gt;</tt> y <tt>&lt;/pieceofcode&gt;</tt>.<sup>[[Special:PieceOfCode|más]]</sup>',
	'poc-present'		=> 'presente',

	'poc-errmsg-empty-tcode'	=> 'No se permiten archivos sin extensión',
	'poc-errmsg-forbidden-tcode'	=> 'Tipo de código fuente no permitido (extensión: *.$1)',
	'poc-errmsg-invalid-connection'	=> 'Connexion inválida',
	'poc-errmsg-no-connection'	=> 'No se especificó la connexion',
	'poc-errmsg-no-fileinfo'	=> 'No se puede obtener la información del archivo ($1>$2:$3)',
	'poc-errmsg-svn-no-file'	=> 'El archivo no existe (ruta: \'$1\')',
	'poc-errmsg-no-upload-rights'	=> 'El usuario actual no tiene pemisos de subida',
	'poc-errmsg-no-insert'		=> 'No se puede realizar el INSERT en la base de datos',
	'poc-errmsg-no-svn-file'	=> 'No se puede obtener el archivo de SVN ($1>$2:$3)',
	'poc-errmsg-large-highlight'	=> 'Coloreado específico deshabilitado. El código supera los $1 bytes',
	'poc-errmsg-large-showall'	=> 'Previsualización sin formatos. El código supera los $1 bytes',
	'poc-errmsg-large-show'		=> 'Previsualización truncada. El código super los $1 bytes',
	'poc-errmsg-svn-file-exist'	=> 'Este archivo ya se encuentra presente en el sistema (ruta: \'$1\')',
	'poc-errmsg-svn-file-exist'	=> 'Este archivo ya se encuentra presente en el sistema (ruta: \'$1\')',
	'poc-errmsg-unknown-dbtype'	=> 'Tipo de base de datos \'$1\' desconocido',
	'poc-errmsg-unknown-tcode'	=> 'Tipo de código fuente desconocido (extensión: *.$1)',

	'poc-sinfo-author'			=> 'Autor',
	'poc-sinfo-installation-directory'	=> 'Directorio de Instalación',
	'poc-sinfo-description'			=> 'Descripción',
	'poc-sinfo-extension-information'	=> 'Información de la Extensión',
	'poc-sinfo-links'			=> 'Enlaces',
	'poc-sinfo-name'			=> 'Nombre',
	'poc-sinfo-pieceofcode-desc'		=> 'Página especial de PieceOfCode. Visite [[Special:PieceOfCode]].',
	'poc-sinfo-stored-codes-code'		=> 'Código',
	'poc-sinfo-stored-codes-conn'		=> 'Connexion',
	'poc-sinfo-stored-codes-date'		=> 'Fecha',
	'poc-sinfo-stored-codes'		=> 'Fuentes Almacenados',
	'poc-sinfo-stored-codes-lang'		=> 'Lenguaje',
	'poc-sinfo-stored-codes-path'		=> 'Ruta',
	'poc-sinfo-stored-codes-rev'		=> 'Revisión',
	'poc-sinfo-stored-codes-user'		=> 'Usuario',
	'poc-sinfo-svn-connections'		=> 'Connexiones a SVN',
	'poc-sinfo-svnconn-password'		=> 'Contraseña',
	'poc-sinfo-svnconn-url'			=> 'URL',
	'poc-sinfo-svnconn-username'		=> 'Usuario',
	'poc-sinfo-svn-date'			=> 'Última fecha de cambio',
	'poc-sinfo-svn-revision'		=> 'Última número de revisión',
	'poc-sinfo-svn'				=> 'Subversion',
	'poc-sinfo-url'				=> 'URL',
	'poc-sinfo-version'			=> 'Versión',
);

?>