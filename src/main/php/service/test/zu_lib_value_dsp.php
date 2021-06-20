<?php

/*

  zu_lib_value_dsp.php - value related display functions
  --------------------

  function prefix: zuv_dsp_* 

  
  edit functions to display change views
  ----
  
  zuv_dsp_add       - created the html code for adding a new value
  zuv_dsp_edit      - lists all words related to a given value except the given word
  zuv_dsp_source    - display a selector for the value source
  zuv_btn_add_value - button to add an related value to $words but mainly to one word
                      edit button not needed because a click on the value itself is used to edit
  zuv_btn_del_value - button to allow the user to exclude a single value from calculation and display
  zuv_list          - display all values related to a given word
  zuv_table         - creates a table of all values related to a word and a related word and all the sub words of the related word
  zuv_tbl_val       - display a value, means create the HTML code that allows to edit the value
  zuv_dsp           - display a value and formats is according to the format word

  
  combined functions that may be dismissed
  --------
  
  zuv_wrd_lst_dsp   - display a value and the related words; used where ???
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/*
   display functions
   -------
*/

// lists all words related to a given value except the given word
// and offer to add a formula to the value as an alternative
// $wrd_add is onöy optional to display the last added word at the end
// todo: take user unlink of words into account
// save data to the database only if "save" is pressed add and remove the word links "on the fly", which means that after the first call the edit view is more or less the same as the add view
function zuv_dsp_edit_or_add($val_id, $wrd_ids, $type_ids, $db_ids, $src_id, $back_link, $user_id, $debug) {
  log_debug("zuv_dsp_edit_or_add (".$val_id.",t".implode(",",$wrd_ids).",type".implode(",",$type_ids).",db".implode(",",$db_ids)."b".$back_link.",u".$user_id.")", $debug);

  $result = ''; // reset the html code var
  
  // check and prepare the parameters
  if ($val_id > 0) { 
    $script = "value_edit";
    $title  = "Change value for";
  } else { 
    $script = "value_add";
    $title  = "Add value for";
  }
  $this_url = '/http/'.$script.'.php?id='.$val_id.'&back='.$back_link; // url to call this display again to display the user changes
  
  // display the header  
  $result .= zuh_text_h3($title); 
  $result .= zuh_form_start($script);
  
  // list all linked words
  $result .= zuh_tbl_start_half();

  // convert and validate the parameters
  if (count($wrd_ids) <> count($type_ids) OR count($wrd_ids) <> count($db_ids)) {
    if (count($wrd_ids) <> count($type_ids)) {
      log_err ("Invalide parameter size at value edit: number of words (".implode(",",$wrd_ids).") differ from the number of types (".implode(",",$type_ids).") given.","zuv_dsp_edit_or_add");
    }
    if (count($wrd_ids) <> count($db_ids)) {
      log_err ("Invalide parameter size at value edit: number of words (".implode(",",$wrd_ids).") differ from the number of db ids (".implode(",",$db_ids). ") given.","zuv_dsp_edit_or_add");
    }
  } else {
    $word_pos = 1; // the word position (combined number for fixed, type and free words)
    // if the form is confirmed, save the value or the other way round: if with the plus sign only a new word is added, do not yet save the value
    $result .= '  <input type="hidden" name="id" value="'.$val_id.'">';
    $result .= '  <input type="hidden" name="confirm" value="1">';
    
    // reset the word sample setting s
    $main_wrd_id = 0;
 
    // show first the words, that are not supposed to be changed
    foreach (array_keys($wrd_ids) AS $pos) {
      if ($type_ids[$pos] < 0) {
        // allow the user to change also the fixed words
        $type_ids_adj = $type_ids;
        $type_ids_adj[$pos] = 0;
        $used_url = $this_url.zu_ids_to_url($wrd_ids,     "word", $debug-1).
                              zu_ids_to_url($type_ids_adj,"type", $debug-1).
                              zu_ids_to_url($db_ids,      "db",   $debug-1);
        $result .= zut_html_del($wrd_ids[$pos], zut_name($wrd_ids[$pos], $user_id), $used_url, $debug-1);
        $result .= '  <input type="hidden" name="word'.$word_pos.'" value="'.$wrd_ids[$pos].'">';
        $result .= '  <input type="hidden" name="db'.$word_pos.'"   value="'.$db_ids[$pos].'">';
        $word_pos = $word_pos + 1;
      } 
    }

    // guess the missing word types 
    foreach (array_keys($wrd_ids) AS $pos) {
      // if no type is given, guess a type
      if ($type_ids[$pos] == 0) {
        log_debug("zuv_dsp_edit_or_add -> guess type for position ".$pos.".", $debug);
        $wrd_type_ids = zut_ids_is ($wrd_ids[$pos], $user_id, $debug);
        if (!empty($wrd_type_ids)) {
          // use the first type as a guess
          $type_ids[$pos] = $wrd_type_ids[0];
          log_debug("zuv_dsp_edit_or_add -> guessed type for position ".$pos.": ".$type_ids[$pos].".", $debug);
        }
      }
    }  
   
    // show the words that the user can change: first the non specific ones, that the words of a selective type and new words at the end
    for ($dsp_type = 0; $dsp_type <= 2; $dsp_type++) {
      foreach (array_keys($wrd_ids) AS $pos) {
        // build a list of suggested words
        $wrd_lst = array();
        if ($type_ids[$pos] > 0) {
          // prepare the selector for the type word
          //$sql_type_words = zu_sql_words_linked($type_ids, cl(SQL_LINK_TYPE_IS), "up", $debug-1);
          //$sub_words = zu_sql_get_lst($sql_type_words, $debug-1);
          $sub_words = zut_ids_are($type_ids[$pos], $debug-1);
          log_debug("zuv_dsp_edit_or_add -> suggested word ids for position ".$pos.": ".implode(",",$sub_words).".", $debug);
          $wrd_lst = zu_sql_wrd_ids_to_lst_names($sub_words, $user_id, $debug) ;
          log_debug("zuv_dsp_edit_or_add -> suggested words for position ".$pos.": ".implode(",",$wrd_lst).".", $debug);
        } else {
          // if no word group is found, use the word type time if the word is a time word
          if (zut_is_time($wrd_ids[$pos])) {
            $wrd_lst = zut_type_lst(cl(DBL_WORD_TYPE_TIME), $debug-1);
          }
        }

        // build the url for the case that this word should be removed
        $wrd_ids_adj  = $wrd_ids;
        $type_ids_adj = $type_ids;
        $db_ids_adj   = $db_ids;
        array_splice($wrd_ids_adj,  $pos, 1);
        array_splice($type_ids_adj, $pos, 1);
        array_splice($db_ids_adj, $pos, 1);
        $used_url = $this_url.zu_ids_to_url($wrd_ids_adj, "word", $debug-1).
                              zu_ids_to_url($type_ids_adj,"type", $debug-1).
                              zu_ids_to_url($db_ids_adj,  "db",   $debug-1);
        // url for the case that this word should be renamed                      
        $word_url = '/http/word_edit.php?id='.$wrd_ids[$pos].'&back='.$back_link;
        
        // show the word selector
        $result .= '  <tr>';

        // show the words that don't have a type
        if ($dsp_type == 0) {
          if ($type_ids[$pos] == 0 AND $wrd_ids[$pos] > 0) {
            if ($main_wrd_id == 0) {
              $main_wrd_id = $wrd_ids[$pos];
            }
            $result .= '    <input type="hidden" name="db'.$word_pos.'" value="'.$db_ids[$pos].'">';
            $result .= '    <td colspan="2">';
            $result .= '      '.zuh_selector("word".$word_pos, $script, "SELECT word_id, word_name FROM words ORDER BY word_name;", $wrd_ids[$pos]);
            $word_pos = $word_pos + 1;
            
            $result .= '    </td>';
            $result .= '    <td>'.zuh_btn_del  ("Remove ".zut_name($wrd_ids[$pos], $user_id), $used_url, $debug-1).'</td>';
            $result .= '    <td>'.zuh_btn_edit ("Rename ".zut_name($wrd_ids[$pos], $user_id), $word_url, $debug-1).'</td>';
          }
        }  

        // show the words that have a type
        if ($dsp_type == 1) {
          if ($type_ids[$pos] > 0) {
            $result .= '    <td>';
            $result .= zut_name($type_ids[$pos], $user_id).':';
            $result .= '    </td>';
            $result .= '    <input type="hidden" name="db'.$word_pos.'" value="'.$db_ids[$pos].'">';
            $result .= '    <td>';
            if (!empty($wrd_lst)) {
              $result .= '      '.zuh_selector_lst("word".$word_pos, $script, $wrd_lst, $wrd_ids[$pos], $debug-1);
            } else {  
              $result .= '      '.zuh_selector("word".$word_pos, $script, "SELECT word_id, word_name FROM words ORDER BY word_name;", $wrd_ids[$pos]);
            }
            $word_pos = $word_pos + 1;
            
            $result .= '    </td>';
            $result .= '    <td>'.zuh_btn_del  ("Remove ".zut_name($wrd_ids[$pos], $user_id), $used_url, $debug-1).'</td>';
            $result .= '    <td>'.zuh_btn_edit ("Rename ".zut_name($wrd_ids[$pos], $user_id), $word_url, $debug-1).'</td>';
          }
        }

        // show the new words
        if ($dsp_type == 2) {
          if ($wrd_ids[$pos] == 0) {
            $result .= '    <td colspan="2">';
            $result .= '      '.zuh_selector("word".$word_pos, $script, "SELECT word_id, word_name FROM words ORDER BY word_name;", $wrd_ids[$pos]);
            $word_pos = $word_pos + 1;
            
            $result .= '    </td>';
            $result .= '    <td>'.zuh_btn_del  ("Remove ".zut_name($wrd_ids[$pos], $user_id), $used_url, $debug-1).'</td>';
          }
        }  

        $result .= '  </tr>';
      } 
    } 
  }

  $result .= '</table> ';

  $wrd_ids_new    = $wrd_ids;
  //$wrd_ids_new[]  = $new_word_default;
  $wrd_ids_new[]  = 0;
  $type_ids_new   = $type_ids;
  $type_ids_new[] = 0;
  $db_ids_new     = $db_ids;
  $db_ids_new[]   = 0;
  $used_url = $this_url.zu_ids_to_url($wrd_ids_new, "word", $debug-1).
                        zu_ids_to_url($type_ids_new,"type", $debug-1).
                        zu_ids_to_url($db_ids_new,  "db",   $debug-1);
  $result .= '  '.zuh_btn_add ("Add another word", $used_url);
  $result .= '  <br><br>';
  $result .= '  <input type="hidden" name="back" value="'.$back_link.'">';
  if ($val_id > 0) {   
    $result .= '  to <input type="text" name="value" value="'.zuv_value($val_id, $user_id, $debug).'">';
  } else {
    $result .= '  is <input type="text" name="value">';
  }
  $result .= zuh_form_end("Save");
  $result .= '<br><br>';
  $result .= zuv_dsp_source($src_id, $script, $back_link, $user_id, $debug-1);
  $result .= '<br><br>';
  $result .= zuh_btn_back($back_link);
  
  // display the user changes 
  if ($val_id > 0) { 
    $changes = zuv_dsp_hist($val_id, 20, $back_link, $debug-1);
    if (trim($changes) <> "") {
      $result .= zuh_text_h3("Latest changes related to this value", "change_hist");
      $result .= $changes;
    }
    $changes = zuv_dsp_hist_links($val_id, 20, $back_link, $debug-1);
    if (trim($changes) <> "") {
      $result .= zuh_text_h3("Latest link changes related to this value", "change_hist");
      $result .= $changes;
    }
  } else {
    // display similar values as a sample for the user to force a consistent type of entry e.g. cost should always be a negative number
    if ($main_wrd_id > 0) {
      $samples = zuv_dsp_samples($main_wrd_id, $wrd_ids, 10, $user_id, $back_link, $debug);
      if (trim($samples) <> "") {
        $result .= zuh_text_h3("Please have a look at these other \"".zut_link_style($main_wrd_id, zut_name($main_wrd_id, $user_id), "grey")."\" values as an indication", "change_hist");
        $result .= $samples;
      }
    }
  }

  log_debug("zuv_dsp_edit_or_add -> done", $debug);
  return $result;
}



