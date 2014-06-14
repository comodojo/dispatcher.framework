<?php namespace comodojo;

/**
 * standard spare parts random func
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

/**
 * Generate random alphanumerical string
 * 
 * @param	int		$length	[optional] The random string length; default 128
 * @return	string	
 */
function random($length=128) {
	
	if ($length == 128) {
		$randNum = md5(uniqid(rand(), true), 0);
	}
	if ($length < 128) {
		$randNum = substr(md5(uniqid(rand(), true)), 0, $length);
	}
	else {
		$numString = (int)($length/128) + 1;
		$randNum = "";
		for ($i = 0; $i < $numString; $i++) {
			$randNum .=  md5(uniqid(rand(), true));
		}
		$randNum = substr($randNum, 0, $length);
	}
	return $randNum;
	
}

?>