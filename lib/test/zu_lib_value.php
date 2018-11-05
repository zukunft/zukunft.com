<?php

/*

  zu_lib_value.php - old value related functions (just just for regression code testing)
  ----------------

  function prefix: zuv_* 

  
  get functions - basic functions that return one parameter for one value
  ---
  
  zuv_value                - simply get the value from the database if the value id is known
  zuv_source               - 
  zuv_type                 - return the value type

  
  parameter functions - get a list of parameter for one value
  ---------

  zuv_wrd_lst              - lists all words related to a given value taking the user settings into account
  zuv_wrd_ids              - lists all words related to a given value (should be replaced by zuv_wrd_lst)
  zuv_words                - lists all words related to a given value execpt the given word (should be replaced by zuv_wrd_lst)
  zuv_words_name           - lists all words related to a given value execpt the given word
  zuv_words_names_linked   - 
  zuv_words_id_txt         - creats a short string with the word ids related to a given value
  zuv_words_id_txt_ex_time -
  
  
  convert functions - adjust one value or a list of values
  -------
  
  zuv_convert              - convert a user entry for a value to a useful database number 
  zuv_scale                - 


  select functions - returns a value or a list of values based on selection criterias (prefix: zuv_of_* )
  ------
  
  zuv_lst_get              - selects from a given list of values with its words the best matching value 
                             (a kind of in memory query to avoid too many queries while building a value table)
  zuv_value_table          - create an array with all values that are part of one word list and one selecting word
  zuv_of_wrd_lst           - return the first value related to the word lst
  zuv_of_wrd_lst_id        - return the id of first value related to the word lst

  
  display functions (prefix: zuv_dsp_* )
  -------
  
  zuv_table                - creates a table of all values related to a word and a related word and all the subwords of the related word
  zuv_tbl_val              - 
  zuv_dsp                  - display a value and formats is according to the format word

  
  ToDo: 
  1. order of words that are related to a value (new added word should be at the end)
  2. move the add word button higher
  3. show linked formulas to value


zukunft.com - calc with words

copyright 1995-2018 by zukunft.com AG, Zurich

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
   retrieve functions
   --------
*/

// ??? never used ????
// selects from a val_lst_wrd the best matching value
// best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
function zuv_lst_get ($val_lst_wrd, $word_ids, $debug) {
  zu_debug("zuv_lst_get (".zu_lst_dsp($val_lst_wrd).",t".implode(",",$word_ids).")", $debug);

  $found = false;
  $result = array();
  foreach (array_keys($val_lst_wrd) AS $val_id) {
    if (!$found) {
      $val_lst = $val_lst_wrd[$val_id];
      $val_wrd_ids = $val_lst[2];
      $wrd_missing = zu_lst_not_in_no_key($word_ids, $val_wrd_ids, $debug-10);
      if (empty($wrd_missing)) {
        // potential result candidate, because the value has all needed words 
        zu_debug("zuv_lst_get -> can (".$val_lst[0].")", $debug)-10;
        $wrd_extra = zu_lst_not_in_no_key($val_wrd_ids, $word_ids, $debug-10);
        if (empty($wrd_extra)) {
          // if there is no extra word, it is the correct value 
          zu_debug("zuv_lst_get -> is (".$val_lst[0].")", $debug-10);
          $found = true;
          $result = array();
          $result[] = $val_lst[0];
          $result[] = $val_id;
          $result[] = $val_lst[1]; // the user_id if the value is user specific
        } else {
          zu_debug("zuv_lst_get -> is not, because (".implode(",",$wrd_extra).")", $debug-10);
        }
      }
    }
  }

  zu_debug("zuv_lst_get -> done (".implode(",",$result).")", $debug);
  return $result;
}



/*
  get functions
  ---
*/

// simply get the value from the database if the value id is known
// todo: combine to one query
function zuv_value($val_id, $user_id, $debug) {
  $result = NULL;
  if ($val_id > 0) {
    $value_del  = zu_sql_get1 ("SELECT value_id FROM user_values WHERE value_id = ".$val_id." AND user_id = ".$user_id." AND excluded = 1;", $debug-1);
    // only return a value if the user has not yet excluded the values
    if ($val_id <> $value_del) {
      $result = zu_sql_get1 ("SELECT user_value FROM user_values WHERE value_id = ".$val_id." AND user_id = ".$user_id." AND (excluded is NULL OR excluded = 0);", $debug-1);
      if ($result == NULL) {
        $result  = zu_sql_get1 ("SELECT word_value FROM `values` WHERE value_id = ".$val_id." AND (excluded is NULL OR excluded = 0);", $debug-1);
      }  
    }
  }
  return $result;
}

