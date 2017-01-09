<?php namespace Comodojo\Dispatcher\Request;

use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Foundation\Timing\TimingTrait;
use \Comodojo\Foundation\Base\Configuration;
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

class Model extends AbstractModel {

    use TimingTrait;

    protected $mode = self::READONLY;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        parent::__construct($configuration, $logger);

        $this->setTiming($_SERVER['REQUEST_TIME_FLOAT']);

        $this->setRaw('headers', new Headers());
        $this->setRaw('uri', HttpUri::createFromServer($_SERVER));
        $this->setRaw('post', new Post());
        $this->setRaw('query', new Query());
        $this->setRaw('useragent', new UserAgent());
        $this->setRaw('method', new Method());
        $this->setRaw('version', new Version());
        $this->setRaw('files', Files::load());

    }

    public function route() {

        return str_replace($this->configuration->get("base-uri"), "", $this->uri->getPath());

    }

}
