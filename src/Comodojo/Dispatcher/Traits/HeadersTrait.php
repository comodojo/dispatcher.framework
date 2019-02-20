<?php namespace Comodojo\Dispatcher\Traits;

/**
 * Common trait to manage request and response headers
 *
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

    /**
     * Headers array
     * @var array
     */
    protected $headers = [];

    /**
     * Get one or all header(s)
     * If $header param is not provided, this method will return all the
     * registered headers.
     *
     * Starting from (v 4.1+) this method is CASE INSENSITIVE
     *
     * @param string $header
     * @return string|array|null
     */
    public function get($header = null) {

        if (is_null($header)) {
            return $this->headers;
        }

        $index = $this->getIndexCaseInsensitive($header);

        return $index === null ? null : $this->headers[$index];

    }

    /**
     * Get one or all header(s) as a string
     * If $header param is not provided, this method will return all the
     * registered headers.
     *
     * Starting from (v 4.1+) this method is CASE INSENSITIVE
     *
     * @param string $header
     * @return string|array|null
     */
    public function getAsString($header = null) {

        if (is_null($header)) {

            return array_map([$this, 'headerToString'],
                array_keys($this->headers),
                array_values($this->headers)
            );

        }

        $index = $this->getIndexCaseInsensitive($header);

        return $index === null ? null : self::headerToString($index, $this->headers[$index]);

    }

    /**
     * Check if an header is registered
     *
     * Starting from (v 4.1+) this method is CASE INSENSITIVE
     *
     * @param string $header
     * @return bool
     */
    public function has($header) {

        return $this->getIndexCaseInsensitive($header) === null ? false : true;

    }

    /**
     * Set an header
     * If $header is an inline header and value is null, this method will split
     *  it automatically.
     * $value can be a string or an array. In the latter case, these values will
     *  separated by a comma in the string representation of the header.
     *
     * @param string $header Header name or string representation
     * @param mixed $value Header value(s)
     * @return self
     */
    public function set($header, $value = null) {

        if (is_null($value)) {

            $header = explode(":", $header, 2);

            $this->headers[$header[0]] = isset($header[1]) ? trim($header[1]) : '';

        } else {

            $this->headers[$header] = $value;

        }

        return $this;

    }

    /**
     * Delete one or all header(s)
     *
     * If no argument is provided, all headers will be deleted.
     *
     * Starting from (v 4.1+) this method is CASE INSENSITIVE
     *
     * @param string $header
     * @return bool
     */
    public function delete($header = null) {

        if (is_null($header)) {

            $this->headers = [];

            return true;

        }

        $index = $this->getIndexCaseInsensitive($header);

        if ( $index === null ) {
            return false;
        }

        unset($this->headers[$index]);
        return true;

    }

    /**
     * Merge actual headers with an array of headers
     *
     * @param string $header
     * @return self
     */
    public function merge($headers) {

        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }

        return $this;

    }

    private function getIndexCaseInsensitive($header) {

        $mapping = array_combine(
            array_keys(array_change_key_case($this->headers, CASE_LOWER)),
            array_keys($this->headers)
        );

        $header = strtolower($header);

        return array_key_exists($header, $mapping) ?
            $mapping[$header] : null;

    }

    private static function headerToString($header, $value) {

        return is_array($value) ?
            (string)("$header: ".implode(',',$value)) :
            (string)("$header: $value");

    }

}
