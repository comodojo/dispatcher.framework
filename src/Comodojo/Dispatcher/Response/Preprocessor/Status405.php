<?php namespace Comodojo\Dispatcher\Response\Preprocessor;

use \Comodojo\Dispatcher\Response\Model as Response;
use \Exception;

/**
 * Status: Method Not Allowed
 *
 * @package     Comodojo Dispatcher
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

class Status405 extends Status400 {

    /**
     * {@inheritdoc}
     */
    public function consolidate(Response $response) {

        // @NOTE Allow Header should be provided by DispatcherException
        $allow = $response->getHeaders()->get('Allow');

        if (empty($allow)) {
            throw new Exception("Missing Allow header");
        }

        parent::consolidate();

    }

}
