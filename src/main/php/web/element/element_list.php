<?php

/*

    model/element/element_list.php - a list of formula elements to place the name function
    ----------------------------

    The main sections of this object are
    - construct and map: including the mapping of the db row to this element object
    - load:              database access object (DAO) functions
    - modify:            change potentially all object and all variables of this list with one function call


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\element;

include_once WEB_SANDBOX_PATH . 'list_dsp.php';

use html\sandbox\list_dsp;

class element_list extends list_dsp
{


}

