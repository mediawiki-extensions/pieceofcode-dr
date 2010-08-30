<?php
/**
 * @file POCCodeExtractor.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-08-29
 */

class POCCodeExtractor {
	/**
	 * @var PieceOfCode
	 */
	protected	$_pocInstance;
	/**
	 * @var POCStoredCodes
	 */
	protected	$_storeCodes;

	/**
	 * @var string
	 */
	protected	$_connection;
	/**
	 * @var array
	 */
	protected	$_fileInfo;
	/**
	 * @var string
	 */
	protected	$_filename;
	/**
	 * @var array
	 */
	protected	$_lines;
	/**
	 * @var int
	 */
	protected	$_revision;
	/**
	 * @var bool
	 */
	protected	$_showTitle;

	public  function __construct() {
		$this->_pocInstance = PieceOfCode::Instance();
		$this->_storeCodes  = POCStoredCodes::Instance();

		$this->clear();
	}

	/*
	 * Public methods.
	 */
	/**
	 * @todo doc
	 * @param string $input @todo doc
	 * @param array $params @todo doc
	 * @param Parser $parser @todo doc
	 */
	public function load($input, $params, $parser) {
		$out = "";
		/*
		 * Clearing status.
		 */
		$this->clear();

		/*
		 * Loading configuration from tags.
		 */
		$out.= $this->loadParams($params);
		if(!$this->getLastError()) {
			$out.= $this->loadVariables($input);
		}

		/*
		 * Loading file information
		 */
		if(!$this->getLastError()) {
			$this->_fileInfo = $this->_storeCodes->getFile($this->_connection, $this->_filename, $this->_revision);
			if(!$this->_fileInfo) {
				$out.=$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-no-fileinfo', $this->_connection, $this->_filename, $this->_revision)));
			}
		}

		return $out;
	}
	/**
	 * @todo doc
	 */
	public function show() {
		$out = "";

		if($this->_fileInfo) {
			global	$wgPieceOfCodeConfig;
			global	$wgParser;
			$tags = $wgParser->getTags();

			if(in_array('syntaxhighlight', $tags)) {
				$tag = 'syntaxhighlight';
			} elseif(in_array('source', $tags)) {
				$tag = 'source';
			}

			$upload_path = $wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$this->_fileInfo['upload_path'];

			$out.= "<div class=\"PieceOfCode_code\">\n";
			if($this->_showTitle) {
				$out.="<span class=\"PieceOfCode_title\"><strong>{$this->_connection}></strong>{$this->_filename}:{$this->_revision}</span>";
			}

			if(isset($this->_lines[0])) {
				$auxOut = "<{$tag} lang=\"{$this->_fileInfo['lang']}\" line start=\"{$this->_lines[0]}\">";
				$file = file($upload_path);
				for($i=$this->_lines[0]-1; $i<$this->_lines[1]; $i++) {
					if(isset($file[$i])) {
						$auxOut.=$file[$i];
					}
				}
				$auxOut.= "</{$tag}>";
				$out.= $wgParser->recursiveTagParse($auxOut);
			} else {
				$auxOut = "<{$tag} lang=\"{$this->_fileInfo['lang']}\" line start=\"1\">";
				$auxOut.= file_get_contents($upload_path);
				$auxOut.= "</{$tag}>";
				$out.= $wgParser->recursiveTagParse($auxOut);
			}
			$out.= "</div>\n";
		}

		return $out;
	}

	/*
	 * Protected Methods
	 */
	/**
	 * Clears all data concerning the file to be shown.
	 */
	protected function clear() {
		$this->_showTitle = false;

		$this->_filename   = '';
		$this->_revision   = '';
		$this->_connection = '';
		$this->_lines      = array();

		$this->_fileInfo = null;
	}
	/**
	 * @todo doc
	 * @param string $msg @todo doc
	 */
	protected function formatErrorMessage($msg) {
		return $this->_pocInstance->formatErrorMessage($msg);
	}
	/**
	 * @todo doc
	 */
	protected function getLastError() {
		return $this->_pocInstance->getLastError();
	}
	/**
	 * Return parameters from mediaWiki;
	 *	use Default if parameter not provided;
	 *	use '' or 0 if Default not provided
	 */
	protected function getVariable($input, $name, $isNumber=false) {
		if($this->_pocInstance->varDefault($name)) {
			$out = $this->_pocInstance->varDefault($name);
		} else {
			$out = ($isNumber) ? 0 : '';
		}

		if(preg_match("/^\s*$name\s*=\s*(.*)/mi", $input, $matches)) {
			if($isNumber) {
				$out = intval($matches[1]);
			} elseif($matches[1] != null) {
				$out = htmlspecialchars($matches[1]);
			}
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param array $params @todo doc
	 */
	protected function loadParams($params) {
		$out = "";

		if(isset($params['title'])) {
			$this->_showTitle = (strtolower($params['title']) == 'true');
		}

		return $out;
	}
	/**
	 * This method tries to load all the useful information set between tags
	 * <pieceofcode> and </pieceofcode>.
	 * @param string $input Configuration text to be analyzed.
	 */
	protected function loadVariables($input) {
		$out = "";

		$this->_filename   = $this->getVariable($input, 'file');
		$this->_revision   = $this->getVariable($input, 'revision', true);
		$this->_connection = $this->getVariable($input, 'connection');
		$this->_lines      = explode('-', $this->getVariable($input, 'lines'));

		if(!isset($this->_lines[0]) || !isset($this->_lines[1]) || $this->_lines[0] > $this->_lines[1]) {
			unset($this->_lines);
			$this->_lines = array();
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param string $msg @todo doc
	 */
	protected function setLastError($msg="") {
		return $this->_pocInstance->setLastError($msg);
	}

	/*
	 * Public class methods
	 */
}

?>
