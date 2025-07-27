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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::USER . 'user_message.php';

use html\sandbox\list_dsp;
use html\component\component_exe as component;
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
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new component());
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
     * base
     */

    /**
     * @return string with a list of the component names with html links
     * ex. names_linked
     */
    function name_tip(): string
    {
        $components = array();
        foreach ($this->lst() as $cmp) {
            $components[] = $cmp->name_tip();
        }
        return implode(', ', $components);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the component names with html links
     * ex. names_linked
     */
    function name_link(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the component names with html links
     */
    private function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst() as $cmp) {
            $result[] = $cmp->name_link($back);
        }
        return $result;
    }

}
