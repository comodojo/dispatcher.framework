<?php namespace Comodojo\Dispatcher\Tests\Service;

use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Cache\CacheManager;
use \Monolog\Logger;

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
        $request = new Request($configuration, $logger);
        $router = new Router($configuration, $logger, $cache, $extra);
        $response = new Response($configuration, $logger);
        $extra = new Extra($configuration, $logger);

        self::$service = new ConcreteService(
            $configuration,
            $logger,
            $request,
            $router,
            $response,
            $extra
        );

    }

    public function testGetters() {

        $service = self::$service;

        $this->assertInstanceOf('\Comodojo\Dispatcher\Request\Model', $service->request());

    }

    public function testGetImplementedMethods() {

        $service = self::$service;

        $methods = $service->getImplementedMethods();

        $this->assertEquals(array("GET"), $methods);

        $method = $service->getMethod("GET");

        $status = $service->response()->status()->get();

        $this->assertEquals(200, $status);

        $run = call_user_func(array($service, $method));

        $this->assertEquals('this is a test', $run);
    }

}
