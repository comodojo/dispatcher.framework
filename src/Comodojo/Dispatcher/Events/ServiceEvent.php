<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Dispatcher\Traits\ConfigurationTrait;
use \Comodojo\Dispatcher\Traits\LoggerTrait;
use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Dispatcher\Traits\EventsTrait;
use \Comodojo\Dispatcher\Traits\RequestTrait;
use \Comodojo\Dispatcher\Traits\ResponseTrait;
use \Comodojo\Dispatcher\Traits\RouterTrait;
use \Comodojo\Dispatcher\Traits\ExtraTrait;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Foundation\Events\AbstractEvent;
use \Comodojo\SimpleCache\Manager as CacheManager;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Base\Configuration;
use \Psr\Log\LoggerInterface;

/**
 * @package     Comodojo Dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
 * @license     GPL-3.0+
 *
 * LICENSE:
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ServiceEvent extends AbstractEvent {

    use ConfigurationTrait;
    use LoggerTrait;
    use CacheTrait;
    use EventsTrait;
    use RequestTrait;
    use RouterTrait;
    use ResponseTrait;
    use ExtraTrait;

    public function __construct(
        $name,
        Configuration $configuration,
        LoggerInterface $logger,
        CacheManager $cache,
        EventsManager $events,
        Request $request,
        Router $router,
        Response $response,
        Extra $extra
    ) {

        parent::__construct($name);

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setCache($cache);
        $this->setEvents($events);
        $this->setRequest($request);
        $this->setRouter($router);
        $this->setResponse($response);
        $this->setExtra($extra);

    }

}
