<?php
/**
 * @file PieceOfCode-dr.body.php
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
 * @class PieceOfCode
 */
class PieceOfCode extends SpecialPage {
	/**
	 * Singleton instance holder.
	 * @var PieceOfCode
	 */
	private static	$_Instance;
	/**
	 * Extension properties holder.
	 * @var array
	 */
	protected static	$_Properties = array(
						'name'                 => 'PieceOfCode',
						'version'              => '0.1',
						'date'                 => '2010-08-28',
						'_description'         => "PieceOfCode.",
						'description'          => "PieceOfCode.<sup>[[Special:PieceOfCode|more]]</sup>",
						'descriptionmsg'       => 'poc-pieceofcode-desc',
						'sinfo-description'    => "PieceOfCode",
						'sinfo-descriptionmsg' => 'poc-sinfo-pieceofcode-desc',
						'author'               => array('Alejandro Darío Simi'),
						'url'                  => 'http://wiki.daemonraco.com/wiki/PieceOfCode-dr',
						'svn-date'             => '$LastChangedDate$',
						'svn-revision'         => '$LastChangedRevision$',
	);
	/**
	 * @var POCErrorsHolder
	 */
	protected	$_errors;
	/**
	 * Error messages prefix.
	 * @var string
	 */
	protected	$ERROR_PREFIX = 'DR_PieceOfCode Error: ';
	/**
	 * @var string
	 */
	protected	$_lastError;
	/**
	 * @var POCStoredCodes
	 */
	protected	$_storedCodes;
	/**
	 * @var POCSVNConnections
	 */
	protected	$_svnConnections;
	/**
	 * List of default values for several variables.
	 * @var array
	 */
	protected	$_varDefaults = array(
		'file'		=> '',		//!< 
		'revision'	=> '',		//!<
		'connection'	=> '',		//!<
		'lines'		=> '',		//!<
	);