function zuv_value_all($val_id, $debug) {
  $result  = zu_sql_get1 ("SELECT word_value FROM `values` WHERE value_id = ".$val_id." AND (excluded is NULL OR excluded = 0);", $debug-1);
  return $result;
}

// create an array with all values that are part of one word list and one selecting word
// e.g. get all ABB cash flow statement values where "ABB" is the seleting word and "cash flow statement" is the list creating word
function zuv_value_table($word_id, $word_lst, $debug) {
  // get all values of the selecting word, because this is probably the most efective first reduction
  // loop over the word list and remove the values not linked to the word list
  return zu_sql_get_field ('value', $word_id, 'word_value');
}

function zuv_matrix($row_lst, $col_lst, $user_id, $debug) {
  zu_debug('zuv_matrix('.$row_lst.','.$col_lst.')', $debug);
  $result = array();
  
  return $result;
}

// get the source id of an value
function zuv_source ($id, $debug) {
  zu_debug('zuv_source ('.$id.')', $debug);
  return zu_sql_get_field ('value', $id, 'source_id', $debug-1);
}

// return the value type
function zuv_type ($id, $debug) {
  zu_debug('zuv_type('.$id.')', $debug);
  return zu_sql_get_field ('word', $id, 'word_type_id', $debug-1);
}

// convert a user entry for a value to a useful database number
// e.g. remove leading spaces and tabulators
// if the value contains a single quote "'" the function asks once if to use it as a commy or a tausend operator
// once the user has given an answer it saves the answer in the database and uses it for the next values
// if the type of the value differs the user should be asked again
function zuv_convert ($user_value, $user_id, $debug) {
  zu_debug('zuv_convert ('.$user_value.',u'.$user_id.')', $debug);
  $result = $user_value;
  $result = str_replace(" ", "", $result);
  $result = str_replace("'", "", $result);
  //$result = str_replace(".", "", $result);
  return $result;
}

function zuv_scale ($user_value, $value_words, $user_id, $debug) {
  zu_debug('zuv_scale ('.$user_value.',t'.implode(",",$value_words).',u'.$user_id.')', $debug);
  $result = $user_value;

  // if it has a scaling word, scale it to one
  if (zut_has_scaling($value_words, $debug-1)) {
    // get any scaling words related to the value
    $scale_word = zut_scale_id($value_words, $user_id, $debug-1); 
    zu_debug('zuv_scale -> word ('.$scale_word.')', $debug-1);
    if ($scale_word > 0) {
      $formula_id = zut_formula($scale_word, $user_id, $debug-1);
      if ($formula_id > 0) {
        $formula_text = zuf_text($formula_id, $user_id, $debug-1);
        if ($formula_text <> "") {
          $l_part = zu_str_left_of($formula_text, ZUP_CHAR_CALC);
          $r_part = zu_str_right_of($formula_text, ZUP_CHAR_CALC);
          $l_part_wrd_id = zuf_2num_get_word($l_part, $debug-1);
          $r_part_wrd_id = zuf_2num_get_word($r_part, $debug-1);
          
          // test if it is a valid scale formula
          if (zut_is_type($l_part_wrd_id, SQL_WORD_TYPE_SCALING_HIDDEN, $debug-1) 
          AND zut_is_type($r_part_wrd_id, SQL_WORD_TYPE_SCALING, $debug-1) ) {
            $wrd_symbol = ZUP_CHAR_WORD_START.$r_part_wrd_id.ZUP_CHAR_WORD_END;
            zu_debug('zuv_scale -> replace ('.$wrd_symbol.' in '.$r_part.' with '.$user_value.')', $debug-1);
            $r_part = str_replace($wrd_symbol,$user_value,$r_part);
            zu_debug('zuv_scale -> replace done ('.$r_part.')', $debug-1);
            $result = zuc_math_parse($r_part, $value_words, 0, $debug-1);
          } else {
            zu_err ('Formula "'.$formula_text.'" seems to be not a valid scaling formula.');
          }
        }
      }
    }
    
  }
  return $result;
}