// display a selector for the value source
function zuv_dsp_source($src_id, $php_script, $back_link, $user_id, $debug) {
  log_debug("zuv_dsp_source (".$src_id.",".$php_script.",b".$back_link.",u".$user_id.")", $debug);

  $result = ''; // reset the html code var

  // for new values assume the last source used, but not for existing value to enable only changing the value, but not setting the source
  if ($src_id <= 0 and $php_script == "value_add") {
    $src_id = zuu_last_source ($user_id, $debug);
  }

  log_debug("zuv_dsp_source -> source id used (".$src_id.")", $debug-5);
  $result .= '      taken from '.zuh_selector ("source", $php_script, zu_sql_std_lst ("source"), $src_id, "please define the source" , $debug-1).' ';
  $result .= '    <td>'.zuh_btn_edit ("Rename ".zus_name($src_id), '/http/source_edit.php?id='.$src_id.'&back='.$back_link).'</td>';
  $result .= '    <td>'.zuh_btn_add  ("Add new source", '/http/source_add.php?back='.$back_link).'</td>';
  return $result;
}

// button to add an related value to $words but mainly to the word $id
// $fixed_words - words that the user is not suggested to change this time
// $select_word - suggested words which the user can change
// $type_word   - word to preselect the suggested words e.g. "Country" to list all ther countries first for the suggested word
// $back_id     - id of the calling word to define what should be displayed after the adding of the value
function zuv_btn_add_value ($wrd_ids, $type_ids, $back_id, $debug) {
  log_debug("zuv_btn_add_value (".implode(",",$wrd_ids).",t".implode(",",$type_ids).",b".$back_id.")", $debug-1);
  $url = '/http/value_add.php?back='.$back_id.zu_ids_to_url($wrd_ids,"word", $debug-1).zu_ids_to_url($type_ids,"type", $debug-1);
  $result = zuh_btn_add ('add new value', $url);
  log_debug("zuv_btn_add_value -> (".$result.")", $debug-1);
  return $result;
}

