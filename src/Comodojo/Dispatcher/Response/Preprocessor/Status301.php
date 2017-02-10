<?php namespace Comodojo\Dispatcher\Response\Preprocessor;

use \Exception;

/**
 * Status: Moved Permanently
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

class Status301 extends Status200 {

    public function consolidate() {

        $location = $this->response->getLocation()->get();

        if (empty($location)) throw new Exception("Invalid location, cannot redirect");

        $this->response->getHeaders()->set("Location", $location);

        $content = $this->response->getContent();

        if (empty($content->get())) {

            $content->set(sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />
        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($location, ENT_QUOTES, 'UTF-8')));

        }

        parent::consolidate();

    }

}
