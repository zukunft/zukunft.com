<?php

/*

    web/user/sandbox_value.php - the superclass for the html frontend of value sandbox objects
    ---------------------------

    This superclass should be used by the classes value_dsp, result_dsp, ...


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

include_once WEB_SANDBOX_PATH . 'db_object.php';

class sandbox_value_dsp extends db_object_dsp
{

    private phrase_group_dsp $grp; // the phrase group with the list of words and triples (not the source words and triples)
    private ?float $number; // the number calculated by the system

    // true if the user has done no personal overwrites which is the default case
    public bool $is_std;


    /*
     * construct and map
     */

    function __construct(int $id = 0)
    {
        parent::__construct($id);

        $this->grp = new phrase_group_dsp();
        $this->number = null;
        $this->is_std = true;
    }


    /*
     * set and get
     */

    function set_grp(phrase_group_dsp $grp)
    {
        $this->grp = $grp;
    }

    function set_number(?float $number)
    {
        $this->number = $number;
    }

    function set_is_std(bool $is_std = true): void
    {
        $this->is_std = $is_std;
    }

    function grp(): phrase_group_dsp
    {
        return $this->grp;
    }

    function number(): ?float
    {
        return $this->number;
    }

    /**
     * @return bool false if the loaded value is user specific
     */
    function is_std(): bool
    {
        return $this->is_std;
    }

    /**
     * @returns phrase_list_dsp the list of phrases as an object
     */
    function phr_lst(): phrase_list_dsp
    {
        return $this->grp()->phr_lst();
    }


    /*
     * display
     */

    /**
     * @returns string the html code to display the value with reference links
     * TODO create a popup with the details e.g. the values of other users
     */
    function value_linked(): string
    {
        return $this->number;
    }

}