// button to allow the user to change a single value
function zuv_btn_edit_value ($value_id, $back_id, $debug) {
  $result = zuh_btn_edit ('change this value', '/http/value_edit.php?id='.$value_id.'&back='.$back_id.'');
  return $result;
}

// button to allow the user to exclude a single value from calulation and display
function zuv_btn_del_value ($value_id, $back_id, $debug) {
  //zu_debug("zuv_btn_del_value ... ", $debug);
  $result = zuh_btn_del ('delete this value', '/http/value_del.php?id='.$value_id.'&back='.$back_id.'');
  return $result;
}

// the same as zuv_btn_del_value, but with another icon
function zuv_btn_undo_add_value ($value_id, $back_id, $debug) {
  $result = zuh_btn_undo ('delete this value', '/http/value_del.php?id='.$value_id.'&back='.$back_id.'');
  return $result;
}


// display a value and formats is according to the format word
function zuv_dsp ($num_value, $format_word_id, $debug) {
  log_debug('zuv_dsp ('.$num_value.','.$format_word_id.')', $debug);
  $result = $num_value;
  if (is_numeric($num_value)) {
    if ($format_word_id == cl(DBL_WORD_TYPE_PERCENT)) {
      $result = round($num_value*100,2)."%";
    }
  }
  return $result;
}

