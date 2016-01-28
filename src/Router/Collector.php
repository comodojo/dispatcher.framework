<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;

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

class Collector extends DispatcherClassModel {

    use TimestampTrait;

    private $bypass     = false;

    private $cache      = null;

    private $classname  = "";

    private $type       = "";

    private $parameters = array();

    private $cache      = null;

    private $table      = array();

    public function __construct($routing_table, $configuration = null, $logger = null, $cache = null) {

        parent::__construct($configuration, $logger);
        
        $this->table = $routing_table->get();

        $this->setTimestamp();

    }

    public function bypass($mode = true) {

        $this->bypass = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    public function route(Request $request) {
        
        $value = $this->parse($request);
        
        $this->query($request);
        
        $this->classname  = $value['class'];
        $this->type       = $value['type'];
        $this->parameters = array_merge($value['parameters'], $request->post()->get());
        
    }
    
    private function query(Request $request) {
        
        $keys = $request->uri()->query->keys();
        
        foreach ($keys as $key) {
            
            $request->post()->set(rawurldecode($key), $request->uri()->query->getValue($key));
            
        }
        
    }
    
    private function parse(Request $request) {
        
        $path = $request->uri()->getPath();
        
        foreach ($this->table as $regex => $value) {
            
            if (preg_match("/" . $regex . "/", $path, $matches)) {
                
                array_shift($matches);
                
                $this->setParameters($value['query'], $matches, $request);
                
                return $value;
                
            }
            
        }
        
    }
    
    private function setParameters($parameters, $values, Request $request) {
        
        $params = array_keys($parameters);
        
        $count  = 0;
        
        foreach ($values as $paramValue) {
            
            while ($count < count($params)) {
                
                $parameter   = $params[$count];
                
                $param_regex = $parameters[$parameter];
                
                $count++;
                
                if (preg_match("/" . $param_regex . "/", $paramValue)) {
                    
                    $request->post()->set($parameter, $paramValue);
                    
                    break;
                    
                }
                
            }
            
        }
        
    }

    public function compose(Response $response) {

    }

}
