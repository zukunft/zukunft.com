<?php

/*

  zu_lib_word_dsp.php - old functions to display words (just just for regression code testing)
  -------------------

  prefix: zut_dsp_* 

  simple functions
  ------
  
  zut_html          - simply to display a single word in a table row    e.g. used to create a word list 
  zut_html_tbl      - simply to display a single word in a table column e.g. used to create a value table 
  zut_html_tbl_head - simply to display a single word in a table header e.g. used to create a value table 
  
  zut_unlink_html   - allow the user to unlick a word
  
  zut_dsp_add       - show the html form to add a new word
  zut_dsp_edit      - show the html form to adjust a word
  

  deprecated functions
  ----------
  
  zut_html_id - because the word name should be retrived already with the initial database call

  
  Var name convention
  
  $id         - number of a database primary index
  $word_ids   - comma seperated string of word word_ids
  $word_names - comma seperated string with word string, each capsulet by highquotes
  $word_array - array of comma seperated string with word string, each capsulet by highquotes
  
  for selectors four parameters are used
  $selected:  word id that is now selected by the user and used for to display the values
  $suggested: word id that is most often used in this eviroment
  $useful:    list of word ids that are likely to be used (target 3 to 4, max 7)
  $possible:  list of all possible word ids; the user can select from this list by typing
  $all:       list of all words mainly as a fallback used only if the typed name does not lead to a result and these word should be displayed in a different format
  
  
zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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


// simply to display a single word and also get the name
function zut_html_id ($id, $user_id, $debug) {
  zu_debug('zut_html_id('.$id.')', $debug);
  $result  = zut_html ($id, zut_name($id, $user_id), $debug-1);
  return $result;
}

// simply to display a single word
function zut_html ($id, $name, $debug) {
  zu_debug('zut_html', $debug);
  $result  = '  <tr>'."\n";
  $result .= zut_html_tbl($id, $name, $debug-1);
  $result .= '  </tr>'."\n";
  return $result;
}

// simply to display a single word and allow to delete it
// used by zuv_dsp_add
function zut_html_del ($id, $name, $del_call, $debug) {
  zu_debug('zut_html', $debug);
  $result  = '  <tr>'."\n";
  $result .= zut_html_tbl($id, $name, $debug-1);
  $result .= '    <td>'."\n";
  $result .= '      '.btn_del ("delete", $del_call).'<br> ';
  $result .= '    </td>'."\n";
  $result .= '  </tr>'."\n";
  return $result;
}

// simply to display a single word in a table
function zut_link ($id, $name, $debug) {
  // to be replace by object code
  $user_id = 0;
  $description = zut_description($id, $user_id, $debug);
  $result = '<a href="/http/view.php?words='.$id.'" title="'.$description.'">'.$name.'</a>';
  return $result;
}

// similar to zut_link 
function zut_link_style ($id, $name, $style, $debug) {
  $result = '<a href="/http/view.php?words='.$id.'" class="'.$style.'">'.$name.'</a>';
  return $result;
}

// simply to display a single word in a table
function zut_html_tbl ($id, $name, $intent, $debug) {
  zu_debug('zut_tbl_html', $debug);
  $result  = '    <td>'."\n";
  while ($intent > 0) {
    $result .= '&nbsp;';
    $intent = $intent - 1;
  }
  $result .= '      '.zut_link ($id, $name, $debug-1).''."\n";
  $result .= '    </td>'."\n";
  return $result;
}

// simply to display a single word in a table as a header
function zut_html_tbl_head ($id, $name, $debug) {
  zu_debug('zut_html_tbl_head', $debug);
  $result  = '    <th>'."\n";
  $result .= '      '.zut_link ($id, $name, $debug-1)."\n";
  $result .= '    </th>'."\n";
  return $result;
}

// simply to display a single word in a table as a header
function zut_html_tbl_head_right ($id, $name, $debug) {
  zu_debug('zut_html_tbl_head_right', $debug);
  $result  = '    <th>'."\n";
  $result .= '      <p align="right">'.zut_link ($id, $name, $debug-1).'</p>'."\n";
  $result .= '    </th>'."\n";
  return $result;
}

// allow the user to unlick a word
function zut_unlink_html ($link_id, $word_id, $debug) {
  zu_debug('zut_unlink_html('.$link_id.')', $debug);
  $result  = '    <td>'."\n";
  $result .= btn_del ("unlink word", "/http/link_del.php?id=".$link_id."&back=".$word_id);
  $result .= '    </td>'."\n";
  return $result;
}

// display a word as the view header
function zut_dsp_header ($wrd_id, $user_id, $debug) {
  zu_debug('zut_dsp_header ('.$wrd_id.')', $debug);
  $result  = '';
  
  if ($wrd_id <= 0) {
    $result .= 'no word selected';
  } else {
    $is_part_of = zut_is_name($wrd_id);
    $result .= '<h2>';
    $result .= zut_name ($wrd_id, $user_id);
    if ($is_part_of <> '' and $is_part_of <> 'not set') {
      $result .= ' (<a href="/http/view.php?words='.zut_is_id($wrd_id).'">'.$is_part_of.'</a>)';
    }
/*    $result .= '  '.'<a href="/http/word_edit.php?id='.$wrd_id.'&back='.$wrd_id.'" title="Rename word"><img src="'.ZUH_IMG_EDIT.'" alt="Rename word" style="height: 0.65em;"></a>'; */
    $result .= '  '.'<a href="/http/word_edit.php?id='.$wrd_id.'&back='.$wrd_id.'" title="Rename word"><span class="glyphicon glyphicon-pencil"></span></a>';
    $result .= '</h2>';
  }
    
  zu_debug('zut_dsp_header done', $debug);
  return $result;
}

