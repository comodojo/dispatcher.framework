<?php namespace Comodojo\Dispatcher\Tests\Output;

use \Monolog\Logger;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Request\Model as RequestModel;
use \Comodojo\Dispatcher\Response\Model as ResponseModel;
use \Comodojo\Dispatcher\Output\Processor;

class OutputTest extends \PHPUnit_Framework_TestCase {

    private $request;
    private $response;
    private $configuration;
    private $logger;

    public function setUp() {

        $this->configuration = new Configuration( DefaultConfiguration::get() );
        $this->logger = new Logger('test');

        $this->request = new RequestModel($this->configuration, $this->logger);
        $this->response = new ResponseModel($this->configuration, $this->logger);

    }

    /**
    * @runInSeparateProcess
     */
    public function testDefaultOutput() {

        $output = Processor::parse($this->configuration, $this->logger, $this->request, $this->response);

        $this->assertNull($output);

    }

    /**
    * @runInSeparateProcess
     */
    public function testSuccessOutput() {

        $this->response->getContent()->set("test");

        $this->response->getStatus()->set(201);

        $output = Processor::parse($this->configuration, $this->logger, $this->request, $this->response);

        $this->assertEquals("test", $output);

    }

    /**
    * @runInSeparateProcess
     */
    public function testNoContentOutput() {

        $this->response->getContent()->set("test");

        $this->response->getStatus()->set(204);

        $this->response->consolidate($this->request);

        $output = Processor::parse($this->configuration, $this->logger, $this->request, $this->response);

        $this->assertNull($output);

    }

}
