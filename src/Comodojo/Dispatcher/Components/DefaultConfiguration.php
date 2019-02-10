<?php namespace Comodojo\Dispatcher\Components;

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

class DefaultConfiguration {

    private static $configuration = array(
        'enabled' => true,
        'encoding' => 'UTF-8',
        'disabled-status' => 503,
        'disabled-message' => 'Dispatcher offline'
    );

    public static function get() {

        $config = self::$configuration;

        $config['base-path'] = getcwd();

        $config['base-url'] = self::getAbsoluteUrl();

        $config['base-uri'] = self::getAbsoluteUri();

        $config['base-location'] = self::getAbsoluteLocation();

        return $config;

    }

    private static function getAbsoluteUrl() {

        $http = self::getHttpSchema();

        $location = self::getAbsoluteLocation();

        return ($http.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost').$location."/");

    }

    private static function getAbsoluteLocation() {

        return preg_replace("/\/index.php(.*?)$/i", "", $_SERVER['PHP_SELF']);

    }

    private static function getAbsoluteUri() {

        $http = self::getHttpSchema();

        return "$http$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    }

    private static function getHttpSchema() {

        return 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://';

    }

}
