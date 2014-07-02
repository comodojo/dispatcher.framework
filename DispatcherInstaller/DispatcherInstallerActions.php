<?php namespace DispatcherInstaller;

/**
 * Dispatcher installer - a simple class (static methods) to manage plugin installations
 *
 * It currently support:
 * - dispatcher-plugin - generic plugins such as tracer, database, ...
 * - dispatcher-service-bundle - service bundles
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

use Composer\Script\Event;

class DispatcherInstallerActions {

	private static $vendor = 'vendor/';

	private static $plugins_cfg = 'configs/plugins-config.php';

	private static $routing_cfg = 'configs/routing-config.php';

	public static function postPackageInstall(Event $event) {

		$type = $event->getOperation()->getPackage()->getType();

		$name = $event->getOperation()->getPackage()->getName();

		$extra = $event->getOperation()->getPackage()->getExtra();

		$plugins_loaders = isset($extra["comodojo-plugin-load"]) ? $extra["comodojo-load"] : Array();

		$services_loaders = isset($extra["comodojo-service-route"]) ? $extra["comodojo-services-route"] : Array();

		// $folders_to_create = isset($extra["comodojo-folders-create"]) ? $extra["comodojo-folders-create"] : Array();

		// $files_to_create = isset($extra["comodojo-files-create"]) ? $extra["comodojo-files-create"] : Array();

		try {
			
			if ( $type == "dispatcher-plugin" ) self::loadPlugin($name, $plugins_loaders);

			if ( $type == "dispatcher-service-bundle" ) self::loadServices($name, $services_loaders);

			// self::create_folders($folders_to_create);

			// self::create_files($files_to_create);


		} catch (Exception $e) {
			
			throw $e;
			
		}

		echo "DispatcherInstaller install tasks completed\n";

	}

	public static function postPackageUninstall(Event $event) {

		$type = $event->getOperation()->getPackage()->getType();

		$name = $event->getOperation()->getPackage()->getName();

		$extra = $event->getOperation()->getPackage()->getExtra();

		// $folders_to_delete = isset($extra["comodojo-folders-create"]) ? $extra["comodojo-folders-create"] : Array();

		// $files_to_delete = isset($extra["comodojo-files-create"]) ? $extra["comodojo-files-create"] : Array();

		try {
			
			if ( $type == "dispatcher-plugin" ) self::unloadPlugin($name, $plugins_loaders);

			if ( $type == "dispatcher-service-bundle" ) self::unloadServices($name, $services_loaders);

			// self::delete_folders($folders_to_create);

			// self::delete_files($files_to_create);


		} catch (Exception $e) {
			
			throw $e;
			
		}

		echo "DispatcherInstaller uninstall tasks completed\n";

	}

	public static function loadPlugin($package_name, $package_loader) {

		$line_mark = "/****** PLUGIN - ".$package_name." - PLUGIN ******/";

		list($author,$name) = explode("/", $package_name);

		$plugin_path = self::$vendor.$author."/".$name."/";

		if ( is_array($package_loader) ) {

			$line_load = "";

			foreach ($package_loader as $loader) $line_load .= '$dispatcher->loadPlugin("$package_loader", "$plugin_path");'."\n";

		}
		else {
			$line_load = '$dispatcher->loadPlugin("$package_loader", "$plugin_path");'."\n";
		}
		
		$to_append = "\n\n".$line_mark."\n".$line_load.$line_mark;

		$action = file_put_contents(self::$plugins_cfg, $to_append, FILE_APPEND | LOCK_EX);

		if ( $action === false ) throw new Exception("Cannot activate plugin");

	}

	public static function unloadPlugin($package_name) {

		$line_mark = "/****** PLUGIN - ".$package_name." - PLUGIN ******/";

		$cfg = file(self::$plugins_cfg, FILE_IGNORE_NEW_LINES);

		$found = false;

		foreach ($cfg as $position => $line) {
			
			if ( stristr($line, $line_mark) ) {

				unset($cfg[$position]);

				$found = !$found;

			}

			else {

				if ( $found ) unset($cfg[$position]);
				else continue;

			}

		}

		$action = file_put_contents(self::$plugins_cfg, implode("\n", array_values($cfg)), LOCK_EX);

		if ( $action === false ) throw new Exception("Cannot deactivate plugin");

	}

	public static function loadService($package_name, $package_loader) {

		$line_mark = "/****** SERVICE - ".$package_name." - SERVICE ******/";

		$line_load = "";

		list($author,$name) = explode("/", $package_name);

		$service_path = self::$vendor.$author."/".$name."/";

		if ( is_array($package_loader) ) {

			foreach ($package_loader as $service) {

				if ( !isset($service["service"]) OR !isset($service["type"]) OR !isset($service["target"]) ) throw new Exception("Wrong service route");

				$service = $service["service"];
				$type = $service["type"];
				$target = service_path.$service["target"];

				if ( isset($service["parameters"]) AND @is_array($service["parameters"]) ) {
					$line_load .= '$dispatcher->setRoute("$service", "$type", "$target", ' . var_export($service["parameters"], true) . ', false);'."\n";
				}
				else {
					$line_load .= '$dispatcher->setRoute("$service", "$type", "$target", Array(), false);'."\n";
				}

			}

		}
		else throw new Exception("Wrong service loader");
		
		$to_append = "\n\n".$line_mark."\n".$line_load.$line_mark;

		$action = file_put_contents(self::$routing_cfg, $to_append, FILE_APPEND | LOCK_EX);

		if ( $action === false ) throw new Exception("Cannot activate service route");

	}

	public static function unloadService(package_name) {

		$line_mark = "/****** SERVICE - ".$package_name." - SERVICE ******/";

		$cfg = file(self::$routing_cfg, FILE_IGNORE_NEW_LINES);

		$found = false;

		foreach ($cfg as $position => $line) {
			
			if ( stristr($line, $line_mark) ) {

				unset($cfg[$position]);

				$found = !$found;

			}

			else {

				if ( $found ) unset($cfg[$position]);
				else continue;

			}

		}

		$action = file_put_contents(self::$routing_cfg, implode("\n", array_values($cfg)), LOCK_EX);

		if ( $action === false ) throw new Exception("Cannot deactivate route");

	}

}

?>