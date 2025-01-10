<?php

/*

    web/helper/data_object.php - a header object for all frontend data objects e.g. phrase_list, values, formulas
    --------------------------


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

namespace html\helper;

include_once SHARED_PATH . 'json_fields.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_VIEW_PATH . 'view_list.php';

use html\user\user_message;
use html\view\view_list;

class data_object
{

    /*
     *  object vars
     */

    private view_list $msk_lst;
    // for warning and errors while filling the data_object
    private user_message $usr_msg;


    /*
     * construct and map
     */

    /**
     * always set the user because always someone must have requested to create the list
     * e.g. an admin can have requested to import words for another user
     */
    function __construct()
    {
        $this->msk_lst = new view_list();
        $this->usr_msg = new user_message();
    }


    /*
     * set and get
     */

    /**
     * set the view_list of this data object
     * @param view_list $msk_lst
     */
    function set_view_list(view_list $msk_lst): void
    {
        $this->msk_lst = $msk_lst;
    }

    /**
     * @return view_list with the view of this data object
     */
    function view_list(): view_list
    {
        return $this->msk_lst;
    }

    /**
     * @return bool true if this context object contains a view list
     */
    function has_view_list(): bool
    {
        if ($this->msk_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

}
