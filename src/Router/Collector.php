<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;

/**
 *
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class Collector extends DispatcherClassModel {

    use TimestampTrait;

    private $bypass = false;

    public function __construct() {

        parent::__construct($configuration, $logger);

        $this->setTimestamp();

    }

    public function bypass($mode = true) {

        $this->bypass = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    public function route(Request $request) {

    }

    public function compose(Response $response) {

    }

}