/*
// creates a table of all values related to a word and a related word and all the sub words of the related word
// e.g. for "ABB" (word) list all values for the cash flow statement (related word)
function zuv_table ($word_id, $related_word_id, $user_id, $debug) {
  log_debug("zuv_table (t".$word_id.",".$related_word_id."u".$user_id.")", $debug);
  $result = '';

  // create the table headline e.g. cash flow statement
  $related_word_name = zut_name($related_word_id, $user_id, $debug-1);
  $result .= zut_html($related_word_id, $related_word_name);
  $result .= '<br>';

  // get all values related to the selectiong word, because this is probably strongest selection and to save time reduce the number of records asap
  $value_lst = zu_sql_val_lst_wrd($word_id, $user_id, $debug-1);

  // get all words related to the value list to be able to define the column and the row names
  $all_word_ids = zu_lst_all_ids($value_lst, 2, $debug-1);
  $all_word_lst = zu_sql_wrd_ids_to_lst($all_word_ids, $user_id, $debug-1);
  
  // get the time words for the column heads
  $all_time_lst = zut_time_lst($all_word_lst, $debug-1);
  
  // adjust the time words to display
  $time_lst = zut_time_useful($all_time_lst, $debug-1);
  
  // filter the value list by the time words used
  $used_value_lst = zu_lst_id_filter($value_lst, $time_lst, 2, $debug-1);
  
  // get the word tree for the left side of the table
  $word_ids = zut_ids_contains_and_are($related_word_id, $user_id, $debug-1);

  // add potential differentiators to the word tree
  $word_incl_differentiator_lst = zut_db_lst_differentiators_filtered($word_ids, $all_word_ids, $user_id, $debug-1);

  // filter the value list by the row words used
  $used_value_lst = zu_lst_id_filter($used_value_lst, $word_incl_differentiator_lst, 2, $debug-1);
  
  // get the common words
  $common_ids = zu_lst_get_common_ids($used_value_lst, 2, $debug-1);
  $common_lst = zu_sql_wrd_ids_to_lst($common_ids, $user_id, $debug-1);

  // get all words not yet part of the table rows, columns or common words
  $xtra_words = zu_lst_not_in($all_word_lst, $word_incl_differentiator_lst, $debug-1);
  $xtra_words = zu_lst_not_in($xtra_words, array_keys($time_lst), $debug-1);
  $xtra_words = zu_lst_not_in($xtra_words, array_keys($common_lst), $debug-1);

  // display the common words 
  log_debug("zuv_table common", $debug);
  // to do: sort the words and use the short form e.g. in mio. CHF instead of in CHF millios
  if (count($common_lst) > 0) {
    $commen_text = '(in ';
    foreach (array_keys($common_lst) as $common_word) {
      if ($common_word <> $word_id) {
        $commen_text .= zut_html($common_word, $common_lst[$common_word][0]);
      }
    }
    $commen_text .= ')';
    $result .= zuh_line_small($commen_text);
  }
  $result .= '<br>';
  log_debug("zuv_table start", $debug);
    
  // display the table
  $result .= zuh_tbl_start();
  $result .= '   <colgroup>'."\n";
  //$result .= '<col span="'.sizeof($time_lst)+1.'">';
  $result .= '    <col span="7">'."\n";
  $result .= '  </colgroup>'."\n"; 
  $result .= '  <tbody>'."\n"; 
  
  // display the column heads
  $result .= '  <tr>'."\n";
  $result .= '    <th></th>'."\n";
  foreach (array_keys($time_lst) as $time_word) {
    $result .= zut_html_tbl_head($time_word, $time_lst[$time_word][0]);
  }
  $result .= '  </tr>'."\n";

  // temp: display the word tree
  foreach ($word_ids as $sub_word_id) {
    $result .= '  <tr>'."\n";
    $sub_word_name = zut_name($sub_word_id, $user_id, $debug-1);
    $result .= zut_html_tbl($sub_word_id, $sub_word_name, 0, $debug-1);
    // 
    $wrd_ids = array();
    $wrd_ids[] = $word_id;
    $wrd_ids[] = $sub_word_id;
    foreach ($common_ids AS $xtra_id) {
      if (!in_array($xtra_id, $wrd_ids)) {
        $wrd_ids[] = $xtra_id;
      }
    }
    foreach (array_keys($time_lst) as $val_word_id) {
      $val_wrd_ids   = $wrd_ids;
      if (!in_array($val_word_id, $val_wrd_ids)) {
        $val_wrd_ids[] = $val_word_id;
      }
      //$tbl_value = zu_sql_tbl_value($word_id, $sub_word_id, $val_word_id, $user_id, $debug-1);
      $tbl_value = zuv_lst_get($value_lst, $val_wrd_ids, $debug-1);
      if ($tbl_value[0] == "") {
        //$result .= '      '.zuv_btn_add_value (zuv_words_id_txt($value_id[1], 0, $debug), $word_id, $debug);
        $result .= '      <td><div>'."\n";

        // to review
        $add_wrd_ids = $common_ids;
        $type_ids  = array();
        foreach ($add_wrd_ids AS $pos) {
          $type_ids[] = 0;
        }
        
        if ($sub_word_id > 0) {
          $add_wrd_ids[] = $sub_word_id;
          $type_ids[] = $type_word_id;
        }  
        if ($diff_word_id > 0) {
          $add_wrd_ids[] = $diff_word_id;
          $type_ids[] = 0;
        }
        // if values for just one column are added, the column head word id is already in the common id list and due to that does not need to be added
        if (!in_array($val_word_id, $add_wrd_ids) and $val_word_id > 0) {
          $add_wrd_ids[] = $val_word_id;
          $type_ids[] = 0;
        }
        
        $result .= '      '.zuv_btn_add_value ($add_wrd_ids, $type_ids, $word_id, $debug-1);
        $result .= '      </div></td>'."\n";
      } else {  
        if ($tbl_value[2] > 0) {
          $result .= zuv_tbl_val_usr($tbl_value[1], $tbl_value[0], $word_id);
        } else {
          $result .= zuv_tbl_val($tbl_value[1], $tbl_value[0], $word_id);
        }  
          // display the extra words of this value
          /*$result .= '      <td><div>'."\n";
          $result .= zuv_words($tbl_value[1], $word_id, 'names');
          $result .= '      </div></td>'."\n";
      }
    }
    $result .= '  </tr>'."\n";
    
    // display the row differentiators
    log_debug("zuv_table ... get differentiator for ".$sub_word_id.".", $debug-1);
    // get all potential differentiator words
    $differentiator_words = zut_db_differantiator_words_filtered($sub_word_id, $all_word_ids, $user_id, $debug-1);
    //$differentiator_words = zut_db_differentiator_words($sub_word_id, $debug-1);
    log_debug("zuv_table ... show differentiator of ".explode(",",$differentiator_words).".", $debug-1);
    // select only the differentiator words that have a value for the main word
    $differentiator_words = zu_lst_in($differentiator_words, $xtra_words);

    // find direct differentiator words
    $differentiator_type = sql_code_link(SQL_LINK_TYPE_DIFFERANTIATOR);
    $type_word_ids = array_keys(zu_sql_get_lst(zu_sql_words_linked($sub_word_id, $differentiator_type, "up", $debug), $debug-1));
    log_debug("zuv_table -> differentiator types ".implode(",",$type_word_ids).".", $debug-1);
    
    // if there is more than one type of differentiator group the differentiators by type
    // and add on each one an "other" line, if the sum is not 100%

    foreach ($type_word_ids as $type_word_id) {
      if (sizeof($type_word_ids) > 1) {
        $result .= '  <tr>'."\n";
        //$result .= '      <td>&nbsp;</td>';
        $result .= zut_html_tbl($type_word_id, zut_name($type_word_id, $user_id), $debug-1);
        $result .= '  </tr>'."\n";
      }
      // display the differentiator rows that are matching to the word type (e.g. the country)
      foreach (array_keys($differentiator_words) as $diff_word_id) {
        if (zut_is_a($diff_word_id, $type_word_id, $debug-1)) {
          $diff_word_type_ids = zut_ids_contains_and_are($diff_word_id, $debug-1); // should be taken from the original array to increase speed
          $result .= '  <tr>'."\n";
          //$result .= '      <td>&nbsp;</td>';
          $sub_word_name = zut_name($diff_word_id, $user_id, $debug-1);
          $result .= zut_html_tbl($diff_word_id, $sub_word_name, 2, $debug-1);
          $wrd_ids = array();
          $wrd_ids[] = $word_id;
          if (!in_array($sub_word_id, $wrd_ids)) {
            $wrd_ids[] = $sub_word_id;
          }  
          if (!in_array($diff_word_id, $wrd_ids)) {
            $wrd_ids[] = $diff_word_id;
          }  
          foreach ($common_ids AS $xtra_id) {
            if (!in_array($xtra_id, $wrd_ids)) {
              $wrd_ids[] = $xtra_id;
            }  
          }
          foreach (array_keys($time_lst) as $val_word_id) {
            $val_wrd_ids   = $wrd_ids;
            if (!in_array($val_word_id, $val_wrd_ids)) {
              $val_wrd_ids[] = $val_word_id;
            }  
            //$tbl_value = zu_sql_tbl_value_part($word_id, $diff_word_id, $val_word_id, $sub_word_id, $user_id, $debug-1);
            $tbl_value = zuv_lst_get($used_value_lst, $val_wrd_ids, $debug-1);
            if ($tbl_value[0] == "") {
              $result .= '      <td><div>'."\n";

              // to review
              $add_wrd_ids = $common_ids;
              $type_ids  = array();
              foreach ($add_wrd_ids AS $pos) {
                $type_ids[] = 0;
              }
              
              if ($sub_word_id > 0) {
                $add_wrd_ids[] = $sub_word_id;
                $type_ids[] = $type_word_id;
              }
              if ($diff_word_id > 0) {
                $add_wrd_ids[] = $diff_word_id;
                $type_ids[] = 0;
              }
              // if values for just one column are added, the column head word id is already in the commen id list and due to that does not need to be added
              if (!in_array($val_word_id, $add_wrd_ids) and $val_word_id > 0) {
                $add_wrd_ids[] = $val_word_id;
                $type_ids[] = 0;
              }  
        
              $result .= '      '.zuv_btn_add_value ($add_wrd_ids, $type_ids, $word_id, $debug-1);
              $result .= '      </div></td>'."\n";
            } else {  
              if ($tbl_value[2] > 0) {
                $result .= zuv_tbl_val_usr($tbl_value[1], $tbl_value[0], $word_id);
              } else {
                $result .= zuv_tbl_val($tbl_value[1], $tbl_value[0], $word_id);
              }
              // display the extra words of this value
              /*$result .= '      <td><div>'."\n";
              $result .= zuv_words($tbl_value[1], $word_id, 'names');
              $result .= '      </div></td>'."\n";
            }
          }
          $result .= '  </tr>'."\n";
        }
      }
      // add a new part value for the sub_word
      if (!empty($differentiator_words)) {
        $result .= '  <tr>'."\n";
        $result .= '      <td><div>'."\n";

        // to review
        $add_wrd_ids = $common_ids;
        $type_ids  = array();
        foreach ($add_wrd_ids AS $pos) {
          $type_ids[] = 0;
        }
        
        $add_wrd_ids[] = $sub_word_id;
        $add_wrd_ids[] = $val_word_id;
        $add_wrd_ids[] = $diff_word_id;
        $type_ids[] = $type_word_id;
        $type_ids[] = $type_word_id;
        $type_ids[] = $type_word_id;
        
        $result .= '      &nbsp;&nbsp;'.zuv_btn_add_value ($add_wrd_ids,$type_ids, $word_id, $debug-1);
        $result .= '      </div></td>'."\n";
        $result .= '  </tr>'."\n";
      }
    }
  }

  $result .= '    </tbody>'."\n"; 
  $result .= '  </table> ';
  
  // allow the user to add a completely new value 
  if ($last_words == '') {
    $last_words = $id;
  }

  $wrd_link_type = cl(SQL_LINK_TYPE_CONTAIN);
  $wrd_type = cl(SQL_WORD_TYPE_NORMAL); // maybe base it on the other linked words
  $wrd_add_title = "add new word to ".zut_name($related_word_id, $user_id, $debug-1);
  $wrd_add_call = "/http/word_add.php?verb=".$wrd_link_type."&word=".$related_word_id."&type=".$wrd_type."&back=".$word_id."";
  $result .= zuh_btn_add ($wrd_add_title, $wrd_add_call); 
  $result .= '&nbsp;&nbsp;';

  // not really needed because this can be done also with the other add value links
  // $result .= zuv_btn_add_value ($last_words, $last_words, $word_id, $debug);

  $result .= '<br><br>';

  log_debug("zuv_table ... done", $debug-1);

  return $result;
}
*/

