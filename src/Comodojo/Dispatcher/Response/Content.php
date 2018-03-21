<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Traits\ToStringTrait;
use \Exception;

/**
 * @package     Comodojo Dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
