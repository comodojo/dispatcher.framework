<?php

/**
 * This example service tests all supported HTTP methods (GET,PUT,POST,DELETE)
 * defining single method for each of them.
 *
 * For example to global "logic" magic method please see
 * "example_hello_world_logic.php" service.
 */

include getcwd()."/../global/simpleDataRestDispatcher.php";
include getcwd()."/../configs/example_hello_world-config.php";

class service extends simpleDataRestDispatcher {
	
        public function get($attributes) {
            $this->result = $this->say_hello_to(isset($attributes['to']) ? $attributes['to'] : 'comodojo','GET');
	}
        
        public function put($attributes) {
    	$this->result = $this->say_hello_to(isset($attributes['to']) ? $attributes['to'] : 'comodojo','PUT');
	}
        
        public function post($attributes) {
	    $this->result = $this->say_hello_to(isset($attributes['to']) ? $attributes['to'] : 'comodojo','POST');
	}
        
	public function delete($attributes) {
            $this->result = $this->say_hello_to(isset($attributes['to']) ? $attributes['to'] : 'comodojo','DELETE');
	}
	
        private function say_hello_to($to,$method) {
            $this->success = true;
            return "(HTTP Method: ".$method.") Hello " . $to . "!";
        }
        
}

$rest = new service();

?>