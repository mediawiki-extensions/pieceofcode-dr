<?php
/**
 * @file POCSVNConnections.php
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

class POCSVNConnections {
	/**
	 * @var POCSVNConnections
	 */
	protected static	$_Instance = null;

	/**
	 * @var array
	 */
	protected	$_connections;
	/**
	 * @var bool
	 */
	protected	$_isLoaded;

	protected function __construct() {
		$this->_connections = array();
		$this->_isLoaded    = false;

		$this->load();
	}

	/*
	 * Public methods.
	 */
	/**
	 * @todo doc
	 */
	public function getConnection($connection) {
		return (isset($this->_connections[$connection]) ? $this->_connections[$connection] : false);
	}
	/**
	 * @todo doc
	 */
	public function isLoaded() {
		return $this->_isLoaded;
	}

	/*
	 * Protected Methods
	 */
	/**
	 * @todo doc
	 */
	protected function load() {
		global	$wgPieceOfCodeSVNConnections;

		foreach($wgPieceOfCodeSVNConnections as $k => $v) {
			if(is_array($v)) {
				if(isset($v['url']) && isset($v['username'])) {
					$this->_connections[$k] = $v;
				}
				if(!isset($this->_connections[$k]['password'])) {
					$this->_connections[$k]['password'] = false;
				}
			}
		}
		$this->_isLoaded = true;

		return $this->isLoaded();
	}

	/*
	 * Public class methods
	 */
	/**
	 * @todo doc
	 */
	public static function Instance() {
		if(!POCSVNConnections::$_Instance) {
			POCSVNConnections::$_Instance = new POCSVNConnections();
		}
		return POCSVNConnections::$_Instance;
	}
}

?>
