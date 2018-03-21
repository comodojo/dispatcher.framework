<?php namespace Comodojo\Dispatcher\Traits;

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

trait HeadersTrait {

    protected $headers = [];

    public function get($header = null) {

        if (is_null($header)) return $this->headers;

        else if (array_key_exists($header, $this->headers)) return $this->headers[$header];

        else return null;

    }

    public function getAsString($header = null) {

        if (is_null($header)) {

            return array_map([$this, 'headerToString'],
                array_keys($this->headers),
                array_values($this->headers)
            );

        } else if (array_key_exists($header, $this->headers)) {

            return self::headerToString($header, $this->headers[$header]);

        } else {
            return null;
        }

    }

    public function set($header, $value = null) {

        if (is_null($value)) {

            $header = explode(":", $header, 2);

            $this->headers[$header[0]] = isset($header[1]) ? $header[1] : '';

        } else {

            $this->headers[$header] = $value;

        }

        return $this;

    }

    public function delete($header = null) {

        if (is_null($header)) {

            $this->headers = array();

            return true;

        } else if (array_key_exists($header, $this->headers)) {

            unset($this->headers[$header]);

            return true;

        } else {

            return false;

        }

    }

    public function merge($headers) {

        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }

        return $this;

    }

    private static function headerToString($header, $value) {

        return (string)($header.':'.$value);

    }

}
