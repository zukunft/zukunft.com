<?php

/*

    web/verb/verb_list.php - al list of verb objects
    ----------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\verb;

include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_TYPES_PATH . 'type_list.php';
include_once WEB_USER_PATH . 'user.php';
include_once SHARED_PATH . 'library.php';

use html\html_base;
use html\types\type_list;
use html\user\user;
use shared\library;

class verb_list extends type_list
{

    private ?user $usr = null; // the user object of the person for whom the verb list is loaded, so to say the viewer

    function list(string $class, string $title = ''): string
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        $html = new html_base();
        if ($title != '') {
            $title = $html->text_h2($title);
        }
        return $title . $html->list($this->lst(), $class);
    }


}