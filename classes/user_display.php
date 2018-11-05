<?php

/*

  user.php - to display the user specific settings
  --------
  
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

class user_dsp extends user {

  // display a form with the user parameters such as name or email
  // add back here ???
  function dsp_edit ($debug) {
    zu_debug('user_dsp->dsp_edit(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // display the user fields using a table and not using px in css to be independend from any screen solution
    $result .= dsp_text_h2('User "'.$this->name.'"');
    $result .= dsp_form_start("user");
    $result .= '<table>';
    $result .=                             '<input type="hidden" name="id"    value="'.$this->id.'">';
    $result .= '<tr><td>username  </td><td> <input type="text"   name="name"  value="'.$this->name.'"></td></tr>';
    $result .= '<tr><td>email     </td><td> <input type="text"   name="email" value="'.$this->email.'"></td></tr>';
    $result .= '<tr><td>first name</td><td> <input type="text"   name="fname" value="'.$this->first_name.'"></td></tr>';
    $result .= '<tr><td>last name </td><td> <input type="text"   name="lname" value="'.$this->last_name.'"></td></tr>';
    $result .= '</table>';
    $result .= dsp_form_end();
    
    zu_debug('user_dsp->dsp_edit -> done.', $debug-1);
    return $result;
  }

  // display the latest changes by the user
  function dsp_changes ($page, $size, $call, $back, $debug) {
    zu_debug('user_dsp->dsp_changes (u'.$this->id.',b'.$back.')', $debug-10);
    $result = ''; // reset the html code var

    // get value changes by the user that are not standard
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this;
    $log_dsp->type = 'user';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist($debug-1);
    
    zu_debug('user_dsp->dsp_changes -> done.', $debug-1);
    return $result;
  }

  // display the error that are related to the user, so that he can track when they are closed
  // or display the error that are related to the user, so that he can track when they are closed
  function dsp_errors ($dsp_type, $back, $debug) {
    zu_debug('user_dsp->dsp_errors '.$dsp_type.' errors for user '.$this->name.'.', $debug-10);

    $err_lst = New system_error_log_list;
    $err_lst->usr      = $this;
    $err_lst->dsp_type = $dsp_type;
    $err_lst->back     = $back;
    $result = $err_lst->display($debug-1);
    
    zu_debug('user_dsp->dsp_errors -> done.', $debug-12);
    return $result;
  }

  // display word changes by the user which are not (yet) standard 
  function dsp_sandbox_wrd ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_wrd(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // get word changes by the user that are not standard
    $sql = "SELECT u.word_name AS usr_word_name, 
                   t.word_name, 
                   t.word_id 
              FROM user_words u,
                   words t
             WHERE u.user_id = ".$this->id."
               AND u.word_id = t.word_id;";
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    $wrd_lst = $db_con->get($sql, $debug-5);  

    // prepare to show the word link
    $row_nbr = 0;
    $result .= '<table>';
    foreach ($wrd_lst AS $wrd_row) {
      $row_nbr++;
      $result .= '<tr>';
      if ($row_nbr == 1) {
        $result .= '<th>Your name vs. </th><th>common name</th></tr><tr>';
      }
      $result .= '<td>'.$wrd_row['usr_word_name'].'</td><td>'.$wrd_row['word_name'].'</td>';
      //$result .= '<td><a href="/http/user.php?id='.$this->id.'&undo_word='.$log_row['type_table'].'&back='.$id.'"><img src="../images/button_del_small.jpg" alt="undo change"></a></td>';
      $url = '/http/user.php?id='.$this->id.'&undo_word='.$wrd_row['word_id'].'&back='.$id.'';
      $result .= '<td>'.btn_del("Undo your change and use the standard word ".$wrd_row['word_name'], $url).'</td>';
      $result .= '</tr>';
    }
    $result .= '</table>';
    
    zu_debug('user_dsp->dsp_sandbox_wrd -> done.', $debug-1);
    return $result;
  }

  // display word_link changes by the user which are not (yet) standard 
  function dsp_sandbox_wrd_link ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_wrd_link(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard word_link
    $sql = "SELECT u.word_link_id AS id, 
                   l.user_id      AS owner_id, 
                   l.from_phrase_id, 
                   l.verb_id, 
                   l.to_phrase_id, 
                   IF(u.name IS NULL,     l.name,     u.name)     AS usr_name, 
                   l.name                                         AS std_name, 
                   IF(u.excluded IS NULL, l.excluded, u.excluded) AS usr_excluded,
                   l.excluded                                     AS std_excluded
              FROM user_word_links u,
                   word_links l
             WHERE u.user_id = ".$this->id."
               AND u.word_link_id = l.word_link_id;";
    $sbx_lst = $db_con->get($sql, $debug-5);  

    if (count($sbx_lst) > 0) {
      // prepare to show where the user uses different word_enty_link than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($sbx_lst AS $sbx_row) {
        $row_nbr++;
        
        // create the word_link objects with the minimal parameter needed
        $wrd_usr = New word_link;
        $wrd_usr->id       = $sbx_row['id'];
        $wrd_usr->from_id  = $sbx_row['from_phrase_id'];
        $wrd_usr->verb_id  = $sbx_row['verb_id'];
        $wrd_usr->to_id    = $sbx_row['to_phrase_id'];
        $wrd_usr->name     = $sbx_row['usr_name'];
        $wrd_usr->excluded = $sbx_row['usr_excluded'];
        $wrd_usr->usr = $this;
        $wrd_usr->load($debug-1);

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $sbx_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $wrd_std = clone $wrd_usr;
        $wrd_std->usr      = $usr_std;
        $wrd_std->load($debug-1);
        $wrd_std->name     = $sbx_row['std_name'];
        $wrd_std->excluded = $sbx_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($wrd_usr->name     == $wrd_std->name
        AND $wrd_usr->excluded == $wrd_std->excluded) {
          $wrd_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row word_links
          //$sandbox_item_name = $wrd_usr->name_linked($back, $debug-1);
          
          // format the user word_link
          if ($wrd_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $wrd_usr->name($debug-1);
          }
          
          // format the standard word_link
          if ($wrd_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $wrd_std->name($debug-1);
          }
            
          // format the word_link of other users
          $sandbox_other = '';
          $sql_other = "SELECT l.word_link_id, 
                               u.user_id, 
                               u.name, 
                               u.excluded
                          FROM user_word_links u,
                               word_links l
                         WHERE u.user_id <> ".$this->id."
                           AND u.word_link_id = l.word_link_id
                           AND u.word_link_id = ".$sbx_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $sbx_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($sbx_lst_other AS $wrd_lnk_other_row) {
            $usr_other = New user;
            $usr_other->id = $wrd_lnk_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user word_links with one query
            $wrd_lnk_other = clone $wrd_usr;
            $wrd_lnk_other->usr      = $usr_other;
            $wrd_lnk_other->load($debug-1);
            $wrd_lnk_other->name     = $wrd_lnk_other_row['name'];
            $wrd_lnk_other->excluded = $wrd_lnk_other_row['excluded'];
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $wrd_lnk_other->name($debug-1);
          }
          $sandbox_other = '<a href="/http/user_word_link.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_triple='.$sbx_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard word_link ".$sbx_row['std_word_link'], $url).'</td>';
          
          // display the word_link changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            //$result .= '<th>Triple</th>';
            $result .= '<th>Your triple vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          // display one user adjustment
          //$result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
        
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_wrd_link -> done.', $debug-1);
    return $result;
  }

  // display formula changes by the user which are not (yet) standard 
  function dsp_sandbox_frm ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_frm(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // get word changes by the user that are not standard
    $sql = "SELECT u.formula_name, 
                  u.resolved_text AS usr_formula_text, 
                  f.resolved_text AS formula_text, 
                  f.formula_id 
              FROM user_formulas u,
                  formulas f
            WHERE u.user_id = ".$this->id."
              AND u.formula_id = f.formula_id;";
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    $frm_lst = $db_con->get($sql, $debug-5);  

    // prepare to show the word link
    $row_nbr = 0;
    $result .= '<table>';
    foreach ($frm_lst AS $frm_row) {
      $row_nbr++;
      $result .= '<tr>';
      if ($row_nbr == 1) {
        $result .= '<th>Formula name </th>';
        $result .= '<th>Your formula vs. </th>';
        $result .= '<th>common formula</th>';
        $result .= '</tr><tr>';
      }
      $result .= '<td>'.$frm_row['formula_name'].'</td>';
      $result .= '<td>'.$frm_row['usr_formula_text'].'</td>';
      $result .= '<td>'.$frm_row['formula_text'].'</td>';
      //$result .= '<td><a href="/http/user.php?id='.$this->id.'&undo_formula='.$frm_row['formula_id'].'&back='.$id.'"><img src="../images/button_del_small.jpg" alt="undo change"></a></td>';
      $url = '/http/user.php?id='.$this->id.'&undo_formula='.$frm_row['formula_id'].'&back='.$id.'';
      $result .= '<td>'.btn_del("Undo your change and use the standard formula ".$frm_row['formula_text'], $url).'</td>';
      $result .= '</tr>';
    }
    $result .= '</table>';
    
    zu_debug('user_dsp->dsp_sandbox_frm -> done.', $debug-1);
    return $result;
  }

  // display formula_link changes by the user which are not (yet) standard 
  function dsp_sandbox_frm_link ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_frm_link(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard formula_link
    $sql = "SELECT u.formula_link_id AS id, 
                   l.user_id              AS owner_id, 
                   l.formula_id, 
                   l.phrase_id, 
                   IF(u.link_type_id IS NULL, l.link_type_id, u.link_type_id) AS usr_type, 
                   l.link_type_id                                             AS std_type, 
                   IF(u.excluded IS NULL,     l.excluded,     u.excluded)     AS usr_excluded,
                   l.excluded                                                 AS std_excluded
              FROM user_formula_links u,
                   formula_links l
             WHERE u.user_id = ".$this->id."
               AND u.formula_link_id = l.formula_link_id;";
    $sbx_lst = $db_con->get($sql, $debug-5);  

    if (count($sbx_lst) > 0) {
      // prepare to show where the user uses different formula_enty_link than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($sbx_lst AS $sbx_row) {
        $row_nbr++;
        
        // create the formula_link objects with the minimal parameter needed
        $frm_usr = New formula_link;
        $frm_usr->id           = $sbx_row['id'];
        $frm_usr->formula_id   = $sbx_row['formula_id'];
        $frm_usr->phrase_id    = $sbx_row['phrase_id'];
        $frm_usr->link_type_id = $sbx_row['usr_type'];
        $frm_usr->excluded     = $sbx_row['usr_excluded'];
        $frm_usr->usr = $this;
        $frm_usr->load_objects($debug-1);

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $sbx_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $frm_std = clone $frm_usr;
        $frm_std->usr          = $usr_std;
        $frm_std->link_type_id = $sbx_row['std_type'];
        $frm_std->excluded     = $sbx_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($frm_usr->link_type_id == $frm_std->link_type_id
        AND $frm_usr->excluded     == $frm_std->excluded) {
          $frm_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row formula_links
          $sandbox_item_name = $frm_usr->frm->name_linked($back, $debug-1);
          //$sandbox_item_name = $frm_usr->name_linked($back, $debug-1);
          
          // format the user formula_link
          if ($frm_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $frm_usr->wrd->dsp_link($debug-1);
            //$sandbox_usr_txt = $frm_usr->link_name;
          }
          
          // format the standard formula_link
          if ($frm_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $frm_std->wrd->dsp_link($debug-1);
            //$sandbox_std_txt = $frm_std->link_name;
          }
            
          // format the formula_link of other users
          $sandbox_other = '';
          $sql_other = "SELECT l.formula_link_id, 
                               u.user_id, 
                               u.link_type_id, 
                               u.excluded
                          FROM user_formula_links u,
                               formula_links l
                         WHERE u.user_id <> ".$this->id."
                           AND u.formula_link_id = l.formula_link_id
                           AND u.formula_link_id = ".$sbx_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $sbx_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($sbx_lst_other AS $frm_lnk_other_row) {
            $usr_other = New user;
            $usr_other->id = $frm_lnk_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user formula_links with one query
            $frm_lnk_other = clone $frm_usr;
            $frm_lnk_other->usr = $usr_other;
            $frm_lnk_other->link_type_id = $frm_lnk_other_row['link_type_id'];
            $frm_lnk_other->excluded     = $frm_lnk_other_row['excluded'];
            $frm_lnk_other->load_objects($debug-1);
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $frm_lnk_other->wrd->dsp_link($debug-1);
          }
          $sandbox_other = '<a href="/http/user_formula_link.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_formula_link='.$sbx_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard formula_link ".$sbx_row['std_formula_link'], $url).'</td>';
          
          // display the formula_link changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            $result .= '<th>Formula</th>';
            $result .= '<th>you linked to word vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          // display one user adjustment
          $result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
        
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_frm_link -> done.', $debug-1);
    return $result;
  }

  // display value changes by the user which are not (yet) standard 
  function dsp_sandbox_val ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_val(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard value
    $sql = "SELECT u.value_id   AS id, 
                   v.user_id    AS owner_id, 
                   IF(u.user_value IS NULL,    v.word_value,    u.user_value) AS usr_value, 
                   v.word_value                                               AS std_value, 
                   IF(u.source_id IS NULL,     v.source_id,     u.source_id)  AS usr_source, 
                   v.source_id                                                AS std_source, 
                   IF(u.excluded IS NULL,      v.excluded,      u.excluded)   AS usr_excluded,
                   v.excluded                                                 AS std_excluded, 
                   v.phrase_group_id,
                   v.time_word_id
              FROM user_values u,
                  `values` v
             WHERE u.user_id = ".$this->id."
               AND u.value_id = v.value_id;";
    $val_lst = $db_con->get($sql, $debug-5);  

    if (count($val_lst) > 0) {
      // prepare to show where the user uses different value than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($val_lst AS $val_row) {
        $row_nbr++;
      
        // create the value objects with the minimal parameter needed
        $val_usr = New value;
        $val_usr->id        = $val_row['id'];
        $val_usr->number    = $val_row['usr_value'];
        $val_usr->source_id = $val_row['usr_value'];
        $val_usr->excluded  = $val_row['usr_excluded'];
        $val_usr->grp_id    = $val_row['phrase_group_id'];
        $val_usr->time_id   = $val_row['time_word_id'];
        $val_usr->usr = $this;
        $val_usr->load_phrases($debug-1);

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $val_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $val_std = clone $val_usr;
        $val_std->usr       = $usr_std;
        $val_std->number    = $val_row['std_value'];
        $val_std->source_id = $val_row['std_excluded'];
        $val_std->excluded  = $val_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($val_usr->number    == $val_std->number
        AND $val_usr->source_id == $val_std->source_id
        AND $val_usr->excluded  == $val_std->excluded) {
          $val_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row values
          $sandbox_item_name = '';
          if (isset($val_usr->wrd_lst)) {
            $sandbox_item_name = $val_usr->wrd_lst->name_linked($debug-1);
          } 
          
          // format the user value
          if ($val_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $val_usr->val_formatted($debug-1);
          }
          $sandbox_usr_txt = '<a href="/http/value_edit.php?id='.$val_usr->id.'&back='.$back.'">'.$sandbox_usr_txt.'</a>';
          
          // format the standard value
          if ($val_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $val_std->val_formatted($debug-1);
          }
                        
          // format the value of other users
          $sandbox_other = '';
          $sql_other = "SELECT v.value_id, 
                               u.user_id, 
                               u.user_value, 
                               u.source_id, 
                               u.excluded
                          FROM user_values u,
                               `values` v
                         WHERE u.user_id <> ".$this->id."
                           AND u.value_id = v.value_id
                           AND u.value_id = ".$val_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $val_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($val_lst_other AS $val_other_row) {
            $usr_other = New user;
            $usr_other->id = $val_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user values with one query
            $val_other = clone $val_usr;
            $val_other->usr = $usr_other;
            $val_other->number    = $val_other_row['user_value'];
            $val_other->source_id = $val_other_row['source_id'];
            $val_other->excluded  = $val_other_row['excluded'];
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $val_other->val_formatted($debug-1);
          }
          $sandbox_other = '<a href="/http/user_value.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_value='.$val_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard value ".$val_row['std_value'], $url).'</td>';
          
          // display the value changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            $result .= '<th>Value</th>';
            $result .= '<th>your vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          //
          $result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_val -> done.', $debug-1);
    return $result;
  }

  // display view changes by the user which are not (yet) standard 
  function dsp_sandbox_view ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_view(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard view
    $sql = "SELECT u.view_id AS id, 
                   m.user_id AS owner_id, 
                   IF(u.view_name IS NULL,    m.view_name,    u.view_name)    AS usr_name, 
                   m.view_name                                                AS std_name, 
                   IF(u.comment IS NULL,      m.comment,      u.comment  )    AS usr_comment, 
                   m.comment                                                  AS std_comment, 
                   IF(u.view_type_id IS NULL, m.view_type_id, u.view_type_id) AS usr_type, 
                   m.view_type_id                                             AS std_type, 
                   IF(u.excluded IS NULL,     m.excluded,     u.excluded)     AS usr_excluded,
                   m.excluded                                                 AS std_excluded
              FROM user_views u,
                   views m
             WHERE u.user_id = ".$this->id."
               AND u.view_id = m.view_id;";
    $sbx_lst = $db_con->get($sql, $debug-5);  

    if (count($sbx_lst) > 0) {
      // prepare to show where the user uses different view than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($sbx_lst AS $sbx_row) {
        $row_nbr++;
      
        // create the view objects with the minimal parameter needed
        $dsp_usr = new view_dsp;
        $dsp_usr->id       = $sbx_row['id'];
        $dsp_usr->name     = $sbx_row['usr_name'];
        $dsp_usr->comment  = $sbx_row['usr_comment'];
        $dsp_usr->type_id  = $sbx_row['usr_type'];
        $dsp_usr->excluded = $sbx_row['usr_excluded'];
        $dsp_usr->usr = $this;

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $sbx_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $dsp_std = clone $dsp_usr;
        $dsp_std->usr       = $usr_std;
        $dsp_std->name      = $sbx_row['std_name'];
        $dsp_std->comment   = $sbx_row['std_comment'];
        $dsp_std->type_id   = $sbx_row['std_type'];
        $dsp_std->excluded  = $sbx_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($dsp_usr->name     == $dsp_std->name
        AND $dsp_usr->comment  == $dsp_std->comment
        AND $dsp_usr->type_id  == $dsp_std->type_id
        AND $dsp_usr->excluded == $dsp_std->excluded) {
          $dsp_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row views
          $sandbox_item_name = $dsp_usr->name;
          
          // format the user view
          if ($dsp_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $dsp_usr->name;
          }
          $sandbox_usr_txt = '<a href="/http/view_edit.php?id='.$dsp_usr->id.'&back='.$back.'">'.$sandbox_usr_txt.'</a>';
          
          // format the standard view
          if ($dsp_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $dsp_std->name;
          }
            
          // format the view of other users
          $sandbox_other = '';
          $sql_other = "SELECT m.view_id, 
                               u.user_id, 
                               u.view_name, 
                               u.comment, 
                               u.view_type_id, 
                               u.excluded
                          FROM user_views u,
                               views m
                         WHERE u.user_id <> ".$this->id."
                           AND u.view_id = m.view_id
                           AND u.view_id = ".$sbx_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $sbx_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($sbx_lst_other AS $dsp_other_row) {
            $usr_other = New user;
            $usr_other->id = $dsp_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user views with one query
            $dsp_other = clone $dsp_usr;
            $dsp_other->usr = $usr_other;
            $dsp_other->name     = $dsp_other_row['view_name'];
            $dsp_other->comment  = $dsp_other_row['comment'];
            $dsp_other->type_id  = $dsp_other_row['view_type_id'];
            $dsp_other->excluded = $dsp_other_row['excluded'];
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $dsp_other->name;
          }
          $sandbox_other = '<a href="/http/user_view.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_view='.$sbx_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard view ".$sbx_row['std_view'], $url).'</td>';
          
          // display the view changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            $result .= '<th>View name vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          //
          //$result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_view -> done.', $debug-1);
    return $result;
  }

  // display view_entry changes by the user which are not (yet) standard 
  function dsp_sandbox_view_entry ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_view_entry(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard view_entry
    $sql = "SELECT u.view_entry_id AS id, 
                   m.user_id AS owner_id, 
                   IF(u.view_entry_name IS NULL,    m.view_entry_name,    u.view_entry_name)    AS usr_name, 
                   m.view_entry_name                                                            AS std_name, 
                   IF(u.comment IS NULL,            m.comment,            u.comment)            AS usr_comment, 
                   m.comment                                                                    AS std_comment, 
                   IF(u.view_entry_type_id IS NULL, m.view_entry_type_id, u.view_entry_type_id) AS usr_type, 
                   m.view_entry_type_id                                                         AS std_type, 
                   IF(u.excluded IS NULL,           m.excluded,           u.excluded)           AS usr_excluded,
                   m.excluded                                                                   AS std_excluded
              FROM user_view_entries u,
                   view_entries m
             WHERE u.user_id = ".$this->id."
               AND u.view_entry_id = m.view_entry_id;";
    $sbx_lst = $db_con->get($sql, $debug-5);  

    if (count($sbx_lst) > 0) {
      // prepare to show where the user uses different view_entry than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($sbx_lst AS $sbx_row) {
        $row_nbr++;
      
        // create the view_entry object with the minimal parameter needed
        $dsp_usr = new view_component_dsp;
        $dsp_usr->id       = $sbx_row['id'];
        $dsp_usr->name     = $sbx_row['usr_name'];
        $dsp_usr->comment  = $sbx_row['usr_comment'];
        $dsp_usr->type_id  = $sbx_row['usr_type'];
        $dsp_usr->excluded = $sbx_row['usr_excluded'];
        $dsp_usr->usr = $this;

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $sbx_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $dsp_std = clone $dsp_usr;
        $dsp_std->usr       = $usr_std;
        $dsp_std->name      = $sbx_row['std_name'];
        $dsp_std->comment   = $sbx_row['std_comment'];
        $dsp_std->type_id   = $sbx_row['std_type'];
        $dsp_std->excluded  = $sbx_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($dsp_usr->name     == $dsp_std->name
        AND $dsp_usr->comment  == $dsp_std->comment
        AND $dsp_usr->type_id  == $dsp_std->type_id
        AND $dsp_usr->excluded == $dsp_std->excluded) {
          //$dsp_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row view_entrys
          $sandbox_item_name = $dsp_usr->name;
          
          // format the user view_entry
          if ($dsp_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $dsp_usr->name;
          }
          $sandbox_usr_txt = '<a href="/http/view_component_edit.php?id='.$dsp_usr->id.'&back='.$back.'">'.$sandbox_usr_txt.'</a>';
          
          // format the standard view_entry
          if ($dsp_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $dsp_std->name;
          }
            
          // format the view_entry of other users
          $sandbox_other = '';
          $sql_other = "SELECT m.view_entry_id, 
                               u.user_id, 
                               u.view_entry_name, 
                               u.comment, 
                               u.view_entry_type_id, 
                               u.excluded
                          FROM user_view_entries u,
                               view_entries m
                         WHERE u.user_id <> ".$this->id."
                           AND u.view_entry_id = m.view_entry_id
                           AND u.view_entry_id = ".$sbx_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $sbx_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($sbx_lst_other AS $cmp_other_row) {
            $usr_other = New user;
            $usr_other->id = $cmp_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user view_entrys with one query
            $cmp_other = clone $dsp_usr;
            $cmp_other->usr = $usr_other;
            $cmp_other->name     = $cmp_other_row['view_entry_name'];
            $cmp_other->comment  = $cmp_other_row['comment'];
            $cmp_other->type_id  = $cmp_other_row['view_entry_type_id'];
            $cmp_other->excluded = $cmp_other_row['excluded'];
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $cmp_other->name;
          }
          $sandbox_other = '<a href="/http/user.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_view_entry='.$sbx_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard view_entry ".$sbx_row['std_view_entry'], $url).'</td>';
          
          // display the view_entry changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            $result .= '<th>View component vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          //
          //$result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_view_entry -> done.', $debug-1);
    return $result;
  }

  // display view_entry_link changes by the user which are not (yet) standard 
  function dsp_sandbox_view_link ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_view_link(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard view_entry_link
    $sql = "SELECT u.view_entry_link_id AS id, 
                   l.user_id            AS owner_id, 
                   l.view_id, 
                   l.view_entry_id, 
                   IF(u.order_nbr IS NULL,     l.order_nbr,     u.order_nbr)     AS usr_order, 
                   l.order_nbr                                                   AS std_order, 
                   IF(u.position_type IS NULL, l.position_type, u.position_type) AS usr_type, 
                   l.position_type                                               AS std_type, 
                   IF(u.excluded IS NULL,      l.excluded,      u.excluded)      AS usr_excluded,
                   l.excluded                                                    AS std_excluded
              FROM user_view_entry_links u,
                   view_entry_links l
             WHERE u.user_id = ".$this->id."
               AND u.view_entry_link_id = l.view_entry_link_id;";
    $sbx_lst = $db_con->get($sql, $debug-5);  

    if (count($sbx_lst) > 0) {
      // prepare to show where the user uses different view_enty_link than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($sbx_lst AS $sbx_row) {
        $row_nbr++;
      
        // create the view_entry_link objects with the minimal parameter needed
        $dsp_usr = new view_component_link;
        $dsp_usr->id            = $sbx_row['id'];
        $dsp_usr->view_id       = $sbx_row['view_id'];
        $dsp_usr->view_entry_id = $sbx_row['view_entry_id'];
        $dsp_usr->order_nbr     = $sbx_row['usr_order'];
        $dsp_usr->position_type = $sbx_row['usr_type'];
        $dsp_usr->excluded      = $sbx_row['usr_excluded'];
        $dsp_usr->usr = $this;
        $dsp_usr->load_objects($debug-1);

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $sbx_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $dsp_std = clone $dsp_usr;
        $dsp_std->usr           = $usr_std;
        $dsp_std->order_nbr     = $sbx_row['std_order'];
        $dsp_std->position_type = $sbx_row['std_type'];
        $dsp_std->excluded      = $sbx_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($dsp_usr->order_nbr     == $dsp_std->order_nbr
        AND $dsp_usr->position_type == $dsp_std->position_type
        AND $dsp_usr->excluded      == $dsp_std->excluded) {
          $dsp_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row view_entry_links
          $sandbox_item_name = $dsp_usr->name_linked($back, $debug-1);
          
          // format the user view_entry_link
          if ($dsp_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $dsp_usr->order_nbr;
          }
          
          // format the standard view_entry_link
          if ($dsp_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $dsp_std->order_nbr;
          }
            
          // format the view_entry_link of other users
          $sandbox_other = '';
          $sql_other = "SELECT l.view_entry_link_id, 
                               u.user_id, 
                               u.order_nbr, 
                               u.position_type, 
                               u.excluded
                          FROM user_view_entry_links u,
                               view_entry_links l
                         WHERE u.user_id <> ".$this->id."
                           AND u.view_entry_link_id = l.view_entry_link_id
                           AND u.view_entry_link_id = ".$sbx_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $sbx_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($sbx_lst_other AS $dsp_lnk_other_row) {
            $usr_other = New user;
            $usr_other->id = $dsp_lnk_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user view_entry_links with one query
            $dsp_lnk_other = clone $dsp_usr;
            $dsp_lnk_other->usr = $usr_other;
            $dsp_lnk_other->order_nbr     = $dsp_lnk_other_row['order_nbr'];
            $dsp_lnk_other->position_type = $dsp_lnk_other_row['position_type'];
            $dsp_lnk_other->excluded      = $dsp_lnk_other_row['excluded'];
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $dsp_lnk_other->name;
          }
          $sandbox_other = '<a href="/http/user_view_entry_link.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_view_entry_link='.$sbx_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard view_entry_link ".$sbx_row['std_view_entry_link'], $url).'</td>';
          
          // display the view_entry_link changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            $result .= '<th>View link</th>';
            $result .= '<th>your position vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          // display one user adjustment
          $result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
        
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_view_link -> done.', $debug-1);
    return $result;
  }

  // display source changes by the user which are not (yet) standard 
  function dsp_sandbox_source ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox_source(u'.$this->id.')', $debug-10);
    $result = ''; // reset the html code var

    // create the databased link
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    
    // get all values changed by the user to a non standard source
    $sql = "SELECT u.source_id AS id, 
                   m.user_id AS owner_id, 
                   IF(u.source_name IS NULL,    m.source_name,    u.source_name)    AS usr_name, 
                   m.source_name                                                    AS std_name, 
                   IF(u.url IS NULL,            m.url,            u.url  )          AS usr_url, 
                   m.url                                                            AS std_url, 
                   IF(u.comment IS NULL,        m.comment,        u.comment)        AS usr_comment, 
                   m.comment                                                        AS std_comment, 
                   IF(u.source_type_id IS NULL, m.source_type_id, u.source_type_id) AS usr_type, 
                   m.source_type_id                                                 AS std_type, 
                   IF(u.excluded IS NULL,       m.excluded,       u.excluded)       AS usr_excluded,
                   m.excluded                                                       AS std_excluded
              FROM user_sources u,
                   sources m
             WHERE u.user_id = ".$this->id."
               AND u.source_id = m.source_id;";
    $sbx_lst = $db_con->get($sql, $debug-5);  

    if (count($sbx_lst) > 0) {
      // prepare to show where the user uses different source than a normal viewer
      $row_nbr = 0;
      $result .= '<table>';
      foreach ($sbx_lst AS $sbx_row) {
        $row_nbr++;
      
        // create the source objects with the minimal parameter needed
        $dsp_usr = New source;
        $dsp_usr->id       = $sbx_row['id'];
        $dsp_usr->name     = $sbx_row['usr_name'];
        $dsp_usr->url      = $sbx_row['usr_url'];
        $dsp_usr->comment  = $sbx_row['usr_comment'];
        $dsp_usr->type_id  = $sbx_row['usr_type'];
        $dsp_usr->excluded = $sbx_row['usr_excluded'];
        $dsp_usr->usr = $this;

        // to review: try to avoid using load_test_user
        $usr_std = New user;
        $usr_std->id = $sbx_row['owner_id'];
        $usr_std->load_test_user($debug-1);

        $dsp_std = clone $dsp_usr;
        $dsp_std->usr       = $usr_std;
        $dsp_std->name      = $sbx_row['std_name'];
        $dsp_std->url       = $sbx_row['std_url'];
        $dsp_std->comment   = $sbx_row['std_comment'];
        $dsp_std->type_id   = $sbx_row['std_type'];
        $dsp_std->excluded  = $sbx_row['std_excluded'];
          
        // check database consistency and correct it if needed
        if ($dsp_usr->name     == $dsp_std->name
        AND $dsp_usr->url      == $dsp_std->url
        AND $dsp_usr->comment  == $dsp_std->comment
        AND $dsp_usr->type_id  == $dsp_std->type_id
        AND $dsp_usr->excluded == $dsp_std->excluded) {
          $dsp_usr->del_usr_cfg($debug-1);
        } else {
        
          // prepare the row sources
          $sandbox_item_name = $dsp_usr->name;
          
          // format the user source
          if ($dsp_usr->excluded == 1) {
            $sandbox_usr_txt = "deleted";
          } else {
            $sandbox_usr_txt = $dsp_usr->name;
          }
          $sandbox_usr_txt = '<a href="/http/source_edit.php?id='.$dsp_usr->id.'&back='.$back.'">'.$sandbox_usr_txt.'</a>';
          
          // format the standard source
          if ($dsp_std->excluded == 1) {
            $sandbox_std_txt = "deleted";
          } else {
            $sandbox_std_txt = $dsp_std->name;
          }
            
          // format the source of other users
          $sandbox_other = '';
          $sql_other = "SELECT m.source_id, 
                               u.user_id, 
                               u.source_name, 
                               u.url, 
                               u.comment, 
                               u.source_type_id, 
                               u.excluded
                          FROM user_sources u,
                               sources m
                         WHERE u.user_id <> ".$this->id."
                           AND u.source_id = m.source_id
                           AND u.source_id = ".$sbx_row['id']."
                           AND u.excluded <> 1;";
          zu_debug('user_dsp->dsp_sandbox_val other sql ('.$sql_other.')', $debug-10);
          $sbx_lst_other = $db_con->get($sql_other, $debug-5);  
          foreach ($sbx_lst_other AS $dsp_other_row) {
            $usr_other = New user;
            $usr_other->id = $dsp_other_row['user_id'];
            $usr_other->load_test_user($debug-1);

            // to review: load all user sources with one query
            $dsp_other = clone $dsp_usr;
            $dsp_other->usr = $usr_other;
            $dsp_other->name     = $dsp_other_row['source_name'];
            $dsp_other->url      = $dsp_other_row['url'];
            $dsp_other->comment  = $dsp_other_row['comment'];
            $dsp_other->type_id  = $dsp_other_row['source_type_id'];
            $dsp_other->excluded = $dsp_other_row['excluded'];
            if ($sandbox_other <> '') {
              $sandbox_other .= ',';
            }
            $sandbox_other .= $dsp_other->name;
          }
          $sandbox_other = '<a href="/http/user_source.php?id='.$this->id.'&back='.$back.'">'.$sandbox_other.'</a> ';
          
          // create the button
          $sandbox_undo_btn = '';
          $url = '/http/user.php?id='.$this->id.'&undo_source='.$sbx_row['id'].'&back='.$back;
          $sandbox_undo_btn = '<td>'.btn_del("Undo your change and use the standard source ".$sbx_row['std_source'], $url).'</td>';
          
          // display the source changes by the user 
          $result .= '<tr>';
          // display headline
          if ($row_nbr == 1) {
            $result .= '<th>Source name vs. </th>';
            $result .= '<th>common</th>';
            $result .= '<th>other user</th>';
            $result .= '<th></th>'; // for the buttons
            $result .= '</tr><tr>';
          }
          
          //
          //$result .= '<td>'.$sandbox_item_name.'</td>';
          $result .= '<td>'.$sandbox_usr_txt.'</td>';
          $result .= '<td>'.$sandbox_std_txt.'</td>';
          $result .= '<td>'.$sandbox_other.'</td>';
          $result .= '<td>'.$sandbox_undo_btn.'</td>';

          $result .= '</tr>';
        }
      }
      $result .= '</table>';
    }
    
    zu_debug('user_dsp->dsp_sandbox_source -> done.', $debug-1);
    return $result;
  }

  // display changes by the user which are not (yet) standard 
  function dsp_sandbox ($back, $debug) {
    zu_debug('user_dsp->dsp_sandbox(u'.$this->id.',b'.$back.')', $debug-10);
    $result  = $this->dsp_sandbox_val        ($back, $debug-1); 
    $result .= $this->dsp_sandbox_frm        ($back, $debug-1); 
    $result .= $this->dsp_sandbox_frm_link   ($back, $debug-1); 
    $result .= $this->dsp_sandbox_wrd        ($back, $debug-1); 
    $result .= $this->dsp_sandbox_wrd_link   ($back, $debug-1); 
    $result .= $this->dsp_sandbox_view       ($back, $debug-1); 
    $result .= $this->dsp_sandbox_view_entry ($back, $debug-1); 
    $result .= $this->dsp_sandbox_view_link  ($back, $debug-1); 
    $result .= $this->dsp_sandbox_source     ($back, $debug-1); 
    return $result;
  }



}

?>