// return the first value related to the word lst (ex zuv_word_lst)
// or an array with the value and the user_id if the result is user specific
function zuv_of_wrd_lst($wrd_lst, $user_id, $debug) {
  zu_debug("zuv_of_wrd_lst (".implode(",",$wrd_lst).",u".$user_id.")", $debug);

  // remove the general word if a more specific word is already part of the selection e.g. remove country, if Germany is a selection word
  $used_wrd_lst = zut_keep_only_specific($wrd_lst, $debug);
  zu_debug("zuv_of_wrd_lst -> (".implode(",",$used_wrd_lst).")", $debug-1);
  $result = zu_sql_wrd_ids_val ($used_wrd_lst, $user_id, $debug);

  zu_debug("zuv_of_wrd_lst -> (".$result['num'].")", $debug-1);
  return $result;
}

// similar to zuv_of_wrd_lst but with correct naming and always returns an array to know the user specific result
function zuv_of_wrd_ids($wrd_ids, $user_id, $debug) {
  zu_debug("zuv_of_wrd_ids (".implode(",",$wrd_ids).",u".$user_id.")", $debug);

  // remove the general word if a more specific word is already part of the selection e.g. remove country, if Germany is a selection word
  $used_wrd_ids = zut_keep_only_specific($wrd_ids, $debug);
  $result = zu_sql_wrd_ids_val ($used_wrd_ids, $user_id, $debug);
  return $result;
}

// 
function zuv_of_wrd_lst_scaled($wrd_lst, $user_id, $debug) {
  zu_debug("zuv_of_wrd_lst_scaled (".implode(",",$wrd_lst).",u".$user_id.")", $debug);

  $wrd_val = zuv_of_wrd_lst($wrd_lst, $user_id, $debug-5);
  
  if ($wrd_val === false) {
    $result = false;
  } else {
    // get the best matching value id related to a word list
    //$val_id = zuv_of_wrd_lst_id($wrd_lst, $debug-1); 

    // get all words related to the value id; in many cases this does not match with the value_words there are use to get the word: it may contains additional word ids
    if ($wrd_val['id'] > 0) {
      zu_debug("zuv_of_wrd_lst_scaled -> get word ids ".implode(",",$wrd_lst), $debug-5);        
      $val_wrd_ids = zuv_wrd_ids($wrd_val['id'], $user_id, $debug-5);
      $wrd_val['num'] = zuv_scale($wrd_val['num'], $val_wrd_ids, $user_id, $debug-5);      
    }
    $result = $wrd_val;      
  }

  return $result;
}

// 
function zuv_wrd_group_result($wrd_grp_id, $time_wrd_id, $user_id, $debug) {
  zu_debug("zuv_wrd_group_result (".$wrd_grp_id.",time".$time_wrd_id.",u".$user_id.")", $debug);
  $result = array(); 
  
  if ($time_wrd_id > 0) {
    $sql_time = " time_word_id = ".$time_wrd_id." ";
  } else {
    $sql_time = " (time_word_id IS NULL OR time_word_id = 0) ";
  }

  $sql = "SELECT formula_value_id AS id,
                 formula_value    AS num,
                 user_id          AS usr,
                 last_update      AS upd
            FROM formula_values 
           WHERE phrase_group_id = ".$wrd_grp_id."
             AND ".$sql_time."
             AND user_id = ".$user_id.";";
  $result = zudb_get1($sql, $user_id, $debug-1); 
  
  // if no user specific result is found, get the standard result
  if ($result === false) {
    $sql = "SELECT formula_value_id AS id,
                   formula_value    AS num,
                   user_id          AS usr,
                   last_update      AS upd
              FROM formula_values 
             WHERE phrase_group_id = ".$wrd_grp_id."
               AND ".$sql_time."
               AND (user_id = 0 OR user_id IS NULL);";
    $result = zudb_get1($sql, $user_id, $debug-1); 

    // get any time value: to be adjusted to: use the latest
    if ($result === false) {
      $sql = "SELECT formula_value_id AS id,
                    formula_value    AS num,
                    user_id          AS usr,
                    last_update      AS upd
                FROM formula_values 
              WHERE phrase_group_id = ".$wrd_grp_id."
                AND (user_id = 0 OR user_id IS NULL);";
      $result = zudb_get1($sql, $user_id, $debug-1); 
      zu_debug("zuv_wrd_group_result -> (".$result['num'].")", $debug-1);
    } else {
      zu_debug("zuv_wrd_group_result -> (".$result['num'].")", $debug-1);
    }  
  } else {
    zu_debug("zuv_wrd_group_result -> (".$result['num']." for ".$user_id.")", $debug-1);
  }
  
  return $result;
}

