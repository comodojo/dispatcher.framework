<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Traits\ToStringTrait;
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

    use ToStringTrait;

    private $content;

    private $type = "text/plain";

    private $charset = "utf-8";

    public function get() {

        return $this->content;

    }

    public function set($content = null) {

        if (!is_scalar($content) && $content != null) {

            throw new Exception("Invalid HTTP content");

        }

        $this->content = $content;

        return $this;

    }

    public function getType() {

        return $this->type;

    }

    public function setType($type) {

        $this->type = $type;

        return $this;

    }

    public function type($type = null) {

        return is_null($type) ? $this->getType() : $this->setType($type);

    }

    public function getCharset() {

        return $this->charset;

    }

    public function setCharset($charset) {

        $this->charset = $charset;

        return $this;

    }

    public function charset($charset = null) {

        return is_null($charset) ? $this->getCharset() : $this->setCharset($charset);

    }

    public function getLength() {

        return strlen($this->content);

    }

    public function length() {

        return $this->getLength();

    }

}
