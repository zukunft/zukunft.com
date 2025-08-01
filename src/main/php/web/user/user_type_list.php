<?php

/*

    web/user/user_type_list.php - the display extension of the user specific api type list object
    ---------------------------

    to create the HTML code to display a list of object types


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

namespace html\user;

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';

use html\html_base;
use html\html_selector;
use html\types\type_list;

class user_type_list extends type_list
{

    function list(string $class, string $title = ''): string
    {
        $html = new html_base();
        if ($title != '') {
            $title = $html->text_h2($title);
        }
        return $title . $html->list($this->lst(), $class);
    }

    /**
     * @returns string the html code to select a type from this list
     */
    function selector(string $name = '', string $form = '', int $selected = 0): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
        $sel->lst = $this->db_id_list();
        $sel->selected = $selected;
        return $sel->display();
    }

}
