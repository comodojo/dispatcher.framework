<?php namespace Comodojo\Dispatcher\Request;

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

class File {

    private $slug;

    private $fname;

    private $tname;

    private $upld;

    private $ctype;

    private $size = 0;

    public function __construct($fileControl) {

        $this->load($fileControl);

    }

    /**
     * @return string
     */
    public function getTemporaryName() {

        return $this->tname;

    }

    public function getFileName() {

        return $this->fname;

    }

    public function getSlug() {

        return $this->slug;

    }

    public function getContentType() {

        return $this->ctype;

    }

    public function getSize() {

        return $this->size;

    }

    public function getUploadTime() {

        return $this->upld;

    }

    public function getFileData() {

        $file = $this->getTemporaryName();

        if (file_exists($file)) {
            return file_get_contents($file);
        }

        throw new Exception("File does not exists");

    }

    public function load($slugOrControl) {

        if (isset($_FILES) && isset($_FILES[$slugOrControl])) {

            $this->loadFromUploadedFile($slugOrControl);

            return $this;

        }

        throw new Exception("The requested file has not been uploaded");

    }

    public function save($path, $as_slug = false) {

        if (!empty($path) && file_exists($path)) {

            $local_name = "$path/".($as_slug ? $this->getSlug() : $this->getFileName());

            if (file_exists($local_name)) {

                $files = glob("$local_name*");

                $count = count($files);

                $local_name .= "-$count";

            }

            if (move_uploaded_file($this->getTemporaryName(), $local_name)) {

                // return file_exists($local_name);
                return true;

            }

            throw new Exception("Unable to save file");

        }

        throw new Exception("Repository path not available");

    }

    private function loadFromUploadedFile($fileControl) {

        $file = $_FILES[$fileControl];

        $this->tname = $file['tmp_name'];
        $this->fname = $file['name'];
        $this->ctype = $file['type'];
        $this->size = intval($file['size']);
        $this->upld = filectime($file['tmp_name']);
        $this->slug = self::createSlug($this->fname);

        return $this;

    }

    private static function createSlug($filename) {

        preg_match_all("/[a-z0-9]+/", iconv("UTF-8", "ASCII//TRANSLIT", strtolower(preg_replace('/\..*?$/', '', $filename))), $matches);

        return implode('-', $matches[0]);

    }

    public static function fromUploadedFiles($repository = '') {

        $files = array();

        foreach ($_FILES as $idx => $data) {

            $files[] = new File($idx, $repository);

        }

        return $files;

    }

}
