<?php namespace Comodojo\Routes;

use \Comodojo\Database\Database;
use \Comodojo\Base\Element;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Exception\ConfigurationException;
use \Exception;

/**
 *
 *
 * @package     Comodojo Framework
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
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

class RoutingTable implements RoutingTableInterface {

    private $routes = array();
    private $def_route = array();
    
    public function add($route, $type, $class, $parameters = array()) {
        
        $folders = explode("/", $route);
        
        $value   = array(
            "type"       => $type,
            "class"      => $class,
            "parameters" => $parameters,
            "query"      => array()
        );
        
        if (count($folders) > 1 && empty($folders[0]))
            array_shift($folders);
        
        if (count($folders) == 1 && empty($folders[0])) {
            $this->def_route = $value;
            
            return $this;
        }
        
        $regex = $this->readpath($folders, $value);
        
        $this->routes[$regex] = $value;
        
    }
    
    public function remove($route) {
        
        $folders = explode("/", $route);
        
        $regex = $this->readpath($folders);
        
        if (isset($this->routes[$regex])) unset($this->routes[$regex]);
        
    }
    
    public function routes() {
        
        return $this->routes;
        
    }
    
    public function defaultRoute() {
        
        return $this->def_route;
        
    }
    
    private function readpath($folders, &$value = null, $regex = '\/') {
        
        if (empty($folders)) {
            
            return $regex;
            
        } else {
            
            $folder = array_shift($folders);
            
            $decoded = json_decode($folder, true);
            
            if (!is_null($decoded) && is_array($decoded)) {
                
                $key = array_keys($decoded);
                
                $key = $key[0];
                
                $required = isset($decoded['required'])?
                    filter_var($decoded['required'], FILTER_VALIDATE_BOOLEAN) :
                    false;
                    
                if (!empty($value)) {
                    $value['query'][$key] = $decoded[$key];
                }
                
                $this->readpath( 
                    $folders,
                    $value,
                    $regex.'('.$decoded[$key].'\/)'. (($required)?'{1}':'?')
                );
                
            } else {
                
                $this->readpath(
                    $folders,
                    $value,
                    $regex.$folder.'\/'
                );
                
            }
            
        }
        
    }

}
