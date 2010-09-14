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
						'version'              => '0.2',
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
	 * @var POCFlags
	 */
	protected	$_flags;
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
	 * @var POCStats
	 */
	protected	$_stats;
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
	/**
	 * @var POCVersionManager
	 */
	protected	$_versionManager;

	public function __construct() {
		parent::__construct('PieceOfCode');

		/*
		 * Checking and updating version.
		 */
		$this->_versionManager = POCVersionManager::Instance();

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
		$this->_flags          = POCFlags::Instance();
		$this->_svnConnections = POCSVNConnections::Instance();
		$this->_storedCodes    = POCStoredCodes::Instance();
		$this->_stats          = POCStats::Instance();

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

		$fontcode = array(
			'action'	=> $wgRequest->getVal('action', null),
			'code'		=> $wgRequest->getVal('code', null),
			'connection'	=> $wgRequest->getVal('connection', null),
			'path'		=> $wgRequest->getVal('path', null),
			'revision'	=> $wgRequest->getVal('revision', null),
		);
		$fontcode['showit'] = ($fontcode['connection'] !== null && $fontcode['path'] !== null && $fontcode['revision'] !== null);

		switch($fontcode['action']) {
			case 'delete':
				$this->deleteFontCode(&$fontcode);
				break;
			case 'show':
				if($wgEnableUploads && $fontcode['showit']) {
					$this->showFontCode($fontcode);
					break;
				}
			case 'page_stats':
				$this->statPagesByCode(&$fontcode);
				break;
			default:
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
		if($this->_errors->ok()) {
			$out.= $codeExtractor->show();
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param string $name @todo doc
	 */
	public function varDefault($name) {
		return (isset($this->_varDefaults[$name])?$this->_varDefaults[$name]:'');
	}

	/*
	 * Protected Methods.
	 */
	/**
	 * @todo doc
	 */
	protected function enableTagHTML() {
		global	$wgRawHtml;
		global	$wgParser;

		$wgRawHtml = true;
		/*
		 * Resetting core tags to enable tag <html>
		 * Only, from version 1.17 and above.
		 * @{
		 */
		if(class_exists('CoreTagHooks')) {
			CoreTagHooks::register($wgParser);
		}
		/* @} */
	}
	/**
	 * Prints basic information on Special:PieceOfCode
	 */
	protected function basicInformation() {
		global	$wgOut;
		global	$wgUser;
		global	$wgDBprefix;
		global	$wgPieceOfCodeSVNConnections;
		global	$wgPieceOfCodeConfig;
		global	$wgPieceOfCodeExtensionSysDir;
		global	$wgPieceOfCodeExtensionWebDir;
		
		$isAdmin = in_array('sysop', $wgUser->getGroups());

		$this->enableTagHTML();

		$out = "\t\t<html><div style=\"float:right;text-align:center;\"><a href=\"http://wiki.daemonraco.com/\"><img src=\"http://wiki.daemonraco.com/wiki/dr.png\"/></a><br/><a href=\"http://wiki.daemonraco.com/\">DAEMonRaco</a></div></html>\n";
		$out.= "__TOC__\n";
		$out.= "__NOEDITSECTION__\n";
		
		/*
		 * Section: Extension information.
		 * @{
		 */
		$out.= "== ".wfMsg('poc-sinfo-extension-information')." ==\n";
		$out.= "*'''".wfMsg('poc-sinfo-name').":''' ".PieceOfCode::Property('name')."\n";
		$out.= "*'''".wfMsg('poc-sinfo-version').":''' ".PieceOfCode::Property('version')."\n";
		$out.= "*'''".wfMsg('poc-sinfo-description').":''' ".PieceOfCode::Property('_description')."\n";
		$out.= "*'''".wfMsg('poc-sinfo-author').":'''\n";
		foreach(PieceOfCode::Property('author') as $author) {
			$out.= "**{$author}\n";
		}
		$out.= "*'''".wfMsg('poc-sinfo-url').":''' ".PieceOfCode::Property('url')."\n";
		if($wgPieceOfCodeConfig['show']['installdir']) {
			$out.= "*'''".wfMsg('poc-sinfo-installation-directory').":''' ".dirname(__FILE__)."\n";
		}
		$out.= "*'''".wfMsg('poc-sinfo-svn').":'''\n";
		$aux = str_replace('$', '', PieceOfCode::Property('svn-revision'));
		$aux = str_replace('LastChangedRevision: ', '', $aux);
		$out.= "**'''".wfMsg('poc-sinfo-svn-revision').":''' r{$aux}\n";
		$aux = str_replace('$', '', PieceOfCode::Property('svn-date'));
		$aux = str_replace('LastChangedDate: ', '', $aux);
		$out.= "**'''".wfMsg('poc-sinfo-svn-date').":''' {$aux}\n";
		/* @} */
		/*
		 * Section: SVN Connections.
		 * @{
		 */
		$out.= "== ".wfMsg('poc-sinfo-svn-connections')." ==\n";
		$out.= "{| class=\"wikitable\"\n";
		$out.= "|-\n";
		$out.= "!colspan=\"3\"|".wfMsg('poc-sinfo-svn-connections')."\n";
		ksort($wgPieceOfCodeSVNConnections);
		foreach($wgPieceOfCodeSVNConnections as $ksvnconn => $svnconn) {
			$out.= "|-\n";
			$out.= "!rowspan=\"3\" style=\"text-align:left\"|{$ksvnconn}\n";
			$out.= "!style=\"text-align:left\"|".wfMsg('poc-sinfo-svnconn-url')."\n";
			$out.= "|{$svnconn['url']}\n";
			$out.= "|-\n";
			$out.= "!style=\"text-align:left\"|".wfMsg('poc-sinfo-svnconn-username')."\n";
			if($wgPieceOfCodeConfig['show']['svnusernames']) {
				$out.= "|".(isset($svnconn['username'])?$svnconn['username']:wfMsg('poc-anonymous'))."\n";
			} else {
				$out.= "|".(isset($svnconn['username'])?wfMsg('poc-present'):wfMsg('poc-anonymous'))."\n";
			}
			$out.= "|-\n";
			$out.= "!style=\"text-align:left\"|".wfMsg('poc-sinfo-svnconn-password')."\n";
			if($wgPieceOfCodeConfig['show']['svnpasswords']) {
				$out.= "|".($svnconn['password']?$svnconn['password']:wfMsg('poc-not-present'))."\n";
			} else {
				$out.= "|".($svnconn['password']?wfMsg('poc-present'):wfMsg('poc-not-present'))."\n";
			}
		}
		$out.= "|}\n";
		/* @} */
		/*
		 * Section: Stored Codes.
		 * @{
		 */
		$out.= "== ".wfMsg('poc-sinfo-stored-codes')." ==\n";
		$out.= "{| class=\"wikitable sortable\"\n";
		$out.= "|-\n";
		$out.= "!".wfMsg('poc-sinfo-stored-codes-conn')."\n";
		$out.= "!".wfMsg('poc-sinfo-stored-codes-path')."\n";
		$out.= "!".wfMsg('poc-sinfo-stored-codes-lang')."\n";
		$out.= "!".wfMsg('poc-sinfo-stored-codes-rev')."\n";
		if($wgPieceOfCodeConfig['stats']) {
			$out.= "!".wfMsg('poc-sinfo-stored-codes-count')."\n";
		}
		$out.= "!".wfMsg('poc-sinfo-stored-codes-user')."\n";
		$out.= "!".wfMsg('poc-sinfo-stored-codes-date')."\n";
		$out.= "!class=\"unsortable\"|<html><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-zoom-fit-best-24px.png\" alt=\"".wfMsg('poc-open')."\" title=\"".wfMsg('poc-open')."\"/></html>\n";
		if($wgPieceOfCodeConfig['stats']) {
			$out.= "!class=\"unsortable\"|<html><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-system-search-24px.png\" alt=\"".wfMsg('poc-sinfo-stat-pages')."\" title=\"".wfMsg('poc-sinfo-stat-pages')."\"/></html>\n";
		}
		if($isAdmin) {
			$out.= "!class=\"unsortable\"|<html><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-process-stop-24px.png\" alt=\"".wfMsg('poc-delete')."\" title=\"".wfMsg('poc-delete')."\"/></html>\n";
		}
		$files = POCStoredCodes::Instance()->selectFiles();
		foreach($files as $fileInfo) {
			$out.= "|-\n";
			$out.= "|{$fileInfo['connection']}\n";
			$out.= "|{$fileInfo['path']}\n";
			$out.= "|{$fileInfo['lang']}\n";
			$out.= "|{$fileInfo['revision']}\n";
			if($wgPieceOfCodeConfig['stats']) {
				$out.= "|{$fileInfo['count']}\n";
			}
			$out.= "|[[User:{$fileInfo['user']}|{$fileInfo['user']}]]\n";
			$out.= "|{$fileInfo['timestamp']}\n";
			$auxUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL("action=show&connection={$fileInfo['connection']}&path={$fileInfo['path']}&revision={$fileInfo['revision']}");
			$out.= "|<html><a href=\"{$auxUrl}\"><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-zoom-fit-best-24px.png\" alt=\"".wfMsg('poc-open')."\" title=\"".wfMsg('poc-open')."\"/></a></html>\n";
			if($wgPieceOfCodeConfig['stats']) {
				$auxUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL("action=page_stats&code={$fileInfo['code']}");
				$out.= "|<html><a href=\"{$auxUrl}\"><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-system-search-24px.png\" alt=\"".wfMsg('poc-sinfo-stat-pages')."\" title=\"".wfMsg('poc-sinfo-stat-pages')."\"/></a></html>\n";
			}
			if($isAdmin) {
				$auxUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL("action=delete&code={$fileInfo['code']}");
				$out.= "|<html><a href=\"{$auxUrl}\"><img src=\"{$wgPieceOfCodeExtensionWebDir}/images/gnome-process-stop-24px.png\" alt=\"".wfMsg('poc-delete')."\" title=\"".wfMsg('poc-delete')."\"/></a></html>\n";
			}
		}
		$out.= "|}\n";
		/* @} */
		/*
		 * Section: Configuration.
		 * @{
		 */
		$out.= "== ".wfMsg('poc-sinfo-configuration')." ==\n";
		$out.= "{|class=\"wikitable\"\n";
		$out.= "|-\n";
		$out.= "!colspan=\"3\"|".wfMsg('poc-sinfo-general')."\n";
		$out.= "|-\n";
		if($wgPieceOfCodeConfig['show']['binarypaths']) {
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-svn-path')."\n";
			$out.= "|colspan=\"2\"|{$wgPieceOfCodeConfig['svn-binary']}\n";
			$out.= "|-\n";
		}
		if($wgPieceOfCodeConfig['enableuploads']) {
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-enable-uploads')."\n";
			$out.= "|colspan=\"2\"|".wfMsg('poc-enabled')."\n";
			$out.= "|-\n";
			if($wgPieceOfCodeConfig['show']['updaloaddirs']) {
				$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-upload-directory')."\n";
				$out.= "|colspan=\"2\"|{$wgPieceOfCodeConfig['uploaddirectory']}\n";
				$out.= "|-\n";
			}
		} else {
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-enable-uploads')."\n";
			$out.= "|colspan=\"2\"|".wfMsg('poc-disabled')."\n";
			$out.= "|-\n";
		}
		if($wgPieceOfCodeConfig['show']['tablenames']) {
			$out.= "!rowspan=\"4\" style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-tablenames')."\n";
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-tablename')."\n";
			$out.= "|{$wgDBprefix}{$wgPieceOfCodeConfig['db-tablename']}\n";
			$out.= "|-\n";
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-tablename-texts')."\n";
			$out.= "|{$wgDBprefix}{$wgPieceOfCodeConfig['db-tablename-texts']}\n";
			$out.= "|-\n";
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-tablename-ccounts')."\n";
			$out.= "|{$wgDBprefix}{$wgPieceOfCodeConfig['db-tablename-ccounts']}\n";
			$out.= "|-\n";
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-tablename-flags')."\n";
			$out.= "|{$wgDBprefix}{$wgPieceOfCodeConfig['db-tablename-flags']}\n";
			$out.= "|-\n";
		}

		$out.= "!colspan=\"3\"|".wfMsg('poc-sinfo-codes-cnf')."\n";
		$out.= "|-\n";
		$out.= "!rowspan=\"".count($wgPieceOfCodeConfig['fontcodes'])."\" style=\"text-align:left;\"|".wfMsg('poc-sinfo-cnf-types-and-exts')."\n";
		ksort($wgPieceOfCodeConfig['fontcodes']);
		foreach($wgPieceOfCodeConfig['fontcodes'] as $type => $list) {
			$out.= "!style=\"text-align:left;\"|{$type}\n";
			if(count($list)) {
				$out.= "|*.".implode(', *.',$list)."\n";
			} else {
				$out.= "|colspan=\"2\"|''".wfMsg('poc-none')."''\n";
			}
			$out.= "|-\n";
		}
		$out.= "!style=\"text-align:justify;word-wrap:break-word;\"|".wfMsg('poc-sinfo-cnf-forbidden-exts')."\n";
		if(count($wgPieceOfCodeConfig['fontcodes-forbidden'])) {
			$out.= "|colspan=\"2\"|<nowiki>*</nowiki>.".implode(', <nowiki>*</nowiki>.',$wgPieceOfCodeConfig['fontcodes-forbidden'])."\n";
		} else {
			$out.= "|colspan=\"2\"|''".wfMsg('poc-none')."''\n";
		}
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-cnf-empty-exts')."\n";
		$out.= "|colspan=\"2\"|".($wgPieceOfCodeConfig['fontcodes-allowempty']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-cnf-maxhighlight')."\n";
		$out.= "|colspan=\"2\"|".round($wgPieceOfCodeConfig['maxsize']['highlighting']/1024)."KB\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-cnf-maxshowing')."\n";
		$out.= "|colspan=\"2\"|".round($wgPieceOfCodeConfig['maxsize']['showing']/1024)."KB\n";
		$out.= "|-\n";

		$out.= "!colspan=\"3\"|".wfMsg('poc-sinfo-statistics')."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-stat')."\n";
		$out.= "|colspan=\"2\"|".($wgPieceOfCodeConfig['stats']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		if($wgPieceOfCodeConfig['stats']) {
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-stats-timelimit')."\n";
			$out.= "|colspan=\"2\"|{$wgPieceOfCodeConfig['db-stats-timelimit']} ".wfMsg('poc-seconds')."\n";
			$out.= "|-\n";
			$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-stats-limited')."\n";
			$out.= "|colspan=\"2\"|".($wgPieceOfCodeConfig['db-stats-limited']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
			$out.= "|-\n";
			if($wgPieceOfCodeConfig['db-stats-limited']) {
				$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-db-stats-per-try')."\n";
				$out.= "|colspan=\"2\"|{$wgPieceOfCodeConfig['db-stats-per-try']}\n";
				$out.= "|-\n";
			}
		}

		$out.= "!colspan=\"3\"|".wfMsg('poc-sinfo-miscellaneous')."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-internal-css')."\n";
		$out.= "|colspan=\"2\"|".($wgPieceOfCodeConfig['autocss']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!rowspan=\"6\" style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-flags')."\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-installdir')."\n";
		$out.= "|".($wgPieceOfCodeConfig['show']['installdir']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-tablenames')."\n";
		$out.= "|".($wgPieceOfCodeConfig['show']['tablenames']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-binarypaths')."\n";
		$out.= "|".($wgPieceOfCodeConfig['show']['binarypaths']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-updaloaddirs')."\n";
		$out.= "|".($wgPieceOfCodeConfig['show']['updaloaddirs']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-svnusernames')."\n";
		$out.= "|".($wgPieceOfCodeConfig['show']['svnusernames']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('poc-sinfo-show-svnpasswords')."\n";
		$out.= "|".($wgPieceOfCodeConfig['show']['svnpasswords']?wfMsg('poc-enabled'):wfMsg('poc-disabled'))."\n";
		$out.= "|}\n";
		/* @} */
		/*
		 * Section: Links
		 * @{
		 */
		$out.= "== ".wfMsg('poc-sinfo-links')." ==\n";
		$out.= "*'''MediaWiki Extensions:''' http://www.mediawiki.org/wiki/Extension:PieceOfCode\n";
		$out.= "*'''Official Documentation:''' http://wiki.daemonraco.com/wiki/PieceOfCode-dr\n";
		$out.= "*'''GoogleCode Proyect Site:''' http://code.google.com/p/pieceofcode-dr/\n";
		$out.= "*'''GoogleCode Issues Trak:''' http://code.google.com/p/pieceofcode-dr/issues\n";
		/* @} */
		
		$wgOut->addWikiText($out);
	}
	/**
	 * @todo doc
	 * @param array $fontcode @todo doc
	 */
	protected function deleteFontCode(&$fontcode) {
		global	$wgOut;
		global	$wgUser;
		global	$wgPieceOfCodeSVNConnections;
		global	$wgPieceOfCodeConfig;
		global	$wgPieceOfCodeExtensionSysDir;
		global	$wgPieceOfCodeExtensionWebDir;

		$isAdmin = in_array('sysop', $wgUser->getGroups());
		if($isAdmin) {
			$out = "";

			global	$wgRequest;

			if($wgRequest->wasPosted()) {
				$returnUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL();

				POCStoredCodes::Instance()->removeByCode($fontcode['code']);
				if(!$this->_errors->ok()) {
					$out.= $this->_errors->getLastError()."<br/>";
				} else {
					$out.="\t\t<p>".wfMsg('poc-sinfo-file-deleted')."</p>\n";
				}

				$out.= "\t\t\t\t<input type=\"button\" value=\"".wfMsg('poc-back')."\" onClick=\"document.location.href='{$returnUrl}';return false\"/>\n";
			} else {
				$sendUrl   = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL("action={$fontcode['action']}&code={$fontcode['code']}");
				$cancelUrl = Title::makeTitle(NS_SPECIAL,'PieceOfCode')->escapeFullURL();

				$fileInfo = POCStoredCodes::Instance()->getByCode($fontcode['code']);

				if($this->_errors->ok()) {
					$out.="\t\t<form action=\"{$sendUrl}\" method=\"post\">\n";
					$out.="\t\t\t<input type=\"hidden\" name=\"action\" value=\"{$fontcode['action']}\"/>\n";
					$out.="\t\t\t<input type=\"hidden\" name=\"code\"   value=\"{$fontcode['code']}\"/>\n";
					$out.="\t\t\t<p>\n";
					$out.="\t\t\t\t".wfMsg('poc-sinfo-about-to-delete', $fileInfo['connection'], $fileInfo['path'], $fileInfo['revision'], $fileInfo['lang'])."\n";
					$out.="\t\t\t</p>\n";
					$out.="\t\t\t<p>\n";
					$out.="\t\t\t\t<input type=\"submit\" value=\"".wfMsg('poc-yes')."\"/>\n";
					$out.="\t\t\t\t<input type=\"reset\" value=\"".wfMsg('poc-no')."\" onClick=\"document.location.href='{$cancelUrl}';return false\"/>\n";
					$out.="\t\t\t</p>\n";
					$out.="\t\t</form>\n";
				} else {
					$wgOut->addHTML($this->_errors->getLastError());
				}
			}
			$wgOut->addHTML($out);
		} else {
			$wgOut->addHTML($this->_errors->setLastError(wfMsg('poc-errmsg-only-admin')));
		}
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
			$tag = '';
			if(!PieceOfCode::CheckSyntaxHighlightExtension($tag)) {
				$out.= "<br/>".$this->_errors->getLastError();
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
			$out.= "<div class=\"PieceOfCode_code\"><{$tag} lang=\"{$lang}\" line=\"GESHI_NORMAL_LINE_NUMBERS\" start=\"1\">";
			$out.= file_get_contents($filepath, false, null, -1, $wgPieceOfCodeConfig['maxsize']['showing']);
			$out.= "</{$tag}></div>";
		} else {
			if($this->_errors->ok()) {
				$this->_errors->setLastError(wfMsg('poc-errmsg-no-fileinfo', $fontcode['connection'], $fontcode['path'], $fontcode['revision']));
			}
			$out.=$this->_errors->getLastError();
		}
		$wgOut->addWikiText($out);
	}
	protected function statPagesByCode(&$fontcode) {
		global	$wgOut;
		global	$wgPieceOfCodeConfig;
		if($wgPieceOfCodeConfig['stats']) {
			$out = "";

			$code = POCStoredCodes::Instance()->getByCode($fontcode['code']);
			if($this->_errors->ok()) {
				$out.="__TOC__\n";
				$out.="== ".wfMsg('poc-sinfo-information')." ==\n";
				$out.="*'''".wfMsg('poc-sinfo-connection')."''': {$code['connection']}\n";
				$out.="*'''".wfMsg('poc-sinfo-path')."''': {$code['path']}\n";
				$out.="*'''".wfMsg('poc-sinfo-revision')."''': {$code['revision']}\n";
				$out.="*'''".wfMsg('poc-sinfo-lang')."''': {$code['lang']}\n";
				$auxUrl = Title::makeTitle(NS_USER, $code['user'])->getFullURL();
				$out.="*'''".wfMsg('poc-sinfo-user')."''': [[User:{$code['user']}|{$code['user']}]]\n";

				$out.="== ".wfMsg('poc-sinfo-usage')." ==\n";
				$out.="{|class=\"wikitable sortable\"\n";
				$out.="|-\n";
				$out.="!".wfMsg('poc-sinfo-page')."\n";
				$out.="!".wfMsg('poc-sinfo-stored-codes-count')."\n";
				$out.="!".wfMsg('poc-sinfo-stored-codes-user')."\n";
				foreach(POCStats::Instance()->getCodePages($fontcode['code']) as $c) {
					$out.="|-\n";
					$auxPage = Title::newFromID($c['page_id']);
					$title   = $c['title'];
					/*
					 * Checking if it isn't the default namesapace.
					 */
					if($auxPage->getNamespace() != NS_MAIN) {
						$title = $auxPage->getNsText().$title;
					}
					$out.="|[[{$title}]]\n";
					$out.="|{$c['times']}\n";
					$out.="|[[User:{$c['last_user']}|{$c['last_user']}]]\n";
				}
				$out.="|}\n";

			} else {
				$wgOut->addHTML($this->_errors->getLastError());
			}

			$wgOut->addWikiText($out);
		} else {
			$wgOut->addHTML($this->_errors->setLastError(wfMsg('poc-errmsg-stats-disabled')));
		}
	}

	/*
	 * Public Class Methods.
	 */
	/**
	 * This class method looks for a hook for tag &lt;syntaxhighlight&gt; or
	 * &lt;source%gt;. When one of these tags is present, it means, the
	 * extension SyntaxHighlight is loaded.
	 * @param string $tag When this method finishes, this parameter contains
	 * the tag. Or a null string when it's not found.
	 * @return boolean Returns true when a tag is found. Otherwise, false.
	 */
	public static function CheckSyntaxHighlightExtension(&$tag) {
		$tag = '';

		global	$wgParser;
		$tags = $wgParser->getTags();

		if(in_array('syntaxhighlight', $tags)) {
			$tag = 'syntaxhighlight';
		} elseif(in_array('source', $tags)) {
			$tag = 'source';
		}

		if(!$tag) {
			POCErrorsHolder::Instance()->setLastError(wfMsg('poc-errmsg-stylecode-extension'));
			return false;
		} else {
			return true;
		}
	}
	/**
	 * @todo doc
	 */
	public static function Instance() {
		if (!isset(self::$_Instance)) {
			$c = __CLASS__;
			self::$_Instance = new $c;
		}

		return self::$_Instance;
	}
	/**
	 * @todo doc
	 * @param string $name @todo doc
	 */
	public static function Property($name) {
		$name = strtolower($name);
		if(!isset(PieceOfCode::$_Properties[$name])) {
			die("PieceOfCode::Property(): Property '{$name}' does not exist (".__FILE__.":".__LINE__.").");
		}
		return PieceOfCode::$_Properties[$name];
	}
}

?>
