<?php namespace Comodojo\Dispatcher\Components;

use \Comodojo\Foundation\Base\Configuration;
use \Psr\Log\LoggerInterface;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Foundation\Logging\LoggerTrait;

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

abstract class AbstractModel {

    use ConfigurationTrait;
    use LoggerTrait;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);

    }

}
