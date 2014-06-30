<?php namespace comodojo\ObjectResult;

/**
 * The ObjectResultInterface, base interface that any result class should implement
 *
 * @package 	Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license 	GPL-3.0+
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

interface ObjectResultInterface {

	public function setService($service);

	public function getService();

	public function setStatusCode($code);

	public function getStatusCode();

	public function setContent($message);

	public function getContent();

	public function setLocation($location);

	public function getLocation();

	public function setHeader($header, $value);

	public function getHeader($header);

	public function unsetHeader($header);

	public function setHeaders($headers);

	public function getHeaders();

	public function unsetHeaders();

	public function setContentType($type);

	public function getContentType();

	public function setCharset($type);

	public function getCharset();

}

?>