// word group that are relevant for formula calculation
/* example

parameters:
$wrd_id = "ABB";         // the word assigned to the formula
$wrd_ids = "sales,cost"; // the words used in the formula

values:
abb sales eur 2016
abb sales eur 2017
abb sales chf 2016
abb sales chf 2017
abb cost eur 2016
abb cost eur 2017
abb cost chf 2016
abb cost chf 2017

abb tax eur 2016
abb tax eur 2017
abb tax chf 2016
abb tax chf 2017


test it with

cost = 144 
sales = 6

nestlé = 7

formula:

operating profit = sales - cost

-> words of formula -> sales + cost
-> calc word abb


expected result 
 
ABB 

+

eur 2016
eur 2017
chf 2016
chf 2017

*/

function zuv_frm_related_grp_wrds($wrd_id, $wrd_ids, $user_id, $debug) {
  zu_debug("zuv_frm_related_grp_wrds (".$wrd_id.",ft".implode(",",$wrd_ids).",u".$user_id.")", $debug);
  $result = array();

  if ($wrd_id > 0 AND !empty($wrd_ids)) {
    // get the relevant values
    $val_ids = zuv_frm_related($wrd_id, $wrd_ids, $user_id, $debug-1);

    // get the word groups for which a formula result is expected
    // maybe exclude word groups already here where not all needed values for the formula are in the database
    $result = zuv_frm_related_grp_wrds_part($val_ids, $wrd_id, $wrd_ids, $user_id, $debug-1);
  }
   
  zu_debug("zuv_frm_related_grp_wrds -> (".zu_lst_dsp($result).")", $debug-1);
  return $result;
}

// similar to zuv_frm_related_grp_wrds, but for calculated values (therefore the prefic is VC for ValuesCalculated)
function zuvc_frm_related_grp_wrds($val_wrd_lst, $wrd_id, $frm_ids, $user_id, $debug) {
  zu_debug("zuvc_frm_related_grp_wrds (vt".implode(",",$val_wrd_lst).",".$wrd_id.",f".implode(",",$frm_ids).",u".$user_id.")", $debug);
  $result = array();

  if ($wrd_id > 0 AND !empty($frm_ids)) {
    // get the relevant values
    $valc_ids = zuvc_frm_related($frm_ids, $wrd_id, $user_id, $debug-1);

    // get the word groups for which a formula result is expected
    // maybe exclude word groups already here where not all needed values for the formula are in the database
    $result = zuvc_frm_related_grp_wrds_part($valc_ids, $wrd_id, $frm_ids, $user_id, $debug-1);
  }
   
  zu_debug("zuvc_frm_related_grp_wrds -> (".zu_lst_dsp($result).")", $debug-1);
  return $result;
}

// list of values related to a formula 
// described by the word to which the formula is assigned 
// and the words used in the formula
function zuv_frm_related($wrd_id, $wrd_ids, $user_id, $debug) {
  zu_debug("zuv_frm_related (".$wrd_id.",ft".implode(",",$wrd_ids).",u".$user_id.")", $debug);
  $result = array();

  if ($wrd_id > 0 AND !empty($wrd_ids)) {
    $sql = "SELECT l1.value_id
              FROM value_phrase_links l1,
                   value_phrase_links l2
             WHERE l1.value_id = l2.value_id
               AND l1.phrase_id = ".$wrd_id."
               AND l2.phrase_id IN (".implode(",",$wrd_ids).");";
    $result = zu_sql_get_ids($sql, $debug-10); 
  }
   
  zu_debug("zuv_frm_related -> (".implode(",",$result).")", $debug-1);
  return $result;
}

