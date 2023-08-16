<?php

/*

    web/types/type_list.php - base object for preloaded types used in the html frontend
    -----------------------

    this base object is without set_from_json function,
    because the setting is done once for all type objects with the parent object


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\types;

include_once WEB_TYPES_PATH . 'type_object.php';
include_once WEB_TYPES_PATH . 'protection.php';

use api\api;
use controller\controller;
use html\html_selector;
use html\types\type_object as type_object_dsp;
use html\view\view_list as view_list_dsp;

class type_list
{

    // the protected main var without id list because this is only loaded once
    protected array $lst = array();


    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        foreach ($json_array as $value) {
            if (array_key_exists(api::FLD_DESCRIPTION, $value)) {
                $typ = new type_object_dsp(
                    $value[api::FLD_ID],
                    $value[api::FLD_CODE_ID],
                    $value[api::FLD_NAME],
                    $value[api::FLD_DESCRIPTION]
                );
            } else {
                $typ = new type_object_dsp(
                    $value[api::FLD_ID],
                    $value[api::FLD_CODE_ID],
                    $value[api::FLD_NAME]
                );
            }
            $this->add_obj($typ);
        }
    }

    /**
     * @returns array with the names on the db keys
     */
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst as $typ) {
            $result[$typ->id()] = $typ->name();
        }
        return $result;
    }


    /*
     * modify functions
     */

    /**
     * add a phrase or ... to the list
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj): bool
    {
        $result = false;
        if (!in_array($obj->id(), $this->id_lst())) {
            $this->lst[] = $obj;
            $result = true;
        }
        return $result;
    }

    /**
     * @returns array with all unique ids of this list
     */
    protected function id_lst(): array
    {
        $result = array();
        foreach ($this->lst as $val) {
            if (!in_array($val->id(), $result)) {
                $result[] = $val->id();
            }
        }
        return $result;
    }


    /*
     * display
     */

    /**
     * @returns string the html code to select a type from this list
     */
    function type_selector(array $key_lst, string $name = '', string $form = '', int $selected = 0): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
        $sel->lst = $key_lst;
        $sel->selected = $selected;
        return $sel->display();
    }

}