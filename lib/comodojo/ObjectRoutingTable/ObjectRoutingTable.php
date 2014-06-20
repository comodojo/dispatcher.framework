<?php namespace comodojo\ObjectRoutingTable;

class ObjectRoutingTable {

	private $table = Array();

	private $supported_route_types = Array("ROUTE","REDIRECT","ERROR");

	public final function __construct() {

		$this->table["default"] = Array(
			"type"			=>	"ERROR",
			"target"		=>	"Default service not found",
			"parameters"	=>	Array(
				"errorCode" 	=> 404
			)
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

	/**
	 * The routing logic
	 *
	 */
	public function getRoute($service) {

		// If service is null, return default route

		if ( is_null($service) ) return $this->route_to_default();

		// If service is in table, check if it is routable (in case of "ROUTE" route).
		// If true, route request
		// If false, return a 500 error

		if ( isset($this->table[$service]) ) {

			if ( $this->table[$service]["type"] == "ROUTE" ) {

				if ( $this->routable($this->table[$service]["target"]) ) return $this->table[$service];

				else return $this->route_to_nowhere();

			}

			else return $this->table[$service];

		}

		// If autoroute is enabled, try to match service name with a service file.
		// If true, try to route request

		else if ( DISPATCHER_AUTO_ROUTE AND $this->routable($service) ) {

			return Array(
				"type"			=>	"ROUTE",
				"target"		=>	$service,
				"parameters"	=>	Array()
			);

		}

		// If a route cannot be traced, emit a 404 - not found - response

		else return $this->route_to_notfound();

	}

	public function getRoutes() {

		return $this->table;

	}

	private function routable($target) {

		return is_readable(DISPATCHER_SERVICE_FOLDER.$target) ? true : false;

	}

	private function route_to_default() {

		if ( isset($this->table["default"]) ) {

			return $this->table["default"];

		}

		else return $this->route_to_notfound();

	}

	private function route_to_notfound() {

		return Array(
			"type"			=>	"ERROR",
			"target"		=>	"Service not found",
			"parameters"	=>	Array(
				"errorCode" 	=> 404
			)
		);

	}

	private function route_to_nowhere() {

		return Array(
			"type"			=>	"ERROR",
			"target"		=>	"Service not runnable",
			"parameters"	=>	Array(
				"errorCode" 	=> 500
			)
		);

	}

}

?>