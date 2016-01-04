<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Events\AbstractEvent;

class ServiceEvent extends AbstractEvent {

    public function getName() {

        return 'ServiceEvent';

    }

}
