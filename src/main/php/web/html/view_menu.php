<?php

/*

    web/html/view_menu.php - what the page knows for the edit menu of the navbar
    ----------------------

    $mnu is the suggested var name

    the page fills it e.g. with the views of the shown word and the columns of its value table,
    so that the navbar gets the parts of the edit menu as one object


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\html;

class view_menu
{

    // the view id of the editor of the current view e.g. the table editor; 0 if it has none
    public int $edt_id = 0;
    // the name of the editor view shown in the edit menu
    public string $edt_name = '';
    // the menu entries of the views suggested for the shown object e.g. of a word, shown after the editor
    public string $view_items = '';
    // the menu entries of the page e.g. the columns of its value table, shown first
    public string $page_items = '';
    // the names of some views that the menu does not offer, which the tooltip of "... more" names
    public array $more_names = [];
    // the name of the shown object e.g. the word "Pi", which the tooltip of "add view" names
    public string $obj_name = '';

}
