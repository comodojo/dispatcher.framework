<?php namespace Comodojo\Dispatcher\Tests;

use \Comodojo\Dispatcher\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {

        $this->dispatcher = new Dispatcher();

    }

    protected function tearDown() {

        unset($this->dispatcher);

    }

    public function testInstances() {

        $this->assertInstanceOf('\Comodojo\Dispatcher\Components\Configuration', $this->dispatcher->configuration());
        $this->assertInstanceOf('\League\Event\Emitter', $this->dispatcher->events());
        $this->assertInstanceOf('\Comodojo\Cache\CacheManager', $this->dispatcher->cache());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Request\Model', $this->dispatcher->request());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Collector', $this->dispatcher->router());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Model', $this->dispatcher->response());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Extra\Model', $this->dispatcher->extra());

        ob_end_clean();

    }

}
