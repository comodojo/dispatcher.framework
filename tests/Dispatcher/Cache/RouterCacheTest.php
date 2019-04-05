<?php namespace Comodojo\Dispatcher\Tests\Cache;

use \Comodojo\SimpleCache\Manager as CacheManager;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\Dispatcher\Cache\RouterCache;

class RouterCacheTest extends \PHPUnit_Framework_TestCase {

    protected $routes = [
        "^\/helloworld(?:\/(?P<to>[a-zA-Z0-9_\s!]+)?)?[\/]?$" => []
    ];

    protected static $cache;

    public static function setUpBeforeClass() {

        $logger = LogManager::create('dispatcher', false)->getLogger();
        $manager = new CacheManager(CacheManager::PICK_FIRST, $logger, true, 5);
        $manager->addProvider(new Memory([], $logger));
        self::$cache = new RouterCache($manager);

    }

    public function testCacheDump() {

        $this->assertTrue(self::$cache->dump($this->routes, 10));

    }

    public function testCacheRead() {

        $this->assertEquals($this->routes, self::$cache->read());

    }

}
