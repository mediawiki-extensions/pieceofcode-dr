<?php
/**
 * @file POCVersionManager.php
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

class POCVersionManager {
	/**
	 * @var POCVersionManager
	 */
	private static	$_Instance;

	/**
	 * @var POCErrorsHolder
	 */
	protected	$_errors;
	/**
	 * @var POCFlags
	 */
	protected	$_flags;
	/**
	 * @var string
	 */
	protected	$_dbtype;

	protected function __construct() {
		global $wgDBtype;

		$this->_errors = POCErrorsHolder::Instance();

		$this->_flags	= POCFlags::Instance();
		$this->_dbtype	= $wgDBtype;

		$this->upToVersion(PieceOfCode::Property('version'));
	}
	/**
	 * Prevent users to clone the instance.
	 */
	public function __clone() {
		trigger_error(__CLASS__.': Clone is not allowed.', E_USER_ERROR);
	}

	/*
	 * Protected Methods
	 */
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function getVersion() {
		$version = $this->_flags->get('POC_VERSION');
		if($version === false) {
			global	$wgDBprefix;
			global	$wgPieceOfCodeConfig;

			$dbr = &wfGetDB(DB_SLAVE);
			/*
			 * @author Alejandro Darío Simi
			 * @date 2010-09-07
			 * @warning
			 * This checking should be wrong, but it is here to solve
			 * possible problems with version 0.1.
			 * These kind of things happen when somebody (in this case
			 * ME) designs something for a version 0.1, and then new
			 * version comes out bringging a few details.
			 */
			if($dbr->tableExists($wgPieceOfCodeConfig['db-tablename'])) {
				$version = $this->setVersion('0.1');
			} else {
				$version = $this->setVersion(PieceOfCode::Property('version'));
			}
			if($version === false) {
				die(__FILE__.':'.__LINE__);
			}
		}
		return $version;
	}
	/**
	 * @todo doc
	 * @param $version @todo doc
	 * @return @todo doc
	 */
	protected function setVersion($version) {
		if($this->_flags->set('POC_VERSION', $version, 'S')) {
			die(__FILE__.':'.__LINE__);
		}
		return $this->_flags->get('POC_VERSION', true);
	}
	/**
	 * This is the main method to launches every upgrade in the right way. 
	 * @param $finalVersion This is the version number to reach. 
	 */
	protected function upToVersion($finalVersion) {
		$currentVersion = $this->getVersion();
		$updates        = 0;
		/*
		 * This loop run until current version is the last one
		 * 
		 * @code
		 * $updates < 100
		 * @endcode
		 * This condition avoids possible problems with versión checks
		 */
		while(version_compare($currentVersion, $finalVersion) < 0 && $updates < 100) {
			$updates++;
			
			switch($currentVersion) {
				case '0.1':
					$this->upToVersion0_2();
					$this->setVersion('0.2');
					break;
				default:
					die(__FILE__.':'.__LINE__); //@fixme This should print a error message.
			}
			$currentVersion = $this->getVersion();
		}
	}
	/**
	 * This method tries to upgrade system from verion 0.1 up to 0.2.
	 */
	protected function upToVersion0_2() {
		if($this->_dbtype == 'mysql') {
			global	$wgDBprefix;
			global	$wgPieceOfCodeConfig;

			$dbr = &wfGetDB(DB_SLAVE);
			if(!$dbr->fieldExists($wgPieceOfCodeConfig['db-tablename'], 'cod_count')) {
				$sql =	"alter table {$wgDBprefix}{$wgPieceOfCodeConfig['db-tablename']}\n".
					"        add (cod_count int(10) not null default '-1')";
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
	}
	/*
	 * Public class methods
	 */
	/**
	 * @return Returns the singleton instance of this class POCVersionManager.
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
