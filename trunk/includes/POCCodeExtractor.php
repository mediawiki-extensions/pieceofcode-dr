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
	 * @param unknown_type $input @todo doc
	 * @param unknown_type $params @todo doc
	 * @param unknown_type $parser @todo doc
	 */
	public function load($input, $params, $parser) {
		$out = "";
		/*
		 * Clearing status.
		 */
		$this->clear();

		/*
		 * Loading the configuration set between tags.
		 */
		$this->loadVariables($input);

		/*
		 * Loading file information
		 */
		$this->_fileInfo = $this->_storeCodes->getFile($this->_connection, $this->_filename, $this->_revision);
		if(!$this->_fileInfo) {
			die(__FILE__.":".__LINE__);
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

			if(isset($this->_lines[0])) {
				$auxOut = "<{$tag} lang=\"{$this->_fileInfo['lang']}\" line start=\"{$this->_lines[0]}\">";
				$file = file($upload_path);
				for($i=$this->_lines[0]-1; $i<$this->_lines[1]; $i++) {
					if(isset($file[$i])) {
						$auxOut.=$file[$i];
					}
				}
				$auxOut.= "</{$tag}>";
				$out.= "<div class=\"PieceOfCode_code\">".$wgParser->recursiveTagParse($auxOut)."</div>";
			} else {
				$auxOut = "<{$tag} lang=\"{$this->_fileInfo['lang']}\" line start=\"1\">";
				$auxOut.= file_get_contents($upload_path);
				$auxOut.= "</{$tag}>";
				$out.= "<div class=\"PieceOfCode_code\">".$wgParser->recursiveTagParse($auxOut)."</div>";
			}
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
	 * This method tries to load all the useful information set between tags
	 * <pieceofcode> and </pieceofcode>.
	 * @param $input Configuration text to be analyzed.
	 */
	protected function loadVariables($input) {
		$this->_filename   = $this->getVariable($input, 'file');
		$this->_revision   = $this->getVariable($input, 'revision');
		$this->_connection = $this->getVariable($input, 'connection');
		$this->_lines      = explode('-', $this->getVariable($input, 'lines'));

		if(!isset($this->_lines[0]) || !isset($this->_lines[1]) || $this->_lines[0] > $this->_lines[1]) {
			unset($this->_lines);
			$this->_lines = array();
		}
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
