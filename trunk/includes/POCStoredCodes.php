<?php
/**
 * @file POCStoredCodes.php
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

class POCStoredCodes {
	/**
	 * @var POCStoredCodes
	 */
	private static	$_Instance;

	/**
	 * @var bool
	 */
	protected	$_isLoaded;
	/**
	 * @var string
	 */
	protected	$_dbtype;

	protected function __construct() {
		global $wgDBtype;

		$this->_isLoaded    = false;
		$this->_dbtype      = $wgDBtype;

		$this->createTable();
	}
	/**
	 * Prevent users to clone the instance.
	 */
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	/*
	 * Public methods.
	 */
	/**
	 * @todo doc
	 * @param unknown_type $connection @todo doc
	 * @param unknown_type $filepath @todo doc
	 */
	public function getFile($connection, $filepath, $revision) {
		$out = false;

		$conn = POCSVNConnections::Instance()->getConnection($connection);
		if($conn) {
			global	$wgPieceOfCodeConfig;

			$this->setLastError();
			$fileInfo = $this->selectFiles($connection, $filepath, $revision);
				
			if(!$fileInfo && !$this->getLastError()) {
				global	$wgUser;

				$code   = md5("{$connection}{$revision}{$filepath}");
				$auxDir = $code[0].DIRECTORY_SEPARATOR.$code[0].$code[1].DIRECTORY_SEPARATOR;
				if(!is_dir($wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$auxDir)) {
					mkdir($wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$auxDir, 0755, true);
				}
				$uploadPath  = $auxDir.$code."_".$revision."_".basename($filepath);
				$svnPath     = $conn['url'].$filepath;
				$auxFileInfo = array(
					'connection'	=> $connection,
					'code'		=> $code,
					'path'		=> $filepath,
					'revision'	=> $revision,
					'lang'		=> $this->getLangFromExtension($filepath),
					'upload_path'	=> $uploadPath,
					'user'		=> $wgUser->getName(),
				);

				if(!$this->getLastError() && $this->getSVNFile($conn, $auxFileInfo)) {
					$this->insertFile($auxFileInfo);
						
					if(!$this->getLastError()) {
						$out = $this->selectFiles($connection, $filepath, $revision);
					}
				} else {
					echo("<h4>".$this->getLastError()."</h4>"); //@todo ver por qué este mensage no llega a POC para ser impreso.
					if(!$this->getLastError()) {
						$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-no-svn-file', $connection, $filepath, $revision)));
					}
				}
			} else {
				$out = $fileInfo;
			}
		} else {
			$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-invalid-connection')));
		}

		return $out;
	}
	/**
	 * @todo doc
	 */
	public function isLoaded() {
		return $this->_isLoaded;
	}
	/**
	 * @todo doc
	 * @param unknown_type $connection @todo doc
	 * @param unknown_type $filepath @todo doc
	 */
	public function selectFiles($connection=false, $filepath=false, $revision=false) {
		$out = null;

		$multiple = ($connection === false || $filepath === false || $revision === false);

		if($this->_dbtype == 'mysql') {
			global	$wgDBprefix;
			global	$wgPieceOfCodeConfig;

			$dbr = &wfGetDB(DB_SLAVE);
			$res = $dbr->select($wgPieceOfCodeConfig['db-tablename'], array('cod_id', 'cod_connection', 'cod_code', 'cod_path', 'cod_lang', 'cod_revision', 'cod_upload_path', 'cod_user', 'cod_timestamp'),
			(!$multiple?"cod_connection = '{$connection}' and cod_path = '{$filepath}' and cod_revision = '{$revision}'":""));
			if($multiple) {
				$out = array();
				while($row = $dbr->fetchRow($res)) {
					$out[] = array(
							'id'		=> $row[0],
							'connection'	=> $row[1],
							'code'		=> $row[2],
							'path'		=> $row[3],
							'lang'		=> $row[4],
							'revision'	=> $row[5],
							'upload_path'	=> $row[6],
							'user'		=> $row[7],
							'timestamp'	=> $row[8]
					);
				}
			} else {
				if($row = $dbr->fetchRow($res)) {
					$out = array(
							'id'		=> $row[0],
							'connection'	=> $row[1],
							'code'		=> $row[2],
							'path'		=> $row[3],
							'lang'		=> $row[4],
							'revision'	=> $row[5],
							'upload_path'	=> $row[6],
							'user'		=> $row[7],
							'timestamp'	=> $row[8]
					);
				}
			}
		} else {
			$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype)));
		}

		return $out;
	}

	/*
	 * Protected Methods
	 */
	/**
	 * @todo doc
	 */
	protected function createTable() {
		$out = false;

		if($this->_dbtype == 'mysql') {
			global	$wgDBprefix;
			global	$wgPieceOfCodeConfig;

			$dbr = &wfGetDB(DB_SLAVE);
			if($dbr->tableExists($wgPieceOfCodeConfig['db-tablename'])) {
				$out = true;	// @todo need to check the table struct is the same or not???
			} else {
				$sql =	"create table ".$wgDBprefix.$wgPieceOfCodeConfig['db-tablename']."(\n".
					"        cod_id             integer      not null auto_increment primary key,\n".
					"        cod_connection     varchar(20)  not null,\n".
					"        cod_code           varchar(40)  not null,\n".
					"        cod_path           varchar(255) not null,\n".
					"        cod_lang	    varchar(10)  not null default 'text',\n".
					"        cod_revision	    integer      not null default '-1',\n".
					"        cod_upload_path    varchar(255) not null,\n".
					"        cod_user           varchar(40)  not null,\n".
					"        cod_timestamp      timestamp default current_timestamp\n".
					")";
				$error = $dbr->query($sql);
				if($error === true) {
					$out = true;
				} else {
					die(__FILE__.":".__LINE__);
				}
			}
		} else {
			$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype)));
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param string $msg @todo doc
	 */
	protected function formatErrorMessage($msg) {
		return PieceOfCode::Instance()->formatErrorMessage($msg);
	}
	/**
	 * @todo doc
	 * @param string $filename @todo doc
	 */
	protected function getLangFromExtension($filename) {
		$out = '';

		global	$wgPieceOfCodeConfig;

		$pieces = explode('.', basename($filename));
		$len    = count($pieces);
		if($len > 1) {
		$ext    = $pieces[$len-1];
		foreach($wgPieceOfCodeConfig['fontcodes'] as $type => $extList) {
			if(in_array($ext, $extList)) {
				$out = $type;
				break;
			}
		}
		if(!$out && in_array($ext, $wgPieceOfCodeConfig['fontcodes-forbidden'])) {
			$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-forbidden-tcode', $pieces[count($pieces)-1])));
		} elseif(!$out) {
				$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-unknown-tcode', $pieces[count($pieces)-1])));
			$out = 'text';
		}
		} elseif($wgPieceOfCodeConfig['fontcodes-allowempty']) {
			$out = 'text';
		} else {
				$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-empty-tcode')));
		}

		return $out;
	}
	/**
	 * @todo doc
	 */
	protected function getLastError() {
		return PieceOfCode::Instance()->getLastError();
	}
	/**
	 * @todo doc
	 */
	protected function getSVNFile(&$connInfo, &$fileInfo) {
		$out = false;

		global	$wgPieceOfCodeConfig;

		$filepath = $wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$fileInfo['upload_path'];
		if(!is_readable($filepath)) {
			$command = $wgPieceOfCodeConfig['svn-binary']." ";
			$command.= "cat ";
			$command.= "'{$connInfo['url']}{$fileInfo['path']}' ";
			$command.= "-r{$fileInfo['revision']} ";
			if(isset($connInfo['username'])) {
				$command.= "--username '{$connInfo['username']}' ";
			}
			if(isset($connInfo['password'])) {
				$command.= "--password '{$connInfo['password']}' ";
			}
			$command.= "> '{$filepath}'";
			passthru($command, $error);

			if(!$error && is_readable($filepath)) {
				$out = true;
			} elseif($error && is_readable($filepath)) {
				unlink($filepath);
			}
		} else {
			$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-svn-file-exist', $filepath)));
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param unknown_type $fileInfo @todo doc
	 */
	protected function insertFile(&$fileInfo) {
		$out = false;

		if($this->_dbtype == 'mysql') {
			global	$wgDBprefix;
			global	$wgUser;
			global	$wgPieceOfCodeConfig;

			if(!$this->getLastError()) {
				$dbr = &wfGetDB(DB_SLAVE);
				$res = $dbr->insert($wgPieceOfCodeConfig['db-tablename'],
				array(	'cod_connection'	=> $fileInfo['connection'],
						'cod_code'		=> $fileInfo['code'],
						'cod_path'		=> $fileInfo['path'],
						'cod_lang'		=> $fileInfo['lang'],
						'cod_revision'		=> $fileInfo['revision'],
						'cod_upload_path'	=> $fileInfo['upload_path'],
						'cod_user'		=> $wgUser->getName(),
				));
				if($res === true) {
					$out = true;
				} else {
					$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-no-insert')));
				}
			}
		} else {
			$this->setLastError($this->formatErrorMessage(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype)));
		}

		return	$out;
	}
	/**
	 * @todo doc
	 * @param string $msg @todo doc
	 */
	protected function setLastError($msg="") {
		return PieceOfCode::Instance()->setLastError($msg);
	}

	/*
	 * Public class methods
	 */
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
}

?>
