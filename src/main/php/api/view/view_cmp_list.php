<?php

/*

    api/view/view_cmp_list_api.php - a list object of minimal/api view component objects
    -----------------


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

namespace api;

use html\word_list_dsp;

class view_cmp_list_api extends list_api
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a view component to the list
     * @returns bool true if at least one view component has been added
     */
    function add(word_api $phr): bool
    {
        return parent::add_obj($phr);
    }

    /*
     * casting objects
     */

    /**
     * @returns word_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): word_list_dsp
    {
        $dsp_obj = new word_list_dsp();

        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $wrd) {
            if ($wrd != null) {
                $wrd_dsp = $wrd->dsp_obj();
                $lst_dsp[] = $wrd_dsp;
            }
        }

        $dsp_obj->set_lst($lst_dsp);
        $dsp_obj->set_lst_dirty();

        return $dsp_obj;
    }

}
