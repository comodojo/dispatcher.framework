<?php namespace Comodojo\Dispatcher\Template;

/**
 * A simple interface for dispatcher templates
 *
 * @package     Comodojo dispatcher (Spare Parts)
 * @author      Marco Giovinazzi <info@comodojo.org>
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

interface TemplateInterface {

    /**
     * This method is intended to be a string replace, using special tags in template
     * to be substituted by service content
     *
     * @param   string  $tag    The tag to replace
     * @param   string  $data   The data to inject
     */
    public function replace($tag, $data);

    /**
     * This method is intended to be the end method for template, returning back the complete
     * content.
     *
     * @return  string
     */
    public function serialize();

}