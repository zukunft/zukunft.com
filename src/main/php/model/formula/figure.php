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

include_once API_FORMULA_PATH . 'figure.php';

use api\figure_api;
use html\figure_dsp;
use DateTime;
use db_object;
use formula;
use user;

class figure extends db_object
{

    /*
     * object vars
     */

    public user $usr;                 // the person who wants to see the figure (value or formula result)
    private bool $is_result;          // true if the value has been calculated and not set by a user
    public ?float $number = null;     // the numeric value
    public ?string $symbol = null;    // the reference text that has lead to the value
    public ?DateTime $last_update;    // the time of the last update of fields that may influence the calculated results
    public ?object $obj = null;       // the value or formula result object

    /*
     * construct and map
     */

    /**
     * set the figure default value and the user
     * @param user $usr the user who requested to see this value or formula result
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->usr = $usr;
        $this->set_type_result();
        $this->last_update = new DateTime();
    }


    /*
     * set and get
     */

    /**
     * set the user of the user sandbox object
     *
     * @param user $usr the person who wants to access the object e.g. the word
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * define that this figure has been calculated based on other numbers
     * @return void
     */
    function set_type_result(): void
    {
        $this->is_result = true;
    }

    /**
     * set the number of the value or result
     *
     * @param float|null $number the person who wants to access the object e.g. the word
     * @return void
     */
    function set_number(?float $number): void
    {
        $this->number = $number;
    }

    /**
     * define that this figure has been defined by a user
     * @return void
     */
    function set_type_value(): void
    {
        $this->is_result = false;
    }

    /**
     * @return user the person who wants to see a word, verb, triple, formula or view
     */
    function user(): user
    {
        return $this->usr;
    }

    /**
     * @return float the value either from the formula result or the db value from a user or source
     */
    function number(): float
    {
        return $this->number;
    }

    function obj(): object
    {
        return $this->obj;
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
                if (get_class($this->obj) == formula::class or get_class($this->obj) == formula_dsp_old::class) {
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

    function is_result(): bool
    {
        if ($this->is_result) {
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
        $result .= ' ' . $this->number;
        $result .= ' ' . $this->symbol;
        $result .= ' ' . $this->last_update->format('Y-m-d H:i:s');
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

        $result = ' ' . $this->number;
        $result .= ' ' . $this->symbol;
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