/*

old zuv_table code that may be needed for cleanup

//$value_lst = zu_sql_word_values($word_id, $user_id, $debug-1);
  //$all_word_lst = zu_sql_value_lst_words(array_keys($value_lst), $user_id, $debug-1);
  //$used_value_lst = zu_sql_word_lst_values(array_keys($time_lst), array_keys($value_lst), $user_id, $debug-1);
  //$used_value_lst = zu_sql_word_lst_values(array_keys($word_lst_incl), array_keys($used_value_lst), $user_id, $debug-1);
  //$common_lst = zu_sql_value_lst_common_words(array_keys($used_value_lst), $debug-1);

*/

// display a value, means create the HTML code that allows to edit the value
function zuv_tbl_val ($id, $name, $back, $debug) {
  log_debug('zuv_tbl_val ('.$id.','.$name.','.$back.')', $debug);
  $result  = '';
  $result .= '    <td>'."\n";
  $result .= '      <div align="right"><a href="/http/value_edit.php?id='.$id.'&back='.$back.'">'.$name.'</a></div>'."\n";
  $result .= '    </td>'."\n";
  return $result;
}

// same as zuv_tbl_val, but in the user specific color
function zuv_tbl_val_usr ($id, $name, $back, $debug) {
  log_debug('zuv_tbl_val ('.$id.','.$name.','.$back.')', $debug);
  $result  = '';
  $result .= '    <td>'."\n";
  $result .= '      <div align="right"><a href="/http/value_edit.php?id='.$id.'&back='.$back.'" class="user_specific">'.$name.'</a></div>'."\n";
  $result .= '    </td>'."\n";
  return $result;
}


