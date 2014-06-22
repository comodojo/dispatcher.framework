<?php namespace comodojo;

class serve extends service {
	
	public function setup() {

		//$this->expects("get",Array("foo","bla"));

	}

	public function get() {

		return $this->serialize->toDUMP($this->getAttributes());

	}

}

?>