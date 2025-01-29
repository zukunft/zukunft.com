<?php

/*

    web/formula/figure.php - to create the html code to display a value or result
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\figure;

include_once HTML_PATH . 'html_base.php';
include_once HTML_PATH . 'rest_ctrl.php';
include_once SHARED_PATH . 'api.php';
include_once API_OBJECT_PATH . 'controller.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_PHRASE_PATH . 'phrase_group.php';
include_once WEB_RESULT_PATH . 'result.php';
include_once WEB_SANDBOX_PATH . 'combine_named.php';
include_once WEB_VALUE_PATH . 'value.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use html\phrase\phrase_list;
use html\rest_ctrl as api_dsp;
use html\sandbox\combine_named as combine_named_dsp;
use html\html_base;
use html\phrase\phrase_group as phrase_group_dsp;
use html\result\result;
use html\user\user_message;
use html\value\value;
use shared\json_fields;
use shared\library;

class figure extends combine_named_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of this figure html display object bases on the api message
     * @param array $json_array an api json message as a string
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if (array_key_exists(json_fields::OBJECT_CLASS, $json_array)) {
            if ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_RESULT) {
                $res_dsp = new result();
                $res_dsp->set_from_json_array($json_array);
                $this->set_obj($res_dsp);
            } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_VALUE) {
                $val = new value();
                $val->set_from_json_array($json_array);
                $this->set_obj($val);
            } else {
                $usr_msg->add_err('Json class ' . $json_array[json_fields::OBJECT_CLASS] . ' not expected for a figure');
            }
        } else {
            $usr_msg->add_err('Json class missing, but expected for a figure');
        }
        return $usr_msg;
    }

    /**
     * @return int the figure id based on the value or result id
     * must have the same logic as the database view and the frontend
     */
    function id(): int
    {
        if ($this->obj() == null) {
            return 0;
        } else {
            if ($this->is_result()) {
                return $this->obj_id() * -1;
            } else {
                return $this->obj_id();
            }
        }
    }

    /**
     * @return int|string|null the id of the value or result id (not unique!)
     * must have the same logic as the database view and the frontend
     */
    function obj_id(): int|string|null
    {
        if ($this->obj() == null) {
            return 0;
        } else {
            return $this->obj()->id();
        }
    }

    function grp(): phrase_group_dsp
    {
        return $this->obj()->grp();
    }

    function number(): float
    {
        return $this->obj()->number();
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string ) to enable combinations of api_message() calls
     */
    function api_array(): array
    {
        $lib = new library();
        $vars = array();
        if ($this->is_result()) {
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_RESULT;
        } else {
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_VALUE;
        }
        $vars[json_fields::ID] = $this->obj_id();
        $vars[json_fields::NUMBER] = $this->number();
        $vars[json_fields::PHRASES] = $this->obj->grp()->api_array();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this figure has been calculated based on other numbers
     *              false if this figure has been defined by a user
     */
    function is_result(): bool
    {
        if ($this->obj() == null) {
            return false;
        } else {
            if ($this->obj()::class == result::class) {
                return true;
            } else {
                return false;
            }
        }
    }


    /*
     * display
     */

    function val_formatted(): string
    {
        return $this->obj()->val_formatted();
    }

    /**
     * @param phrase_list|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list $phr_lst_header = null): string
    {
        return $this->grp()->display_linked($phr_lst_header);
    }


    /**
     * return the html code to display a value
     * this is the opposite of the convert function
     */
    function display(): string
    {
        return round($this->number(), 2);
    }

    /**
     * html code to show the value with the possibility to click for the result explanation
     */
    function display_linked(string $back = ''): string
    {
        // TODO check if $result .= $this->obj->display_linked($back) can be used
        $html = new html_base();
        if ($this->is_result()) {
            $url = $html->url(api_dsp::VALUE_EDIT, $this->obj_id(), $back);
        } else {
            $url = $html->url(api_dsp::RESULT_EDIT, $this->obj_id(), $back);
        }
        return $html->ref($url, $this->val_formatted());
    }

}
