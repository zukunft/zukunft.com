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

class figure
{
    /*
     * types
     */

    // the main types of a figure objects
    const TYPE_VALUE = 'value';   // a number set by a user or imported
    const TYPE_RESULT = 'result'; // a calculated number based on other values

    /*
     * object vars
     */

    public ?int $id = null;               // the database id of the value or formula result
    public user $usr;                     // the person who wants to see the figure (value or formula result)
    public string $type;                  // either "value" or "result"
    public ?float $number = null;         // the numeric value
    public ?string $symbol = null;        // the reference text that has lead to the value
    public ?DateTime $last_update;    // the time of the last update of fields that may influence the calculated results
    public ?word $time_wrd = null;        // the time word object, if the figure value time is adjusted by a special formula
    public ?object $obj = null;           // the value or formula result object

    /*
     * construct and map
     */

    /**
     * set the figure default value and the user
     * @param user $usr the user who requested to see this value or formula result
     */
    function __construct(user $usr)
    {
        $this->usr = $usr;
        $this->type = self::TYPE_RESULT;
        $this->last_update = new DateTime();
    }

    /**
     * @return bool true if the user has done no overwrites either of the value direct
     * or the formula or the formula assignment
     */
    function is_std(): bool
    {
        if ($this->type == self::TYPE_RESULT) {
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
     * display functions
     */

    /**
     * display the unique id fields of a figure mainly for debugging
     */
    function dsp_id(): string
    {

        $result = $this->type;
        $result .= ' ' . $this->number;
        $result .= ' ' . $this->symbol;
        $result .= ' ' . $this->last_update->format('Y-m-d H:i:s');
        if (isset($this->obj)) {
            $result .= $this->obj->dsp_id();
        }
        if (isset($this->time_wrd)) {
            $result .= $this->time_wrd->dsp_id();
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
        if (isset($this->time_wrd)) {
            $result .= $this->time_wrd->name_dsp();
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

        if ($this->type == 'value') {
            if ($this->obj != null) {
                $result .= $this->obj->dsp_obj()->display($back);
            }
        } elseif ($this->type == 'result') {
            $result .= $this->obj->display($back);
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

        log_debug('type ' . $this->type);
        if ($this->type == 'value') {
            log_debug('value ' . $this->number);
            $val_dsp = $this->obj->dsp_obj();
            $result .= $val_dsp->display_linked($back);
        } elseif ($this->type == 'result') {
            log_debug('result ' . $this->number);
            $result .= $this->obj->display_linked($back);
        }

        return $result;
    }

}