function zuvc_frm_related($frm_ids, $wrd_id, $user_id, $debug) {
  zu_debug("zuvc_frm_related (f".implode(",",$frm_ids).",u".$user_id.")", $debug);
  $result = array();

  if (!empty($frm_ids)) {
    $sql = "SELECT formula_value_id
              FROM formula_values
             WHERE formula_id IN (".implode(",",$frm_ids).");";
    $result = zu_sql_get_ids($sql, $debug-10); 
  }
   
  zu_debug("zuvc_frm_related -> (".implode(",",$result).")", $debug-1);
  return $result;
}

// group words
// kind of similar to zu_sql_val_lst_wrd
function zuv_frm_related_grp_wrds_part($val_ids, $wrd_id, $wrd_ids, $user_id, $debug) {
  zu_debug("zuv_frm_related_grp_wrds_part (v".implode(",",$val_ids).",t".$wrd_id.",ft".implode(",",$wrd_ids).",u".$user_id.")", $debug);
  $result = array();

  if ($wrd_id > 0 AND !empty($wrd_ids) AND !empty($val_ids)) {
    $wrd_ids[] = $wrd_id; // add the main word to the exclude words
    $sql = "SELECT l.value_id,
                  IF(u.user_value IS NULL,v.word_value,u.user_value) AS word_value, 
                  l.phrase_id, 
                  v.excluded, 
                  u.excluded AS user_excluded 
              FROM value_phrase_links l,
                  `values` v 
        LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = ".$user_id." 
            WHERE l.value_id = v.value_id
              AND l.phrase_id NOT IN (".implode(",",$wrd_ids).")
              AND l.value_id IN (".implode(",",$val_ids).")
              AND (u.excluded IS NULL OR u.excluded = 0) 
          GROUP BY l.value_id, l.phrase_id;";

    $sql_result = zu_sql_get_all($sql, $debug-10);
    $value_id = -1; // set to an id that is never used to force the creation of a new entry at start
    while ($val_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
      if ($value_id == $val_entry[0]) {
        $wrd_result[] = $val_entry[2];
        //zu_debug('zu_sql_val_lst_wrd -> add word '.$val_entry[2].' to ('.$value_id.')', $debug);  
      } else {  
        if ($value_id >= 0) {
          // remember the previous values
          $row_result[] = $wrd_result;
          $result[$value_id] = $row_result;
          //zu_debug('zu_sql_val_lst_wrd -> add value '.$value_id.'', $debug);  
        } 
        // remember the values for a new result row
        $value_id  = $val_entry[0];
        $val_num = $val_entry[1];
        $row_result   = array();
        $row_result[] = $val_num;
        $wrd_result   = array();
        $wrd_result[] = $val_entry[2];
        //zu_debug('zu_sql_val_lst_wrd -> found value '.$value_id.'', $debug);  
      }  
    } 
    if ($value_id >= 0) {
      // remember the last values
      $row_result[] = $wrd_result;
      $result[$value_id] = $row_result;
    } 
  } 

  zu_debug("zuv_frm_related_grp_wrds_part -> (".zu_lst_dsp($result).")", $debug);
  return $result;
}

