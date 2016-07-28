<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Components\ToString as ToStringTrait;
use \Comodojo\Dispatcher\Components\HttpStatusCodes;
use \Exception;

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

class Status {
    
    use ToStringTrait;

    private $status_code = 200;
    
    private $codes;
    
    public function __construct() {
        
        $this->codes = new HttpStatusCodes;
        
    }

    public function get() {

        return $this->status_code;

    }

    public function set($code) {

        if ( !$this->codes->exists($code) ) {

            throw new Exception("Invalid HTTP Status Code $code");

        }

        $this->status_code = $code;

        return $this;

    }

    public function description($code=null) {

        if ( is_null($code) ) return $this->codes->getMessage($this->status_code);
        
        return $this->codes->getMessage($code);

    }

}
