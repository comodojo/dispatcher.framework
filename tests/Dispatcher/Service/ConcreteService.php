<?php namespace Comodojo\Dispatcher\Tests\Service;

use \Comodojo\Dispatcher\Service\AbstractService;

class ConcreteService extends AbstractService {

    public function get() {
        return 'this is a test';
    }

    public function trace() {
        return 'method not allowed';
    }
}
