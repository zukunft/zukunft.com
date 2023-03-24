<?php

/*

    figure.php - either a value of a formula result object or a value if a user has overwritten a formula result
    ----------

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

namespace model;

include_once MODEL_SANDBOX_PATH . 'combine_object.php';
include_once API_FORMULA_PATH . 'figure.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_FORMULA_PATH . 'formula_value.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_USER_PATH . 'user.php';

use api\figure_api;
use html\figure_dsp;
use value;
use formula_value;
use formula;
use user;
use DateTime;

class figure extends combine_object
{

    /*
     * construct and map
     */

    /**
     * a figure is either created based on a user value or formula result
     * @param value|formula_value $obj
     */
    function __construct(value|formula_value $obj)
    {
        $this->set_obj($obj);
    }


    /*
     * set and get
     */

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

    /**
     * @return user the person who wants to see a word, verb, triple, formula or view
     */
    function user(): user
    {
        return $this->obj()->user();
    }

    /**
     * @return float with the value either from the formula result or the db value from a user or source
     */
    function number(): float
    {
        return $this->obj()->number();
    }

    /**
     * set by the formula element that has be used to get this figure
     * @param string $symbol the reference text either from the formula result or the db value from a user or source
     */
    function set_symbol(string $symbol): void
    {
        $this->obj()->set_symbol($symbol);
    }

    /**
     * @return string the reference text either from the formula result or the db value from a user or source
     */
    function symbol(): string
    {
        return $this->obj()->symbol();
    }

    /**
     * @return DateTime the timestamp of the last update either from the formula result or the db value from a user or source
     */
    function last_update(): DateTime
    {
        return $this->obj()->last_update();
    }

    /**
     * @return bool true if the user has done no overwrites either of the value direct
     * or the formula or the formula assignment
     */
    function is_std(): bool
    {
        if ($this->is_result()) {
            if ($this->obj == null) {
                return false;
            } else {
                if (get_class($this->obj) == formula::class) {
                    return $this->obj->is_std();
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /*
     * cast
     */

    /**
     * @returns figure_api the cast object for the api
     */
    function api_obj(): figure_api
    {
        return new figure_api($this->obj->api_obj());
    }

    /**
     * @returns figure_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): figure_dsp
    {
        $fig_dsp = new figure_dsp();
        $json_msg = json_encode($this->api_obj());
        $fig_dsp->set_from_json($json_msg);
        return $fig_dsp;
    }


    /*
     * classification
     */

    /**
     * @return bool true if the value has been calculated and not set by a user
     */
    function is_result(): bool
    {
        if ($this->obj()::class == formula_value::class) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * display
     */

    /**
     * display the unique id fields of a figure mainly for debugging
     */
    function dsp_id(): string
    {

        $result = '';
        if ($this->is_result()) {
            $result .= 'result';
        } else {
            $result .= 'value';
        }
        $result .= ' ' . $this->number();
        $result .= ' ' . $this->symbol();
        $result .= ' ' . $this->last_update()->format('Y-m-d H:i:s');
        if (isset($this->obj)) {
            $result .= $this->obj->dsp_id();
        }

        return $result;
    }

    /**
     * @return string the created name of a figure
     */
    function name(): string
    {

        $result = ' ' . $this->number();
        $result .= ' ' . $this->symbol();
        if (isset($this->obj)) {
            $result .= $this->obj->name();
        }

        return $result;
    }

    /**
     * return the html code to display a value
     * this is the opposite of the convert function
     */
    function display(string $back = ''): string
    {
        log_debug();
        $result = '';

        if ($this->is_result()) {
            $result .= $this->obj->display($back);
        } else {
            if ($this->obj != null) {
                $result .= $this->obj->dsp_obj()->display($back);
            }
        }

        return $result;
    }

    /**
     * html code to show the value with the possibility to click for the result explanation
     */
    function display_linked(string $back = ''): string
    {
        log_debug('figure->display_linked');
        $result = '';

        if ($this->is_result()) {
            $result .= $this->obj->display_linked($back);
        } else {
            $val_dsp = $this->obj->dsp_obj();
            $result .= $val_dsp->display_linked($back);
        }

        return $result;
    }

}