<?php namespace comodojo\Dispatcher;

/**
 * dspatcher cache controller
 * 
 * @package		Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license		GPL-3.0+
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

use \comodojo\Exception\IOException;

class cache {
	
	/**
	 * If true, cache methods will not throw exception in case of error.
	 *
	 * @var	bool
	 */
	private $fail_silently = DISPATCHER_CACHE_FAIL_SILENTLY;
	
	/**
	 * Path for cache files
	 *
	 * @var	bool
	 */
	private $cache_path = DISPATCHER_CACHE_FOLDER;

	/**
	 * Current time, as provided by dispatcher
	 *
	 * @var	float
	 */
	private $current_time = NULL;

	/**
	 * Constructor method. It only acquire current time and notify that cache is ready
	 *
	 * @param 	string 	$time 	Dispatcher time
	 */
	public final function __construct($time=false) {

		$this->current_time = $time !== false ? $time : time();

		debug(' + Cache up and running; current time: '.$this->current_time,'INFO','cache');

	}

	/**
	 * Set cache.
	 * 
	 * @param	string	$request	The GET request
	 * @param	string	$data		The data to cache
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
	 * @param	string	$request		The request to retrieve
	 * @param	string	$ttl			[optional] The cache time-to-live
	 * 
	 * @return	array 	An array containing maxage, bestbefore, object (data)
	 */
	public final function get($request, $ttl=DISPATCHER_CACHE_TTL) {
		
		if (!DISPATCHER_CACHE_ENABLED) {
			debug('Caching administratively disabled','INFO','cache');
			return false;
		}
		
		$last_time_limit = (int)$this->current_time - $ttl;
		
		$cacheTag = md5($request) . ".cache";

		$cacheFile = $this->cache_path . ( $this->cache_path[strlen($this->cache_path)-1] == "/" ? "" : "/" ) . $cacheTag;
		
		$cache_time = @filemtime($cacheFile);

		if (is_readable($cacheFile) AND $cache_time >= $last_time_limit) {
			
			$max_age = $cache_time + $ttl - (int)$this->current_time;

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