// display a word list as a text, means word by word within one line
function zut_dsp_lst_txt ($wrd_lst, $debug) {
  zu_debug('zut_dsp_lst_txt ('.implode(",",$wrd_lst).')', $debug);
  $result = '';

  foreach (array_keys($wrd_lst) AS $wrd_id) {
    if ($result <> '') {
      $result .= ', ';
    }
    if (is_array($wrd_lst[$wrd_id])) {
      $result .= '<a href="/http/view.php?words='.$wrd_id.'">'.$wrd_lst[$wrd_id][0].'</a>';
    } else {
      $result .= '<a href="/http/view.php?words='.$wrd_id.'">'.$wrd_lst[$wrd_id].'</a>';
    }
  }

  return $result;
}

// ??? identical to zum_word_list ???
// shows all words the link to the given word
// returns the html code to select a word that can be edit
// database link must be open
function zut_html_list_related ($id, $direction, $user_id, $debug) {
  zu_debug('zut_html_list_related('.$id.','.$direction.',u'.$user_id.')', $debug);
  $result  = '';

  
  // get the link types related to the word
  if ($direction == "down") {
    $type_query = "SELECT verb_id FROM word_links WHERE to_phrase_id = ".$id." GROUP BY verb_id;";
  } else {  
    $type_query = "SELECT verb_id FROM word_links WHERE from_phrase_id = ".$id." GROUP BY verb_id;";
  }
  $sql_type_result = zu_sql_get_all($type_query, $debug);

  // loop over the link types
  while ($type_entry = mysql_fetch_array($sql_type_result, MYSQL_NUM)) {

    // select the words
    $link_type_id = $type_entry[0];
    $sql = zu_sql_words_linked ($id, $link_type_id, $direction, $user_id, $debug);
    $sql_result = zu_sql_get_all($sql, $debug);

    // select the same side of the verb
    if ($direction == "down") {
      $directional_link_type_id = $link_type_id;
    } else {  
      $directional_link_type_id = $link_type_id * -1;
    }
    
    // in case of the verb "following" continue the series
    if ($link_type_id == cl(SQL_LINK_TYPE_FOLLOW)) {
      $start_id = $link_type_id * -1;
    } else {  
      $start_id = $id;
    }
    
    zu_debug('zut_html_list_related link', $debug);
    
    // display the link type
    $num_rows = mysql_num_rows($sql_result);
    if ($num_rows > 1) {
      $result .= zut_plural ($id, $user_id, $debug);
      zu_debug('zut_html_list_related plu', $debug);
      if ($direction == "down") {
	$result .= " " . zul_plural_reverse($link_type_id);
      } else {  
	$result .= " " . zul_plural($link_type_id);
      }
    } else {  
      zu_debug('zut_html_list_related rev', $debug);
      $result .= zut_name ($id, $user_id, $debug);
      zu_debug('zut_html_list_related revn', $debug);
      if ($direction == "down") {
	$result .= " " . zul_reverse($link_type_id);
      } else {  
	$result .= " " . zul_name($link_type_id);
      }
    }

    zu_debug('zut_html_list_related link done', $debug);
    
    // display the words
    $result .= '<table class="table col-sm-5 table-borderless">';
    while ($word_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
      $result .= '  <tbody><tr>'."\n";
      $result .= zut_html_tbl($word_entry[0], $word_entry[1], $debug-1);
      zu_debug('zut_html_list_related btn link', $debug);
      $result .= zutl_btn_edit ($word_entry[3], $id, $debug-1);
      zu_debug('zut_html_list_related btn link done', $debug);
      $result .= zut_unlink_html ($word_entry[3], $id, $debug-1);
      zu_debug('zut_html_list_related btn unlink done', $debug);
      $result .= '  </tr>'."\n";
      $result .= '  </tbody>'."\n";
      //$result .= zut_html($word_entry[0], $word_entry[1], $debug);
      // use the last word as a sample for the new word type
      $word_type_id = $word_entry[2];
      if ($link_type_id == cl(SQL_LINK_TYPE_FOLLOW)) {
        $last_linked_word_id = $word_entry[0];
      }  
    }
    zu_debug('zut_html_list_related btn done', $debug);

    // in case of the verb "following" continue the series after the last element
    if ($link_type_id == cl(SQL_LINK_TYPE_FOLLOW)) {
      $start_id = $last_linked_word_id;
/*      if ($directional_link_type_id > 0) {
        $directional_link_type_id = $directional_link_type_id * -1;
      } */
    } else {  
      $start_id = $id;
    }
    
    // give the user the possibility to add a simular word
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= '      '.btn_add ("Add similar word", '/http/word_add.php?verb='.$directional_link_type_id.'&word='.$start_id.'&type='.$word_type_id.'&back='.$start_id);
    $result .= '    </td>';
    $result .= '  </tr>';

    $result .= '</table><br> ';
  
  }

  return $result;
}

