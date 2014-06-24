<?php namespace comodojo;

/**
 * standard spare parts events class
 * 
 * @package 	Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
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

class events {

	private $hooks = Array();

	public final function __construct() {

		debug(' + Events engine up and running','INFO','events');

	}

	public final function add($event, $callback, $method=NULL) {

		if ( is_null($method) ) {

			if ( isset($this->hooks[$event]) ) array_push($this->hooks[$event], $callback);

			else $this->hooks[$event] = Array($callback);

		}

		else {

			if ( isset($this->hooks[$event]) ) array_push($this->hooks[$event], Array($callback, $method));

			else $this->hooks[$event] = Array(Array($callback, $method));

		}

	}

	public final function remove($event, $callback=NULL) {

		if ( is_null($callback) AND isset($this->hooks[$event]) ) {

			unset($this->hooks[$event]);

			return true;

		}

		else if ( isset($this->hooks[$event]) ) {

			foreach ($this->hooks[$event] as $key => $hook) {
				
				if ( is_array($hook) ) {

					if ( $hook[0] == $callback ) {

						unset($this->hooks[$event][$key]);

						return true;

					}

				}
				else {

					if ( $hook == $callback ) {

						unset($this->hooks[$event][$key]);

						return true;

					}

				}

			}

			return false;

			// $callback_position = array_search($callback, $this->hooks[$event]);

			// if ( $callback_position === false ) return false;

			// else {

			// 	unset($this->hooks[$event][$callback_position]);

			// 	return true;

			// }

		}

		else return false;

	}

	public final function fire($event, $type, $data) {

		debug("Firing event ".$event, "DEBUG", "events");

		$value = $data;

		if ( isset($this->hooks[$event]) ) {

			foreach($this->hooks[$event] as $callback) {

				$return_value = NULL;

				if ( is_array($callback) ) {

					if ( is_callable(Array($callback[0], $callback[1])) ) {

						try {
							
							$return_value = call_user_func(Array($callback[0], $callback[1]), $value);

						} catch (Exception $e) {
							
							debug("Error running hook ".$event." - ".$e->getMessage(), "ERROR", "events");
							continue;

						}

					}
					else {

						debug("Skipping not-callable hook ".$event."::".$callback[0].":".$callback[1], "WARNING", "events");
						continue;

					}

				}
				else {

					if ( is_callable($callback) ) {

						try {
							
							$return_value = call_user_func($callback, $value);

						} catch (Exception $e) {
							
							debug("Error running hook ".$event." - ".$e->getMessage(), "ERROR", "events");
							continue;

						}

					}
					else {

						debug("Skipping not-callable hook ".$event."::".$callback, "WARNING", "events");
						continue;

					}

				}

				switch ($type) {

					case 'DISPATCHER':
						
						$value = is_bool($return_value) ? $return_value : $value;

						break;

					case 'REQUEST':
						
						$value = $return_value instanceof \comodojo\ObjectRequest\ObjectRequest ? $return_value : $value;

						break;

					case 'TABLE':
						
						$value = $return_value instanceof \comodojo\ObjectRoutingTable\ObjectRoutingTable ? $return_value : $value;

						break;
					
					case 'ROUTE':
						
						$value = $return_value instanceof \comodojo\ObjectRoute\ObjectRoute ? $return_value : $value;

						break;
					
					case 'RESULT':
						
						$value = $return_value instanceof \comodojo\ObjectResult\ObjectResultInterface ? $return_value : $value;

						break;
					
					default:
						
						$value = $value;

						break;

				}
			
			}

			return $value;

		}

		return $data;

	}

}

?>