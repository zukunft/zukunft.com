<?php

/*

    cfg/view/view_link_type.php - to define the behaviour of the link between a term and a view
    ---------------------------

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

namespace cfg;

class view_link_type extends type_object
{

    /*
     * code links
     */

    // list of link types that have a coded functionality
    const DEFAULT = "default"; // a simple link between a formula and a phrase


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to define the behaviour of the link between a term and a view';

}