// display a value and the related words
function zuv_wrd_lst_dsp($id, $user_id, $debug) {
  log_debug("zuv_wrd_lst_dsp (".$id.",u".$user_id.")", $debug);

  $result = ''; // reset the html code var
  
  $val_lst = zu_sql_val($id, $user_id, $debug-1);
  $result .= '<a href="/http/value_edit.php?id='.$id.'&back='.$id.'">'.$val_lst[$id].'</a> ';

  // list all linked words
  $wrd_lst = zu_sql_val_wrd_lst($id, $user_id, $debug-1);
  if (!empty($wrd_lst)) {
    $result .= 'as '.implode (",",$wrd_lst).' ';
  }

  log_debug("zuv_wrd_lst_dsp ... done", $debug-1);

  return $result;
}

// display some value samples related to the wrd_id
// with a preference of the start_word_ids
function zuv_dsp_samples($wrd_id, $start_wrd_ids, $size, $user_id, $back, $debug) {
  log_debug("zuv_dsp_samples (".$wrd_id.",rt".implode(",",$start_wrd_ids).",size".$size.")", $debug);
  $result = ''; // reset the html code var
  
  // get value changes by the user that are not standard
  $sql = "SELECT v.value_id,
                 IF(u.user_value IS NULL,v.word_value,u.user_value) AS word_value, 
                 t.word_id,
                 t.word_name
            FROM value_phrase_links l,
                 value_phrase_links lt,
                 words t,
                 `values` v
       LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = ".$user_id." 
           WHERE l.phrase_id = ".$wrd_id."
             AND l.value_id = v.value_id
             AND v.value_id = lt.value_id
             AND lt.word_id <> ".$wrd_id."
             AND lt.word_id = t.word_id
             AND (u.excluded IS NULL OR u.excluded = 0);";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show where the user uses different value than a normal viewer
  $row_nbr = 0;
  $value_id = 0;
  $word_names = "";
  $result .= '<table class="change_hist">';
  while ($wrd_row = mysqli_fetch_array($sql_result, MYSQL_ASSOC) AND $row_nbr <= $size) {
    // display the headline first if there is at least on entry
    if ($row_nbr == 0) {
      $result .= '<tr>';
      $result .= '<th>samples</th>';
      $result .= '<th>for</th>';
      $result .= '</tr>';
      $row_nbr++;
    }

    $new_value_id = $wrd_row["value_id"];
    if ($value_id <> $new_value_id) {
      if ($word_names <> "") {
        // display a row if the value has changed and 
        $result .= '<tr>';
        $result .= '<td><a href="/http/value_edit.php?id='.$value_id.'&back='.$back.'" class="grey">'.$row_value.'</a></td>';
        $result .= '<td>'.$word_names.'</td>';
        $result .= '</tr>';
        $row_nbr++;
      }
      // prepare a new value display
      $row_value = $wrd_row["word_value"];
      $word_names = zut_link_style($wrd_row["word_id"], $wrd_row["word_name"], "grey");
      $value_id = $new_value_id;
    } else {
      $word_names .= ", ".zut_link_style($wrd_row["word_id"], $wrd_row["word_name"], "grey");
    }
  }
  // display the last row if there has been at least one word
  if ($word_names <> "") {
    $result .= '<tr>';
    $result .= '<td><a href="/http/value_edit.php?id='.$value_id.'&back='.$back.'" class="grey">'.$row_value.'</a></td>';
    $result .= '<td>'.$word_names.'</td>';
    $result .= '</tr>';
  }
  $result .= '</table>';
  
  return $result;
}  

