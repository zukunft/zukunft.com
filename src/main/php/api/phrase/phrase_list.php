<?php

/*

    api/phrase/phrase_list.php - a list object of minimal/api phrase objects
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

namespace api;

include_once API_SANDBOX_PATH . 'list.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';

use html\phrase\phrase_list as phrase_list_dsp;
use JsonSerializable;

class phrase_list_api extends list_api implements JsonSerializable
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
     * cast
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
     * interface
     */

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the value vars including the protected vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst as $phr) {
            $vars[] = $phr->jsonSerialize();
        }
        return $vars;
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
     * modification functions
     */

    function remove(phrase_list_api $del_lst): phrase_list_api
    {
        if (!$del_lst->is_empty()) {
            // next line would work if array_intersect could handle objects
            // $this->lst = array_intersect($this->lst, $new_lst->lst());
            $remain_lst = new phrase_list_api();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id(), $del_lst->id_lst())) {
                    $remain_lst->add($phr);
                }
            }
            $this->set_lst($remain_lst->lst);
        }
        return $this;
    }

}
