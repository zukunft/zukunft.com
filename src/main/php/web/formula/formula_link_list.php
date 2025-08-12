<?php

/*

    web/formula/formula_link_list.php - create the html code for a list of formula links
    ---------------------------------

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

namespace formula;

use html\const\paths as html_paths;

include_once html_paths::SANDBOX . 'list_dsp.php';

use html\sandbox\list_dsp;

class formula_link_list extends list_dsp
{

    /*
     * display
     */

    /**
     * @return string with a list of the formula names with html links
     * ex. names_linked
     */
    function name_tip(): string
    {
        $names = array();
        foreach ($this->lst() as $lnk) {
            $names[] = $lnk->name_tip();
        }
        return implode(', ', $names);
    }

}
