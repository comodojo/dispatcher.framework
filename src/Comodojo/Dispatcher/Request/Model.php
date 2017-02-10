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

    protected $headers;

    protected $uri;

    protected $post;

    protected $query;

    protected $useragent;

    protected $method;

    protected $version;

    protected $files;
    
    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        parent::__construct($configuration, $logger);

        $this->setTiming($_SERVER['REQUEST_TIME_FLOAT']);

        $this->setHeaders(new Headers());
        $this->setUri(HttpUri::createFromServer($_SERVER));
        $this->setPost(new Post());
        $this->setQuery(new Query());
        $this->setUserAgent(new UserAgent());
        $this->setMethod(new Method());
        $this->setVersion(new Version());
        $this->setFiles(Files::load());

    }

    public function getHeaders() {

        return $this->headers;

    }

    public function setHeaders(Headers $headers) {

        $this->headers = $headers;

        return $this;

    }

    public function getUri() {

        return $this->uri;

    }

    public function setUri(HttpUri $uri) {

        $this->uri = $uri;

        return $this;

    }

    public function getPost() {

        return $this->post;

    }

    public function setPost(Post $post) {

        $this->post = $post;

        return $this;

    }

    public function getQuery() {

        return $this->query;

    }

    public function setQuery(Query $query) {

        $this->query = $query;

        return $this;

    }

    public function getUserAgent() {

        return $this->useragent;

    }

    public function setUserAgent(UserAgent $useragent) {

        $this->useragent = $useragent;

        return $this;

    }

    public function getMethod() {

        return $this->method;

    }

    public function setMethod(Method $method) {

        $this->method = $method;

        return $this;

    }

    public function getVersion() {

        return $this->version;

    }

    public function setVersion(Version $version) {

        $this->version = $version;

        return $this;

    }

    public function getFiles() {

        return $this->files;

    }

    public function setFiles(array $files) {

        $this->files = $files;

        return $this;

    }

    public function route() {

        return str_replace($this->getConfiguration()->get("base-uri"), "", $this->getUri()->getPath());

    }

}
