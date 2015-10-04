<?php namespace Comodojo\Dispatcher;

use \Comodojo\Exception\IOException;

/**
 * dspatcher cache controller
 * 
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     GPL-3.0+
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

class Cache {
    
    /**
     * If true, cache methods will not throw exception in case of error.
     *
     * @var bool
     */
    private $fail_silently = DISPATCHER_CACHE_FAIL_SILENTLY;
    
    /**
     * Path for cache files
     *
     * @var bool
     */
    private $cache_path = DISPATCHER_CACHE_FOLDER;

    /**
     * Current time, as provided by dispatcher
     *
     * @var float
     */
    private $current_time = null;

    /**
     * Logger, injected by dispatcher
     *
     * @var \Comodojo\Dispatcher\Debug
     */
    private $logger = null;

    /**
     * Constructor method. It only acquire current time and notify that cache is ready
     *
     * @param   float   $time   Dispatcher time
     * @param   Object  $logger Logger, injected by dispatcher
     */
    final public function __construct($time, $logger) {

        $this->current_time = $time;

        $this->logger = $logger;

    }

    /**
     * Set cache.
     * 
     * @param   string  $request    The GET request
     * @param   string  $data       The data to cache
     * 
     * @return  bool
     */
    final public function set($request, $data) {
        
        if (!DISPATCHER_CACHE_ENABLED) {

            $this->logger->info('Caching administratively disabled');

            return false;

        }
        
        if (empty($data)) {

            if ($this->fail_silently) {

                $this->logger->warning('Empty data, nothign to cache');

                return false;

            }
            else {

                $this->logger->error('Empty data, nothign to cache');

                throw new IOException("Nothing to cache");

            }
        }
        
        $cacheTag = md5($request) . ".cache";

        $cacheFile = $this->cache_path . ( $this->cache_path[strlen($this->cache_path)-1] == "/" ? "" : "/" ) . $cacheTag;

        $f_data = serialize($data);

        $cached = file_put_contents($cacheFile, $f_data);
        if ($cached === false) {

            $this->logger->error('Error writing to cache', array(
                'CACHEFILE' => $cacheFile
            ));
            
            if ($this->fail_silently) {
                return false;
            }
            else {
                throw new IOException("Error writing to cache");
            }
        }

        return true;
        
    }
  
    /**
     * Get cache
     * 
     * @param   string  $request        The request to retrieve
     * @param   string  $ttl            [optional] The cache time-to-live
     * 
     * @return  array   An array containing maxage, bestbefore, object (data)
     */
    final public function get($request, $ttl=DISPATCHER_CACHE_TTL) {
        
        if (!DISPATCHER_CACHE_ENABLED) {

            $this->logger->info('Caching administratively disabled');

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

                $this->logger->error('Error reading from cache', array(
                    'CACHEFILE' => $cacheFile
                ));
                
                if ($this->fail_silently) {

                    return false;

                }
                else {

                    throw new IOException("Error reading from cache");

                }
            }
            
            return Array(
                "maxage"    =>  $max_age,
                "bestbefore"=>  $best_before,
                //"content" =>  $u_data["cache_content"]
                "object"    =>  $u_data
             );

        }
        
        else return false;
        
    }

    /**
     * Purge cache
     * 
     * Clean cache folder; errors are not caught nor thrown.
     *
     * @return  bool
     */
    public function purge($request=null) {

        if ( is_null($request) ) {

            $this->logger->info('Purging whole cache');

            $cache_files_number = 0;

            $cache_path = opendir($this->cache_path);
            if ( $cache_path === false ) {

                $this->logger->error('Unable to open cache folder', array(
                    'CACHEFOLDER' => $this->cache_path
                ));

                return false;

            }
            
            while( false !== ( $cache_file = readdir($cache_path) ) ) {

                if ( pathinfo($cache_file, PATHINFO_EXTENSION) == "cache" ) {
                    if ( unlink( $this->cache_path . ( $this->cache_path[strlen($this->cache_path)-1] == "/" ? "" : "/" ) . $cache_file ) == false ) return false;
                    else $cache_files_number++;
                }
                else continue;
                
            }
          
            closedir($cache_path);

        }
        else {

            $this->logger->info('Purging request cache', array(
                'REQUEST' => $request
            ));

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