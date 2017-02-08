<?php namespace Comodojo\Dispatcher\Tests\Service;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\SimpleCache\Manager as CacheManager;
use \Monolog\Logger;
use \Comodojo\Foundation\Events\Manager as EventsManager;

class AbstractServiceTest extends \PHPUnit_Framework_TestCase {

    protected static $service;

    protected static $std_configuration = array(
        "supported-methods" => array("GET","PUT","POST","DELETE","OPTION")
    );

    public static function setupBeforeClass() {

        $configuration = new Configuration(self::$std_configuration);
        $logger = new Logger('test');
        $cache = new CacheManager();
        $extra = new Extra($configuration, $logger);
        $events = EventsManager::create($logger);

        $request = new Request($configuration, $logger);
        $router = new Router($configuration, $logger, $cache, $events, $extra);
        $response = new Response($configuration, $logger);

        self::$service = new ConcreteService(
            $configuration,
            $logger,
            $cache,
            $events,
            $request,
            $router,
            $response,
            $extra
        );

    }

    public function testGetters() {

        $service = self::$service;

        $this->assertInstanceOf('\Comodojo\Dispatcher\Request\Model', $service->getRequest());

    }

    public function testGetImplementedMethods() {

        $service = self::$service;

        $methods = $service->getImplementedMethods();

        $this->assertEquals(array("GET","TRACE"), $methods);

        $method = $service->getMethod("GET");

        $status = $service->getResponse()->getStatus()->get();

        $this->assertEquals(200, $status);

        $run = call_user_func(array($service, $method));

        $this->assertEquals('this is a test', $run);

    }

}
