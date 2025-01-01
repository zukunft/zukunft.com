<?php

/*

    component_list_dsp.php - a list function to create the HTML code to display a view component list
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

namespace html\component;

include_once SANDBOX_PATH . 'list_dsp.php';
include_once COMPONENT_PATH . 'component.php';

use html\sandbox\list_dsp;
use html\component\component as component_dsp;
use html\user\user_message;

class component_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        return parent::set_list_from_json($json_array, new component_dsp());
    }

    /*
     * load
     */

    function load_by_view_id(int $id): bool
    {
        $url = '';
        return true;

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
