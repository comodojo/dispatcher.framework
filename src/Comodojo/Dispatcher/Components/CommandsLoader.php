<?php namespace Comodojo\Dispatcher\Components;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \Comodojo\Exception\ConfigurationException;
use \Exception;

/**
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class CommandsLoader {

    public static function load($commandconfig) {

        $config = self::importData($commandconfig);

        $classes = [];

        foreach ($config as $package) {
            foreach ($package as $command) {
                if ( $command['scope'] === 'dispatcher') {
                    $classes[] = $command['class'];
                }
            }
        }

        return $classes;

    }

    protected static function importData($file) {

        if ( file_exists($file) && is_readable($file) ) {

            $data = @file_get_contents($file);

            if ( $data !== false ) {

               return Yaml::parse($data);

           } else {
               throw new ConfigurationException("Configuration file $file not readable");
           }

        } else {
            throw new ConfigurationException("Configuration file $file not found");
        }

    }

}
