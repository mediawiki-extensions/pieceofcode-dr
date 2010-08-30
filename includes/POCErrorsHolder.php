<?php
/**
 * @file POCErrorsHolder.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-08-30
 */

class POCErrorsHolder {
	/**
	 * @var POCErrorsHolder
	 */
	private static	$_Instance;

	/**
	 * Error messages prefix.
	 * @var string
	 */
	protected	$ERROR_PREFIX = "DR_PieceOfCode Error: ";
	/**
	* @var string
	*/
	protected	$_lastError;

	protected function __construct() {
		$this->_lastError = "";
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
	 * @param string $msg @todo doc
	 */
	public function clearError() {
		return $this->setLastError("", false);
	}
	/**
	 * @todo doc
	 * @param string $msg @todo doc
	 */
	public function formatErrorMessage($message) {
		return "<span style=\"color:red;font-weight:bold;\">".$this->ERROR_PREFIX."$message</span>";
	}
	/**
	 * @todo doc
	 */
	public function getLastError() {
		return $this->_lastError;
	}
	/**
	 * @todo doc
	 * @param string $msg @todo doc
	 */
	public function setLastError($message="", $autoFormat=true) {
		$this->_lastError = ($autoFormat ? $this->formatErrorMessage($message) : $message);

		return $this->getLastError();
	}

	/*
	 * Protected Methods
	 */

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
