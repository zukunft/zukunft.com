<?php

/*

    web/sandbox/list.php - the superclass for html list objects
    --------------------

    e.g. used to display phrase, term and figure lists

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

namespace html\sandbox;

use html\rest_ctrl as api_dsp;
use html\html_selector;
use html\user\user_message;
use shared\api;

class list_dsp
{

    // the protected main var
    protected array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;


    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->lst = array();

        $this->id_lst = array();
        $this->lst_dirty = false;

        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_list_from_json(array $json_array, db_object|combine_object $dbo): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_array as $value) {
            $new = clone $dbo;
            $msg = $new->set_from_json_array($value);
            $usr_msg->add($msg);
            $this->add_obj($new, true);
        }
        return $usr_msg;
    }

    /**
     * @returns true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->lst = $lst;
        $this->set_lst_dirty();
        return true;
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
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst as $val) {
            $result[$val->id()] = $val->name();
        }
        return $result;
    }

    /**
     * @returns true if the list has been replaced
     */
    function set_lst_dirty(): bool
    {
        $this->lst_dirty = true;
        return true;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $result = array();
        foreach ($this->lst as $obj) {
            if ($obj != null) {
                $result[] = $obj->api_array();
            }
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * add the objects from the backend
     * @param string $pattern part of the name that should be used to select the objects
     * @return bool true if at least one object has been found
     */
    function load_like(string $pattern): bool
    {
        $result = false;

        $api = new api_dsp();
        $data = array();
        $data[api::URL_VAR_PATTERN] = $pattern;
        $json_body = $api->api_get($this::class, $data);
        $this->set_from_json_array($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * @returns int the number of objects of the protected list
     */
    function count(): int
    {
        return count($this->lst);
    }

    /**
     * @returns true if the list does not contain any object
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
     * modify functions
     */

    /**
     * add a phrase or ... to the list but only if it does not exist
     * @param object $obj the frontend object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj, bool $allow_duplicates = false): bool
    {
        $result = false;
        if (!in_array($obj->id(), $this->id_lst()) or $allow_duplicates) {
            $this->lst[] = $obj;
            $this->lst_dirty = true;
            $result = true;
        }
        return $result;
    }

    /**
     * add a phrase or ... to the list also if it is already part of the list
     */
    protected function add_always(object $obj): void
    {
        $this->lst[] = $obj;
        $this->lst_dirty = true;
    }

    /**
     * @returns array with all unique ids of this list
     */
    function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $val) {
                if (!in_array($val->id(), $result)) {
                    $result[] = $val->id();
                }
            }
            $this->id_lst = $result;
            $this->lst_dirty = false;
        } else {
            $result = $this->id_lst;
        }
        return $result;
    }


    /*
     * html - function that create html code
     */

    /**
     * create a selector for this list
     * used for words, triples, phrases, formulas, terms, view and components
     *
     * @param string $name the name of this selector which must be unique within the form
     * @param string $form the html form name which must be unique within the html page
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the unique database id of the object that has been selected
     * @returns string the html code to select a word from this list
     */
    function selector(
        string $name = '',
        string $form = '',
        string $label = '',
        string $col_class = '',
        int $selected = 0,
        string $type = html_selector::TYPE_SELECT
    ): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
        $sel->label = $label;
        $sel->bs_class = $col_class;
        $sel->type = $type;
        $sel->lst = $this->lst_key();
        $sel->selected = $selected;
        return $sel->display();
    }

}
