<?php

/*

  formula_element_list.php - simply a list of formula elements to place the name function
  ------------------------
  
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

class formula_element_list
{

    public ?array $lst = null; // the list of formula elements
    public ?user $usr = null;  // the person who has requested the formula elements

    /*
    display functions
    */

    // return best possible identification for this element list mainly used for debugging
    function dsp_id(): string
    {
        $id = dsp_array($this->ids());
        $name = $this->name();
        if ($name <> '""') {
            $result = '' . $name . ' (' . $id . ')';
        } else {
            $result = '' . $id . '';
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    // to show the element name to the user in the most simple form (without any ids)
    // this function is called from dsp_id, so no other call is allowed
    function name(): string
    {
        $result = '';
        if (isset($this->lst)) {
            foreach ($this->lst as $elm) {
                $result .= $elm->name() . ' ';
            }
        }
        return $result;
    }

    // this function is called from dsp_id, so no other call is allowed
    function ids(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $elm) {
                // use only valid ids
                if ($elm->id <> 0) {
                    $result[] = $elm->id;
                }
            }
        }
        return $result;
    }

}

class formula_element_type extends BasicEnum
{
    const WORD = 1;
    const VERB = 2;
    const FORMULA = 3;
    const TRIPLE = 4;

    protected static function get_description($value): string {
        $result = parent::get_description($value);

        switch ($value) {

            // system log
            case formula_element_type::WORD:
                $result = 'a reference to a simple word';
                break;
            case formula_element_type::VERB:
                $result = 'a reference to predicate';
                break;
            case formula_element_type::FORMULA:
                $result = 'a reference to another formula';
                break;
            case formula_element_type::TRIPLE:
                $result = 'a reference to word link';
                break;
        }

        return $result;
    }
}

