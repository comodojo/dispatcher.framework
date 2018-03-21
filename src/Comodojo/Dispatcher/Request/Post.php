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

class Post {

    use ParametersTrait;

    protected $raw_parameters;

    public function __construct() {

        $this->parameters = self::getParameters();

        $this->raw_parameters = self::getRawParameters();

    }

    public function getRaw() {

        return $this->raw_parameters;

    }

    private static function getParameters() {

        switch ($_SERVER['REQUEST_METHOD']) {

            case 'POST':

                $parameters = $_POST;

                break;

            case 'PUT':
            case 'DELETE':

                parse_str(file_get_contents('php://input'), $parameters);

                break;

            default:

                $parameters = array();

                break;

        }

        return $parameters;

    }

    private static function getRawParameters() {

        return file_get_contents('php://input');

    }

}