// display a list of words that match to the given pattern
function zut_dsp_like ($word_pattern, $user_id, $debug) {
  zu_debug('zut_dsp_like ('.$word_pattern.',u'.$user_id.')', $debug);
  $result  = '';

  $back_link = 1;
  // get the link types related to the word
  $sql = " ( SELECT t.word_id, t.word_name AS name, 'word' AS type
               FROM words t 
              WHERE t.word_name like '".$word_pattern."%' 
                AND t.word_type_id <> ".cl(SQL_WORD_TYPE_FORMULA_LINK).")
     UNION ( SELECT f.formula_id, f.formula_name AS name, 'formula' AS type
               FROM formulas f 
              WHERE f.formula_name like '".$word_pattern."%' )
           ORDER BY name;";
  $sql_result = zu_sql_get_all($sql, $debug-1);

  // loop over the words and display it with the link
  while ($entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    if ($entry[2] == "word") {
      $result .= zut_html($entry[0], $entry[1], $debug-1);
    }  
    if ($entry[2] == "formula") {
      $result .= zuf_dsp($entry[0], $entry[1], $user_id, $back_link, $debug-1);
    }  
  }

  return $result;
}

// list of related words filtered by a link type
function zut_dsp_list_wrd_val ($wrd_id, $col_wrd_id, $user_id, $debug) {
  zu_debug('zut_dsp_list_wrd_val (rt'.$wrd_id.',ct'.$col_wrd_id.',u'.$user_id.')', $debug);
  $result = '';
  
  $result .= zut_dsp_header ($wrd_id, $user_id, $debug);
  
  //$result .= zut_name($wrd_id, $user_id, $debug-1)."<br>";
  //$result .= zut_name($col_wrd_id, $user_id, $debug-1)."<br>";
  
  zu_debug('zut_dsp_list_wrd_val -> get columns "'.implode('","',$col_lst).'"', $debug);
  $row_lst = zut_lst_are($wrd_id, $user_id, $debug-1);
  $col_lst = zut_lst_are($col_wrd_id, $user_id, $debug-1);
  zu_debug('zut_dsp_list_wrd_val -> columns "'.implode('","',$col_lst).'"', $debug);

  asort($row_lst);
  asort($col_lst);
  
  //$val_matrix = zuv_matrix($row_lst, $col_lst, $user_id, $debug-1);
  //$result    .= zuv_dsp_matrix($val_matrix, $user_id, $debug-1);
  
  
  zu_debug('zut_dsp_list_wrd_val -> table', $debug);

  // display the words
  $row_nbr = 0;
  //$result .= '<table style="width:50%">';
  $result .= '<table style="width:50rem">';
  foreach (array_keys($row_lst) AS $row_wrd_id) {
    // display the column headers
    if ($row_nbr == 0) {
      $result .= '  <tr>'."\n";
      $result .= '    <th>'."\n";
      $result .= '    </th>'."\n";
      foreach (array_keys($col_lst) AS $col_wrd_id) {
        $result .= zut_html_tbl_head_right ($col_wrd_id, $col_lst[$col_wrd_id][0], $debug);
      }
      $result .= '  </tr>'."\n";
    }

    // display the row
    $result .= '  <tr>'."\n";
    $result .= '      '.zut_html_tbl($row_wrd_id, $row_lst[$row_wrd_id][0], $debug-1).''."\n";
    foreach (array_keys($col_lst) AS $col_wrd_id) {
      $result .= '    <td>'."\n";
      $val_wrd_ids = array();
      $val_wrd_ids[] = $row_wrd_id;
      $val_wrd_ids[] = $col_wrd_id;
      asort($val_wrd_ids);
      $wrd_grp_id = zut_group_id(implode(",",$val_wrd_ids), $user_id, $debug-1);
      if ($wrd_grp_id > 0) {
        $in_value = zuv_wrd_group_result($wrd_grp_id, 0, $user_id, $debug-1);
/*        if ($wrd_grp_id == 370) {
          echo $wrd_grp_id."<br>";
          $in_value = zuv_wrd_group_result($wrd_grp_id, 0, $user_id, 20);
        } */
        $value = $in_value['num'];
        if ($value <> 0) {
          //$back_link = $row_wrd_id;
          $back_link = $wrd_id;
          if ($in_value['usr'] > 0) {
            $result .= '      <p align="right"><a href="/http/formula_result.php?id='.$in_value['id'].'&word='.$row_wrd_id.'&group='.$wrd_grp_id.'&back='.$back_link.'" class="user_specific">'.round($value,2).'</a></p>'."\n";
          } else {  
            $result .= '      <p align="right"><a href="/http/formula_result.php?id='.$in_value['id'].'&word='.$row_wrd_id.'&group='.$wrd_grp_id.'&back='.$back_link.'">'.round($value,2).'</a></p>'."\n";
          }  
        }
      }
      $result .= '    </td>'."\n";
    }
    $result .= '  </tr>'."\n";
    $row_nbr++;
  }
  $result .= '</table>';
  
/*  
  while ($word_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $result .= zutl_btn_edit ($word_entry[3], $word_id, $debug-1);
    $result .= zut_unlink_html ($word_entry[3], $word_id, $debug-1);
    // use the last word as a sample for the new word type
    $word_type_id = $word_entry[2];
  }

  // give the user the possibility to add a simular word
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      <a href="/http/word_add.php?link='.$link_type_id.'&word='.$word_id.'&type='.$word_type_id.'&back='.$word_id.'"><img src="/images/button_add_small.jpg" alt="add new"></a>';
  $result .= '    </td>';
  $result .= '  </tr>';

  $result .= '</table><br> ';
*/
  return $result;
}

