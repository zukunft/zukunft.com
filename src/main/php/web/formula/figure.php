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

namespace html;

include_once API_SANDBOX_PATH . 'combine_object.php';
include_once API_FORMULA_PATH . 'figure.php';
include_once API_PHRASE_PATH . 'phrase_list.php';
include_once API_PATH . 'controller.php';
include_once WEB_VALUE_PATH . 'value.php';

use api\combine_object_api;
use api\figure_api;
use api\phrase_list_api;
use api\sandbox_value_api;
use controller\controller;

class figure_dsp
{

    /*
     * object vars
     */

    private value_dsp|formula_value_dsp|null $obj;


    /*
     * construct and map
     */

    function __construct()
    {
        $this->set_obj(null);
    }


    /*
     * set and get
     */

    function set_from_json(string $json_api_msg): void
    {
        $json_array = json_decode($json_api_msg, true);
        if (array_key_exists(combine_object_api::FLD_CLASS, $json_array)) {
            if ($json_array[combine_object_api::FLD_CLASS] == figure_api::CLASS_RESULT) {
                $fv_dsp = new formula_value_dsp();
                $fv_dsp->set_from_json_array($json_array);
                $this->set_obj($fv_dsp);
            } elseif ($json_array[combine_object_api::FLD_CLASS] == figure_api::CLASS_VALUE) {
                $val = new value_dsp();
                $val->set_from_json_array($json_array);
                $this->set_obj($val);
            } else {
                log_err('Json class ' . $json_array[combine_object_api::FLD_CLASS] . ' not expected for a figure');
            }
        } else {
            log_err('Json class missing, but expected for a figure');
        }
    }

    function set_obj(value_dsp|formula_value_dsp|null $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): value_dsp|formula_value_dsp|null
    {
        return $this->obj;
    }

    /**
     * @return int the figure id based on the value or result id
     * must have the same logic as the database view and the frontend
     */
    function id(): int
    {
        if ($this->is_result()) {
            return $this->obj_id() * -1;
        } else {
            return $this->obj_id();
        }
    }

    /**
     * @return int the id of the value or result id (not unique!)
     * must have the same logic as the database view and the frontend
     */
    function obj_id(): int
    {
        return $this->obj()->id();
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
        $vars = array();
        if ($this->is_result()) {
            $vars[combine_object_api::FLD_CLASS] = figure_api::CLASS_RESULT;
        } else {
            $vars[combine_object_api::FLD_CLASS] = figure_api::CLASS_VALUE;
        }
        $vars[controller::API_FLD_ID] = $this->id();
        $vars[sandbox_value_api::FLD_NUMBER] = $this->number();
        $vars[controller::API_FLD_PHRASES] = $this->obj->grp()->api_array();
        return $vars;
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
        if ($this->obj()::class == formula_value_dsp::class) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * display
     */

    function val_formatted(): float
    {
        return $this->obj()->val_formatted();
    }

    /**
     * @param phrase_list_api|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list_api $phr_lst_header = null): string
    {
        return $this->grp()->name_linked($phr_lst_header);
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
            $url = $html->url(api::VALUE_EDIT, $this->obj_id(), $back);
        } else {
            $url = $html->url(api::RESULT_EDIT, $this->obj_id(), $back);
        }
        return $html->ref($url, $this->val_formatted());
    }

}
