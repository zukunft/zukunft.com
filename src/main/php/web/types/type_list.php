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
use cfg\library;
use controller\controller;
use html\html_selector;
use html\types\type_object as type_object_dsp;
use html\view\view_list as view_list_dsp;

class type_list
{

    // error return codes
    const CODE_ID_NOT_FOUND = -1;

    // the protected main var without id list because this is only loaded once
    protected array $lst = array();
    private array $hash = []; // hash list with the code id for fast selection


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

    /**
     * return the database row id based on the code_id
     *
     * @param string $code_id
     * @return int the database id for the given code_id
     */
    function id(string $code_id): int
    {
        $lib = new library();
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->hash)) {
                $result = $this->hash[$code_id];
            } else {
                $result = self::CODE_ID_NOT_FOUND;
                log_debug('Type id not found for "' . $code_id . '" in ' . $lib->dsp_array_keys($this->hash));
            }
        } else {
            log_debug('Type code id not not set');
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
            $this->hash[$obj->code_id] = $obj->id();
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
     * @param array $key_lst the key value list for the selector
     * @param string $name the unique name inside the form for this selector
     * @param string $form_name the name of the html form
     * @param int $selected the id of the preselected phrase
     * @param string $col_class the formatting code to adjust the formatting
     * @param string $label the text show to the user
     * @returns string the html code to select a type from this list
     */
    function type_selector(
        array $key_lst,
        string $name = '',
        string $form_name = '',
        int $selected = 0,
        string $col_class = '',
        string $label = ''
    ): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form_name;
        $sel->label = $label;
        $sel->lst = $key_lst;
        $sel->selected = $selected;
        $sel->bs_class = $col_class;
        return $sel->display();
    }


    /*
     * internal
     */

    /**
     * recreate the hash table to get the database id for a code_id
     */
    private function set_hash(): void
    {
        $this->hash = [];
        if ($this->lst != null) {
            foreach ($this->lst as $key => $type) {
                $this->hash[$type->code_id] = $key;
            }
        }
    }

}