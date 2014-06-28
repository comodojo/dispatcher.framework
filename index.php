<?php use comodojo\dispatcher;

/**
 * Comodojo dispatcher - REST services microframework
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

/*
 |--------------------------------
 | Load dispatcher configuration
 |--------------------------------
 |
 | Load defined constants via dispatcher-config
 |
 */
require "configs/dispatcher-config.php";

/*
 |--------------------------------
 | Autoloader
 |--------------------------------
 |
 | Register the autoloader, located in vendor
 | directory. In a composer installation, this
 | will be handled directly with composer.
 |
 */
require 'vendor/autoload.php';

/*
 |--------------------------------
 | Init a dispatcher instance
 |--------------------------------
 |
 | Create the dispatcher instance
 |
 */
$dispatcher = new dispatcher();

/*
 |--------------------------------
 | Load routing table
 |--------------------------------
 |
 | Load site-specific routing options from
 | routing configuration.
 |
 */
require "configs/routing-config.php";

/*
 |--------------------------------
 | Load plugins
 |--------------------------------
 |
 | Load installed plugins
 |
 */
require "configs/plugins-config.php";

/*
 |--------------------------------
 | Dispatch!
 |--------------------------------
 |
 | Handle request, dispatch result :)
 |
 */
$dispatcher->dispatch();

?>