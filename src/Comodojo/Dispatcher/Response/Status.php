<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Traits\ToStringTrait;
use \Comodojo\Dispatcher\Components\HttpStatusCodes;
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

class Status {

    use ToStringTrait;

    private $status_code = 200;

    private $codes;

    public function __construct() {

        $this->codes = new HttpStatusCodes;

    }

    public function get() {

        return $this->status_code;

    }

    public function set($code) {

        if (!$this->codes->exists($code)) {

            throw new Exception("Invalid HTTP Status Code $code");

        }

        $this->status_code = $code;

        return $this;

    }

    public function description($code=null) {

        if ( is_null($code) ) return $this->codes->getMessage($this->status_code);

        return $this->codes->getMessage($code);

    }

}
