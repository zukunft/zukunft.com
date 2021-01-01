<?php

/*

  display_list.php - to display a list that can be sorted
  ----------------
  
  this should be as easy as possible that's why it got its own class
  e.g. the list of display components related to a display view
  
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

class dsp_list {

  // the parameters
  public $lst              = Null; // a array of objects that must have id and name
  public $id_field         = Null; // 
  public $script_name      = Null; // name of the code that handles the list
  public $script_parameter = Null; // 
  
  // converts a id field name to an edit php script name
  // assuming that ...
  private function id_to_edit($debug) {
    zu_debug("zu_id_to_edit(".$this->id_field.")", $debug-10);
    $result = zu_str_left_of($this->id_field, "_id")."_edit.php";
    // todo: cleanup
    if ($result == 'view_component_edit.php') { $result = 'view_component_edit.php'; }
    return $result;
  }

  // display a list that can be sorted using the fixed field "order_nbr"
  function display ($back, $debug) {
    $result  = '';
    
    // set the default values
    $row_nbr = 0;
    $num_rows = count($this->lst);
    foreach ($this->lst AS $entry) {
      if (UI_USE_BOOTSTRAP) { $result .= '<tr><td>'; }    
      
      // list of all possible view components
      $row_nbr = $row_nbr + 1;
      $edit_script = $this->id_to_edit($debug-1);
      $result .=  '<a href="/http/'.$edit_script.'?id='.$entry->id.'&back='.$this->script_parameter.'">'.$entry->name.'</a> ';
      if ($row_nbr > 1) {
        $result .= '<a href="/http/'.$this->script_name.'?id='.$this->script_parameter.'&move_up='.$entry->id.'">up</a>';
      }
      if ($row_nbr > 1 and $row_nbr < $num_rows) {
        $result .= '/';
      }
      if ($row_nbr < $num_rows) {
        $result .= '<a href="/http/'.$this->script_name.'?id='.$this->script_parameter.'&move_down='.$entry->id.'">down</a>';
      }
      if (UI_USE_BOOTSTRAP) { $result .= '</td><td>'; }    
      $result .= ' ';
      $result .= btn_del ('Delete '.zu_str_left_of($this->id_field, "_id"), $this->script_name.'?id='.$this->script_parameter.'&del='.$entry->id);
      if (UI_USE_BOOTSTRAP) { $result .= '</td></tr>'; }    
      $result .= '<br>';
    }

    return $result;
  }

}



?>
