<?php namespace Comodojo\Dispatcher\Request;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Components\Configuration;
use \League\Uri\Schemes\Http as HttpUri;
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

class Model extends DispatcherClassModel {

    use TimestampTrait;
    
    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        parent::__construct($configuration, $logger);

        $this->setTimestamp($_SERVER['REQUEST_TIME_FLOAT']);
        
        $this->headers = new Headers();

        $this->uri = HttpUri::createFromServer($_SERVER);

        $this->post = new Post();

        $this->query = new Query();

        $this->useragent = new UserAgent();

        $this->method = new Method();

        $this->version = new Version();
        
        $this->file = File::fromUploadedFiles();

    }

    public function route() {

        return str_replace($this->configuration->get("base-uri"), "", $this->uri->getPath());
    }

}