// returns the html code to select a word
// database link must be open
function zut_html_selector_word ($id, $pos, $form_name, $debug) {
  zu_debug('zut_html_selector_word ... word id '.$id, $debug);
  
  //$result = zuh_selector("word",      "word_add", "SELECT word_id, word_name FROM words;", $id);
  if ($pos > 0) {
    $field_id = "word".$pos;
  } else {
    $field_id = "word";
  }
  $result = zuh_selector($field_id, $form_name, "SELECT word_id, word_name FROM words ORDER BY word_name;", $id, "", $debug);
  //zuh_selector ($name, $form, $query, $selected, $debug)
  
  zu_debug('zut_html_selector_word ... done '.$id, $debug);
  return $result;
}

function zut_html_selector_word_time ($id, $debug) {
  zu_debug('zut_html_selector_word_time ... word id '.$id, $debug);
  $result = zuh_selector("word", "word_add", "SELECT word_id, word_name FROM words WHERE word_type_id = 2 ORDER BY word_name;", $id, "", $debug);
  return $result;
}

// to select a existing word to be added
function zut_html_selector_add ($id, $debug) {
  zu_debug('zut_html_selector_add ... word id '.$id, $debug);
  $result = zuh_selector("add", "word_add", "SELECT word_id, word_name FROM words WHERE word_id <> ".$id." ORDER BY word_name;", 0, "... or select an existing word to link it", $debug);
  return $result;
}

