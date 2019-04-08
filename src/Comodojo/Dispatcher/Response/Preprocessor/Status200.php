<?php namespace Comodojo\Dispatcher\Response\Preprocessor;

use \Comodojo\Dispatcher\Response\Model as Response;

/**
 * Status: OK
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

class Status200 extends AbstractHttpStatusPreprocessor {

    /**
     * {@inheritdoc}
     */
    public function consolidate(Response $response) {

        $response->getHeaders()
            ->set('Content-Length', $response->getContent()->length());

        $type = $response->getContent()->type();
        $charset = $response->getContent()->charset();

        if (empty($charset)) {
            $response->getHeaders()->set("Content-type", strtolower($type));
        } else {
            $response->getHeaders()->set("Content-type", strtolower($type)."; charset=".$charset);
        }

    }

}
