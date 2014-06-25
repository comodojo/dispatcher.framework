<?php namespace comodojo\composer;

use Composer\Script\Event;

class DispatcherInstallActions {

	private static $configs_folder = 'configs';

	private static $services_folder = 'service';

	private static $cache_folder = 'cache';

	private static $plugins_folder = 'plugins';

	private static $htaccess = '.htaccess';

	private static $index = 'index.php';

	public static function postRootInstall(Event $event) {

		$root_folder = __DIR__ . '/../../../../../..';

		try {
			
			self::iterative_directory_copier(self::$configs_folder,$root_folder.self::$configs_folder);

			self::iterative_directory_copier(self::$services_folder,$root_folder.self::$services_folder);

			self::iterative_directory_copier(self::$cache_folder,$root_folder.self::$cache_folder);

			self::iterative_directory_copier(self::$plugins_folder,$root_folder.self::$plugins_folder);	

			self::plain_file_copier(self::$htaccess,$root_folder.self::$htaccess);	

			self::plain_file_copier(self::$index,$root_folder.self::$index);						

		} catch (Exception $e) {
			
			throw $e;

		}

	}

	public static function postUpdate(Event $event) {

		$root_folder = __DIR__ . '/../../../../../..';

		try {
			
			self::plain_file_copier(self::$htaccess,$root_folder.self::$htaccess);	

			self::plain_file_copier(self::$index,$root_folder.self::$index);						

		} catch (Exception $e) {
			
			throw $e;

		}

	}

	public static function iterative_directory_copier($source, $destination) {

		$iterator = new RecursiveIteratorIterator( 
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
  				RecursiveIteratorIterator::SELF_FIRST);

		foreach ( $iterator as $item ) {

			if ( $item->isDir() )  {

				$d = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

				$e = mkdir($d);

				if ($e === false) throw new Exception("Cannot create directory ".$d);
	
			}

			else {

				$f = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

				$e = copy($item, $f);

				if ($e === false) throw new Exception("Cannot copy file ".$d);

			}
  
  		}

	}

	public static function plain_file_copier($source, $destination) {

		$e = copy($source, $destination);

		if ($e === false) throw new Exception("Cannot copy file ".$d);

	}

}