// returns the html code to select a word link type
// database link must be open
function zut_dsp_selector_link ($id, $user_id, $back_link, $debug) {
  zu_debug('zut_dsp_selector_link ... word id '.$id, $debug);
  $result = '';
  
  $sql = "SELECT * FROM (
          SELECT verb_id, 
                 IF (name_reverse <> '', CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                 words
            FROM verbs 
    UNION SELECT verb_id*-1, 
                 CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                 words
            FROM verbs 
           WHERE name_reverse <> '' ) AS links
        ORDER BY words DESC, name;";
  $result  = zuh_selector("verb", "word_add", $sql, $id, "", $debug);

  if (zuu_is_admin ($user_id, $debug-1)) {
    // admin users should always have the possibility to create a new link type
    $result .= btn_add ('add new link type', '/http/verb_add.php?back='.$back_link);
  }

  return $result;
}

// similar to zut_dsp_selector_link, but displays only the "forward" links, means not the reverse
function zut_dsp_selector_link_fwd ($id, $back_link, $user_id, $debug) {
  zu_debug('zut_dsp_selector_link ... word id '.$id, $debug);
  $result = '';
  
  $sql = "SELECT * FROM (
          SELECT verb_id, 
                 IF (name_reverse <> '', CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                 words
            FROM verbs ) AS links
        ORDER BY words DESC, name;";
  $result  = zuh_selector("verb", "link_edit", $sql, $id, "", $debug);

  if (zuu_is_admin ($user_id, $debug-1)) {
    // admin users should always have the possibility to create a new link type
    $result .= btn_add ('add new link type', '/http/verb_add.php?back='.$back_link);
  }

  return $result;
}

// returns the html code to select a word link type
// database link must be open
function zut_html_selector_type ($id, $debug) {
  zu_debug('zut_html_selector_type ... word id '.$id, $debug);
  $result = zuh_selector("type", "word_add", "SELECT word_type_id, type_name FROM word_types;", $id, "", $debug);

  return $result;
}


// show a word with its the default view
function zut_dsp ($id, $user_id, $debug) {
  zu_debug('zut_dsp('.$id.')', $debug);
  $result = '';

  // check input and set default if needed
  if ($id      <= 0) { $id      = 1; }
  if ($user_id <= 0) { $user_id = zuu_id(); }
  
  $view_id = zum_default_id($user_id, $id, $debug-1);
  $result .= zum_html($view_id, $id, 0, $debug-1);

  zu_debug('zut_dsp ... done.', $debug-1);
  return $result;
}

