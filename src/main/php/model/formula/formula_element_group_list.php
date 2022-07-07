<?php

/*

  formula_element_group_list.php - simply a list of formula element groups to place the name function
  -----------------------------
  
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

class formula_element_group_list
{

    public ?array $lst = null; // the list of formula element groups
    public ?user $usr = null;  // the person who has requested the formula element groups

    /*
    display functions
    */

    // return best possible identification for this element group list mainly used for debugging
    function dsp_id(): string
    {
        $result = dsp_array($this->ids());
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    // to show the element group list name to the user in the most simple form (without any ids)
    function name(): string
    {
        $lst = array();
        foreach ($this->lst as $elm_grp) {
            if (isset($elm_grp)) {
                $lst[] = $elm_grp->name();
            }
        }
        return implode(" / ", $lst);
    }

    // this function is called from dsp_id, so no other call is allowed
    function ids(): ?array
    {
        $result = null;
        if (isset($this->lst)) {
            foreach ($this->lst as $elm_grp) {
                // use only valid ids
                if ($elm_grp->lst != null) {
                    $result[] = $elm_grp->dsp_id();
                }
            }
        }
        return $result;
    }

}