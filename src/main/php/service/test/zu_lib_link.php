<?php

/*

  zu_lib_link.php - old verb (word link type) functions (just just for regression code testing)
  ---------------

  prefix: zul_* 
  prefix: zutl_* 

  word links are the "verbs" that combine two words to a RDF tripple


  get functions
  ---
  
  zul_plural_reverse - return the link word name for more than one item and for the reverse relation
  zul_reverse        - 
  zul_plural         -
  zul_name           -
  zul_id             - the word link / verb database id independent from the user; what if a user has renamed a verb? 
  zul_type           -

  
  display functions
  -------
  
  zul_dsp_list       - display all verbs and allow an admin to change it
  zutl_dsp           - display all verbs and allow an admin to change it

  
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


// return the link word name for more than one item and for the reverse relation
// e.g. for the link "is a" the plural reverse is "are": "ABB is a company and Roche is a company" -> "Companies are ABB and Roche"
function zul_plural_reverse ($id) {
  return zu_sql_get_field ('verb', $id, 'name_plural_reverse');
}

function zul_reverse ($id) {
  return zu_sql_get_field ('verb', $id, 'name_reverse');
}

function zul_plural ($id) {
  return zu_sql_get_field ('verb', $id, 'name_plural');
}

// name including the user id as a parameter to return the verb in the lanuage of the user
function zul_name ($id, $user_id, $debug) {
  return zu_sql_get_field ('verb', $id, 'verb_name');
}

// the verb id for the given name
function zul_id ($name, $debug) {
  zu_debug('zul_id('.$name.')', $debug);
  return zu_sql_get_id ('verb', $name, $debug-1);
}

// return the link type id
function zul_type ($name) {
  return zu_sql_get_field ('verb', $name, 'verb_name');
}

// display all verbs and allow an admin to change it
function zul_dsp_list ($user_id, $debug) {
  zu_debug('zul_dsp_list('.$user_id.')', $debug);
  $result  = "";

  $verb_lst = zu_sql_verbs($user_id, $debug-1);
  $result .= zuh_list($verb_lst, "verb", $debug-1);

  return $result;
}

// show the html form to add a new verb 
function zul_dsp_add ($verb_name, $name_plural, $name_reverse, $name_plural_reverse, $user_id, $back_link, $debug) {
  zu_debug('zul_dsp_add ('.$verb_name.','.$name_plural.','.$name_reverse.','.$name_plural_reverse.','.$user_id.','.$back_link.')', $debug);
  $result = '';
  
  $result .= zuh_text_h2('Add verb (word link type)');
  $result .= zuh_form_start("verb_add");
  $result .= zuh_tbl_start_half();
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      verb name:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_name" value="'.$verb_name.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      verb plural:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_plural" value="'.$name_plural.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      reverse:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_reverse" value="'.$name_reverse.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      plural_reverse:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_plural_reverse" value="'.$name_plural_reverse.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <input type="hidden" name="back" value="'.$back_link.'">';
  $result .= '  <input type="hidden" name="confirm" value="1">';
  $result .= zuh_tbl_end();
  $result .= zuh_form_end();

  zu_debug('zul_dsp_add ... done', $debug);
  return $result;
}

function zul_db_add ($verb_name, $name_plural, $name_reverse, $name_plural_reverse, $user_id, $back_link, $debug) {
  zu_debug('zul_db_add ('.$verb_name.','.$name_plural.','.$name_reverse.','.$name_plural_reverse.','.$user_id.','.$back_link.')', $debug);

  // check the parameter is expected to be done by the calling function
  
  // add a new or use an existing word
  $verb_id = 0;
  if ($verb_name <> "") {
    // check if a word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
    $id_txt = zu_sql_id($verb_name, $user_id, $debug-1);
    if ($id_txt <> "") {
      echo zu_sql_id_msg($id_txt, $verb_name, $user_id, $debug-1);
    } else {
      // log and add the new verb if valid
      $log_id = zu_log($user_id, "add", "verbs", "verb_name", "", $verb_name, 0, $debug-1);
      if ($log_id > 0) {
        // insert the new verb
        $verb_id = zu_sql_insert("verbs", "verb_name", sf($verb_name), $user_id, $debug);
        if ($verb_id > 0) {
          // update the id in the log
          $result = zu_log_upd($log_id, $verb_id, $user_id, $debug-1);
          // save the other verb names
          if (zu_log($user_id, "update", "verbs", "name_plural", "", $name_plural, $verb_id, $debug-1) > 0 ) {
            $result = zu_sql_update("verbs", $verb_id, "name_plural", sf($name_plural), $user_id, $debug-1);
          }
          if (zu_log($user_id, "update", "verbs", "name_reverse", "", $name_reverse, $verb_id, $debug-1) > 0 ) {
            $result = zu_sql_update("verbs", $verb_id, "name_reverse", sf($name_reverse), $user_id, $debug-1);
          }
          if (zu_log($user_id, "update", "verbs", "name_plural_reverse", "", $name_plural_reverse, $verb_id, $debug-1) > 0 ) {
            $result = zu_sql_update("verbs", $verb_id, "name_plural_reverse", sf($name_plural_reverse), $user_id, $debug-1);
          }
        } else {
          zu_err("Adding verb ".$verb_name." failed.", "zul_db_add");
        }
      }  
    }
  }
  
  return $verb_id;
}

// calulates how many times a word is used, because this can be helpful for sorting
function zul_calc_usage ($debug) {
  zu_debug('zul_calc_usage', $debug);
  
  $sql = "UPDATE verbs l
             SET `words` = ( 
          SELECT COUNT(to_word_id) 
            FROM word_links t
           WHERE l.verb_id = t.verb_id);";
  $result = zu_sql_exe($sql, cl(SQL_USER_SYSTEM), DBL_SYSLOG_ERROR, "zul_calc_usage", (new Exception)->getTraceAsString(), $debug-10);
  
  return $result;           
}



?>
