<?php namespace Comodojo\Dispatcher\Tests\Cache;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\SimpleCache\Manager as CacheManager;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\Dispatcher\Cache\ServerCache;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Response\Model as Response;

class ServerCacheTest extends \PHPUnit_Framework_TestCase {

    protected $configuration;

    protected $logger;

    protected $cache;

    protected $request;

    protected $response;

    protected $route;

    public function setUp() {

        $this->configuration = new Configuration( DefaultConfiguration::get() );
        $this->logger = LogManager::create('dispatcher', false)->getLogger();
        $manager = new CacheManager(CacheManager::PICK_FIRST, $this->logger, true, 5);
        $manager->addProvider(new Memory([], $this->logger));
        $this->cache = new ServerCache($manager);
        $this->request = new Request($this->configuration, $this->logger);
        $this->response = new Response($this->configuration, $this->logger);
        $this->route = new Route;

        $this->route->setParameters([
            "cache" => "SERVER",
            "ttl" => 100
        ]);

    }

    public function testValidCache() {

        $this->request->getMethod()->set('GET');
        $this->response->getContent()->set('this is a sample content');
        $this->assertTrue(
            $this->cache->dump(
                $this->request,
                $this->response,
                $this->route
            )
        );

        $response = new Response($this->configuration, $this->logger);
        $cache = $this->cache->read(
            $this->request,
            $response
        );
        $this->assertEquals(
            $this->response->export(),
            $response->export()
        );

    }

    public function testInvalidCache() {

        $this->response->getContent()->set('this is a sample content');

        $this->request->getMethod()->set('POST');
        $this->assertFalse(
            $this->cache->dump(
                $this->request,
                $this->response,
                $this->route
            )
        );

        $this->request->getMethod()->set('GET');
        $route = new Route;
        $this->assertFalse(
            $this->cache->dump(
                $this->request,
                $this->response,
                $route
            )
        );

        $this->response->getStatus()->set(307);
        $this->assertFalse(
            $this->cache->dump(
                $this->request,
                $this->response,
                $this->route
            )
        );

    }

}
