<?php namespace Comodojo\Dispatcher\Tests\Components;

use \Comodojo\Dispatcher\Components\EventsManager;
use \League\Event\Emitter;
use \Monolog\Logger;

class EventsManagerTest extends \PHPUnit_Framework_TestCase {

    private static $events;

    public static function setupBeforeClass() {

        $logger = new Logger('test');

        self::$events = new EventsManager($logger);

    }

    public function testEmitter() {

        $this->assertInstanceOf('\League\Event\Emitter', self::$events);
        $this->assertInstanceOf('\Comodojo\Dispatcher\Components\EventsManager', self::$events);

    }

    
}