// display the history of a value
function zuv_dsp_hist($val_id, $size, $back_link, $debug) {
  log_debug("zuv_dsp_hist (".$val_id.",size".$size.",b".$size.")", $debug);
  $result = ''; // reset the html code var
  
  // get value changes by the user that are not standard
  $sql = "SELECT c.change_time AS time, 
                 u.user_name AS user, 
                 a.change_action_name AS type, 
                 f.description AS type_field, 
                 f.code_id, 
                 c.old_value AS old, 
                 c.new_value AS new
            FROM changes c,
                 change_actions a,
                 change_fields f,
                 users u
           WHERE (f.table_id = ".cl(DBL_SYSLOG_TBL_VALUE)." 
               OR f.table_id = ".cl(DBL_SYSLOG_TBL_VALUE_USR).")
             AND f.change_field_id  = c.change_field_id 
             AND c.row_id  = ".$val_id." 
             AND c.change_action_id = a.change_action_id 
             AND c.user_id = u.user_id 
        ORDER BY c.change_time DESC
           LIMIT ".$size.";";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show where the user uses different value than a normal viewer
  $row_nbr = 0;
  $result .= '<table class="change_hist">';
  while ($wrd_row = mysqli_fetch_array($sql_result, MYSQL_ASSOC)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>time</th>';
      $result .= '<th>user</th>';
      $result .= '<th>from</th>';
      $result .= '<th>to</th>';
      $result .= '<th></th>'; // extra column for the undo icon
    }
    $result .= '</tr>';
    $result .= '<tr>';
    $result .= '<td>'.$wrd_row["time"].'</td>';
    $result .= '<td>'.$wrd_row["user"].'</td>';
    if ($wrd_row["old"]  == "")    { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$wrd_row["old"].'</td>'; }  
    if ($wrd_row["new"]  == "")    { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$wrd_row["new"].'</td>'; }  
    if ($wrd_row["type"] == "add") { $result .= '<td>'.zuv_btn_undo_add_value ($val_id, $back_link, $debug-1).'</td>'; } else { $result .= '<td>'.''.'</td>'; }  
    $result .= '</tr>';
  }
  $result .= '</table>';

  log_debug("zuv_dsp_hist -> done", $debug-1);
  return $result;
}

