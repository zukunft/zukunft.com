<?php

/*

  figure_lst.php - a list of figures, so either a value of a formula result object
  --------------
  
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

class figure_list
{

    public ?array $lst = null;         // the list of figures
    public ?int $usr_id = null;        // the id of the user for whom the list has been created
    public ?word $time_phr = null;     // the time word object, if the figure value time is adjusted by a special formula
    public ?bool $fig_missing = false; // true if at least one of the formula values is not set which means is NULL (but zero is a value)

    /*
    display functions
    */

    // display the unique id fields
    function dsp_id(): string
    {
        $id = $this->ids_txt();
        $name = $this->display('');
        if ($name <> '""') {
            $result = '' . $name . ' (' . $id . ')';
        } else {
            $result = '' . $id . '';
        }
        /*
        if (isset($this->usr)) {
          $result .= ' for user '.$this->usr->name;
        }
        */

        return $result;
    }

    function name(): string
    {
        $result = '';

        foreach ($this->lst as $fig) {
            $result .= $fig->name() . ' ';
        }

        return $result;
    }

    // return a list of the figure list ids as an sql compatible text
    function ids_txt(): string
    {
        return dsp_array($this->ids());
    }

    // this function is called from dsp_id, so no other call is allowed
    function ids(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $fig) {
                // use only valid ids
                if ($fig->id <> 0) {
                    $result[] = $fig->id;
                }
            }
        }
        return $result;
    }

    // return the html code to display a value
    // this is the opposite of the convert function
    // this function is called from dsp_id, so no other call is allowed
    function display($back): string
    {
        $result = '';

        foreach ($this->lst as $fig) {
            $result .= $fig->display($back) . ' ';
        }

        return $result;
    }

}