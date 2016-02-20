<?php namespace Comodojo\Dispatcher\Tests\Extra;

use \Comodojo\Dispatcher\Extra\Model as ExtraModel;

class ExtraTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {

        $this->extra = new ExtraModel();

    }

    protected function tearDown() {

        unset($this->extra);

    }

    public function testParams() {

        $param = 42;

        $return = $this->extra->set("answer", $param);

        $this->assertInstanceOf('\Comodojo\Dispatcher\Extra\Model', $return);

        $this->assertEquals($this->extra->get("answer"), $param);

        $remove = $this->extra->delete("answer");

        $this->assertTrue($remove);

        $remove = $this->extra->delete("answer");

        $this->assertFalse($remove);

    }

    public function testGetAllExtra() {

        $this->extra->set("answer", 42)
            ->set("foo", "boo")
            ->set("test", 'OK');

        $extras = $this->extra->get();

        $this->assertInternalType('array', $extras);

        $this->assertEquals($extras["answer"], 42);
        $this->assertEquals($extras["foo"], 'boo');
        $this->assertEquals($extras["test"], 'OK');

    }

}
