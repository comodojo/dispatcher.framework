<?php namespace Comodojo\Dispatcher\Tests\Components;

use \Comodojo\Dispatcher\Components\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {

        $test_values = array(
            "foo" => "boo",
            "a" => array("a" => "lorem", "b" => "ipsum"),
            "b" => false,
            "c" => 42,
            "d" => (object) array("a" => "lorem", "b" => "ipsum"),
        );

        $this->config = new Configuration( $test_values );

    }

    protected function tearDown() {

        unset($this->config);

    }

    public function testGetSetDelete() {

        $param = 'test';

        $return = $this->config->set("t", $param);

        $this->assertInstanceOf('\Comodojo\Dispatcher\Components\Configuration', $return);

        $this->assertEquals($this->config->get("t"), $param);

        $remove = $this->config->delete("t");

        $this->assertTrue($remove);

        $remove = $this->config->get("t");

        $this->assertNull($remove);

    }

    public function testGetAll() {

        $config = $this->config->get();

        $this->assertInternalType('array', $config);

        $this->assertEquals($config["c"], 42);
        $this->assertEquals($config["a"]["a"], 'lorem');

    }

    public function testIsDefined() {

        $this->assertTrue($this->config->isDefined('a'));

        $this->assertFalse($this->config->isDefined('z'));

    }

    public function testWholeDelete() {

        $this->assertTrue($result = $this->config->delete());

        $this->assertFalse($this->config->isDefined('a'));

    }

    public function testMerge() {

        $new_props = array("a" => false, "b" => true);

        $this->config->merge($new_props);

        $this->assertFalse($this->config->get('a'));
        $this->assertTrue($this->config->get('b'));

    }



}
