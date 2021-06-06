<?php

/*

  display_button.php - create the html code to display a button to the user
  ------------------
  
  mainly used to have a common user interface
  
  
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

class button {

  // parameters for the simple buttons
  public $title = ''; // title to display on mouse over
  public $call  = ''; // url to call if the user clicks
  public $back  = ''; // word id, word name or url that should be called after the action is completed

  // the common HTML code to display an simple button
  private function html ($icon) {
    $result = '<a href="'.$this->call.'" title="'.$this->title.'"><img src="'.$icon.'" alt="'.$this->title.'"></a>';
    return $result;
  }

  // same as html but the bootstrap version
  private function html_fa ($icon) {
    $result = '<a href="'.$this->call.'" title="'.$this->title.'"><i class="far '.$icon.'"></i></a>';
    return $result;
  }

  // button function to keep the image call on one place
  function add      () { return $this->html_fa(ZUH_IMG_ADD_FA      ); } // an add button to create a new entry
  function edit     () { return $this->html_fa(ZUH_IMG_EDIT_FA     ); } // an edit button to adjust an entry
  function del      () { return $this->html_fa(ZUH_IMG_DEL_FA      ); } // an delete button to remove an entry
  function undo     () { return $this->html   (ZUH_IMG_UNDO        ); } // an undo button to undo an change (not only the last)
  function find     () { return $this->html   (ZUH_IMG_FIND        ); } // a find button to search for a word
  function unfilter () { return $this->html   (ZUH_IMG_UN_FILTER    ); } // button to remove a filter
  function back     () { return $this->html   (ZUH_IMG_BACK        ); } // button to go back to the original calling page

  // display a button to go back to the main calling page (several pages have been show to adjust the view of a word, go back to the word not to the view edit pages)
  // $back can be either the id of the last used word or the url path
  function go_back ($back) {
    if ($back == '') {
      $back = 1; // temp solution
    }
    $this->title = 'back';
    if (is_numeric($back)) {
      $this->call = '/http/view.php?words='.$back;
    } else {
      $this->call = $back;
    }
    $result = $this->back();
    return $result;
  }

  // ask a yes/no question with the default calls
  function confirm ($title, $description, $call) {
    $result = dsp_text_h3($title);
    $result .= $description.'<br><br>';
    $result .= '<a href="'.$call.'&confirm=1" title="Yes">Yes</a> / <a href="'.$call.'&confirm=-1" title="No">No</a>';
    //$result = $title.'<a href="'.$call.'&confirm=1" title="Yes">Yes</a>/<a href="'.$call.'&confirm=-1" title="No">No</a>';
    //$result = '<a href="'.$call.'" onclick="return confirm(\''.$title.'\')">'.$title.'</a>';
    //$result = "<a onClick=\"javascript: return confirm('".$title."');\" href='".$call."'>x</a>"; 
    return $result;
  }

  // the old zuh_btn_confirm without description, replace with zuh_btn_confirm
  function yesno () {
    //zu_debug("button->yesno ".$this->title.".", 10);
    
    $result = dsp_text_h3($this->title);
    $result .= '<a href="'.$this->call.'&confirm=1" title="Yes">Yes</a>/<a href="'.$this->call.'&confirm=-1" title="No">No</a>';
    //$result = $this->title.'<a href="'.$this->call.'&confirm=1" title="Yes">Yes</a>/<a href="'.$this->call.'&confirm=-1" title="No">No</a>';
    //$result = '<a href="'.$this->call.'" onclick="return confirm(\''.$this->title.'\')">'.$this->title.'</a>';
    //$result = "<a onClick=\"javascript: return confirm('".$this->title."');\" href='".$this->call."'>x</a>"; 
    //zu_debug("button->yesno ".$this->title." done.", 10);
    return $result;
  }

  // display a button to add a value
  function add_value ($phr_lst, $type_ids, $back, $debug) {
    log_debug("button->add_value", $debug-18);
    
    $url_phr = '';
    if (isset($phr_lst)) {
      if (get_class($phr_lst) <> 'phrase_list') {
        log_err("Object to add must be of type phrase_list, but it is ".get_class($phr_lst).".", "button->add_value", '', (new Exception)->getTraceAsString(), null);
      } else {
        if (!empty($phr_lst->ids)) {
          $this->title = "add new value similar to ".$phr_lst->name($debug);
        } else {
          $this->title = "add new value";
        }  
        $url_phr = $phr_lst->id_url_long($debug);
      }
    }  
    
    log_debug("button->add_value -> type URL", $debug-18);
    $url_type = '';
    if (isset($type_ids)) {
      $url_type = zu_ids_to_url($type_ids,"type", $debug-1);
    }  

    $this->call  = '/http/value_add.php?back='.$back.$url_phr.$url_type;
    $result = $this->add();
    
    log_debug("button->add_value -> (".$result.")", $debug-16);
    return $result;
  }

  // similar to btn_add_value, but uses a simple modal box
  function add_value_fast ($modal_nbr, $phr_lst, $phr_main, $common_lst, $back, $debug) {
    log_debug("button->add_value", $debug-18);
    $result = '';
    
    // group the modal box with the button
    $result .= '<div class="container">';
    
    // build the phrase list for the modal box header
    $phr_time = $phr_lst->time_lst($debug-1);
    $common_lst_ex_main = clone $common_lst;
    $common_lst_ex_main->del($phr_main, $debug-1);
    $phr_lst_header = clone $phr_lst;
    $phr_lst_header->diff($common_lst_ex_main, $debug-1);
    $phr_lst_header->diff($phr_time, $debug-1);
    

    // the button to call the modal box
    $result .= '  <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#val_add'.$modal_nbr.'">';
    $result .= '    ';
    $result .= '  </button>';
    // the modal box itself
    $form_name = '/http/value_add';
    $result .= '  <div class="modal" id="val_add'.$modal_nbr.'">';
    $result .= '    <div class="modal-dialog">';
    $result .= '      <div class="modal-content">';
    $result .= '        <div class="modal-header">';
    $result .= '          <h4 class="modal-title">';
    $result .= '            '.$phr_lst_header->name_dsp();
    $result .= '          </h4>';
    $result .= '          <button type="button" class="save" data-dismiss="modal">&times;</button>';
    $result .= '        </div>';
    $result .= '        <div class="modal-body">';
    $result .= dsp_form_start ($form_name);
    $result .= '            '.$phr_time->name_dsp();
    $result .= '            <input type="hidden" name="phrases" value="'.implode(",",$phr_lst->ids()).'">';
    $result .= '            <input type="hidden" name="back" value="'.$back.'">';
    $result .= '            <input type="hidden" name="confirm" value="1">';
    $result .= '            <input type="text" name="value" value="0">';
    $result .= '            '.$common_lst_ex_main->name_dsp();
    $result .= '          </form>';
    $result .= '        </div>';
    $result .= '        <div class="modal-footer">';
    //$result .= dsp_form_end ('', $back);
    $result .= '          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>';
    $result .= '          <button type="submit" class="btn btn-outline-success"   data-dismiss="modal">Save</button>';
    $result .= '        </div>';
    $result .= '      </div>';
    $result .= '    </div>';
    $result .= '  </div>';
    
    // close the modal group
    $result .= '</div>';
    
    log_debug("button->add_value -> (".$result.")", $debug-16);
    return $result;
  }

  // display a button to adjust a value
  function edit_value ($phr_lst, $value_id, $back, $debug) {
    log_debug("button->edit_value (".$phr_lst->name($debug-1).",v".$value_id.",b".$back.")", $debug-1);
    
    if (!empty($phr_lst->ids)) {
      $this->title = "change the value for ".$phr_lst->name();
    } else {
      $this->title = "change this value";
    }  
    $this->call  = '/http/value_edit.php?id='.$value_id.'&back='.$back;
    $result = $this->edit();
    log_debug("button->edit_value -> (".$result.")", $debug-1);
    return $result;
  }

  // display a button to exclude a value
  function del_value ($phr_lst, $value_id, $back, $debug) {
    log_debug("button->del_value (".$phr_lst->name($debug-1).",v".$value_id.",b".$back.")", $debug-1);
    
    if (!empty($phr_lst->ids)) {
      $this->title = "delete the value for ".$phr_lst->name();
    } else {
      $this->title = "delete this value";
    }  
    $this->call  = '/http/value_del.php?id='.$value_id.'&back='.$back;
    $result = $this->del();
    log_debug("button->del_value -> (".$result.")", $debug-1);
    return $result;
  }

}

// only to shorten the code the basic buttons as a function without object
// this way only one code line is needed 
function btn_add      ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->add(); }      // an add button to create a new entry
function btn_edit     ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->edit(); }     // an edit button to adjust an entry
function btn_del      ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->del(); }      // an delete button to remove an entry
function btn_undo     ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->undo(); }     // an undo button to undo an change (not only the last)
function btn_find     ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->find(); }     // a find button to search for a word
function btn_unfilter ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->unfilter(); } // button to remove a filter
function btn_yesno    ($t, $c) { $b = New button; $b->title = $t; $b->call = $c; return $b->yesno(); }    // button to get the user confirmation
function btn_back     ($bl)    { $b = New button;                                return $b->go_back($bl); } // button to remove a filter


// button to add a new value related to some phrases
function btn_add_value ($phr_lst, $type_ids, $back, $debug) {
  $b = New button;
  $result = $b->add_value ($phr_lst, $type_ids, $back, $debug-1);
  return $result;
}

// similar to btn_add_value, but uses a simple modal box
function btn_add_value_fast ($modal_nbr, $phr_lst, $phr_main, $common_lst, $back, $debug) {
  $b = New button;
  $result = $b->add_value_fast ($modal_nbr, $phr_lst, $phr_main, $common_lst, $back, $debug-1);
  return $result;
}



?>