// similar to zuv_frm_related_grp_wrds_part, but for formula result values
function zuvc_frm_related_grp_wrds_part($frm_val_ids, $wrd_id, $wrd_ids, $user_id, $debug) {
  zu_debug("zuvc_frm_related_grp_wrds_part (v".implode(",",$frm_val_ids).",t".$wrd_id.",ft".implode(",",$wrd_ids).",u".$user_id.")", $debug);
  $result = array();
  
  if ($wrd_id > 0 AND !empty($wrd_ids) AND !empty($frm_val_ids)) {
    $time_wrd_id = zut_time_id($wrd_ids, $user_id, $debug-5);
    $wrd_ids = zut_ids_ex_time($wrd_ids, $user_id, $debug-5);
    $wrd_ids[] = $wrd_id; // add the main word to the exclude words
    if ($time_wrd_id > 0) {
      $sql_time = " AND v.time_word_id = ".$time_wrd_id." ";
    } else {
      $sql_time = " AND v.time_word_id IS NULL ";
    }
    if ($user_id > 0) {
      $sql_usr = " AND v.user_id = ".$user_id." ";
    } else {
      $sql_usr = " ";
    }  
    $sql = "SELECT v.formula_value_id,
                  v.formula_value AS val, 
                  g.word_ids 
              FROM formula_values v,
                  phrase_groups g 
            WHERE v.phrase_group_id = g.phrase_group_id
              AND v.formula_value_id IN (".implode(",",$frm_val_ids).")
              AND v.user_id = ".$user_id."
                  ".$sql_time."
                  ".$sql_usr."
          GROUP BY v.formula_value_id;";

    $sql_result = zu_sql_get_all($sql, $debug-10);
    while ($val_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
      $result_wrd_ids = explode(",",$val_entry[2]);
      if (in_array($wrd_id, $result_wrd_ids)) {
        $row_result   = array();
        $row_result[] = $val_entry[1];
        $row_result[] = $result_wrd_ids;
        $result[$val_entry[0]] = $row_result;
      }
    } 
  } 

  zu_debug("zuvc_frm_related_grp_wrds_part -> (".zu_lst_dsp($result).")", $debug);
  return $result;
}

// return the id of first value related to the word lst
/* should not be used any more
function zuv_of_wrd_lst_id($word_lst, $user_id, $debug) {
  return zu_sql_word_lst_value_id ($word_lst, $user_id, $debug);
} */

// a list of all words related to a given value 
function zuv_wrd_lst($val_id, $user_id, $debug) {
  zu_debug("zuv_wrd_lst (".$val_id.",u".$user_id.")", $debug);
  $result = array();

  if ($val_id > 0) {
    $sql = "SELECT t.word_id, 
                   IF (u.word_name IS NULL, IF (t.word_name IS NULL, 'not set', t.word_name), u.word_name) AS used_word_name
              FROM value_phrase_links l
         LEFT JOIN words t      ON l.phrase_id = t.word_id  
         LEFT JOIN user_words u ON t.word_id = u.word_id AND u.user_id  = ".$user_id."  
             WHERE l.value_id = ".$val_id." 
          GROUP BY t.word_id
          ORDER BY t.values, t.word_name;";
    $result = zu_sql_get_lst($sql, $debug-5);
  } else {
    zu_err("Missing value id","zuv_wrd_lst");
  }

  return $result;
}

// a list of all words related to a list of value ids
function zuv_ids_wrd_lst($val_ids, $user_id, $debug) {
  zu_debug("zuv_ids_wrd_lst (".implode(",",$val_ids).",u".$user_id.")", $debug-5);
  $result = array();

  if (!empty($val_ids)) {
    $sql = "SELECT t.word_id, 
                   IF (u.word_name IS NULL, IF (t.word_name IS NULL, 'not set', t.word_name), u.word_name) AS used_word_name
              FROM value_phrase_links l
         LEFT JOIN words t      ON l.phrase_id = t.word_id  
         LEFT JOIN user_words u ON t.word_id = u.word_id AND u.user_id  = ".$user_id."  
             WHERE l.value_id IN (".implode(",",$val_ids).") 
          GROUP BY t.word_id
          ORDER BY t.values, t.word_name;";
    $result = zu_sql_get_lst($sql, $debug-10);
  } else {
    zu_err("Missing value id","zuv_ids_wrd_lst");
  }

  zu_debug("zuv_ids_wrd_lst -> (".implode(",",$result)." for ".implode(",",$val_ids).")", $debug-1);
  return $result;
}

// same as zuv_ids_wrd_lst, but includes the category words e.g. "Year" for "2016"
function zuv_ids_wrd_lst_incl_cat($val_ids, $user_id, $debug) {
  zu_debug("zuv_ids_wrd_lst_incl_cat (".implode(",",$val_ids).",u".$user_id.")", $debug-8);
  $wrd_lst = zuv_ids_wrd_lst($val_ids, $user_id, $debug-8);

  foreach (array_keys($wrd_lst) AS $wrd_id) {
    $wrd_cat_lst = zut_foaf_parent($wrd_id, $debug-10);
    foreach (array_keys($wrd_cat_lst) AS $wrd_cat_id) {
      if ($wrd_cat_id > 0 AND !in_array($wrd_cat_id, array_keys($wrd_lst))) {
        $wrd_lst[$wrd_cat_id] = $wrd_cat_lst[$wrd_cat_id];
      }
    }
  }

  zu_debug("zuv_ids_wrd_lst_incl_cat -> (the words ".implode(",",$wrd_lst)." are related to the values ".implode(",",$val_ids).")", $debug-1);
  return $wrd_lst;
}

