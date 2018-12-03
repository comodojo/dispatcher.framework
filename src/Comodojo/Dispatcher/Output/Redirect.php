<?php namespace Comodojo\Dispatcher\Output;

use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Router\Route;

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

 class Redirect {

     public static function compose(
         Request $request,
         Response $response,
         Route $route
     ) {

         $status = $response->getStatus();
         $content = $response->getContent();
         $headers = $response->getHeaders();

         $code = $route->getRedirectCode();

         $location = $route->getRedirectLocation();
         $uri = empty($location) ? (string) $request->getUri() : $location;

         $message = $route->getRedirectMessage();

         if ( $route->getRedirectType() == Route::REDIRECT_REFRESH ) {

             $output = empty($message) ?
                 "Please follow <a href=\"$location\">this link</a>"
                 : $message;

             $status->set(200);
             $headers->set("Refresh", "0;url=$location");
             $content->set($output);

         } else {

             if ( !empty($code) ) {
                 $status->set($code);
             } else if ( $request->getVersion() === 'HTTP/1.1' ) {
                 $status->set( (string) $request->getMethod() !== 'GET' ? 303 : 307);
             } else {
                 $status->set(302);
             }

             $content->set($message);
             $response->getLocation()->set($uri);

         }

     }

 }
