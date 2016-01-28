<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Events\AbstractEvent;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Collector as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Monolog\Logger;

class ServiceEvent extends AbstractEvent {

    private $logger = null;

    private $request = null;

    private $router = null;

    private $response = null;

    public function __construct(
        $name,
        Logger $logger,
        Request $request,
        Router $router,
        Response $response
    ) {

        parent::__construct($name);

        $this->logger = $logger;

        $this->request = $request;

        $this->router = $router;

        $this->response = $response;

    }

    final public function logger() {

        return $this->logger;

    }

    final public function request() {

        return $this->request;

    }

    final public function router() {

        return $this->router;

    }

    final public function response() {

        return $this->response;

    }

}
