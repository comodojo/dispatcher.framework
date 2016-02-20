<?php namespace Comodojo\Dispatcher\Tests\Components;

use \Comodojo\Dispatcher\Components\Timestamp;

class TimestampTest extends \PHPUnit_Framework_TestCase {

    use Timestamp;

    protected $time;

    public function testTimestamp() {

        $this->time = microtime(true);

        sleep(1);

        $this->assertInstanceOf('\Comodojo\Dispatcher\Tests\Components\TimestampTest', $this->setTimestamp($this->time));

        $this->assertEquals($this->time, $this->getTimestamp());

        $this->setTimestamp();

        $this->assertGreaterThan($this->time, $this->getTimestamp());

    }

}
