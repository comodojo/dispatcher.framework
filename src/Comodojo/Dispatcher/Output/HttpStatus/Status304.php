<?php namespace Comodojo\Dispatcher\Output\HttpStatus;

/**
 * Status: Not Modified
 *
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

class Status304 extends AbstractHttpStatus {

    public function consolidate() {

        $last_modified = $this->response()->headers()->get('Last-Modified');

        if ( is_null($last_modified) ) {

            header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');

        } else if ( is_int($last_modified) ) {

            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified).' GMT', true, 304);

        } else {

            header('Last-Modified: '.$last_modified, true, 304);

        }

        header('Content-Length: '.$this->response()->content()->length());

        $this->response()->headers()->remove('Last-Modified');

    }

}
