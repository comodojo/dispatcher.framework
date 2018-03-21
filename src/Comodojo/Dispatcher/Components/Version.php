<?php namespace Comodojo\Dispatcher\Components;

use \Comodojo\Foundation\Base\AbstractVersion;

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

class Version extends AbstractVersion {

    protected $name = 'Comodojo/dispatcher';
    protected $description = 'Hackable PHP REST framework';
    protected $version = '4.0-dev';
    protected $ascii = "\r\n   ___  _               __      __          \r\n".
                       "  / _ \(_)__ ___  ___ _/ /_____/ /  ___ ____\r\n".
                       " / // / (_-</ _ \/ _ `/ __/ __/ _ \/ -_) __/\r\n".
                       "/____/_/___/ .__/\_,_/\__/\__/_//_/\__/_/   \r\n".
                       "          /_/                               \r\n";
    protected $template = "\n\n{ascii}\r\n".
                          "---------------------------------------------\r\n".
                          "{name} (ver {version})\r\n{description}\r\n";
    protected $prefix = 'dispatcher-';

}
