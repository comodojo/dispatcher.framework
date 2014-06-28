<?php namespace DispatcherInstaller;

use Composer\Script\Event;

class DispatcherInstallerActions {

	private static $services_folder = 'service';

	private static $plugins_folder = 'plugins';

	private static $plugins_cfg = 'configs/plugins-config.php';

	public static function postPackageInstall(Event $event) {

		$type = $event->getOperation()->getPackage()->getType();

		if ( $type == "dispatcher-plugin" ) {

			$name = $event->getOperation()->getPackage()->getName();

			$extra = $event->getOperation()->getPackage()->getExtra();

			$loaders = isset($extra["comodojo-load"]) ? $extra["comodojo-load"] : Array();

			try {
			
				self::loadPlugin($name, $loaders);

			} catch (Exception $e) {
				
				throw $e;
				
			}

			echo "DispatcherInstaller has updated plugins-config\n";

		}
		elseif ( $type == "dispatcher-services-bundle" ) {

		}
		else {
			echo "DispatcherInstaller has nothing to do\n";
		}

    }

	public static function postPackageUninstall(Event $event) {

		$type = $event->getOperation()->getPackage()->getType();

		if ( $type == "dispatcher-plugin" ) {

			$name = $event->getOperation()->getPackage()->getName();

			try {
			
				self::unloadPlugin($name);

			} catch (Exception $e) {
				
				throw $e;
				
			}

			echo "DispatcherInstaller has updated plugins-config\n";

		}
		elseif ( $type == "dispatcher-services-bundle" ) {

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

			foreach ($package_loader as $loader) $line_load .= '$dispatcher->load("'.$plugin_path.$line_load.'");';

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

	public static function loadService() {

	}

	public static function unloadService() {

	}

}