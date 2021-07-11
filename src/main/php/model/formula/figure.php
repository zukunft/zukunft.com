<?php

/*

  figure.php - either a value of a formula result object
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class figure
{

    public ?int $id = null;               // the database id of the value or formula result
    public ?user $usr = null;             // the person who wants to see the figure (value or formula result)
    public ?bool $is_std = True;          // true as long as no user specific value, formula or assignment is used for this result
    public string $type = '';             // either "value" or "result"
    public ?float $number = null;         // the numeric value
    public ?string $symbol = null;        // the reference text that has lead to the value
    public ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results
    public ?DateTime $time_wrd = null;    // the time word object, if the figure value time is adjusted by a special formula
    public ?object $obj = null;           // the value or formula result object

    /*
     display functions
     */

    // display the unique id fields
    function dsp_id(): string
    {

        $result = $this->type;
        $result .= ' ' . $this->number;
        $result .= ' ' . $this->symbol;
        $result .= ' ' . $this->last_update;
        if (isset($this->obj)) {
            $result .= $this->obj->dsp_id();
        }
        if (isset($this->time_wrd)) {
            $result .= $this->time_wrd->dsp_id();
        }

        return $result;
    }

    function name(): string
    {

        $result = ' ' . $this->number;
        $result .= ' ' . $this->symbol;
        if (isset($this->obj)) {
            $result .= $this->obj->name();
        }
        if (isset($this->time_wrd)) {
            $result .= $this->time_wrd->name();
        }

        return $result;
    }

    // return the html code to display a value
    // this is the opposite of the convert function
    function display($back): string
    {
        log_debug('figure->display');
        $result = '';

        if ($this->type == 'value') {
            $result .= $this->obj->display($back);
        } elseif ($this->type == 'result') {
            $result .= $this->obj->display($back);
        }

        return $result;
    }

    // html code to show the value with the possibility to click for the result explanation
    function display_linked($back): string
    {
        log_debug('figure->display_linked');
        $result = '';

        log_debug('figure->display_linked -> type ' . $this->type);
        if ($this->type == 'value') {
            log_debug('figure->display_linked -> value ' . $this->number);
            $result .= $this->obj->display_linked($back);
        } elseif ($this->type == 'result') {
            log_debug('figure->display_linked -> result ' . $this->number);
            $result .= $this->obj->display_linked($back);
        }

        return $result;
    }

}