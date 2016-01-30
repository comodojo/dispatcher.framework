<?php namespace Comodojo\Dispatcher\Components;

/**
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

class Configuration {

    protected $attributes = array(
        'dispatcher-enabled' => true,
        'dispatcher-disabled-status' => 503,
        'dispatcher-disabled-message' => 'Dispatcher offline',
        'dispatcher-log-name' => 'dispatcher',
        'dispatcher-log-enabled' => false,
        'dispatcher-log-level' => 'INFO',
        'dispatcher-log-target' => '%dispatcher-log-folder%/dispatcher.log',
        'dispatcher-log-folder' => '/log',
        'dispatcher-supported-methods' => 'GET,PUT,POST,DELETE,OPTIONS,HEAD',
        'dispatcher-default-encoding' => 'UTF-8',
        'dispatcher-cache-enabled' => true,
        'dispatcher-cache-ttl' => 3600,
        'dispatcher-cache-folder' => '/cache',
        'dispatcher-cache-algorithm'  => 'PICK_FIRST'
        // should we implement this?
        //'dispatcher-autoroute' => false
    );

    public function __construct( $configuration = array() ) {

        $this->attributes['dispatcher-base-url'] = self::urlGetAbsolute();
        
        $this->attributes['dispatcher-real-path'] = self::pathGetAbsolute();

        $this->attributes = array_merge($this->attributes, $configuration);

    }

    final public function get($property) {

        if (array_key_exists($property, $this->attributes)) {

            $value = $this->attributes[$property];
            
            if ( preg_match_all('/%(.+?)%/', $value, $matches) ) {
                
                $substitutions = array();
                
                foreach ( $matches as $match ) {
                    
                    $backreference = $match[1];
                    
                    if ( $backreference != $property && !isset($substitutions['/%'.$backreference.'%/']) ) {
                        
                        $substitutions['/%'.$backreference.'%/'] = $this->$backreference;
                        
                    }
                    
                }
                
                $value = preg_replace(array_keys($substitutions), array_values($substitutions), $value);
                
            }
            
            return $value;

        }

        return null;

    }
    
    final public function set($property, $value) {

        $this->attributes[$property] = $value;

        return $this;

    }

    final public function isDefined($property) {

        return isset($this->attributes[$property]);

    }
    
    final public function erase() {
        
        $this->attributes = array();
        
        return $this;
        
    }

    private static function urlGetAbsolute() {

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';

        $uri = preg_replace("/\/index.php(.*?)$/i", "", $_SERVER['PHP_SELF']);

        return ( $http . $_SERVER['HTTP_HOST'] . $uri . "/" );

    }
    
    private static function pathGetAbsolute() {

        return realpath(dirname(__FILE__)."/../../../../../")."/";

    }

}
