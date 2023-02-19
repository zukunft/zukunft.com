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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class figure_list extends sandbox_list
{

    // array $lst is the list of figures
    public ?bool $fig_missing = false; // true if at least one of the formula values is not set which means is NULL (but zero is a value)

    function get_first_id(): int
    {
        $result = 0;
        if ($this != null) {
            if (count($this->lst) > 0) {
                $fig = $this->lst[0];
                if ($fig != null) {
                    $result = $fig->id();
                }
            }
        }
        return $result;
    }


    /*
     * modification function
     */

    /**
     * add one figure to the figure list, but only if it is not yet part of the figure list
     * @returns bool true the term has been added
     */
    function add(?figure $fig_to_add): bool
    {
        $result = false;
        // check parameters
        if ($fig_to_add != null) {
            log_debug($fig_to_add->dsp_id());
            if ($fig_to_add->id() <> 0 or $fig_to_add->name() != '') {
                $result = parent::add_obj($fig_to_add);
            }
        }
        return $result;
    }

    /*
     * display functions
     */

    /**
     * @return string to display the unique id fields
     */
    function dsp_id(): string
    {
        $id = $this->ids_txt();
        $name = $this->display();
        if ($name <> '""') {
            $result = $name . ' (' . $id . ')';
        } else {
            $result = $id;
        }
        /*
        if ($this->user()->is_set()) {
          $result .= ' for user '.$this->user()->name;
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

    /**
     * return a list of the figure list ids as sql compatible text
     */
    function ids_txt(): string
    {
        return dsp_array($this->ids());
    }

    /**
     * this function is called from dsp_id, so no other call is allowed
     */
    function ids(): array
    {
        $result = array();
        foreach ($this->lst as $fig) {
            // use only valid ids
            if ($fig->id() <> 0) {
                $result[] = $fig->id();
            }
        }
        return $result;
    }

    /**
     * return the html code to display a value
     * this is the opposite of the convert function
     * this function is called from dsp_id, so no other call is allowed
     */
    function display($back = ''): string
    {
        $result = '';

        foreach ($this->lst as $fig) {
            $result .= $fig->display($back) . ' ';
        }

        return $result;
    }

}