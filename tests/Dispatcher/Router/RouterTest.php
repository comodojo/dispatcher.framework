<?php namespace Comodojo\Dispatcher\Tests\Router;

use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\SimpleCache\Manager as CacheManager;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Router\Table;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Foundation\Events\Manager as EventsManager;

class RouterTest extends \PHPUnit_Framework_TestCase {

    protected static $router;

    public static function setupBeforeClass() {

        $configuration = new Configuration( DefaultConfiguration::get() );

        $logger = new Logger('test');
        $logger->pushHandler( new NullHandler(Logger::DEBUG) );

        $cache = new CacheManager();

        $events = EventsManager::create($logger);

        $extra = new Extra($configuration, $logger);

        self::$router = new Router($configuration, $logger, $cache, $events, $extra);

    }

    public function testRouter() {

        $request = new Request(self::$router->getConfiguration(), self::$router->getLogger());
        $response = new Response(self::$router->getConfiguration(), self::$router->getLogger());

        $request->getMethod()->set("GET");

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

        $this->assertEquals('this is a test', (string) $response->getContent());

    }

    public function testTableRoute() {

        $table = self::$router->getTable();

        $path = 'test/{"name*": "\\\\w+"}';

        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Table', $table);

        $table->add(
            $path,
            "ROUTE",
            "\\Comodojo\\Dispatcher\\Tests\\Service\\ConcreteService"
        );

        $route = $table->get($path);

        $this->assertInstanceOf('\Comodojo\Dispatcher\Router\Route', $route);

        $this->assertEquals("ROUTE", $route->getType());

        $this->assertEquals("test", $route->getServiceName());

        $this->assertEquals("\\Comodojo\\Dispatcher\\Tests\\Service\\ConcreteService", $route->getClassName());

        $this->assertTrue($route->isQueryRequired("name"));

        $this->assertEquals("\\w+", $route->getQueryRegex("name"));

        if (preg_match("/" . $table->regex($path) . "/", "/test/pattern", $matches)) {

            $route->path($matches);

        }

        $this->assertEquals("pattern", $route->getRequestParameter("name"));

        $this->assertTrue($table->remove($path));

        $this->assertNull($table->get($path));

    }

}
