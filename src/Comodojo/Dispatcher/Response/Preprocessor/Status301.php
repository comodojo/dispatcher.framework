<?php namespace Comodojo\Dispatcher\Response\Preprocessor;

use \Comodojo\Dispatcher\Response\Model as Response;
use \Exception;

/**
 * Status: Moved Permanently
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

class Status301 extends Status200 {

    /**
     * {@inheritdoc}
     */
    public function consolidate(Response $response) {

        $location = $response->getLocation()->get();

        if (empty($location)) {
            throw new Exception("Invalid location, cannot redirect");
        }

        $response->getHeaders()->set("Location", $location);

        $content = $response->getContent();

        if (empty($content->get())) {

            $content->set(sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />
        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($location, ENT_QUOTES, 'UTF-8')));

        }

        parent::consolidate();

    }

}
