<?php namespace Comodojo\Dispatcher\Request;

use \Exception;

/**
 * @package     Comodojo Dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
 * @license     GPL-3.0+
 *
 * LICENSE:
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
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
