<?php namespace Comodojo\Dispatcher\Tests;

use \Comodojo\Dispatcher\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {

    protected static $config = array(
        "cache" => array(
            "pick_methods" => "PICK_ALL",
            "providers" => array(
                "test" => array(
                    "type" => "Filesystem",
                    "cache_folder" => "cache"
                )
            )
        ),
        "log" => array(
            "name" => "test",
            "providers" => array(
                "test" => array(
                    "type" => "StreamHandler",
                    "stream" => "log/test.log",
                    "level" => "debug"
                )
            )
        )
    );

    protected static $dispatcher;

    public static function setUpBeforeClass() {

        $config = array_merge(self::$config, array(
            "base-path" => realpath(dirname(__FILE__)."/../root/")
        ));

        self::$dispatcher = new Dispatcher($config);

    }

    /**
    * @runInSeparateProcess
    */
    public function testAll() {

        $dispatcher = self::$dispatcher;

        $this->assertInstanceOf('\Comodojo\Foundation\Base\Configuration', self::$dispatcher->getConfiguration());
        $this->assertInstanceOf('\League\Event\Emitter', self::$dispatcher->getEvents());
        $this->assertInstanceOf('\Comodojo\SimpleCache\Manager', self::$dispatcher->getCache());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Request\Model', self::$dispatcher->getRequest());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Model', self::$dispatcher->getRouter());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Model', self::$dispatcher->getResponse());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Extra\Model', self::$dispatcher->getExtra());

        $this->assertCount(1, self::$dispatcher->getCache()->getProviders());

        $r = 'Unable to find a valid route for the specified uri';

        $dispatcher->getEvents()->subscribe('dispatcher', '\Comodojo\Dispatcher\Tests\Helpers\MockDispatcherListener');
        $dispatcher->getEvents()->subscribe('dispatcher.request', '\Comodojo\Dispatcher\Tests\Helpers\MockServiceListener');

        $result = $dispatcher->dispatch();

        $uri = $dispatcher->getRequest()->getUri()->getPath();

        $headers = $dispatcher->getResponse()->getHeaders()->get();

        $status = $dispatcher->getResponse()->getStatus()->get();

        $this->assertEquals($r, $result);

        $this->assertEquals(404, $status);

        $this->assertTrue($dispatcher->getExtra()->get('test-dispatcher-event'));
        $this->assertTrue($dispatcher->getExtra()->get('test-service-event'));

    }

}
