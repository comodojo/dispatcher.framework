<?php namespace Comodojo\Dispatcher\Tests\Components;

use \Comodojo\Dispatcher\Components\HeadersTrait;

class HeadersTest extends \PHPUnit_Framework_TestCase {

    use HeadersTrait;

    protected $header = 'X-Test';

    protected $value = "lorem";

    protected function setUp() {

        $this->set($this->header, $this->value);

    }

    public function testHeaders() {

        $this->assertEquals($this->value, $this->get($this->header));

        $this->assertEquals($this->header.':'.$this->value, $this->getAsString($this->header));

        $headers = $this->get();

        $this->assertInternalType('array', $headers);

        $this->assertEquals(1, count($headers));

        $this->assertTrue($this->delete($this->header));

        $headers = $this->get();

        $this->assertEquals(0, count($headers));

        $this->set($this->header, $this->value);

        $this->assertTrue($this->delete());

        $headers = $this->get();

        $this->assertEquals(0, count($headers));

    }

}
