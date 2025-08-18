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

use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'sandbox_list_named.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::USER . 'user_message.php';

use html\sandbox\sandbox_list_named;
use html\component\component_exe as component;
use html\user\user_message;

class component_list extends sandbox_list_named
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

}
