<?php namespace comodojo\Dispatcher\ObjectRoutingTable;

/**
 * Routing table object
 * 
 * @package		Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license		GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ObjectRoutingTable {

	/**
	 * The table. It is an associative array; keys represents services,
	 * values the route.
	 *
	 * There are two special route types:
	 * - default route "default" that will be suggested if no other route will
	 *	 be identified
	 * - special route "" (empty route); it is the landing page - no service - route
	 *	 and may not be specified (in this case "default" route will be suggested)
	 *
	 * @var array
	 */
	private $table = Array();

	/**
	 * Supported route types.
	 * - ROUTE: identifies a real service route; service will be engaged
	 * 			to process request.
	 * - REDIRECT: generate a redirect response (such as 307)
	 * - ERROR: generate an error response
	 *
	 * @var array
	 */
	private $supported_route_types = Array("ROUTE","REDIRECT","ERROR");

	/**
	 * Object constructor.
	 *
	 * It just inject default route into routing table
	 */
	public final function __construct() {

		$this->table["default"] = Array(
			"type"			=>	"ERROR",
			"target"		=>	"Service not found",
			"parameters"	=>	Array(
				"errorCode" 	=> 404
			)
		);

	}

	/**
	 * Set punctual route for a service
	 *
	 * @param 	string 	$service 	The service name
	 * @param 	string 	$type 		The route type
	 * @param 	string 	$target 	The target (php file for a service, content for error, location for redirect)
	 * @param 	string 	$parameters (optional) array of parameters such as cache, ttl, ...
	 *
	 * @return 	Object 	$this
	 */
	public function setRoute($service, $type, $target, $parameters=Array()) {

		$this->table[$service] = Array(
			"type"			=>	$type,
			"target"		=>	$target,
			"parameters"	=>	$parameters
		);

		return $this;

	}

	/**
	 * Unset a punctual route
	 *
	 * @param 	string 	$service 	The service name
	 *
	 * @return 	bool
	 */
	public function unsetRoute($service) {

		if ( isset($this->table[$service]) ) {

			unset($this->table[$service]);

			return true;

		}

		return false;

	}

	/**
	 * Get route depending on $service (aka the router logic)
	 *
	 * @param 	string 	$service 	The service name
	 *
	 * @return 	array 	The route array
	 */
	public function getRoute($service) {

		// If service is null, route to landing or (if not specified) to default route

		if ( empty($service) ) return $this->route_to_landing();

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

		else if ( DISPATCHER_AUTO_ROUTE AND $this->routable($service.".php", true) ) {

			return Array(
				"type"			=>	"ROUTE",
				"target"		=>	DISPATCHER_SERVICES_FOLDER.$service.".php",
				"parameters"	=>	Array()
			);

		}

		// If a route cannot be traced, emit a 404 - not found - response

		else return $this->route_to_notfound();

	}

	/**
	 * Get entire routing table
	 *
	 * @return 	array 	The current routing table
	 */
	public function getRoutes() {

		return $this->table;

	}

	/**
	 * Check if target is routable (it exsists?)
	 *
	 * @param 	string 	$target 	The target file
	 *
	 * @return 	bool
	 */
	private function routable($target, $relative=false) {

		if ( $relative ) return is_readable(DISPATCHER_SERVICES_FOLDER.$target) ? true : false;

		else return is_readable($target) ? true : false;

	}

	/**
	 * Route to landing page (i.e. no service)
	 *
	 * @return 	array 	The route array
	 */
	private function route_to_landing() {

		if ( isset($this->table[""]) ) {

			return $this->table[""];

		}

		else return $this->route_to_default();

	}

	/**
	 * Route to default
	 *
	 * @return 	array 	The route array
	 */
	private function route_to_default() {

		if ( isset($this->table["default"]) ) {

			return $this->table["default"];

		}

		else return $this->route_to_notfound();

	}

	/**
	 * Route to not found (404)
	 *
	 * @return 	array 	The route array
	 */
	private function route_to_notfound() {

		return Array(
			"type"			=>	"ERROR",
			"target"		=>	"Service not found",
			"parameters"	=>	Array(
				"errorCode" 	=> 404
			)
		);

	}

	/**
	 * Route to service error (nowhere)
	 *
	 * @return 	array 	The route array
	 */
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