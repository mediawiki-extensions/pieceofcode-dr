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
	protected static	$_Instance   = null;
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
						'url'                  => 'http://wiki.daemonraco.com/wiki/pieceofcode-dr',
						'svn-date'             => '$LastChangedDate$',
						'svn-revision'         => '$LastChangedRevision$',
	);
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

		$this->setLastError();

		$this->_svnConnections = POCSVNConnections::Instance();
		$this->_storedCodes    = POCStoredCodes::Instance();
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
				$fileInfo = $this->_storedCodes->getFile($fontcode['connection'], $fontcode['path'], $fontcode['revision']);

				if($fileInfo) {
					global	$wgParser;
					$tags = $wgParser->getTags();

					if(in_array('syntaxhighlight', $tags)) {
						$tag = 'syntaxhighlight';
					} elseif(in_array('source', $tags)) {
						$tag = 'source';
					}
						
						
					$out.= "<h2>{$fileInfo['connection']}: {$fileInfo['path']}:{$fontcode['revision']}</h2>";
					$out.= "<div class=\"PieceOfCode_code\"><{$tag} lang=\"{$fileInfo['lang']}\" line start=\"1\">";
					$out.= file_get_contents($wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$fileInfo['upload_path']);
					$out.= "</{$tag}></div>";
				} else {
					die(__FILE__.':'.__LINE__);
				}
				$wgOut->addWikiText($out);
				return;
			}
		}
		/*
		 * Section: Extension information.
		 * @{
		 */
		if($wgAllowExternalImages) {
			$out.= "\t\t<span style=\"float:right;text-align:center;\">http://wiki.daemonraco.com/wiki/dr.png<br/>[http://wiki.daemonraco.com/ DAEMonRaco]</span>\n";
		}
		$out.= "\t\t<h2>".wfMsg('poc-sinfo-extension-information')."</h2>\n";
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
		if($wgXML2WikiConfig['showinstalldir']) {
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
		$out.= "\t\t<h2>".wfMsg('poc-sinfo-svn-connections')."</h2>\n";
		$out.= "\t\t<table class=\"wikitable\">\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th colspan=\"3\">".wfMsg('poc-sinfo-svn-connections')."</th>\n";
		$out.= "\t\t\t</tr>\n";
		foreach($wgPieceOfCodeSVNConnections as $ksvnconn => $svnconn) {
			$out.= "\t\t\t<tr>\n";
			$out.= "\t\t\t\t<th rowspan=\"3\" style=\"text-align:left\">{$ksvnconn}</th>\n";
			$out.= "\t\t\t\t<th style=\"text-align:left\">".wfMsg('poc-sinfo-svnconn-url')."</th>\n";
			$out.= "\t\t\t\t<td>{$svnconn['url']}</td>\n";
			$out.= "\t\t\t</tr><tr>\n";
			$out.= "\t\t\t\t<th style=\"text-align:left\">".wfMsg('poc-sinfo-svnconn-username')."</th>\n";
			$out.= "\t\t\t\t<td>{$svnconn['username']}</td>\n";
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
		$out.= "\t\t<h2>".wfMsg('poc-sinfo-stored-codes')."</h2>\n";
		$out.= "\t\t<table class=\"wikitable sortable\">\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-conn')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-code')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-path')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-lang')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-rev')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-user')."</th>\n";
		$out.= "\t\t\t\t<th>".wfMsg('poc-sinfo-stored-codes-date')."</th>\n";
		$out.= "\t\t\t\t<th></th>\n";
		$out.= "\t\t\t</tr>\n";
		$files = POCStoredCodes::Instance()->selectFiles();
		foreach($files as $fileInfo) {
			$out.= "\t\t\t<tr>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['connection']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['code']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['path']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['lang']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['revision']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['user']}</td>\n";
			$out.= "\t\t\t\t<td>{$fileInfo['timestamp']}</td>\n";
			$auxUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL("connection={$fileInfo['connection']}&path={$fileInfo['path']}&revision={$fileInfo['revision']}");
			$out.= "\t\t\t\t<td>[{$auxUrl} ".wfMsg('poc-open')."]</td>\n";
			$out.= "\t\t\t</tr>\n";
		}
		$out.= "\t\t</table>\n";
		/* @} */
		/*
		 * Section: Links
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('poc-sinfo-links')."</h2>\n";
		$out.= "\t\t<ul>\n";
		$out.= "\t\t\t<li><strong>MediaWiki Extensions:</strong> http://www.mediawiki.org/wiki/Extension:PieceOfCode</li>\n";
		$out.= "\t\t\t<li><strong>GoogleCode Proyect Site:</strong> http://code.google.com/p/pieceofcode-dr/</li>\n";
		$out.= "\t\t\t<li><strong>GoogleCode Issues Trak:</strong> http://code.google.com/p/pieceofcode-dr/issues</li>\n";
		$out.= "\t\t</ul>\n";
		/* @} */
		
		$wgOut->addWikiText($out);
	}
	/**
	 * @todo doc
	 * @param string $message @todo doc
	 */
	public function formatErrorMessage($message) {
		return "<span style=\"color:red;font-weight:bold;\">".$this->ERROR_PREFIX."$message</span>";
	}
	/**
	 * Gets last error message.
	 * @return Returns the message.
	 */
	public function getLastError() {
		return $this->_lastError;
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

		$this->setLastError();
		$out.= $codeExtractor->load($input, $params, $parser);
		if(!$this->getLastError()) {
			$out.= $codeExtractor->show();
		}
		//$out.= '<pre>';
		//ob_start();
		//var_dump($input);
		//$out.=ob_get_contents();
		//ob_end_clean();
		//$out.= '</pre>';
		//
		//$out.= "<pre>$input</pre>";

		return $out;
	}
	/**
	 * Sets last error message.
	 * @param string $msg Message to set.
	 * @return Returns the message set.
	 */
	public function setLastError($msg="") {
		$this->_lastError = $msg;
		return $this->getLastError();
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

	/*
	 * Public Class Methods.
	 */
	public static function Instance() {
		if(!PieceOfCode::$_Instance) {
			PieceOfCode::$_Instance = new PieceOfCode();
		}
		return PieceOfCode::$_Instance;
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