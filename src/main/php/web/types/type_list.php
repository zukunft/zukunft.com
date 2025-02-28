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

include_once SHARED_PATH . 'api.php';
include_once WEB_TYPES_PATH . 'protection.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_TYPES_PATH . 'type_object.php';
include_once WEB_USER_PATH . 'user_message.php';
//include_once WEB_VERB_PATH . 'verb.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use html\user\user_message;
use html\html_selector;
use html\types\type_object as type_object_dsp;
use html\verb\verb;
use shared\json_fields;
use shared\library;
use shared\types\view_styles;

class type_list
{

    // error return codes
    const CODE_ID_NOT_FOUND = -1;

    // the protected main var without id list because this is only loaded once
    private array $lst = [];
    private array $hash = []; // hash list with the code id for fast selection


    function reset(): void
    {
        $this->lst = [];
        $this->hash = [];
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array, string $class = ''): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_array as $value) {
            if ($class == verb::class) {
                $vrb = new verb();
                $vrb->api_mapper($value);
                $this->add_obj($vrb);
            } else {
                if (!array_key_exists(json_fields::CODE_ID, $value)) {
                    $usr_msg->add_err('code id is missing for ' . implode(',', $value));
                }
                if (array_key_exists(json_fields::DESCRIPTION, $value)) {
                    $typ = new type_object_dsp(
                        $value[json_fields::ID],
                        $value[json_fields::CODE_ID],
                        $value[json_fields::NAME],
                        $value[json_fields::DESCRIPTION]
                    );
                } else {
                    $typ = new type_object_dsp(
                        $value[json_fields::ID],
                        $value[json_fields::CODE_ID],
                        $value[json_fields::NAME]
                    );
                }
                $this->add_obj($typ);
            }
        }
        return $usr_msg;
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
     * @returns array the protected list of values or formula results
     */
    function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns array with the names on the db keys
     */
    function db_id_list(): array
    {
        $result = array();
        foreach ($this->lst as $obj) {
            $result[$obj->id()] = $obj->name();
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

    function code_id(int $id): string
    {
        $result = '';
        $type = $this->get($id);
        if ($type != null) {
            $result = $type->code_id;
        } else {
            log_warning('Type code id not found for ' . $id . ' in ' . $this->dsp_id());
        }
        return $result;
    }

    function name(int $id): string
    {
        $result = '';
        $type = $this->get($id);
        if ($type != null) {
            $result = $type->name;
        }
        return $result;
    }

    /**
     * pick a type from the preloaded object list
     * @param int $id the database id of the expected type
     * @return verb|type_object|null the type object
     */
    function get(int $id): verb|type_object|null
    {
        $result = null;
        if ($id > 0) {
            if (in_array($id, $this->hash)) {
                $key = array_search($id, $this->hash);
                $lst_key = array_search($key, array_keys($this->hash));
                $result = $this->lst[$lst_key];
            } else {
                log_warning('Type with is ' . $id . ' not found in ' . $this->dsp_id());
            }
        } else {
            log_debug('Type id not set');
        }
        return $result;
    }

    /**
     * get the type object by code id (just to shorten the code)
     * @param string $code_id
     * @return verb|type_object|null
     */
    function get_by_code_id(string $code_id): verb|type_object|null
    {
        return $this->get($this->id($code_id));
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
     * create the HTML code to select a type
     * @param array $key_lst the key value list for the selector
     * @param string $name the unique name inside the form for this selector
     * @param string $form the unique name of the html form
     * @param int $selected the id of the preselected phrase
     * @param string $col_class the formatting code to adjust the formatting
     * @param string $label the text show to the user
     * @returns string the html code to select a type from this list
     */
    function type_selector(
        array  $key_lst,
        string $name = '',
        string $form = '',
        int    $selected = 0,
        string $col_class = view_styles::COL_SM_4,
        string $label = 'type: '
    ): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
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

    /*
     * debug
     */
    function dsp_id(): string
    {
        return '';
    }

}