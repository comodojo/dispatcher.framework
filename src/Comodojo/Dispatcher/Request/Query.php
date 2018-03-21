<?php namespace Comodojo\Dispatcher\Request;

use \Comodojo\Foundation\Base\ParametersTrait;

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

class Query {

    use ParametersTrait;

    public function __construct() {

        $this->parameters = self::getParameters();

    }

    private static function getParameters() {

        return isset($_GET) ? $_GET : [];

        // switch( $_SERVER['REQUEST_METHOD'] ) {
        //
        //     case 'GET':
        //
        //         $parameters = $_GET;
        //
        //         break;
        //
        //     default:
        //
        //         $parameters = [];
        //
        //         break;
        //
        // }

        // return $parameters;

    }

}
