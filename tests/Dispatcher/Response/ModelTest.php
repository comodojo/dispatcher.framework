<?php namespace Comodojo\Dispatcher\Tests\Response;

use \Monolog\Logger;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Response\Model as ResponseModel;
use \Comodojo\Dispatcher\Response\Headers;
use \Comodojo\Dispatcher\Response\Status;
use \Comodojo\Dispatcher\Response\Content;
use \Comodojo\Dispatcher\Response\Location;
use \Comodojo\Cookies\CookieManager;

class ModelTest extends \PHPUnit_Framework_TestCase {

    private static $response;

    public static function setupBeforeClass() {

        $configuration = new Configuration( DefaultConfiguration::get() );
        $logger = new Logger('test');

        self::$response = new ResponseModel($configuration, $logger);

    }

    public function testComponents() {

        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Headers', self::$response->getHeaders());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Status', self::$response->getStatus());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Content', self::$response->getContent());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Location', self::$response->getLocation());
        $this->assertInstanceOf('\Comodojo\Dispatcher\Response\Preprocessor', self::$response->getPreprocessor());
        $this->assertInstanceOf('\Comodojo\Cookies\CookieManager', self::$response->getCookies());

    }

    public function testLocation() {

        $uri = "www.google.com";

        $location = self::$response->getLocation();
        $location->set($uri);
        $this->assertEquals($uri, $location->get());

    }

    public function testStatus() {

        $status = self::$response->getStatus();
        $this->assertEquals(200, $status->get());

        $status->set(400);
        $this->assertEquals(400, $status->get());

        $this->assertEquals('Bad Request', $status->description());
        $this->assertEquals('Payload Too Large', $status->description(413));

    }

    public function testContent() {

        $content = self::$response->getContent();
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

    public function testPreprocessor() {

        $preprocessor = self::$response->getPreprocessor();

        $this->assertFalse(
            $preprocessor->has(5000)
        );

        $this->assertTrue(
            $preprocessor->has(307)
        );

        $this->assertInstanceOf(
            '\Comodojo\Dispatcher\Response\Preprocessor\Status307',
            $preprocessor->get(307)
        );

        $this->assertInstanceOf(
            '\Comodojo\Dispatcher\Response\Preprocessor\Status200',
            $preprocessor->get(200)
        );

        $this->assertInstanceOf(
            '\Comodojo\Dispatcher\Response\Preprocessor\Status200',
            $preprocessor->get(509)
        );

        $this->assertInstanceOf(
            '\Comodojo\Dispatcher\Response\Preprocessor\Status200',
            $preprocessor->get(200)
        );

    }

    /**
    * @param int $status
    *
    * @testWith        [100]
    *                  [101]
    *                  [102]
    *                  [200]
    *                  [201]
    *                  [202]
    *                  [203]
    *                  [204]
    *                  [205]
    *                  [206]
    *                  [300]
    *                  [301]
    *                  [302]
    *                  [303]
    *                  [304]
    *                  [305]
    *                  [307]
    *                  [308]
    *                  [400]
    *                  [403]
    *                  [404]
    *                  [405]
    *                  [410]
    *                  [500]
    *                  [501]
    *                  [502]
    *                  [503]
    *                  [504]
    *                  [505]
    */
    public function testAllPreprocessors($status) {

        $preprocessor = self::$response->getPreprocessor();

        $this->assertInstanceOf(
            '\Comodojo\Dispatcher\Response\Preprocessor\Status'.$status,
            $preprocessor->get($status)
        );

    }

}
