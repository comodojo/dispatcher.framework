<?php

class ObjectRoutingTableTest extends PHPUnit_Framework_TestCase {

    public function setUp() {

        $this->table = new \Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable();

    }

    public function testDefaultRouting() {

        $table = $this->table->getRoutes();

        $this->assertInternalType('array', $table);

        $this->assertCount(1, $table);

        $route = $this->table->getRoute('test');

        $this->assertInternalType('array', $route);

        $this->assertEquals('ERROR', $route['type']);

        $this->assertEquals('Service not found', $route['target']);

        $this->assertEquals('404', $route['parameters']['errorCode']);

    }

    public function testSetRoute() {

        $result = $this->table->setRoute('test', 'ROUTE', 'testTarget', array());

        $this->assertInstanceOf('\Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable', $result);

    }

}
