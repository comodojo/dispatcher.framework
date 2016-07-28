<?php namespace Comodojo\Dispatcher\Response;

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

class Content {

    private $content;

    private $type = "text/plain";

    private $charset = "utf-8";

    public function get() {

        return $this->content;

    }

    public function set($content=null) {

        if ( !is_scalar($content) && $content != null ) {

            throw new Exception("Invalid HTTP content");

        }

        $this->content = $content;

        return $this;

    }

    public function type($type = null) {

        if ( is_null($type) ) {

            return $this->type;

        }

        $this->type = $type;

        return $this;

    }

    public function charset($charset = null) {

        if ( is_null($charset) ) {

            return $this->charset;

        }

        $this->charset = $charset;

        return $this;

    }

    public function length() {

        return strlen($this->content);

    }

}
