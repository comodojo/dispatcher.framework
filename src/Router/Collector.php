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

    private $classname  = "";

    private $type       = "";

    private $service    = "";

    private $cache      = null;

    private $request    = null;

    private $response   = null;

    private $table      = array();

    public function __construct($routing_table, $configuration = null, $logger = null, $cache = null) {

        parent::__construct($configuration, $logger);
        
        $this->table = $routing_table->get();
        
        $this->cache = $cache;

        $this->setTimestamp();

    }
    
    public function getType() {
        
        return $this->type;
        
    }
    
    public function getService() {
        
        return $this->service;
        
    }
    
    public function getParameters() {
        
        return $this->parameters;
        
    }
    
    public function getClassName() {
        
        return $this->classname;
        
    }
    
    public function getInstance() {
        
        $class = $this->classname;
        
        if (class_exists($class)) {
            
            return new $class(
                $this->service, 
                $this->logger,
                $this,
                $this->response
            );
            
        }
        else return null;
        
    }

    public function bypass($mode = true) {

        $this->bypass = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    public function route(Request $request) {
        
        $this->request = $request;
        
        if (!$this->parse()) {
            
            throw new DispatcherException("Unable to find a valid route for the specified uri");
            
        }
        
        
    }

    public function compose(Response $response) {
        
        $this->response = $response;
        
        $service = $this->getInstance();
        
        if (!is_null($service)) {
            
            $result = "";

            switch( $_SERVER['REQUEST_METHOD'] ) {
    
                case 'POST':
    
                    $result = $service->post();
    
                    break;
    
                case 'PUT':
    
                    $result = $service->put();
    
                    break;
                    
                case 'DELETE':
    
                    $result = $service->delete();
    
                    break;
    
                default:
    
                    $result = $service->get();
    
                    break;
    
            }
            
            $this->response->content()->set($result);
            
        } else {
            
            throw new DispatcherException(sprintf("Unable to execute service '%s'", $this->service));
            
        }
        
        
    }
    
    private function parse() {
        
        $path = $this->request->uri()->getPath();
        
        foreach ($this->table as $regex => $value) {
            
            if (preg_match("/" . $regex . "/", $path, $matches)) {
                
                $this->evalUri($value['query'], $matches);
                
                foreach ($value['parameters'] as $parameter => $value) {
                    
                    $this->request->query()->set($parameter, $value);
                    
                }
                
                $this->classname  = $value['class'];
                $this->type       = $value['type'];
                $this->service    = implode('.', $value['service']);
                $this->service    = empty($this->service)?"default":$this->service;
                
                return true;
                
            }
            
        }
        
        return $false;
        
    }
    
    private function evalUri($parameters, $bits) {
        
        $count  = 0;
        
        foreach ($parameters as $key => $value) {
            
            if (isset($bits[$key])) {
                
                if (preg_match('/^' . $value['regex'] . '$/', $bits[$key], $matches)) {
                    
                    if (count($matches) == 1) $matches = $matches[0];
                    
                    $this->request->query()->set($key, $matches);
                    
                }
                
            } elseif ($value['required']) {
                
                throw new DispatcherException(sprintf("Required parameter '%s' not specified.", $key));
                
            }
            
        }
        
    }

}
