<?php

/*

    api\phrase_list.php - a list object of minimal/api phrase objects
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

use phrase_list_dsp;

class phrase_list_api extends list_api
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add(phrase_api $phr): bool
    {
        return parent::add_obj($phr);
    }

    /*
     * info
     */

    /**
     * @return bool true if one of the phrases is of type percent
     */
    function has_percent(): bool
    {
        $result = false;
        foreach ($this->lst as $phr) {
            if ($phr->is_percent()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * casting objects
     */

    /**
     * @returns phrase_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): phrase_list_dsp
    {
        $dsp_obj = new phrase_list_dsp();

        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $phr) {
            if ($phr != null) {
                $phr_dsp = $phr->dsp_obj();
                $lst_dsp[] = $phr_dsp;
            }
        }

        $dsp_obj->set_lst($lst_dsp);
        $dsp_obj->set_lst_dirty();

        return $dsp_obj;
    }

    /*
     * information functions
     */

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

    /*
     * modification functions
     */

    /**
     * @returns phrase_list_api with the phrases of this list and the new list
     */
    function intersect(phrase_list_api $new_lst): phrase_list_api
    {
        if (!$new_lst->is_empty()) {
            if ($this->is_empty()) {
                $this->set_lst($new_lst->lst);
            } else {
                // next line would work if array_intersect could handle objects
                // $this->lst = array_intersect($this->lst, $new_lst->lst());
                $found_lst = new phrase_list_api();
                foreach ($new_lst->lst() as $phr) {
                    if (in_array($phr->id, $this->id_lst())) {
                        $found_lst->add($phr);
                    }
                }
                $this->set_lst($found_lst->lst);
            }
        }
        return $this;
    }

    function remove(phrase_list_api $del_lst): phrase_list_api
    {
        if (!$del_lst->is_empty()) {
            // next line would work if array_intersect could handle objects
            // $this->lst = array_intersect($this->lst, $new_lst->lst());
            $remain_lst = new phrase_list_api();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id, $del_lst->id_lst())) {
                    $remain_lst->add($phr);
                }
            }
            $this->set_lst($remain_lst->lst);
        }
        return $this;
    }

}
