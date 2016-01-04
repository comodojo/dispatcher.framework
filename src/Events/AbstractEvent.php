<?php namespace Comodojo\Dispatcher\Events;

use \League\Event\EventInterface;
use \Monolog\Logger;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Collector as Router;
use \Comodojo\Dispatcher\Response\Model as Response;

abstract class AbstractEvent implements EventInterface {

    private $request = null;

    private $router = null;

    private $response = null;

    private $logger = null;

    public function __construct(
        Request $request,
        Router $router,
        Response $response,
        Logger $logger
    ) {

        $this->request = $request;

        $this->router = $router;

        $this->response = $response;

        $this->logger = $logger;

    }

    abstract public function getName();

    final public function request() {

        return $this->request;

    }

    final public function router() {

        return $this->router;

    }

    final public function response() {

        return $this->response;

    }

    final public function logger() {

        return $this->logger;

    }

}