// display the history of a value
function zuv_dsp_hist_links($val_id, $size, $back_link, $debug) {
  log_debug("zuv_dsp_hist_links (".$val_id.",size".$size.",b".$size.")", $debug);
  $result = ''; // reset the html code var

  // get value changes by the user that are not standard
  $sql = "SELECT c.change_time AS time, 
                 u.user_name AS user, 
                 a.change_action_name AS type, 
                 c.new_text_link AS link, 
                 c.old_text_to AS old, 
                 c.new_text_to AS new
            FROM change_links c,
                 change_actions a,
                 users u
           WHERE (c.change_table_id = ".cl(DBL_SYSLOG_TBL_VALUE)." 
               OR c.change_table_id = ".cl(DBL_SYSLOG_TBL_VALUE_USR)." 
               OR c.change_table_id = ".cl(DBL_SYSLOG_TBL_VALUE_LINK)." )
             AND (c.old_from_id = ".$val_id." OR c.new_from_id = ".$val_id.")
             AND c.change_action_id = a.change_action_id 
             AND c.user_id = u.user_id 
        ORDER BY c.change_time DESC
           LIMIT ".$size.";";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show where the user uses different value than a normal viewer
  $row_nbr = 0;
  $result .= '<table class="change_hist">';
  while ($wrd_row = mysqli_fetch_array($sql_result, MYSQL_ASSOC)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>time</th>';
      $result .= '<th>user</th>';
      $result .= '<th>link</th>';
      $result .= '<th>from</th>';
      $result .= '<th>to</th>';
    }
    $result .= '</tr>';
    $result .= '<tr>';
    $result .= '<td>'.$wrd_row["time"].'</td>';
    $result .= '<td>'.$wrd_row["user"].'</td>';
    $result .= '<td>'.$wrd_row["link"].'</td>';
    if ($wrd_row["old"] == "") { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$wrd_row["old"].'</td>'; }
    if ($wrd_row["new"] == "") { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$wrd_row["new"].'</td>'; }
    $result .= '</tr>';
  }
  $result .= '</table>';

  log_debug("zuv_dsp_hist_links -> done", $debug-1);
  return $result;
}
