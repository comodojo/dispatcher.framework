<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \League\Event\AbstractEvent as LeagueAbstractEvent;


abstract class AbstractEvent extends LeagueAbstractEvent {

    use TimestampTrait;

    private $name;

    public function __construct($name) {

        $this->setTimestamp();

        $this->name = $name;

    }

    public function getName() {

        return $this->name;

    }

}
