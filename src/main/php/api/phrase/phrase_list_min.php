<?php

/*

    phrase_list_min.php - a list object of minimal/api phrase objects
    -------------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

use api\list_min;
use phrase_list_min_dsp;

class phrase_list_min extends list_min
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add(phrase_min $phr): bool
    {
        $result = false;
        if (!in_array($phr->id, $this->id_lst())) {
            $this->lst[] = $phr;
            $this->lst_dirty = true;
            $result = true;
        }
        return $result;
    }

    /**
     * @returns int the number of phrases of the protected list
     */
    function count(): int
    {
        return count($this->lst);
    }

    /**
     * @returns true if the list does not contain any phrase
     */
    function is_empty(): bool
    {
        if ($this->count() <= 0) {
            return true;
        } else {
            return false;
        }
    }

    function intersect(phrase_list_min $new_lst): phrase_list_min
    {
        if (!$new_lst->is_empty()) {
            if ($this->is_empty()) {
                $this->lst = $new_lst->lst;
            } else {
                $this->lst = array_intersect($this->lst, $new_lst->lst);
            }
        }
        return $this;
    }

    /**
     * @returns phrase_list_min_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): phrase_list_min_dsp
    {
        $dsp_obj = new phrase_list_min_dsp();

        $dsp_obj->lst = $this->lst;
        $dsp_obj->lst_dirty = true;

        return $dsp_obj;
    }


}
