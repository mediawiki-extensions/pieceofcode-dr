<?php
/**
 * @file POCFlags.php
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

class POCFlags {
	/**
	 * @var POCFlags
	 */
	private static	$_Instance;

	/**
	 * @var POCErrorsHolder
	 */
	protected	$_errors;
	/**
	 * @var array
	 */
	protected	$_flags;
	/**
	 * @var string
	 */
	protected	$_dbtype;

	protected function __construct() {
		global $wgDBtype;

		$this->_errors = POCErrorsHolder::Instance();

		$this->_flags	= array();
		$this->_dbtype	= $wgDBtype;

		$this->createTable();
	}
	/**
	 * Prevent users to clone the instance.
	 */
	public function __clone() {
		trigger_error(__CLASS__.': Clone is not allowed.', E_USER_ERROR);
	}

	/*
	 * Public methods.
	 */
	/**
	 * @todo doc
	 * @param string $code @todo doc
	 */
	public function get($code, $reload=false) {
		$out = false;

		if(!isset($this->_flags[$code]) || $reload) {
			global	$wgPieceOfCodeConfig;

			$this->_errors->clearError();
			if($this->_dbtype == 'mysql') {
				global	$wgPieceOfCodeConfig;

				$dbr = &wfGetDB(DB_SLAVE);
				$res = $dbr->select($wgPieceOfCodeConfig['db-tablename-flags'], array('flg_code', 'flg_type', 'flg_bvalue', 'flg_ivalue', 'flg_float', 'flg_svalue'),
					"flg_code = '{$code}'");
				if($row = $dbr->fetchRow($res)) {
					switch($row['flg_type']) {
						case 'B':
							$this->_flags[$code] = $row['flg_bvalue'];
							break;
						case 'I':
							$this->_flags[$code] = $row['flg_ivalue'];
							break;
						case 'F':
							$this->_flags[$code] = $row['flg_fvalue'];
							break;
						case 'S':
							$this->_flags[$code] = $row['flg_svalue'];
							break;
					}
					$out = $this->_flags[$code];
				}
			} else {
				$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype));
			}
		} else {
			$out = $this->_flags[$code];
		}
			
		return $out;
	}
	//	/**
	//	 * @todo doc
	//	 * @param string $connection @todo doc
	//	 * @param string $filepath @todo doc
	//	 * @param int $revision @todo doc
	//	 */
	//	public function getFile($connection, $filepath, $revision) {
	//		$out = false;
	//
	//		$conn = POCSVNConnections::Instance()->getConnection($connection);
	//		if($conn) {
	//			global	$wgPieceOfCodeConfig;
	//
	//			$this->_errors->clearError();
	//			$fileInfo = $this->selectFiles($connection, $filepath, $revision);
	//
	//			if(!$fileInfo && $this->_errors->ok()) {
	//				global	$wgUser;
	//				global	$wgPieceOfCodeConfig;
	//
	//				if($wgPieceOfCodeConfig['enableuploads'] && in_array('upload', $wgUser->getRights())) {
	//					$code   = md5("{$connection}{$revision}{$filepath}");
	//					$auxDir = $code[0].DIRECTORY_SEPARATOR.$code[0].$code[1].DIRECTORY_SEPARATOR;
	//					if(!is_dir($wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$auxDir)) {
	//						mkdir($wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$auxDir, 0755, true);
	//					}
	//					$uploadPath  = $auxDir.$code."_".$revision."_".basename($filepath);
	//					$svnPath     = $conn['url'].$filepath;
	//					$auxFileInfo = array(
	//						'connection'	=> $connection,
	//						'code'		=> $code,
	//						'path'		=> $filepath,
	//						'revision'	=> $revision,
	//						'lang'		=> $this->getLangFromExtension($filepath),
	//						'upload_path'	=> $uploadPath,
	//						'user'		=> $wgUser->getName(),
	//					);
	//
	//					if($this->_errors->ok() && $this->getSVNFile($conn, $auxFileInfo)) {
	//						$this->insertFile($auxFileInfo);
	//
	//						if($this->_errors->ok()) {
	//							$out = $this->selectFiles($connection, $filepath, $revision);
	//						}
	//					} else {
	//						if($this->_errors->ok()) {
	//							$this->_errors->setLastError(wfMsg('poc-errmsg-no-svn-file', $connection, $filepath, $revision));
	//						}
	//					}
	//				} else {
	//					$this->_errors->setLastError(wfMsg('poc-errmsg-no-upload-rights'));
	//				}
	//			} else {
	//				$out = $fileInfo;
	//			}
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-invalid-connection'));
	//		}
	//
	//		return $out;
	//	}
	//	/**
	//	 * @todo doc
	//	 */
	//	public function isLoaded() {
	//		return $this->_isLoaded;
	//	}
	//	/**
	//	 * @todo doc
	//	 * @param string $code @todo doc
	//	 */
	//	public function removeByCode($code) {
	//		$out = false;
	//
	//		global	$wgPieceOfCodeConfig;
	//
	//		$this->_errors->clearError();
	//		$fileInfo = $this->selectByCode($code);
	//		if($this->_errors->ok()) {
	//			global	$wgPieceOfCodeConfig;
	//			$upload_path = $wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$fileInfo['upload_path'];
	//			unlink($upload_path);
	//			if(!is_readable($upload_path)) {
	//				$this->deleteByCode($code);
	//				$out = $this->_errors->ok();
	//			} else {
	//				$this->_errors->setLastError(wfMsg('poc-errmsg-remove-file', $upload_path));
	//			}
	//		}
	//
	//		return $out;
	//	}
	//	/**
	//	 * @todo doc
	//	 * @param string $code @todo doc
	//	 */
	//	public function selectByCode($code) {
	//		$out = null;
	//
	//		if($this->_dbtype == 'mysql') {
	//			global	$wgPieceOfCodeConfig;
	//
	//			$dbr = &wfGetDB(DB_SLAVE);
	//			$res = $dbr->select($wgPieceOfCodeConfig['db-tablename-flags'], array('cod_id', 'cod_connection', 'cod_code', 'cod_path', 'cod_lang', 'cod_revision', 'cod_upload_path', 'cod_user', 'cod_timestamp'),
	//			"cod_code = '{$code}'");
	//			if($row = $dbr->fetchRow($res)) {
	//				$out = array(
	//					'id'		=> $row[0],
	//					'connection'	=> $row[1],
	//					'code'		=> $row[2],
	//					'path'		=> $row[3],
	//					'lang'		=> $row[4],
	//					'revision'	=> $row[5],
	//					'upload_path'	=> $row[6],
	//					'user'		=> $row[7],
	//					'timestamp'	=> $row[8]
	//				);
	//			} else {
	//				$this->_errors->setLastError(wfMsg('poc-errmsg-query-no-result'));
	//			}
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype));
	//		}
	//
	//		return $out;
	//	}
	//	/**
	//	 * @todo doc
	//	 * @param string $connection @todo doc
	//	 * @param string $filepath @todo doc
	//	 * @param stirng $revision @todo doc
	//	 */
	//	public function selectFiles($connection=false, $filepath=false, $revision=false) {
	//		$out = null;
	//
	//		$multiple = ($connection === false || $filepath === false || $revision === false);
	//
	//		if($this->_dbtype == 'mysql') {
	//			global	$wgPieceOfCodeConfig;
	//
	//			$dbr = &wfGetDB(DB_SLAVE);
	//			$res = $dbr->select($wgPieceOfCodeConfig['db-tablename-flags'], array('cod_id', 'cod_connection', 'cod_code', 'cod_path', 'cod_lang', 'cod_revision', 'cod_count', 'cod_upload_path', 'cod_user', 'cod_timestamp'),
	//			(!$multiple?"cod_connection = '{$connection}' and cod_path = '{$filepath}' and cod_revision = '{$revision}'":""));
	//			if($multiple) {
	//				$out = array();
	//				while($row = $dbr->fetchRow($res)) {
	//					$out[] = array(
	//							'id'		=> $row[0],
	//							'connection'	=> $row[1],
	//							'code'		=> $row[2],
	//							'path'		=> $row[3],
	//							'lang'		=> $row[4],
	//							'revision'	=> $row[5],
	//							'count'		=> $row[6],
	//							'upload_path'	=> $row[7],
	//							'user'		=> $row[8],
	//							'timestamp'	=> $row[9]
	//					);
	//				}
	//			} else {
	//				if($row = $dbr->fetchRow($res)) {
	//					$out = array(
	//							'id'		=> $row[0],
	//							'connection'	=> $row[1],
	//							'code'		=> $row[2],
	//							'path'		=> $row[3],
	//							'lang'		=> $row[4],
	//							'revision'	=> $row[5],
	//							'count'		=> $row[6],
	//							'upload_path'	=> $row[7],
	//							'user'		=> $row[8],
	//							'timestamp'	=> $row[9]
	//					);
	//				}
	//			}
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype));
	//		}
	//
	//		return $out;
	//	}

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
			if($dbr->tableExists($wgPieceOfCodeConfig['db-tablename-flags'])) {
				$out = true;	// @todo need to check the table struct is the same or not???
			} else {
				$sql =	"create table ".$wgDBprefix.$wgPieceOfCodeConfig['db-tablename-flags']."(\n".
					"        flg_code           varchar(20)               not null primary key,\n".
					"        flg_type	    enum ('B', 'I', 'S', 'F') not null default 'S',\n".
					"        flg_bvalue         boolean                   not null default false,\n".
					"        flg_ivalue         integer                   not null default '0',\n".
					"        flg_float          float                     not null default '0',\n".
					"        flg_svalue         varchar(255)              not null default '',\n".
					"        flg_timestamp      timestamp default current_timestamp\n".
					")";
				$error = $dbr->query($sql);
				if($error === true) {
					$out = true;
				} else {
					die(__FILE__.":".__LINE__);
				}
			}
		} else {
			$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype));
		}

		return $out;
	}
	//	/**
	//	 * @todo doc
	//	 * @param string $filename @todo doc
	//	 */
	//	protected function getLangFromExtension($filename) {
	//		$out = '';
	//
	//		global	$wgPieceOfCodeConfig;
	//
	//		$pieces = explode('.', basename($filename));
	//		$len    = count($pieces);
	//		if($len > 1) {
	//			$ext    = strtolower($pieces[$len-1]);
	//			foreach($wgPieceOfCodeConfig['fontcodes'] as $type => $extList) {
	//				if(in_array($ext, $extList)) {
	//					$out = $type;
	//					break;
	//				}
	//			}
	//			if(!$out && in_array($ext, $wgPieceOfCodeConfig['fontcodes-forbidden'])) {
	//				$this->_errors->setLastError(wfMsg('poc-errmsg-forbidden-tcode', $pieces[count($pieces)-1]));
	//			} elseif(!$out) {
	//				$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-tcode', $pieces[count($pieces)-1]));
	//				$out = 'text';
	//			}
	//		} elseif($wgPieceOfCodeConfig['fontcodes-allowempty']) {
	//			$out = 'text';
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-empty-tcode'));
	//		}
	//
	//		return $out;
	//	}
	//	/**
	//	 * @todo doc
	//	 */
	//	protected function getSVNFile(&$connInfo, &$fileInfo) {
	//		$out = false;
	//
	//		global	$wgPieceOfCodeConfig;
	//
	//		$filepath = $wgPieceOfCodeConfig['uploaddirectory'].DIRECTORY_SEPARATOR.$fileInfo['upload_path'];
	//		if(!is_readable($filepath)) {
	//			$command = $wgPieceOfCodeConfig['svn-binary']." ";
	//			$command.= "cat ";
	//			$command.= "\"{$connInfo['url']}{$fileInfo['path']}\" ";
	//			$command.= "-r{$fileInfo['revision']} ";
	//			if(isset($connInfo['username'])) {
	//				$command.= "--username {$connInfo['username']} ";
	//			}
	//			if(isset($connInfo['password'])) {
	//				$command.= "--password {$connInfo['password']} ";
	//			}
	//			$command.= "> \"{$filepath}\"";
	//			passthru($command, $error);
	//
	//			if(!$error && is_readable($filepath)) {
	//				$out = true;
	//			} elseif($error && is_readable($filepath)) {
	//				unlink($filepath);
	//			} elseif(is_readable($filepath)) {
	//				$this->_errors->setLastError(wfMsg('poc-errmsg-svn-no-file', $filepath));
	//			}
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-svn-file-exist', $filepath));
	//		}
	//
	//		return $out;
	//	}
	//	/**
	//	 * @todo doc
	//	 * @param unknown_type $fileInfo @todo doc
	//	 */
	//	protected function insertFile(&$fileInfo) {
	//		$out = false;
	//
	//		if($this->_dbtype == 'mysql') {
	//			global	$wgPieceOfCodeConfig;
	//
	//			if($this->_errors->ok()) {
	//				$dbr = &wfGetDB(DB_SLAVE);
	//				$res = $dbr->insert($wgPieceOfCodeConfig['db-tablename-flags'],
	//				array(	'cod_connection'	=> $fileInfo['connection'],
	//						'cod_code'		=> $fileInfo['code'],
	//						'cod_path'		=> $fileInfo['path'],
	//						'cod_lang'		=> $fileInfo['lang'],
	//						'cod_revision'		=> $fileInfo['revision'],
	//						'cod_upload_path'	=> $fileInfo['upload_path'],
	//						'cod_user'		=> $fileInfo['user'],
	//				));
	//				if($res === true) {
	//					$out = true;
	//				} else {
	//					$this->_errors->setLastError(wfMsg('poc-errmsg-no-insert'));
	//				}
	//			}
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype));
	//		}
	//
	//		return	$out;
	//	}
	//	/**
	//	 * @todo doc
	//	 * @param string $code @todo doc
	//	 */
	//	protected function deleteByCode($code) {
	//		$out = null;
	//
	//		if($this->_dbtype == 'mysql') {
	//			global	$wgPieceOfCodeConfig;
	//
	//			$dbr = &wfGetDB(DB_SLAVE);
	//			$res = $dbr->delete($wgPieceOfCodeConfig['db-tablename-flags'], array('cod_code' => $code));
	//			if($res !== true) {
	//				$this->_errors->setLastError(wfMsg('poc-errmsg-query-no-delete'));
	//			}
	//		} else {
	//			$this->_errors->setLastError(wfMsg('poc-errmsg-unknown-dbtype', $this->_dbtype));
	//		}
	//
	//		return $out;
	//	}

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
