<?php

/*

    api/view/view_list.php - a list object of minimal/api view list object
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

namespace api\view;

use api\sandbox\list_object as list_api;
use api\view\view as view_api;
use html\view\view_list as view_list_dsp;
use JsonSerializable;

class view_list extends list_api implements JsonSerializable
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
    function add(view_api $phr): bool
    {
        return parent::add_obj($phr);
    }


    /*
     * cast
     */

    /**
     * @returns view_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): view_list_dsp
    {
        // TODO rename all vars of a view from $dsp to $msk
        $dsp_obj = new view_list_dsp();

        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst() as $wrd) {
            if ($wrd != null) {
                $wrd_dsp = $wrd->dsp_obj();
                $lst_dsp[] = $wrd_dsp;
            }
        }

        $dsp_obj->set_lst($lst_dsp);
        $dsp_obj->set_lst_dirty();

        return $dsp_obj;
    }

    /*
     * interface
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $dsp) {
            $vars[] = json_decode(json_encode($dsp));
        }
        return $vars;
    }

}
