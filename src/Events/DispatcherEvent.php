<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Events\AbstractEvent;
use \Comodojo\Dispatcher\Dispatcher;

class DispatcherEvent extends AbstractEvent {

    private $dispatcher = null;

    public function __construct(
        Dispatcher $dispatcher
    ) {

        parent::__construct('dispatcher');

        $this->dispatcher = $dispatcher;

    }

    final public function dispatcher() {

        return $this->dispatcher;

    }

}
