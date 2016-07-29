<?php namespace Comodojo\Dispatcher\Tests\Router;

use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Cache\CacheManager;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Router\Table;
use \Comodojo\Dispatcher\Router\Route;

class RouterTest extends \PHPUnit_Framework_TestCase {

    protected static $router;

    public static function setupBeforeClass() {

        $configuration = new Configuration( DefaultConfiguration::get() );

        $logger = new Logger('test');
        $logger->pushHandler( new NullHandler(Logger::DEBUG) );

        $cache = new CacheManager();

        $extra = new Extra($configuration, $logger);

        self::$router = new Router($configuration, $logger, $cache, $extra);

    }

    public function testRouter() {

        $request = new Request(self::$router->configuration, self::$router->logger);
        $response = new Response(self::$router->configuration, self::$router->logger);

        $request->method->set("GET");

        $route = new Route();
        $route->setType("ROUTE")
            ->setClassName("\\Comodojo\\Dispatcher\\Tests\\Service\\ConcreteService");

        self::$router->bypassRouting($route);

        $route = self::$router->route($request);

        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Route', $route);

        $this->assertEquals("ROUTE", $route->getType());

        $this->assertEquals('\Comodojo\Dispatcher\Tests\Service\ConcreteService', $route->getClassName());

        // $service = self::$router->getServiceInstance();

        // $this->assertInstanceOf('\Comodojo\Dispatcher\Tests\Service\ConcreteService', $service);

        // $this->assertEquals('this is a test', $service->get());

        //$this->assertEquals('method not allowed', $service->trace());

        self::$router->compose($response);

        $this->assertEquals('this is a test', $response->content->get());

    }

    public function testTableRoute() {

        $path = 'test/{"name*": "\\\\w+"}';

        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Table', self::$router->table);

        self::$router->table->add(
            $path,
            "ROUTE",
            "\\Comodojo\\Dispatcher\\Tests\\Service\\ConcreteService"
        );

        $route = self::$router->table->get($path);

        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Route', $route);

        $this->assertEquals("ROUTE", $route->getType());

        $this->assertEquals("test", $route->getServiceName());

        $this->assertEquals("\\Comodojo\\Dispatcher\\Tests\\Service\\ConcreteService", $route->getClassName());

        $this->assertTrue($route->isQueryRequired("name"));

        $this->assertEquals("\\w+", $route->getQueryRegex("name"));

        if (preg_match("/" . self::$router->table->regex($path) . "/", "/test/pattern", $matches)) {

            $route->path($matches);

        }

        $this->assertEquals("pattern", $route->getParameter("name"));

        $this->assertTrue(self::$router->table->remove($path));

        $this->assertNull(self::$router->table->get($path));

    }

}
