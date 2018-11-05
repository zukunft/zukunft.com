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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class figure_list {

  public $lst         = array(); // the list of figures
  public $usr_id      = NULL;    // the id of the user for whom the list has been created
  public $time_phr    = NULL;    // the time word object, if the figure value time is adjusted by a special formula
  public $fig_missing = false;   // true if at least one of the formula values is not set which means is NULL (but zero is a value)
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id ($debug) {
    $id = $this->ids_txt($debug-1);
    $name = $this->display($debug-1);
    if ($name <> '""') {
      $result = ''.$name.' ('.$id.')';
    } else {
      $result = ''.$id.'';
    }
    /*
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    */

    return $result;    
  }
  
  // return a list of the figure list ids as an sql compatible text
  function ids_txt($debug) {
    $result = implode(',',$this->ids($debug-1));
    return $result; 
  }
  
  function ids ($debug) {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $fig) {
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
  function display ($back, $debug) {
    $result = '';
    
    foreach ($this->lst AS $fig) {
      $result .= $fig->display($back, $debug-1).' ';
    }
    
    return $result;    
  }
  
}

?>
