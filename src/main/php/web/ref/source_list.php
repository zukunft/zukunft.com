<?php

/*

    web\ref\source_list.php - create the HTML code to display a source list
    -----------------------

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

namespace html\ref;

include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_REF_PATH . 'source.php';
include_once WEB_USER_PATH . 'user_message.php';

use html\sandbox\list_dsp;
use html\ref\source as source_dsp;
use html\user\user_message;

class source_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a source object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new source_dsp());
    }


    /*
     * modify
     */

    /**
     * add a source to the list
     * @returns bool true if the source has been added
     */
    function add(source_dsp $src): bool
    {
        return parent::add_obj($src);
    }

}
