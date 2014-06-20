<?php namespace comodojo\ObjectRoutingTable;

class ObjectRoutingTable {

	private $table = Array();

	private $supported_route_types = Array("ROUTE","REDIRECT","ERROR","INTERNAL");

	public final function __construct() {

		$this->table["default"] = Array(
			"type"			=>	"ERROR",
			"target"		=>	404,
			"parameters"	=>	Array()
		);

	}

	public function setRoute($service, $type, $target, $parameters) {

		$this->table[$service] = Array(
			"type"			=>	$type,
			"target"		=>	$target,
			"parameters"	=>	$parameters
		);

		return $this;

	}

	public function unsetRoute($service) {

		if ( isset($this->table[$service]) ) {

			unset($this->table[$service]);

			return true;

		}

		return false;

	}

	public function getRoute($service) {

		return isset($this->table[$service]) ? true : false;

	}

	public function getRoutes() {

		return $this->table;

	}

	public function routable($target) {

		return is_readable($target) ? true : false;

	}

}

?>