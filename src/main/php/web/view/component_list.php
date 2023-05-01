<?php

/*

    view_cmp_list_dsp.php - a list function to create the HTML code to display a view component list
    ---------------------

    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\view;

include_once WEB_SANDBOX_PATH . 'list.php';

use html\list_dsp;
use html\view\component as component_dsp;

class component_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a view object based on the given json
     * @param array $json_array an api single object json message
     * @return object a view set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new component_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * display
     */

    /**
     * @return string with a list of the component names with html links
     * ex. names_linked
     */
    function display(): string
    {
        $components = array();
        foreach ($this->lst as $cmp) {
            $components[] = $cmp->name();
        }
        return implode(', ', $components);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the component names with html links
     * ex. names_linked
     */
    function display_linked(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the component names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst as $cmp) {
            $result[] = $cmp->display_linked($back);
        }
        return $result;
    }

}
