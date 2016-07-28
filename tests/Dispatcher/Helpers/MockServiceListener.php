<?php namespace Comodojo\Dispatcher\Tests\Helpers;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class MockServiceListener extends AbstractListener {

    public function handle(EventInterface $event) {

        $event->extra->set('test-service-event',true);

    }

}
