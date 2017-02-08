<?php namespace Comodojo\Dispatcher\Components;

use \Comodojo\Foundation\Base\Configuration;
use \Psr\Log\LoggerInterface;
use \Comodojo\Dispatcher\Traits\ConfigurationTrait;
use \Comodojo\Dispatcher\Traits\LoggerTrait;

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

abstract class AbstractModel {

    use ConfigurationTrait;
    use LoggerTrait;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);

    }

}
