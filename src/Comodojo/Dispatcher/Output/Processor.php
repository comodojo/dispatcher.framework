<?php namespace Comodojo\Dispatcher\Output;

use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Traits\RequestTrait;
use \Comodojo\Dispatcher\Traits\ResponseTrait;
use \Comodojo\Dispatcher\Components\HttpStatusCodes;
use \Comodojo\Foundation\Base\Configuration;
use \Psr\Log\LoggerInterface;
use \Exception;

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

//
// @WARNING: some method's preprocessor missing
//
//         _______
//        j_______j
//       /_______/_\
//       |Missing| |
//       |  ___  | |
//       | !418! | |
//       | !___! | |
//       |_______|,'
//

class Processor extends AbstractModel {

    use RequestTrait;
    use ResponseTrait;

    protected $codes;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Request $request,
        Response $response
    ) {

        parent::__construct($configuration, $logger);

        $this->setResponse($response);
        $this->setRequest($request);

        $this->codes = new HttpStatusCodes();

    }

    public function send() {

        $response = $this->getResponse();
        $request = $this->getRequest();

        $status = $response->getStatus()->get();

        if (!$this->codes->exists($status)) throw new Exception("Invalid HTTP status code in response");

        $message = $this->codes->getMessage($status);

        $response->getHeaders()->send();

        header(sprintf('HTTP/%s %s %s', (string)$request->getVersion(), $status, $message), true, $status);

        $response->getCookies()->save();

        return $response->getContent()->get();

    }

    public static function parse(
        Configuration $configuration,
        LoggerInterface $logger,
        Request $request,
        Response $response
    ) {

        $processor = new Processor($configuration, $logger, $request, $response);

        return $processor->send();

    }

}