// show the html form to add a new word
// add a word with a link type to a word list
// e.g. add time_pos:2013 to the word list if the word list contains "year" and "now" 
// maybe allow to enter the plural and the description in the same view
function zut_dsp_add ($in_word, $in_link, $in_type, $user_id, $back_id, $debug) {
  zu_debug('zut_dsp_add ('.$in_word.','.$in_link.','.$in_link.','.$user_id.','.$back_id.')', $debug);
  $result = '';
  
  $result .= zuh_text_h2('Add ');
  $result .= zuh_form_start("word_add");
  $result .= 'Enter the new word name <input type="text" name="word_name">';
  $result .= zut_html_selector_add ($in_link, $debug-1).' ';
  $result .= ' (as a '.zut_html_selector_type ($in_type, $debug-1).')';
  $result .= '<br><br>';
  $result .= 'which ';
  $result .= zut_dsp_selector_link ($in_link, $user_id, $back_id, $debug-1);
  $result .= zut_html_selector_word ($in_word, 0, "word_add", $debug-1);
  if (trim($back_id) > 0) {
    $result .= '  <input type="hidden" name="back" value="'.$back_id.'">';
  }
  $result .= zuh_form_end();

  zu_debug('zut_dsp_add ... done.', $debug);
  return $result;
}

// show all related word
// should be moved to a view component
function zut_dsp_edit ($wrd_id, $user_id, $back_link, $debug) {
  zu_debug('zut_dsp_edit('.$wrd_id.',u'.$user_id.',b'.$back_link.')', $debug);
  $result = '';
  
  $wrd_name        = zut_name        ($wrd_id, $user_id, $debug-1);
  $wrd_plural      = zut_plural      ($wrd_id, $user_id, $debug-1);
  $wrd_description = zut_description ($wrd_id, $user_id, $debug-1);
  $wrd_url1        = zut_url_1       ($wrd_id,           $debug-1);
  $wrd_url2        = zut_url_2       ($wrd_id,           $debug-1);
  $wrd_type        = zut_type        ($wrd_id, $user_id, $debug-1);

  if ($wrd_id > 0) {
    //zum_entry_word_name ($wrd_id, $debug-1);
    $result .= zuh_text_h2('Change word "'.$wrd_name.'"');
    $result .= zuh_form_start("word_edit");
    $result .= zuh_form_hidden ("id", $wrd_id);
    $result .= zuh_form_hidden ("back", $back_link);
    if ($wrd_type == cl (SQL_WORD_TYPE_FORMULA_LINK)) {
      $result .= zuh_form_hidden ("name", $wrd_name);
      $result .= '  to change the name of "'.$wrd_name.'" rename the ';
      $result .= zuf_dsp(zuf_id($wrd_name, $user_id), "formula", $user_id, $back_link, $debug-1);
      $result .= '.<br> ';
    } else {
      $result .= '  rename to:<input type="text" name="name" value="'.$wrd_name.'">';
    }
    $result .= '  plural:<input type="text" name="plural" value="'.$wrd_plural.'">';
    if ($wrd_type == cl (SQL_WORD_TYPE_FORMULA_LINK)) {
      $result .= ' type: '.zut_type_name($wrd_type, $debug-1);
    } else {
      $result .= zuh_selector("type", "word_edit", "SELECT word_type_id, type_name FROM word_types;", $wrd_type, "", $debug);
    }
    $result .= '<br>';
    $result .= '  description:        <input type="text" name="description" class="resizedTextbox" value="'.$wrd_description.'"><br>';
    $result .= '  wikipedia url:      <input type="text" name="url1"        class="resizedTextbox" value="'.$wrd_url1.'"><br>';
    $result .= '  other reference url:<input type="text" name="url2"        class="resizedTextbox" value="'.$wrd_url2.'"><br>';
    $result .= zuh_form_end();
    $result .= '<br>';
    $result .= zut_html_list_related ($wrd_id, "up",   $user_id, $debug-1);
    $result .= zut_html_list_related ($wrd_id, "down", $user_id, $debug-1);
  }

  // display the user changes 
  $changes = zut_dsp_hist($wrd_id, 20, $back_link, $debug-1);
  if (trim($changes) <> "") {
    $result .= zuh_text_h3("Latest changes related to this word", "change_hist");
    $result .= $changes;
  }
  $changes = zut_dsp_hist_links($wrd_id, 20, $back_link, $debug-1);
  if (trim($changes) <> "") {
    $result .= zuh_text_h3("Latest link changes related to this word", "change_hist");
    $result .= $changes;
  }

  zu_debug('zut_dsp_edit -> done.', $debug-1);
  return $result;
}

