<?php namespace Comodojo\Dispatcher\Tests;

use \Comodojo\Dispatcher\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {

    protected static $config = array(
        "cache" => array(
            "algorithm" => "PICK_ALL",
            "providers" => array(
                "test" => array(
                    "type" => "FileCache",
                    "folder" => "cache"
                )
            )
        ),
        "log" => array(
            "name" => "test",
            "providers" => array(
                "test" => array(
                    "type" => "streamhandler",
                    "target" => "log/test.log",
                    "level" => "debug"
                )
            )
        )
    );

    protected static $dispatcher;

    public static function setUpBeforeClass() {

        $config = array_merge(self::$config, array(
            "base-path" => realpath(dirname(__FILE__)."/../")
        ));

        self::$dispatcher = new Dispatcher($config);

    }

    /**
    * @runInSeparateProcess
     */
    public function testAll() {

        $this->assertInstanceOf('\Comodojo\Dispatcher\Components\Configuration', self::$dispatcher->configuration());
        $this->assertInstanceOf('\League\Event\Emitter', self::$dispatcher->events());
        $this->assertInstanceOf('\Comodojo\Cache\CacheManager', self::$dispatcher->cache());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Request\Model', self::$dispatcher->request());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Collector', self::$dispatcher->router());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Model', self::$dispatcher->response());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Extra\Model', self::$dispatcher->extra());

        $this->assertCount(1, self::$dispatcher->cache()->getProviders());

        $r = 'Unable to find a valid route for the specified uri';

        $result = self::$dispatcher->dispatch();

        $uri = self::$dispatcher->request()->uri()->getPath();

        $headers = self::$dispatcher->response()->headers()->get();

        $status = self::$dispatcher->response()->status()->get();

        $this->assertEquals($r, $result);

        $this->assertEquals(404, $status);

    }

}
