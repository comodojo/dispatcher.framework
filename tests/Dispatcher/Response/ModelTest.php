<?php namespace Comodojo\Dispatcher\Tests\Response;

use \Monolog\Logger;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Response\Model as ResponseModel;
use \Comodojo\Dispatcher\Response\Headers;
use \Comodojo\Dispatcher\Response\Status;
use \Comodojo\Dispatcher\Response\Content;
use \Comodojo\Dispatcher\Response\Location;
use \Comodojo\Cookies\CookieManager;

class ExtraTest extends \PHPUnit_Framework_TestCase {

    private static $response;

    public static function setupBeforeClass() {

        $configuration = new Configuration( DefaultConfiguration::get() );
        $logger = new Logger('test');

        self::$response = new ResponseModel($configuration, $logger);

    }

    public function testComponents() {

        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Headers', self::$response->headers());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Status', self::$response->status());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Content', self::$response->content());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Location', self::$response->location());
        $this->assertInstanceOf('\Comodojo\Cookies\CookieManager', self::$response->cookies());

    }

    public function testLocation() {

        $uri = "www.google.com";

        $location = self::$response->location();

        $location->set($uri);

        $this->assertEquals($uri, $location->get());

    }

    public function testStatus() {

        $status = self::$response->status();

        $this->assertEquals(200,$status->get());

        $status->set(400);

        $this->assertEquals(400,$status->get());

        $this->assertEquals('Bad Request', $status->description());

        $this->assertEquals('Request Entity Too Large', $status->description(413));

    }

    public function testContent() {

        $content = self::$response->content();
        $out = md5(mt_rand());

        $this->assertNull($content->get());
        $this->assertEquals('text/plain', $content->type());
        $this->assertEquals('utf-8', $content->charset());

        $content->set($out);

        $this->assertEquals($out, $content->get());
        $this->assertEquals(32, $content->length());

        $content->type('application/xml')->charset('utf-7');

        $this->assertEquals('application/xml', $content->type());
        $this->assertEquals('utf-7', $content->charset());

    }


}
