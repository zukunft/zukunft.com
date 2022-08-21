<?php

/*

    user_type_List_min.php - the api object to transfer a list of user setings to the frontend that changes very rarely
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

use html\user_type_list_dsp;

class user_type_list_api extends list_api
{

    // memory vs speed optimize vars
    private array $code_id_lst;
    private bool $code_lst_dirty;

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /*
     * get and set overwrite
     */

    /**
     * @returns true if the list has been replaced
     */
    public function set_lst(array $lst): bool
    {
        if ($this->lst_has_api_items()) {
            parent::set_lst($lst);
        } else {
            $result = array();
            foreach ($lst as $key => $obj) {
                $id = $key;
                if (property_exists($obj, "id")) {
                    $id = $obj->id;
                }
                $api_obj = new user_type_api(
                    $id,
                    $obj->code_id(),
                    $obj->name(),
                    $obj->comment()
                );
                $result[$id] = $api_obj;
            }
            $this->lst = $result;
        }
        $this->set_lst_dirty();
        return true;
    }

    /**
     * @returns true if the list has been replaced
     */
    protected function set_lst_dirty(): bool
    {
        parent::set_lst_dirty();
        $this->code_lst_dirty = true;
        return true;
    }

    /**
     * add a value to the list
     * @returns bool true if the value has been added
     */
    function add(user_type_api $type): bool
    {
        $result = false;
        if ($type->id() == 0) {
            if (!in_array($type->code_id(), $this->code_id_lst())) {
                $this->lst[] = $type;
                $this->set_lst_dirty();
                $result = true;
            }
        } else {
            if (!in_array($type->id(), $this->id_lst())) {
                $this->lst[$type->id()] = $type;
                $this->set_lst_dirty();
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns user_type_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): user_type_list_dsp
    {
        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $val) {
            if ($val != null) {
                $val_dsp = $val->dsp_obj();
                $lst_dsp[] = $val_dsp;
            }
        }

        return new user_type_list_dsp($lst_dsp);
    }

    /**
     * @returns array with all unique code ids of this list
     */
    protected function code_id_lst(): array
    {
        $result = array();
        if ($this->code_lst_dirty) {
            foreach ($this->lst as $type) {
                if (!in_array($type->code_id(), $result)) {
                    $result[] = $type->code_id();
                }
            }
            $this->code_id_lst = $result;
            $this->code_lst_dirty = false;
        } else {
            $result = $this->code_id_lst;
        }
        return $result;
    }

    /**
     * @returns bool true if the list contains elements of type user_type_api (and not user_type)
     */
    private function lst_has_api_items(): bool
    {
        $result = false;
        if (count($this->lst) > 0) {
            if ($this->lst[0]::class == user_type_api::class) {
                $result = true;
            }
        }
        return $result;
    }

}
