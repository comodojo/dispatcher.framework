<?php namespace Comodojo\Dispatcher\Tests\Traits;

use \Comodojo\Dispatcher\Traits\HeadersTrait;

class HeadersTraitTest extends \PHPUnit_Framework_TestCase {

    use HeadersTrait;

    protected $header = 'X-Test';

    protected $value = "lorem";

    protected function setUp() {

        $this->set($this->header, $this->value);

    }

    public function testHeaders() {

        $this->assertTrue($this->has($this->header));

        $this->assertEquals($this->value, $this->get($this->header));

        $this->assertEquals($this->header.': '.$this->value, $this->getAsString($this->header));

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

    public function testCaseInsensitiveFix() {

        $this->assertTrue($this->has('x-test'));
        $this->assertEquals($this->value, $this->get('x-TEST'));
        $this->assertTrue($this->delete('X-TEst'));

    }

    public function testArrayHeaderSupport() {

        $this->set('X-MultipleHeader', ['foo','boo']);
        $this->assertTrue($this->has('X-MultipleHeader'));
        $this->assertEquals(['foo','boo'], $this->get('X-MultipleHeader'));
        $this->assertEquals('X-MultipleHeader: foo,boo', $this->getAsString('X-MultipleHeader'));

    }

}
