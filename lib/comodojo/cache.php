<?php namespace comodojo;

/**
 * standard spare parts cache controller
 * 
 * @package		Comodojo ServerSide Core Packages
 * @author		comodojo.org
 * @copyright	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version		__CURRENT_VERSION__
 * @license		GPL Version 3
 */

use \comodojo\Exception\IOException;

class cache {
	
	/**
	 * If true, cache methods will not throw exception in case of error.
	 * @var	bool
	 */
	private $fail_silently = DISPATCHER_CACHE_FAIL_SILENTLY;
	
	private $cache_path = DISPATCHER_CACHE_FOLDER;

	private $current_time = NULL;

	public final function __construct($time=false) {

		$this->current_time = $time !== false ? $time : time();

		debug(' + Cache up and running; current time: '.$this->current_time,'INFO','cache');

	}

	/**
	 * Set cache.
	 * 
	 * Cache requires $data, that could be an array or a string.
	 * 
	 * If $data is an array, it will be encoded in JSON (default), XML or JAML, depending on $format parameter.
	 * If it's a string, it will be cached like plaintext.
	 *
	 * @param	string	$data			Data to cache.
	 * @param	string	$request		The request to associate the cache to.
	 * @param	string	$format			[optional] Format to encode data to (JSON, XML, YAML). Default JSON.
	 * @param	bool	$userDependent	[optional] If true, cache access will be limited to logged user.
	 * 
	 * @return	bool
	 */
	public final function set($request, $data) {
		
		if (!DISPATCHER_CACHE_ENABLED) {
			debug('Caching administratively disabled','INFO','cache');
			return false;
		}
		
		if (empty($data)) {
			debug('Nothing to cache!','INFO','cache');
			if ($this->fail_silently) {
				return false;
			}
			else {
				throw new IOException("Nothing to cache");
			}
		}
		
		$cacheTag = md5($request) . ".cache";

		$cacheFile = $this->cache_path . ( $this->cache_path[strlen($this->cache_path)-1] == "/" ? "" : "/" ) . $cacheTag;

		// $a_data = Array(
		// 	"service"		=>	$data->getService(),
		// 	"code"			=>	$data->getStatusCode(),
		// 	"content"		=>	$data->getContent(),
		// 	"headers"		=>	$data->getHeaders(),
		// 	"contentType"	=>	$data->getContentType()
		// );

		//$f_data = serialize($a_data);

		$f_data = serialize($data);

		$cached = file_put_contents($cacheFile, $f_data);
		if ($cached === false) {
			debug('Error writing to cache folder','ERROR','cache');
			if ($this->fail_silently) {
				return false;
			}
			else {
				throw new IOException("Error writing to cache folder", 1201);
			}
		}

		return true;
		
	}
		
	/**
	 * Get cache
	 * 
	 * If $format parameter is not false, cache will be decoded according to format specified.
	 * If it's true, cache will try to decode data from JSON
	 *
	 * @param	string	$request		The request to associate the cache to.
	 * @param	string	$decode			[optional] Decode cache from specified format to array (JSON,XML,YAML); if false, disable decoding (will return the plain text).
	 * @param	bool	$userDependent	[optional] If true, cache access will be limited to logged user.
	 * 
	 * @return	array|string|bool		Data cached, in array or plaintext, or false if no cache saved.
	 */
	public final function get($request, $ttl=DISPATCHER_CACHE_TTL) {
		
		if (!DISPATCHER_CACHE_ENABLED) {
			debug('Caching administratively disabled','INFO','cache');
			return false;
		}
		
		$last_time_limit = $this->current_time - $ttl;
		
		$cacheTag = md5($request) . ".cache";

		$cacheFile = $this->cache_path . ( $this->cache_path[strlen($this->cache_path)-1] == "/" ? "" : "/" ) . $cacheTag;
		
		$cache_time = @filemtime($cacheFile);

		if (is_readable($cacheFile) AND $cache_time >= $last_time_limit) {
			
			$max_age = $cache_time + $ttl - $this->current_time;

			$best_before = gmdate("D, d M Y H:i:s", $cache_time + $ttl) . " GMT";
			
			$data = file_get_contents($cacheFile);

			$u_data = unserialize($data);
			
			if ($u_data === false) {
				debug('Error reading from cache file '.$cacheTag,'ERROR','cache');
				if ($this->fail_silently) {
					return false;
				}
				else {
					throw new IOException("Error reading from cache file ".$cacheTag, 1203);
				}
			}
			
			return Array(
				"maxage"	=>	$max_age,
				"bestbefore"=>	$best_before,
				//"content"	=>	$u_data["cache_content"]
				"object"	=>	$u_data
			);

		}
		
		else return false;
		
	}

	/**
	 * Purge cache
	 * 
	 * Clean cache folder; errors are not caught nor thrown.
	 *
	 * @return	bool
	 */
	public function purge($request=NULL) {

		if ( is_null($request) ) {

			debug("Purging cache (everything)","INFO","cache");

			$cache_files_number = 0;

			$cache_path = opendir($this->cache_path);
			if ( $cache_path === false ) {
				debug("Unable to open cache folder","ERROR","cache");
				return false;
			}
			
			while( false !== ( $cache_file = readdir($cache_path) ) ) {

				if ( pathinfo($cache_file, PATHINFO_EXTENSION) == "cache" ) {
					if ( unlink($cache_file) == false ) return false;
					else $cache_files_number++;
				}
				else continue;
				
			}
		
			closedir($cache_path);

		}
		else {

			debug("Purging cache for request: ".$request,"INFO","cache");

			$cacheTag = md5($request) . ".cache";

			$cacheFile = $this->cache_path . ( $this->cache_path[strlen($this->cache_path)-1] == "/" ? "" : "/" ) . $cacheTag;

			if ( is_readable($cacheFile) ) {

				$unlink = unlink($cacheFile);
				$cache_files_number = $unlink ? 1 : false;

			}
			else $cache_files_number = 0;

		}

		return $cache_files_number;
			
    }
	
}

?>