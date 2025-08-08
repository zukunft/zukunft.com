<?php

/*

    web/html/sheet.php - create the html code to display a spreadsheet
    ------------------


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

namespace html;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SHEET . 'position_list.php';

use html\phrase\phrase;
use html\phrase\phrase_list;

class table
{

    private phrase_list $col_lst;

    function __construct(?string $api_json = null)
    {
        $this->reset();
    }

    function reset(): void
    {
        $this->col_lst = new phrase_list();
    }


    function add_column(phrase $phr): void
    {
        $this->col_lst->add($phr);
    }

}