// display the history of a word
function zut_dsp_hist($wrd_id, $size, $back_link, $debug) {
  zu_debug("zut_dsp_hist (".$wrd_id.",size".$size.",b".$size.")", $debug);
  $result = ''; // reset the html code var
  
  // get word changes by the user that are not standard
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
           WHERE (f.table_id = ".cl(DBL_SYSLOG_TBL_WORD)." OR f.table_id = ".cl(DBL_SYSLOG_TBL_WORD_USR).")
             AND f.change_field_id  = c.change_field_id 
             AND c.row_id  = ".$wrd_id." 
             AND c.change_action_id = a.change_action_id 
             AND c.user_id = u.user_id 
        ORDER BY c.change_time DESC
           LIMIT ".$size.";";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show where the user uses different word than a normal viewer
  $row_nbr = 0;
  $result .= '<table class="change_hist">';
  while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>time</th>';
      $result .= '<th>user</th>';
      $result .= '<th>from</th>';
      $result .= '<th>to</th>';
    }
    $result .= '</tr><tr>';
    $result .= '<td>'.$wrd_row["time"].'</td>';
    $result .= '<td>'.$wrd_row["user"].'</td>';
    if ($wrd_row["old"] == "") { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$wrd_row["old"].'</td>'; }  
    if ($wrd_row["new"] == "") { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$wrd_row["new"].'</td>'; }  
    $result .= '</tr>';
  }
  $result .= '</table>';

  zu_debug("zut_dsp_hist -> done", $debug-1);
  return $result;
}

// display the history of a word
function zut_dsp_hist_links($wrd_id, $size, $back_link, $debug) {
  zu_debug("zut_dsp_hist_links (".$wrd_id.",size".$size.",b".$size.")", $debug);
  $result = ''; // reset the html code var

  // get changed links related to one word 
  $sql = "SELECT c.change_time AS time, 
                 u.user_name AS user, 
                 a.change_action_name AS type, 
                 c.old_text_from, 
                 c.old_text_link, 
                 c.old_text_to, 
                 c.new_text_from, 
                 c.new_text_link, 
                 c.new_text_to
            FROM change_links c,
                 change_actions a,
                 users u
           WHERE (c.change_table_id = ".cl(DBL_SYSLOG_TBL_WORD)."      OR c.change_table_id = ".cl(DBL_SYSLOG_TBL_WORD_USR)." 
               OR c.change_table_id = ".cl(DBL_SYSLOG_TBL_WORD_LINK)." )
             AND (c.old_from_id = ".$wrd_id." OR c.new_from_id = ".$wrd_id." OR c.old_to_id = ".$wrd_id." OR c.new_to_id = ".$wrd_id.")
             AND c.change_action_id = a.change_action_id 
             AND c.user_id = u.user_id 
        ORDER BY c.change_time DESC
           LIMIT ".$size.";";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // display the changes
  $row_nbr = 0;
  $result .= '<table class="change_hist">';
  while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>time</th>';
      $result .= '<th>user</th>';
      $result .= '<th>from</th>';
      $result .= '<th>to</th>';
    }
    $result .= '</tr><tr>';
    $result .= '<td>'.$wrd_row["time"].'</td>';
    $result .= '<td>'.$wrd_row["user"].'</td>';
    $old_text = trim($wrd_row["old_text_from"]." ".$wrd_row["old_text_link"]." ".$wrd_row["old_text_to"]);
    $new_text = trim($wrd_row["new_text_from"]." ".$wrd_row["new_text_link"]." ".$wrd_row["new_text_to"]);
    if ($old_text == "") { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$old_text.'</td>'; }
    if ($new_text == "") { $result .= '<td>'.$wrd_row["type"].'</td>'; } else { $result .= '<td>'.$new_text.'</td>'; }
    $result .= '</tr>';
  }
  $result .= '</table>';

  zu_debug("zut_dsp_hist_links -> done", $debug-1);
  return $result;
}

