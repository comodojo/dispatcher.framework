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

	public final function add($event, $callback) {

		if ( isset($this->hooks[$event]) ) array_push($this->hooks[$event], $callback);

		else $this->hooks[$event] = Array($callback);

	}

	public final function remove($event, $callback=NULL) {

		if ( is_null($callback) AND isset($this->hooks[$event]) ) {

			unset($this->hooks[$event]);

			return true;

		}

		else if ( isset($this->hooks[$event]) ) {

			$callback_position = array_search($callback, $this->hooks[$event]);

			if ( $callback_position === false ) return false;

			else {

				unset($this->hooks[$event][$callback_position]);

				return true;

			}

		}

		else return false;

	}

	public final function fire($event, \comodojo\ObjectResult\ObjectResultInterface $data) {

		$value = $data;

		if ( isset($this->hooks[$event]) ) {

			foreach($this->hooks[$event] as $callback) {

				$return_value = NULL;

				if ( is_callable($callback) ) {

					try {
						
						$return_value = call_user_func($callback, $value);

					} catch (Exception $e) {
						
						debug("Error running hook ".$event." - ".$e->getMessage(), "ERROR", "events");
						continue;

					}

				}
				else {

					debug("Skipping not-callable hook ".$event, "WARNING", "events");
					continue;

				}

				$value = $return_value instanceof \comodojo\ObjectResult\ObjectResultInterface ? $return_value : $value;
			
			}

			return $value;

		}

		return $data;

	}

}

?>