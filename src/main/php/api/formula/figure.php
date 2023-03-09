<?php

/*

    api/formula/figure.php - the minimal figure object
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

use html\figure_dsp;

class figure_api extends user_sandbox_value_api
{

    /*
     * object vars
     */

    private bool $is_result;          // true if the value has been calculated and not set by a user


    /*
     * construct and map
     */

    function __construct(int $id = 0)
    {
        parent::__construct($id);
        $this->set_type_result();
    }


    /*
     * set and get
     */

    /**
     * define that this figure has been calculated based on other numbers
     * @return void
     */
    function set_type_result(): void
    {
        $this->is_result = true;
    }

    /**
     * define that this figure has been defined by a user
     * @return void
     */
    function set_type_value(): void
    {
        $this->is_result = false;
    }

    function is_result(): bool
    {
        if ($this->is_result) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * cast
     */

    /**
     * @returns figure_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): figure_dsp
    {
        $dsp_obj = new figure_dsp($this->id);
        $dsp_obj->set_grp($this->grp());
        $dsp_obj->set_number($this->number());
        return $dsp_obj;
    }

}