	protected function __construct() {
		parent::__construct('PieceOfCode');

		/*
		 * Loading messages.
		 */
		wfLoadExtensionMessages('PieceOfCode');

		/*
		 * Setting tag-kooks.
		 */
		if(defined('MEDIAWIKI')) {
			global	$wgParser;

			$wgParser->setHook('pieceofcode', array(&$this, 'parse'));
		}

		$this->_errors         = POCErrorsHolder::Instance();
		$this->_svnConnections = POCSVNConnections::Instance();
		$this->_storedCodes    = POCStoredCodes::Instance();

		$this->_errors->clearError();
	}
	/**
	 * Prevent users to clone the instance.
	 */
	public function __clone() {
		trigger_error(__CLASS__.': Clone is not allowed.', E_USER_ERROR);
	}
	/*
	 * Public Methods.
	 */
	/**
	 * Inherited method. Please check parent class 'SpecialPage'.
	 */
	public function execute($par) {
		$out = "";

		global	$wgRequest;
		global	$wgOut;
		global	$wgPieceOfCodeSVNConnections;
		global	$wgAllowExternalImages;
		global	$wgPieceOfCodeConfig;
		global	$wgEnableUploads;
		global	$wgPieceOfCodeExtensionWebDir;

		$this->setHeaders();

		/*
		 * Get request data from, e.g.
		 */
		$param    = $wgRequest->getText('param');

		if($wgEnableUploads) {
			$fontcode = array(
				'connection'	=> $wgRequest->getVal('connection', null),
				'path'		=> $wgRequest->getVal('path', null),
				'revision'	=> $wgRequest->getVal('revision', null),
			);
			$fontcode['showit'] = ($fontcode['connection'] !== null && $fontcode['path'] !== null && $fontcode['revision'] !== null);

			if($fontcode['showit']) {
				$this->showFontCode($fontcode);
			} else {
				$this->basicInformation();
			}
		} else {
			$this->basicInformation();
		}
	}
	/**
	 * @todo doc
	 * @param string $input @todo doc
	 * @param array $params @todo doc
	 * @param Parser $parser @todo doc
	 */
	public function parse($input, $params, $parser) {
		/*
		 * This variable will hold the content to be retorned. Eighter
		 * some formatted XML text or an error message.
		 */
		$out = "";
		$codeExtractor = new POCCodeExtractor();

		$this->_errors->clearError();
		$out.= $codeExtractor->load($input, $params, $parser);
		if(!$this->_errors->getLastError()) {
			$out.= $codeExtractor->show();
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param unknown_type $name @todo doc
	 */
	public function varDefault($name) {
		return (isset($this->_varDefaults[$name])?$this->_varDefaults[$name]:'');
	}

	/*
	 * Protected Methods.
	 */
	/**
	 * Prints basic information on Special:PieceOfCode
	 */
	protected function basicInformation() {
		global	$wgOut;
		global	$wgPieceOfCodeSVNConnections;
		global	$wgPieceOfCodeConfig;
		global	$wgPieceOfCodeExtensionSysDir;
		global	$wgPieceOfCodeExtensionWebDir;

		$out.= "\t\t<span style=\"float:right;text-align:center;\"><img src=\"http://wiki.daemonraco.com/wiki/dr.png\"/><br/><a href=\"http://wiki.daemonraco.com/\">DAEMonRaco</a></span>";
		$i = 0;
		$out.= "\t\t<table id=\"toc\" class=\"toc\" summary=\"Contents\"><tbody><tr><td><div id=\"toctitle\"><h2>Contents</h2> <span class=\"toctoggle\">[<a id=\"togglelink\" class=\"internal\" href=\"javascript:toggleToc()\">hide</a>]</span></div>\n";
		$out.= "\t\t\t<ul>\n";
		$out.= "\t\t\t\t<li class=\"toclevel-1\"><a href=\"#poc-sinfo-extension-information\"><span class=\"tocnumber\">".(++$i)."</span> <span class=\"toctext\">".wfMsg('poc-sinfo-extension-information')."</span></a></li>\n";
		$out.= "\t\t\t\t<li class=\"toclevel-1\"><a href=\"#poc-sinfo-svn-connections\"><span class=\"tocnumber\">".(++$i)."</span> <span class=\"toctext\">".wfMsg('poc-sinfo-svn-connections')."</span></a></li>\n";
		$out.= "\t\t\t\t<li class=\"toclevel-1\"><a href=\"#poc-sinfo-stored-codes\"><span class=\"tocnumber\">".(++$i)."</span> <span class=\"toctext\">".wfMsg('poc-sinfo-stored-codes')."</span></a></li>\n";
		$out.= "\t\t\t\t<li class=\"toclevel-1\"><a href=\"#poc-sinfo-links\"><span class=\"tocnumber\">".(++$i)."</span> <span class=\"toctext\">".wfMsg('poc-sinfo-links')."</span></a></li>\n";
		$out.= "\t\t\t</ul>\n";
		$out.= "\t\t</td></tr></tbody></table>\n";
		/*
		 * Section: Extension information.
		 * @{
		 */
		$out.= "\t\t<a name=\"poc-sinfo-extension-information\"></a><h2><span class=\"mw-headline\">".wfMsg('poc-sinfo-extension-information')."</span></h2>\n";
		$out.= "\t\t<ul>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-name').":</strong> ".PieceOfCode::Property('name')."</li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-version').":</strong> ".PieceOfCode::Property('version')."</li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-description').":</strong> ".PieceOfCode::Property('_description')."</li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-author').":</strong><ul>\n";
		foreach(PieceOfCode::Property('author') as $author) {
			$out.= "\t\t\t\t<li>{$author}</li>\n";
		}
		$out.= "\t\t\t</ul></li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-url').":</strong> ".PieceOfCode::Property('url')."</li>\n";
		if($wgPieceOfCodeConfig['showinstalldir']) {
			$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-installation-directory').":</strong> ".dirname(__FILE__)."</li>\n";
		}
		$out.= "\t\t\t<li><strong>".wfMsg('poc-sinfo-svn').":</strong><ul>\n";
		$aux = str_replace('$', '', PieceOfCode::Property('svn-revision'));
		$aux = str_replace('LastChangedRevision: ', '', $aux);
		$out.= "\t\t\t\t<li><strong>".wfMsg('poc-sinfo-svn-revision').":</strong> r{$aux}</li>\n";
		$aux = str_replace('$', '', PieceOfCode::Property('svn-date'));
		$aux = str_replace('LastChangedDate: ', '', $aux);
		$out.= "\t\t\t\t<li><strong>".wfMsg('poc-sinfo-svn-date').":</strong> {$aux}</li>\n";
		$out.= "\t\t\t</ul></li>\n";
		$out.= "\t\t</ul>\n";
		/* @} */
		/*
		 * Section: SVN Connections.
		 * @{
		 */
		$out.= "\t\t<a name=\"poc-sinfo-svn-connections\"></a><h2><span class=\"mw-headline\">".wfMsg('poc-sinfo-svn-connections')."</span></h2>\n";
		$out.= "\t\t<table class=\"wikitable\">\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th colspan=\"3\">".wfMsg('poc-sinfo-svn-connections')."</th>\n";
		$out.= "\t\t\t</tr>\n";
		ksort($wgPieceOfCodeSVNConnections);
		foreach($wgPieceOfCodeSVNConnections as $ksvnconn => $svnconn) {
			$out.= "\t\t\t<tr>\n";
			$out.= "\t\t\t\t<th rowspan=\"3\" style=\"text-align:left\">{$ksvnconn}</th>\n";
			$out.= "\t\t\t\t<th style=\"text-align:left\">".wfMsg('poc-sinfo-svnconn-url')."</th>\n";
			$out.= "\t\t\t\t<td>{$svnconn['url']}</td>\n";
			$out.= "\t\t\t</tr><tr>\n";
			$out.= "\t\t\t\t<th style=\"text-align:left\">".wfMsg('poc-sinfo-svnconn-username')."</th>\n";
			$out.= "\t\t\t\t<td>".(isset($svnconn['username'])?$svnconn['username']:wfMsg('poc-anonymous'))."</td>\n";
			$out.= "\t\t\t</tr><tr>\n";
			$out.= "\t\t\t\t<th style=\"text-align:left\">".wfMsg('poc-sinfo-svnconn-password')."</th>\n";
			$out.= "\t\t\t\t<td>".($svnconn['password']?wfMsg('poc-present'):wfMsg('poc-not-present'))."</td>\n";
			$out.= "\t\t\t</tr>\n";
		}
		$out.= "\t\t</table>\n";
		/* @} */
		/*
		 * Section: Stored Codes.
		 * @{
		 */
		$out.= "\t\t<a name=\"poc-sinfo-stored-codes\"></a><h2><span class=\"mw-headline\">".wfMsg('poc-sinfo-stored-codes')."</span></h2>\n";
		$out.= "\t\t<table class=\"wikitable sortable\">\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-conn')."</th>\n";
		//$out.= "\t\t\t\t<th class=\"unsortable\">".wfMsg('poc-sinfo-stored-codes-code')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-path')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-lang')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-rev')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-user')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-date')."</th>\n";
		$out.= "\t\t\t\t<th class=\"unsortable\"><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-zoom-fit-best-16px.png\" alt=\"".wfMsg('poc-open')."\" title=\"".wfMsg('poc-open')."\"/></th>\n";
		$out.= "\t\t\t</tr>\n";
		$files = POCStoredCodes::Instance()->selectFiles();
		foreach($files as $fileInfo) {
			$out.= "\t\t\t<tr>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['connection']}</td>\n";
			//$out.= "\t\t\t\t<td>{$fileInfo['code']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['path']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['lang']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['revision']}</td>\n";
			$auxUrl = Title::makeTitle(NS_USER,$fileInfo['user'])->escapeFullURL();
			$out.= "\t\t\t\t<td><a href=\"{$auxUrl}\">{$fileInfo['user']}</a></td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['timestamp']}</td>\n";
			$auxUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL("connection={$fileInfo['connection']}&path={$fileInfo['path']}&revision={$fileInfo['revision']}");
			$out.= "\t\t\t\t<th class=\"unsortable\"><a href=\"{$auxUrl}\"><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-zoom-fit-best-16px.png\" alt=\"".wfMsg('poc-open')."\" title=\"".wfMsg('poc-open')."\"/></a></th>\n";
			$out.= "\t\t\t</tr>\n";
		}
		$out.= "\t\t</table>\n";
		/* @} */
		/*
		 * Section: Links
		 * @{
		 */
		$out.= "\t\t<a name=\"poc-sinfo-links\"></a><h2><span class=\"mw-headline\">".wfMsg('poc-sinfo-links')."</span></h2>\n";
		$out.= "\t\t<ul>\n";
		$out.= "\t\t\t<li><strong>MediaWiki Extensions:</strong> http://www.mediawiki.org/wiki/Extension:PieceOfCode</li>\n";
		$out.= "\t\t\t<li><strong>GoogleCode Proyect Site:</strong> http://code.google.com/p/pieceofcode-dr/</li>\n";
		$out.= "\t\t\t<li><strong>GoogleCode Issues Trak:</strong> http://code.google.com/p/pieceofcode-dr/issues</li>\n";
		$out.= "\t\t</ul>\n";
		/* @} */

		$wgOut->addHTML($out);
	}
	/**
	 * @todo doc
	 * @param array $fontcode @todo doc
	 */
	protected function showFontCode(&$fontcode) {
		$out = "";

		global	$wgOut;
		global	$wgPieceOfCodeSVNConnections;
		global	$wgPieceOfCodeConfig;
		global	$wgPieceOfCodeExtensionSysDir;
		global	$wgPieceOfCodeExtensionWebDir;

		$this->_errors->clearError();
		$fileInfo = $this->_storedCodes->getFile($fontcode['connection'], $fontcode['path'], $fontcode['revision']);

		if($fileInfo) {
			global	$wgParser;
			$tags = $wgParser->getTags();

			if(in_array('syntaxhighlight', $tags)) {
				$tag = 'syntaxhighlight';
			} elseif(in_array('source', $tags)) {
				$tag = 'source';
			}

			$filepath = $wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$fileInfo['upload_path'];
			$st       = stat($filepath);
			$lang     = $fileInfo['lang'];
			if($st['size'] > $wgPieceOfCodeConfig['maxsize']['highlighting']) {
				$out .= $this->_errors->setLastError(wfMsg('poc-errmsg-large-highlight', $wgPieceOfCodeConfig['maxsize']['highlighting']));
				$lang = "text";
			}
			if($st['size'] > $wgPieceOfCodeConfig['maxsize']['showing']) {
				$out .= "<br/>".$this->_errors->setLastError(wfMsg('poc-errmsg-large-show', $wgPieceOfCodeConfig['maxsize']['showing']));
				$lang = "text";
			}
			$out.= "<h2>{$fileInfo['connection']}: {$fileInfo['path']}:{$fontcode['revision']}</h2>";
			$out.= "<div class=\"PieceOfCode_code\"><{$tag} lang=\"{$lang}\" line start=\"1\">";
			$out.= file_get_contents($filepath, false, null, -1, $wgPieceOfCodeConfig['maxsize']['showing']);
			$out.= "</{$tag}></div>";
		} else {
			if(!$this->_errors->getLastError()) {
				$this->_errors->setLastError(wfMsg('poc-errmsg-no-fileinfo', $fontcode['connection'], $fontcode['path'], $fontcode['revision']));
			}
			$out.=$this->_errors->getLastError();
		}
		$wgOut->addWikiText($out);
	}

	/*
	 * Public Class Methods.
	 */
	public static function Instance() {
		if (!isset(self::$_Instance)) {
			$c = __CLASS__;
			self::$_Instance = new $c;
		}

		return self::$_Instance;
	}
	public static function Property($name) {
		$name = strtolower($name);
		if(!isset(PieceOfCode::$_Properties[$name])) {
			die("PieceOfCode::Property(): Property '{$name}' does not exist (".__FILE__.":".__LINE__.").");
		}
		return PieceOfCode::$_Properties[$name];
	}
}

?>