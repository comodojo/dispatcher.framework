<?php namespace DispatcherInstaller;

/**
 * Dispatcher installer - a simple class to manage plugin installations
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

	private static $services_folder = 'services';

	private static $plugins_folder = 'plugins';

	private static $plugins_cfg = 'configs/plugins-config.php';

	public static function postPackageInstall(Event $event) {

		$type = $event->getOperation()->getPackage()->getType();

		$name = $event->getOperation()->getPackage()->getName();

		$extra = $event->getOperation()->getPackage()->getExtra();

		if ( $type == "dispatcher-plugin" ) {

			$loaders = isset($extra["comodojo-plugin-load"]) ? $extra["comodojo-load"] : Array();

			try {
			
				self::loadPlugin($name, $loaders);

			} catch (Exception $e) {
				
				throw $e;
				
			}

			echo "Plugin added in plugin-config\n";

		}
		elseif ( $type == "dispatcher-service-bundle" ) {

			$loaders = isset($extra["comodojo-service-route"]) ? $extra["comodojo-services-route"] : Array();

			try {
			
				self::loadServices($name, $loaders);

			} catch (Exception $e) {
				
				throw $e;
				
			}

			echo "Service added in plugin-config\n";

		}
		else {
			echo "DispatcherInstaller has nothing to do\n";
		}

    }

	public static function postPackageUninstall(Event $event) {

		$type = $event->getOperation()->getPackage()->getType();

		$name = $event->getOperation()->getPackage()->getName();

		if ( $type == "dispatcher-plugin" ) {

			try {
			
				self::unloadPlugin($name);

			} catch (Exception $e) {
				
				throw $e;
				
			}

			echo "Plugin removed from plugins-config\n";

		}
		elseif ( $type == "dispatcher-services-bundle" ) {

			try {
			
				self::unloadService($name);

			} catch (Exception $e) {
				
				throw $e;
				
			}

			echo "Service removed from plugins-config\n";

		}
		else {
			echo "DispatcherInstaller has nothing to do\n";
		}

	}

	public static function loadPlugin($package_name, $package_loader) {

		$line_mark = "/****** PLUGIN - ".$package_name." - PLUGIN ******/";

		list($vendor,$name) = explode("/", $package_name);

		$root_path = "vendor/";

		$vendor_path = $root_path.$vendor."/";

		$plugin_path = $vendor_path.$name."/";

		if ( is_array($package_loader) ) {

			$line_load = "";

			foreach ($package_loader as $loader) $line_load .= '$dispatcher->loadPlugin("'.$plugin_path.$line_load.'");';

		}
		else {
			$line_load = '$dispatcher->load("' . $package_loader . '", "' . $plugin_path . '");';
		}
		
		$to_append = "\n\n".$line_mark."\n".$line_load."\n".$line_mark;

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

		if ( $action === false ) throw new Exception("Cannot activate plugin");

	}

	public static function loadService($package_name, $package_loader) {

		$line_mark = "/****** SERVICE - ".$package_name." - SERVICE ******/";

		list($vendor,$name) = explode("/", $package_name);

		$root_path = "vendor/";

		$vendor_path = $root_path.$vendor."/";

		$plugin_path = $vendor_path.$name."/";

		if ( is_array($package_loader) ) {

			$line_load = "";

			foreach ($package_loader as $loader) $line_load .= '$dispatcher->setRoute("'.$plugin_path.$line_load.'");';

		}
		else {
			$line_load = '$dispatcher->load("' . $package_loader . '", "' . $plugin_path . '");';
		}
		
		$to_append = "\n\n".$line_mark."\n".$line_load."\n".$line_mark;

		$action = file_put_contents(self::$plugins_cfg, $to_append, FILE_APPEND | LOCK_EX);

		if ( $action === false ) throw new Exception("Cannot activate plugin");

	}

	public static function unloadService() {

	}

}

?>