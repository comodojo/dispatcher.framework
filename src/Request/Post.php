<?php namespace Comodojo\Dispatcher\Request;

use \Comodojo\Dispatcher\Components\Parameters as ParametersTrait;

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

class Post {

    use ParametersTrait;

    protected $raw_parameters;

    public function __construct() {

        $this->parameters = self::getParameters();

        $this->raw_parameters = self::getRawParameters();

    }

    public function raw() {

        return $this->raw_parameters;

    }

    private static function getParameters() {

        switch( $_SERVER['REQUEST_METHOD'] ) {

            case 'POST':

                $parameters = $_POST;

                break;

            case 'PUT':
            case 'DELETE':

                parse_str(file_get_contents('php://input'), $parameters);

                break;

            default:

                $parameters = array();

                break;

        }

        return $parameters;

    }

    private static function getRawParameters() {

        return file_get_contents('php://input');

    }

}