// display a botton to edit the word link in a table cell
function zutl_btn_edit ($link_id, $word_id, $debug) {
  zu_debug("zutl_btn_edit (".$link_id.",b".$word_id.")", $debug);
  $result = ''; // reset the html code var

  // get the link from the database
  $result .= '    <td>'."\n";
  $result .= btn_edit ("edit word link", "/http/link_edit.php?id=".$link_id."&back=".$word_id);
  $result .= '    </td>'."\n";

  zu_debug("zutl_btn_edit done", $debug);
  return $result;
}

// return the word name for more than one
function zut_plural ($wrd_id, $user_id, $debug) {
  zu_debug('zut_plural ('.$wrd_id.',u'.$user_id.')', $debug);
  $result = NULL;
  if ($wrd_id > 0) {
    $wrd_del  = zu_sql_get1 ("SELECT word_id FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id." AND excluded = 1;", $debug-10);
    // only return a word if the user has not yet excluded the word
    if ($wrd_id <> $wrd_del) {
      $result = zu_sql_get1 ("SELECT plural FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id." AND (excluded is NULL OR excluded = 0);", $debug-10);
      if ($result == NULL) {
        $result = zu_sql_get_field ('word', $wrd_id, 'plural', $debug-10);
      }  
    }
  }

  zu_debug('zut_plural ('.$wrd_id.'->'.$result.')', $debug);
  return $result;
}

// return the word name for the user
// todo: combine to one query
function zut_name ($wrd_id, $user_id, $debug) {
  zu_debug('zut_name ('.$wrd_id.',u'.$user_id.')', $debug);
  $result = NULL;
  if ($wrd_id > 0) {
    if ($user_id > 0) {
      $wrd_del  = zu_sql_get1 ("SELECT word_id FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id." AND excluded = 1;", $debug-10);
      // only return a word if the user has not yet excluded the word
      if ($wrd_id <> $wrd_del) {
        $result = zu_sql_get1 ("SELECT word_name FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id." AND (excluded is NULL OR excluded = 0);", $debug-10);
        if ($result == NULL) {
          $result = zu_sql_get_name ('word', $wrd_id, $debug-10);
        }  
      }
    } else {
      // if no user is selected, simply return the standard name
      $result = zu_sql_get_name ('word', $wrd_id, $debug-10);
    }
  }

  zu_debug('zut_name ('.$wrd_id.'->'.$result.')', $debug);
  return $result;
}

// return the word name for more than one
function zut_description ($wrd_id, $user_id, $debug) {
  zu_debug('zut_description ('.$wrd_id.',u'.$user_id.')', $debug);
  $result = NULL;
  if ($wrd_id > 0) {
    $wrd_del  = zu_sql_get1 ("SELECT word_id FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id." AND excluded = 1;", $debug-10);
    // only return a word if the user has not yet excluded the word
    if ($wrd_id <> $wrd_del) {
      $result = zu_sql_get1 ("SELECT description FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id." AND (excluded is NULL OR excluded = 0);", $debug-10);
      if ($result == NULL) {
        $result = zu_sql_get_field ('word', $wrd_id, 'description', $debug-10);
      }  
    }
  }

  zu_debug('zut_description ('.$wrd_id.'->'.$result.')', $debug);
  return $result;
}



?>
