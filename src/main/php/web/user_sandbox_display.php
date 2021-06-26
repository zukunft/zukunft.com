<?php

/*

  user_sandbox_display.php - extends the user sandbox superclass for common display functions
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

class user_sandbox_display extends user_sandbox {

  // create the HTML code to display the protection setting (but only if allowed)
  function dsp_share($form_name, $back) {
    log_debug($this->obj_name.'->dsp_share '.$this->dsp_id());
    $result = ''; // reset the html code var

    // only the owner can change the share type (TODO or an admin)
    if ($this->usr->id == $this->owner_id) {
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = $form_name;
      $sel->name       = "share";  
      $sel->sql        = sql_lst ("share_type");
      $sel->selected   = $this->share_id;
      $sel->dummy_text = 'please define the share level';
      $result .= 'share type '.$sel->display ().' ';
    }
    
    log_debug($this->obj_name.'->dsp_share '.$this->dsp_id().' -> done');
    return $result;    
  }
  
  // create the HTML code to display the protection setting (but only if allowed)
  function dsp_protection($form_name, $back) {
    log_debug($this->obj_name.'->dsp_protection '.$this->dsp_id());
    $result = ''; // reset the html code var

    // only the owner can change the protection level (TODO or an admin)
    if ($this->usr->id == $this->owner_id) {
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = $form_name;
      $sel->name       = "protection";  
      $sel->sql        = sql_lst ("protection_type");
      $sel->selected   = $this->protection_id;
      log_debug($this->obj_name.'->dsp_protection '.$this->dsp_id().' id '.$this->protection_id);
      $sel->dummy_text = 'please define the protection level';
      $result .= 'protection '.$sel->display ().' ';
    }
    
    log_debug($this->obj_name.'->dsp_protection '.$this->dsp_id().' -> done');
    return $result;    
  }
  
}

?>