// a list of all word links related to a given value with the id of the linked word
function zuv_wrd_link_lst($val_id, $user_id, $debug) {
  zu_debug("zuv_wrd_link_lst (".$val_id.",u".$user_id.")", $debug);
  $result = array();

  if ($val_id > 0) {
    $sql = "SELECT l.value_phrase_link_id,
                   t.word_id
              FROM value_phrase_links l
         LEFT JOIN words t      ON l.phrase_id = t.word_id  
         LEFT JOIN user_words u ON t.word_id = u.word_id AND u.user_id  = ".$user_id."  
             WHERE l.value_id = ".$val_id." 
          GROUP BY t.word_id
          ORDER BY t.values, t.word_name;";
    $result = zu_sql_get_lst($sql, $debug-5);
  } else {
    zu_err("Missing value id","zuv_wrd_link_lst");
  }

  return $result;
}

// lists all words related to a given value 
function zuv_wrd_ids($val_id, $user_id, $debug) {
  zu_debug("zuv_wrd_ids (".$val_id.",u".$user_id.")", $debug);
  $result = array();

  if ($val_id > 0) {
    $sql = "SELECT word_id FROM value_phrase_links WHERE value_id = ".$val_id." GROUP BY word_id;";
    $result = zu_sql_get_ids($sql, $debug-5);
  } else {
    zu_err("Missing value id","zuv_wrd_ids");
  }

  return $result;
}


// lists all words related to a given value execpt the given word
// should be replaced by zuv_wrd_lst
function zuv_words($value_id, $ex_word_id, $user_id, $return_type) {
  zu_debug("zuv_words ... ", $debug);
  if ($return_type == 'ids') {
    $result = array();
  } else {  
    $result = '';
  }  

  $query = "SELECT phrase_id FROM value_phrase_links WHERE value_id = ".$value_id." GROUP BY word_id;";
  $sql_result = mysql_query($query) or die('Query failed: ' . mysql_error());
  while ($value_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    if ($value_entry[0] <> $ex_word_id) {
      if ($return_type == 'names') {
	if ($word_id <> $value_entry[0]) {
	  $result .= ' '.zut_name($value_entry[0], $user_id).' ';
	}
      }
      if ($return_type == 'names_linked') {
	if ($word_id <> $value_entry[0]) {
	  $result .= ' <a href="/http/view.php?words='.$value_entry[0].'">'.zut_name($value_entry[0], $user_id).'</a> ';
	}
      }
      if ($return_type == 'id_text') {
	if ($result <> '') {
	  $result .= ',';
	}
	$result .= $value_entry[0];
      }
      if ($return_type == 'ids') {
	$result[] .= $value_entry[0];
      }
      if ($return_type == 'id_text_ex_time') {
	if (zut_type($value_entry[0]) <> 2) {
	  if ($result <> '') {
	    $result .= ',';
	  }
	  $result .= $value_entry[0];
	}
      }
    }
  }

  zu_debug("zuv_words ... done", $debug);

  return $result;
}

// lists all words related to a given value execpt the given word
function zuv_words_name($value_id, $ex_word_id, $user_id, $debug) {
  return zuv_words($value_id, $ex_word_id, $user_id, 'names');
}

// lists all words related to a given value execpt the given word
function zuv_words_names_linked($value_id, $ex_word_id, $user_id, $debug) {
  return zuv_words($value_id, $ex_word_id, $user_id, 'names_linked');
}

// creats a short string with the word ids related to a given value
function zuv_words_id_txt ($value_id, $ex_word_id, $user_id, $debug) {
  return zuv_words($value_id, $ex_word_id, $user_id, 'id_text');
} 

// creats a short string with the word ids related to a given value
function zuv_words_id_txt_ex_time ($value_id, $ex_word_id, $user_id, $debug) {
  return zuv_words($value_id, $ex_word_id, $user_id, 'id_text_ex_time');
} 

?>
