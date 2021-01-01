<?php

/*

  display_selector.php - to select a word (or formula or verb)
  --------------------
  
  this should be as easy as possible that's why it got its own class
  
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

class selector {

  // the parameters
  public $usr        = Null; // if 0 (not NULL) for standard values, otherwise for a user specific values
  public $name       = '';   // the HTML form field name
  public $form       = '';   // the name of the HTML form
  public $label      = '';   // the label of the HTML form
  public $bs_class   = '';   // to add addition class information for the bootstrap version
  public $attribute  = '';   // to add addition attribute information for the bootstrap version e.g. display an disabled selector
  public $sql        = '';   // query to select the items
  public $lst        = Null; // list of objects from which the user can select
  public $selected   = Null; // id of the selected object
  public $dummy_text = '';   // text for the NULL result if allowed
  
  function display ($debug) {
    zu_debug('selector->display ('.$this->name.','.$this->form.','.$this->sql.',s'.$this->selected.','.$this->dummy_text.')', $debug-10);
    $result  = '';

    $result .= dsp_form_fld_select ($this->form, $this->name, $this->label, $this->bs_class, $this->attribute);
    
    if ($this->dummy_text == '') {
      $this->dummy_text == 'please select ...';
    }  
    
    if ($this->selected == 0) {
      $result .= '<option value="0" selected>'.$this->dummy_text.'</option>';
    }

    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_lst = $db_con->get($this->sql, $debug-5);  
    foreach ($db_lst AS $db_entry) {
      $row_option = '';
      if ($db_entry['id'] == $this->selected AND $this->selected <> 0) {
        zu_debug('selector->display ... selected '.$db_entry['id'], $debug-12);
        $row_option = ' selected';
      }
      $result .= '<option value="'.$db_entry['id'].'"'.$row_option.'>'.$db_entry['name'].'</option>';
    }

    $result .= dsp_form_fld_select_end();

    zu_debug('selector->display ... done', $debug-14);
    return $result;
  }

}


?>
