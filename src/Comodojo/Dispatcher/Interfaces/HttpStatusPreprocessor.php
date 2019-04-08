<?php namespace Comodojo\Dispatcher\Interfaces;

use \Comodojo\Dispatcher\Response\Model as Response;
use \Exception;

/**
 * The interface all Response Preprocessors shall implement
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

interface HttpStatusPreprocessor {

    /**
     * Consolidate the Response before triggering the output processor
     *
     * @param Response $response
     *  The response object that will be preprocessed
     * @throws Exception
     * @return void
     */
    public function consolidate(Response $response);

}
