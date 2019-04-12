<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Interfaces\HttpStatusPreprocessor as HttpStatusPreprocessorInterface;
use \Exception;

/**
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

class Preprocessor {

    /**
     * @const int DEFAULT_STATUS
     *  The default preprocessor template to use (200 OK)
     */
    const DEFAULT_STATUS = 200;

    /**
     * @var array $preprocessors
     *  An array of all supported preprocessor
     */
    private $preprocessors = [
        100 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status100",
        101 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status101",
        102 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status102",
        200 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status200",
        201 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status201",
        202 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status202",
        203 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status203",
        204 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status204",
        205 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status205",
        206 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status206",
        300 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status300",
        301 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status301",
        302 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status302",
        303 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status303",
        304 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status304",
        305 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status305",
        307 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status307",
        308 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status308",
        400 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status400",
        403 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status403",
        404 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status404",
        405 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status405",
        410 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status410",
        500 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status500",
        501 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status501",
        502 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status502",
        503 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status503",
        504 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status504",
        505 => "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status505"
    ];

    /**
     * Check if a preprocessor is registered
     *
     * @param int $status
     *  The HTTP status to check with
     * @return bool
     */
    public function has($status) {

        return array_key_exists($status, $this->preprocessors);

    }

    /**
     * Get an instance of the preprocessor for the current status
     *
     * @param int $status
     *  The HTTP status to use
     * @return HttpStatusPreprocessorInterface
     */
    public function get($status) {

        $preprocessor = $this->has($status) ?
            $this->preprocessors[$status] :
            $this->preprocessors[self::DEFAULT_STATUS];

        return ($preprocessor instanceof HttpStatusPreprocessorInterface) ?
            $preprocessor : new $preprocessor;

    }

    /**
     * Get an instance of the preprocessor for the current status
     *
     * @param int $status
     *  The HTTP status to register
     * @param HttpStatusPreprocessorInterface $preprocessor
     *  The preprocessor for the provided HTTP status
     * @return bool
     */
    public function set($status, HttpStatusPreprocessorInterface $preprocessor) {

        $this->preprocessors[$status] = $preprocessor;
        return true;

    }

}
