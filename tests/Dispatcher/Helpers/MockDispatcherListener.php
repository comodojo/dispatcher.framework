<?php namespace Comodojo\Dispatcher\Tests\Helpers;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class MockDispatcherListener extends AbstractListener {

    public function handle(EventInterface $event) {

        $event->dispatcher->extra->set('test-dispatcher-event',true);

    }

}
