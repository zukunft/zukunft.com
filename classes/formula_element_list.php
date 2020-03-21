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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class formula_element_list {

  public $lst = array(); // the list of formula elements
  public $usr = NULL;    // the person who has requested the formula elements
  
  /*
  
  display functions
  
  */
  
  // return best possible identification for this element list mainly used for debugging
  function dsp_id ($debug) {
    $id = implode(",",$this->ids($debug-1));
    $name = $this->name($debug-1);
    if ($name <> '""') {
      $result = ''.$name.' ('.$id.')';
    } else {
      $result = ''.$id.'';
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }

    return $result;    
  }
  
  function ids ($debug) {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $elm) {
        // use only valid ids
        if ($elm->id <> 0) {
          $result[] = $elm->id;
        }      
      }      
    }      
    return $result;
  }  

  // to show the element name to the user in the most simple form (without any ids)
  function name ($debug) {
    $result = '';
    if (isset($this->lst)) {
      foreach ($this->lst AS $elm) {
        $result .= $elm->name($debug-1).' ';
      }
    }
    return $result;    
  }
  
}

?>
