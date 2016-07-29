<?php namespace Comodojo\Dispatcher\Request;

use \Exception;
use \Comodojo\Exception\DispatcherException;

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

    private $path  = '';

    private $slug  = '';

    private $fname = '';

    private $tname = '';

    private $upld  = '';

    private $ctype = 'text/plain';

    private $size  = 0;

    public function __construct($fileControl = null, $repository = '') {

        $this->path = $repository;

        if (!is_null($fileControl)) {

            $this->load($fileControl);

        }

    }

    public function getTemporaryName() {

        return $this->tname;

    }

    public function getLocalName() {

        if (!empty($this->path))
            return $this->path . "/" . $this->slug;
        else
            return '';

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

        if (file_exists($file))
            return file_get_contents($file);

        throw new DispatcherException("File does not exists");

    }

    public function fileIsSaved() {

        return (
            !empty($this->path) &&
            !empty($this->slug) &&
            file_exists($this->getLocalName())
        );

    }

    public function load($slugOrControl) {

        if (isset($_FILES) && isset($_FILES[$slugOrControl])) {

            $this->loadFromUploadedFile($slugOrControl);

            return $this;

        }

        throw new DispatcherException("The requested file has not been uploaded");

    }

    public function save($repository = '') {

        if (!empty($repository)) {

            $this->path = $repository;

        }

        if (!empty($this->path) && file_exists($this->path)) {

            if (move_uploaded_file($this->getTemporaryName(), $this->getLocalName())) {

                return $this->fileIsSaved();

            }

            throw new DispatcherException("Unable to save file");

        }

        throw new DispatcherException("Repository path is not available");

    }

    private function loadFromUploadedFile($fileControl) {

        if (isset($_FILES[$fileControl])) {

            $file = $_FILES[$fileControl];

            $this->tname = $file['tmp_name'];
            $this->fname = $file['name'];
            $this->ctype = $file['type'];
            $this->size  = intval($file['size']);
            $this->upld  = filectime($file['tmp_name']);

            $this->createSlug();

            return $this;

        }

        throw new DispatcherException("File not uploaded");

    }

    private function createSlug() {

        preg_match_all("/[a-z0-9]+/", iconv("UTF-8", "ASCII//TRANSLIT", strtolower(preg_replace('/\..*?$/', '', $this->fname))), $matches);

        $this->slug  = implode('-', $matches[0]);

        if (!empty($this->path)) {

            $files = glob($this->path . "/" . $slug . "*");

            $count = count ($files);

            if ($count > 0) {

                $this->slug .= "-" . $count;

            }

        }

    }

    public static function fromUploadedFiles($repository = '') {

        $files = array();

        foreach ($_FILES as $idx => $data) {

            $files[] = new File($idx, $repository);

        }

        return $files;

    }

}
