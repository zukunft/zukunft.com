<?php

/*

  zu_lib_word_db.php - old Zukunft word related database functions  (just just for regression code testing)
  ------------------
  
  prefix: zut_db_* 

  get functions
  ---
  
  zut_owner                 - get the owner of the word
  zut_changer               - user id of the first one who has changed it
  zut_can_change            - true if the user is allowed to change the word
  zut_db_tree_up_level_type - list of word ids related to the given word e.g. for "ABB" it will be "Company" for type "is a"
  zut_db_tree_level_type    - similar to zut_db_tree_up_level_type, but the other way round
  zut_db_tree_up_level      - build one level of a word tree
  zut_db_tree_level         - similar to zut_db_tree_up_level, but the other way round
  zut_db_up_tree            - array of word ids, that characterises the given word e.g. for "ABB" it will be "Company" for type "is a"
  zut_db_tree               - similar to zut_db_up_tree, but the other way round e.g. for "Company" it will be "ABB" for type "is a"
  zut_ids_is                - all words that characterize the given word
  zut_ids_are               - word ids that ARE of the given type
  zut_ids_contains          - word ids part of the given word
  
  

  
  to do: create a word object with functions .id .name
  
  
  
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


/*
  word owner and permission functions
  ----------
*/  

// get the owner of the word; the owner is the user who created the word; the user that has first changed the word will be the owner if the original owner has exclude the word from his profile
function zut_owner($wrd_id, $debug) {
  zu_debug('zut_owner (t'.$wrd_id.')', $debug);  
  $user_id = zu_sql_get_value("words", "user_id", "word_id", $wrd_id, $debug-10);  
  return $user_id;
}

// if the word has been change by someone else than the owner the user id of the first one who has changed it, is returned
function zut_changer($wrd_id, $debug) {
  zu_debug('zut_changer (t'.$wrd_id.')', $debug);  
  $user_id = zu_sql_get_value("user_words", "user_id", "word_id", $wrd_id, $debug-10);
  return $user_id;
}

// true if the user is the owner and noone else has changed the value
function zut_can_change($wrd_id, $user_id, $debug) {
  zu_debug('zut_can_change (t'.$wrd_id.',u'.$user_id.')', $debug);  
  $can_change = false;
  $wrd_owner = zut_owner($wrd_id, $debug-10);
  if ($wrd_owner == $user_id OR $wrd_owner <= 0) {
    $wrd_user = zut_changer($wrd_id, $debug-10);
    if ($wrd_user == $user_id OR $wrd_user <= 0) {
      $can_change = true;
    }  
  }  
  return $can_change;
}

/*
  tree building function
  -------------
*/

// returns a list of words related to the given word by the special verbs such as 'is' or 'contains'
// should be using zu_sql_word_lst_linked
function zut_db_tree_up_level_type ($level, $word_id, $result, $link_type_id, $user_id, $debug) {
  zu_debug('zut_db_tree_up_level_type('.$level.',t'.$word_id.','.$link_type_id.',u'.$user_id.')', $debug);
  $query = "   SELECT l.to_phrase_id, t.word_name " 
         . "     FROM word_links l, words t " 
         . "    WHERE l.to_phrase_id   = t.word_id " 
         . "      AND l.from_phrase_id = ".$word_id." " 
         . "      AND l.verb_id = ".$link_type_id." " 
         . " ORDER BY t.word_name;";
  $sql_result = zu_sql_get_all($query, $debug-5);
  while ($entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $sub_id = $entry[0];
    if ($sub_id > 0 AND !in_array($sub_id, $result)) {
      zu_debug('zut_db_tree_up_level_type -> add '.$sub_id, $debug-5);
      $result[] = $sub_id;
      $result = zut_db_tree_up_level ($level + 1, $sub_id, $result, $debug-5);
    }
  }  
  return $result;    
}

// similar to zut_db_tree_up_level_type, but the other way round
// should be using zu_sql_word_lst_linked
function zut_db_tree_level_type ($level, $word_id, $result, $link_type_id, $user_id, $debug) {
  zu_debug('zut_db_tree_level_type('.$level.',t'.$word_id.','.$link_type_id.',u'.$user_id.')', $debug);
  $query = "   SELECT l.from_phrase_id, t.word_name " 
         . "     FROM word_links l, words t " 
         . "    WHERE l.from_phrase_id   = t.word_id " 
         . "      AND l.to_phrase_id = ".$word_id." " 
         . "      AND l.verb_id = ".$link_type_id." " 
         . " ORDER BY t.word_name;";
  $sql_result = zu_sql_get_all($query, $debug-5);
  while ($entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $sub_id = $entry[0];
    if ($sub_id > 0 AND !in_array($sub_id, $result)) {
      zu_debug('zut_db_tree_level_type -> add '.$sub_id, $debug-5);
      $result[] = $sub_id;
      $result = zut_db_tree_level ($level + 1, $sub_id, $result, $debug-5);
    }
  }  
  return $result;    
}

// build one level of a word tree
function zut_db_tree_up_level ($level, $word_id, $result, $link_type_id, $user_id, $debug) {
  zu_debug('zut_db_tree_up_level(lev'.$level.',t'.$word_id.')', $debug);
  $loops = 0;
  do {
    $loops = $loops + 1;
    $adj_result   = zut_db_tree_up_level_type ($level, $word_id, $result, $link_type_id, $user_id, $debug-5);
    $added_words  = zu_lst_not_in_no_key      ($adj_result, $result, $debug-5);
    $result       = $adj_result;

    if ($loops >= MAX_RECURSIVE) {
      zu_fatal("max number (".$loops.") of loops for word ".$word_id." reached.","zut_db_tree_up_level");
    }
  } while (!empty($added_words) AND $loops < MAX_RECURSIVE);
  return $result;    
}

// build one level of a word tree
function zut_db_tree_level ($level, $word_id, $result, $link_type_id, $user_id, $debug) {
  zu_debug('zut_db_tree_level(lev'.$level.',t'.$word_id.')', $debug);
  $loops = 0;
  do {
    $loops = $loops + 1;
    $adj_result   = zut_db_tree_level_type ($level, $word_id, $result, $link_type_id, $user_id, $debug-5);
    $added_words  = zu_lst_not_in_no_key   ($adj_result, $result, $debug-5);
    $result       = $adj_result;

    if ($loops >= MAX_RECURSIVE) {
      zu_fatal("max number (".$loops.") of loops for word ".$word_id." reached.","zut_db_tree_level");
    }
  } while (!empty($added_words) AND $loops < MAX_RECURSIVE);
  return $result;    
}

// returns an array of word ids, that characterises the given word e.g. for the id of "ABB Ltd." it will return the id of "Company" if the link type is "is a"
function zut_db_tree_up ($word_id, $link_type_id, $user_id, $debug) {
  zu_debug('zut_db_tree_up(t'.$word_id.',l'.$link_type_id.')', $debug);
  $level = 0;
  $result = zut_db_tree_up_level ($level, $word_id, array(), $link_type_id, $user_id, $debug-5);

  zu_debug('zut_db_tree_up -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// similar to zut_db_tree, but the other way round e.g. for the id of "Companies" it will return the id of "ABB Ltd." and others if the link type is "are"
function zut_db_tree ($word_id, $link_type_id, $user_id, $debug) {
  zu_debug('zut_db_tree(t'.$word_id.',l'.$link_type_id.')', $debug);
  $level = 0;
  $result = zut_db_tree_level ($level, $word_id, array(), $link_type_id, $user_id, $debug-5);

  zu_debug('zut_db_tree -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// id array of all words that the given word is related to e.g. for the id of "ABB Ltd." it will return the id of "Company"
function zut_ids_is ($word_id, $user_id, $debug) {
  zu_debug('zut_ids_is(t'.$word_id.')', $debug);
  $link_type_id = sql_code_link(SQL_LINK_TYPE_IS);
  $result = zut_db_tree_up ($word_id, $link_type_id, $user_id, $debug-1);

  zu_debug('zut_ids_is -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// word ids that ARE of the type of the given word e.g. for "Company" it will be "ABB Ltd." and others
function zut_ids_are ($word_id, $user_id, $debug) {
  zu_debug('zut_ids_are(t'.$word_id.',u'.$user_id.')', $debug);
  $link_type_id = sql_code_link(SQL_LINK_TYPE_IS);
  $result = zut_db_tree ($word_id, $link_type_id, $user_id, $debug-5);

  zu_debug('zut_ids_are -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// similar to zut_ids_are, but returns a word list with the word name included (this should be the normal use)
function zut_lst_are ($word_id, $user_id, $debug) {
  zu_debug('zut_lst_are (t'.$word_id.',u'.$user_id.')', $debug);

  $wrd_ids = zut_ids_are ($word_id, $user_id, $debug);
  $result = zu_sql_wrd_ids_to_lst ($wrd_ids, $user_id, $debug-1);

  zu_debug('zut_lst_are -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// all word ids that are part of the given word e.g. for "cash flow statement" it will be "Sales" and others
function zut_ids_contains ($word_id, $user_id, $debug) {
  zu_debug('zut_ids_contains(t'.$word_id.')', $debug);
  $link_type_id = sql_code_link(SQL_LINK_TYPE_CONTAIN);
  $result = zut_db_tree ($word_id, $link_type_id, $user_id, $debug-1);

  zu_debug('zut_ids_contains -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// similar to zut_ids_contains, but also includes "sub" words that are of a containing type e.g. if Sales contains Country all countries like "Switzerland" are also included in the result
function zut_ids_contains_and_are ($start_wrd_id, $user_id, $debug) {
  zu_debug('zut_ids_contains_and_are(t'.$start_wrd_id.')', $debug);

  $result = array();
  $added[] = $start_wrd_id;
  while (!empty($added)) {
    $added_prior = $added;
    $added = array();
    foreach ($added_prior AS $wrd_id) {
      $wrd_ids = zut_ids_contains ($wrd_id, $user_id, $debug-1);
      foreach ($wrd_ids AS $sub_wrd_id) {
        if (!in_array($sub_wrd_id, $result)) {
          $result[] = $sub_wrd_id;
          $added[]  = $sub_wrd_id;
        }  
      }
      $wrd_ids = zut_ids_are ($wrd_id, $user_id, $debug-1);
      foreach ($wrd_ids AS $sub_wrd_id) {
        if (!in_array($sub_wrd_id, $result)) {
          $result[] = $sub_wrd_id;
          $added[]  = $sub_wrd_id;
        }  
      }
    }  
  }
  
  zu_debug('zut_ids_contains_and_are -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// add all potential differentiator words of the word lst to ther word list
function zut_db_lst_differentiators ($word_lst, $user_id, $debug) {
  zu_debug('zut_db_lst_differentiators (t'.implode(",",$word_lst).'u'.$user_id.')', $debug);
  $result = $word_lst;
  foreach ($word_lst as $sub_word_id) {
    $differantiator_words = zut_db_differantiator_words($sub_word_id, $debug-5);
    //zu_debug("zut_db_lst_differentiators ... differantiator word of ".$sub_word_id." are ".implode(",",$differantiator_words).".", $debug-5);
    // select only the differentiator words that have a value for the main word
    $new_words = zu_lst_not_in        ($differantiator_words, $result);
    //zu_debug("zut_db_lst_differentiators ... new words ".implode(",",$new_words).".", $debug-5);
    $result    = zu_lst_merge_with_key($differantiator_words, $result);
  }  

  zu_debug('zut_db_lst_differentiators -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// similar to zut_db_lst_differentiators, but only a filtered list of differentiators is viewed to increase speed
function zut_db_lst_differentiators_filtered ($word_ids, $filter_ids, $user_id, $debug) {
  zu_debug('zut_db_lst_differentiators_filtered (t'.implode(",",$word_ids).',f'.implode(",",$filter_ids).',u'.$user_id.')', $debug);
  $result = zu_sql_wrd_ids_to_lst($word_ids, $user_id, $debug-1);
  foreach ($word_ids as $sub_word_id) {
    $differantiator_words = zut_db_differantiator_words_filtered($sub_word_id, $filter_ids, $user_id, $debug-5);
    zu_debug('zut_db_lst_differentiators_filtered -> differantiator_words ('.implode(",",$differantiator_words).')', $debug-1);
    // select only the differentiator words that have a value for the main word
    $new_words = zu_lst_not_in        ($differantiator_words, $result);
    zu_debug('zut_db_lst_differentiators_filtered -> new_words ('.implode(",",$new_words).')', $debug-1);
    $result    = zu_lst_merge_with_key($new_words,            $result);
    zu_debug('zut_db_lst_differentiators_filtered -> merged words ('.implode(",",$result).')', $debug-1);
  }  

  zu_debug('zut_db_lst_differentiators_filtered -> ('.implode(",",$result).')', $debug-1);
  return $result;
}

// returns a sorted list of words as an array of word ids 
function zut_db_list ($start_word, $number_of_words, $debug) {
  zu_debug('zut_db_list', $debug);
  $result = 0;
  // find out if a word group matches the word list
  $phrase_group = 0;
  $query = "SELECT phrase_group_id FROM phrase_groups WHERE word_ids = '".zut_sql_ids($word_ids, $debug)."';";
  $sql_array = zu_sql_get($query, $debug);
  $phrase_group = $sql_array[0];

  // get the value for word group
  $query = "SELECT word_value FROM `values` WHERE phrase_group_id = ".$phrase_group." AND time_word_id = ".$time_word_id." ;";
  $sql_array = zu_sql_get($query, $debug);
  $result = $sql_array[0];

  return $result;
    
}

// get the word ids of a word group
function zutg_wrd_ids ($wrd_grp_id, $user_id, $debug) {
  zu_debug('zutg_wrd_ids ('.$wrd_grp_id.',u'.$user_id.')', $debug-10);

  $result = array();
  $sql = "SELECT word_ids FROM phrase_groups WHERE phrase_group_id = ".$wrd_grp_id.";";
  $wrd_ids_txt = zu_sql_get1($sql, $debug-10);
  $result = explode(",",$wrd_ids_txt);

  return $result;
}

// creates a list all parent words of the given word (foaf - friend of a friend - which means using a recursive search)
function zut_foaf_parent($word_id, $debug) {
  zu_debug('zut_foaf_parent('.$word_id.')', $debug);  

  $result = array();
  $parent_type = sql_code_link(SQL_LINK_TYPE_IS);

  // find direct parent words
  $result = zu_sql_get_lst(zu_sql_words_linked($word_id, $parent_type, "down", $debug), $debug-1);
  
  // find the indirect parents
  foreach (array_keys($result) AS $parent_id) {
    $foaf_words = zut_foaf_parent($parent_id, $debug-1);
    $result = zu_lst_merge_with_key($result, $foaf_words);
  }  
  
  zu_debug('zut_foaf_parent ... done ('.implode(",",$result).')', $debug-1);

  return $result;
}

// creates a list with the differantiator words (improve later by using an array instead of single SQL)
function zut_db_differantiator_words($word_id, $debug) {
  zu_debug('zut_db_differantiator_words ('.$word_id.')', $debug);  
  //echo '+diffa: '.$word_id.'<br>';

  $word_lst = array();
  $differantiator_type = sql_code_link(SQL_LINK_TYPE_DIFFERANTIATOR);

  // find direct differantiator words
  $word_lst = zu_sql_get_lst(zu_sql_words_linked($word_id, $differantiator_type, "up", $debug-5), $debug-5);
  zu_debug('zut_db_differantiator_words ... words linked ('.implode(",",$word_lst).')', $debug-5);  
  //echo '+diff: '.implode(",",$word_lst).'<br>';

  $is_a_type = sql_code_link(SQL_LINK_TYPE_IS);

  // add all words that are "is a" to the $differantiator list e.g. if the extra list contains Switzerland and Country is allowed as a differentiator Switzerland should be taken into account
  // temp solution for more than one differentiator
  $sub_words = zu_sql_word_lst_linked($word_lst, $is_a_type, "up", $debug-5);
  $word_lst = zu_lst_merge_with_key($word_lst, $sub_words);
  //echo 'combi: '.implode(",",$word_lst).'<br>';

  //$added_words = zu_lst_not_in($added_words, $xtra_words, $debug-5);
  // while (!empty($added_words)) {
/*  if (!empty($added_words)) {
    $xtra_words = zu_lst_merge_with_key($added_words, $xtra_words);
    $added_words = zu_sql_word_lst_linked($xtra_words, $is_a_type, "down", $debug);
    $added_words = zu_lst_not_in($added_words, $xtra_words, $debug-5);
  } */
  
  zu_debug('zut_db_differantiator_words ... done ('.implode(",",$word_lst).')', $debug-1);

  return $word_lst;
}

// creates a list with the differantiator words (improve later by using an array instead of single SQL)
function zut_db_differantiator_words_filtered($word_id, $filter_ids, $user_id, $debug) {
  zu_debug('zut_db_differantiator_words_filtered ('.$word_id.',f'.implode(",",$filter_ids).',u'.$user_id.')', $debug);  
  //echo '+diffa: '.$word_id.'<br>';

  $word_lst = array();
  $differantiator_type = sql_code_link(SQL_LINK_TYPE_DIFFERANTIATOR);
  zu_debug('zut_db_differantiator_words_filtered ... type ('.$differantiator_type.')', $debug-5);  

  // find direct differantiator words
  $word_lst = zu_sql_get_lst(zu_sql_words_linked($word_id, $differantiator_type, "up", $debug-5), $debug-5);
  if (count($word_lst) > 0) {
    zu_debug('zut_db_differantiator_words_filtered ... words linked ('.implode(",",$word_lst).')', $debug-5);  
  } else {  
    zu_debug('zut_db_differantiator_words_filtered ... no words linked', $debug-5);  
  }
  //echo '+diff: '.implode(",",$word_lst).'<br>';

  $is_a_type = sql_code_link(SQL_LINK_TYPE_IS);

  // add all words that are "is a" to the $differantiator list e.g. if the extra list contains Switzerland and Country is allowed as a differentiator Switzerland should be taken into account
  // temp solution for more than one differentiator
  $sub_words = zu_sql_word_lst_linked($word_lst, $is_a_type, "up", $debug-5);
  zu_debug('zut_db_differantiator_words_filtered ... sub words ('.implode(",",$sub_words).')', $debug-1);  
  $sub_words= zu_lst_in_ids($sub_words, $filter_ids, $debug-1);
  zu_debug('zut_db_differantiator_words_filtered ... sub words filtered ('.implode(",",$sub_words).')', $debug-1);  
  //$sub_wrd_lst = zu_sql_wrd_ids_to_lst($sub_words, $user_id, $debug-1);
  $word_lst = zu_lst_merge_with_key($word_lst, $sub_words);
  //echo 'combi: '.implode(",",$word_lst).'<br>';

  //$added_words = zu_lst_not_in($added_words, $xtra_words, $debug-5);
  // while (!empty($added_words)) {
/*  if (!empty($added_words)) {
    $xtra_words = zu_lst_merge_with_key($added_words, $xtra_words);
    $added_words = zu_sql_word_lst_linked($xtra_words, $is_a_type, "down", $debug);
    $added_words = zu_lst_not_in($added_words, $xtra_words, $debug-5);
  } */
  
  zu_debug('zut_db_differantiator_words_filtered ... done ('.implode(",",$word_lst).')', $debug-1);

  return $word_lst;
}



// write a new word to the database
// $wrd_name - the new name given by the user as a text
// $wrd_to   - the related word: every new word must have at least one relation to an existing word because otherwise it could not be found
// $type_id  - the word relation type of the new word to the existing word
// $add_id   - if just an existing word should be linked to a word, this id is set
// $link_id  - the word relation type of the new word to the existing word
// $user_id  - 

function zut_db_add ($wrd_name, $wrd_to, $type_id, $add_id, $link_id, $user_id, $debug) {
  zu_debug("zut_db_add (".$wrd_name.",to".$wrd_to.",type".$type_id.",add".$add_id.",v".$link_id.",u".$user_id.")", $debug);

  // check the parameter
  if ($link_id == 0) {
    $wrd_name = "";
    echo 'Link missing; Please press back and select a word link, because all new words must be linked in a defined way to an existing word.';
  }
  if ($wrd_to <= 0) {
    $wrd_name = "";
    echo 'Word missing; Please press back and select a related word, because all new words must be linked to an existing word.';
  }
  if ($type_id <= 0 and $wrd_name <> "") {
    $wrd_name = "";
    $add_id =0; // if new word in supposed to be added, but type is missing, do not add an existising word
    echo 'Type missing; Please press back and select a word type.';
  }
  
  // add a new or use an existing word
  $wrd_id = 0;
  if ($wrd_name <> "") {
    // check if word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
    $id_txt = zu_sql_id($wrd_name, $user_id, $debug-1);
    if ($id_txt <> "") {
      if (substr($id_txt, 0, strlen(ZUP_CHAR_FORMULA_START)) == ZUP_CHAR_FORMULA_START) {
        // maybe ask for confirmation
        // change the link type to "formula link"
        $type_id = cl(SQL_WORD_TYPE_FORMULA_LINK);
        zu_debug('word_add -> changed type to ('.$type_id.')', $debug);
        $id_txt = ""; // reset the id_txt, because this case would be fine
      }  
    }
    // test again, because in case of a formula name the word with the type formula link can be added
    if ($id_txt <> "") {
      echo zu_sql_id_msg($id_txt, $wrd_name, $user_id, $debug-1);
    } else {
      // log and add the new word if valid
      $log_id = zu_log($user_id, "add", "words", "word_name", "", $wrd_name, 0, $debug-1);
      if ($log_id > 0) {
        // insert the new word
        $wrd_id = zu_sql_insert("words", "word_name", sf($wrd_name), $user_id, $debug);
        if ($wrd_id > 0) {
          // update the id in the log
          $result = zu_log_upd($log_id, $wrd_id, $user_id, $debug-1);
          // save the owner and the type of the new word
          zu_log_ref($user_id, "add", "words", "word_type_id", "", $type_id, 0, zut_type_name($type_id), $wrd_id, $debug-1);
          zu_log_ref($user_id, "add", "words", "user_id",      "", $user_id, 0, zuu_name(),              $wrd_id, $debug-1);
          zu_sql_update("words", $wrd_id, "word_type_id", $type_id, $user_id, $debug);
          zu_sql_update("words", $wrd_id, "user_id",      $user_id, $user_id, $debug);
        } else {
          zu_err("Adding word ".$wrd_name." failed.", "zut_db_add");
        }
      }  
    }
  } else {
    // check if an existing word has been selected
    if ($add_id > 0) {
      $wrd_id = $add_id;
    }
  }
  
  // link the new or existing word
  $sql_result = zutl_db_add ($wrd_id, $link_id, $wrd_to, $user_id, $debug-1);

  return $wrd_id;
}

// add a new word group
function zutg_db_add ($word_ids, $group_name, $debug) {
  zu_debug('zutg_db_add', $debug);
  $sql_query = "INSERT INTO phrase_groups (word_ids, auto_description) VALUES ('".$word_ids."','".$group_name."');";
  $sql_result = mysql_query($sql_query);

  return $sql_result;
}

// creates a user word record
function zut_db_usr_add ($wrd_id, $user_id, $debug) {
  zu_debug("zut_db_usr_add (t".$wrd_id.",u".$user_id.")", $debug);
  $result = false;

  $usr_wrd_id = zu_sql_get("SELECT word_id FROM `user_words` WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";", $debug-1);
  if ($usr_wrd_id <= 0) {
    // create an entry in the user sandbox
    $sql = "INSERT INTO `user_words` (word_id, user_id) VALUES (".$wrd_id.",".$user_id.");";
    $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zut_db_usr_add", (new Exception)->getTraceAsString(), $debug-1);
  }  

  return $result;
}

// check if the user word record is still needed and if not remove it
function zut_db_usr_check ($wrd_id, $user_id, $debug) {
  zu_debug("zut_db_usr_check (t".$wrd_id.",u".$user_id.")", $debug);
  $result = false;

  $sql_std = "SELECT word_name, plural, description, word_type_id, excluded FROM words      WHERE word_id = ".$wrd_id.";";
  $sql_usr = "SELECT word_name, plural, description, word_type_id, excluded FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";";
  $result_std = zu_sql_get($sql, $debug-5);
  $result_usr = zu_sql_get($sql, $debug-5);
  if (($result_std[0] == $result_usr[0] OR $result_usr[0] === NULL)
  AND ($result_std[1] == $result_usr[1] OR $result_usr[1] === NULL)
  AND ($result_std[2] == $result_usr[2] OR $result_usr[2] === NULL)
  AND ($result_std[3] == $result_usr[3] OR $result_usr[3] === NULL)
  AND ($result_std[4] == $result_usr[4] OR $result_usr[4] === NULL)) {
    $sql_del = "DELETE FROM user_words WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";";
    $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zut_db_usr_check", (new Exception)->getTraceAsString(), $debug-1);
  }

  return $result;
}

// adjust the main parameters of a word
// can not the owner change the word type? Yes
// what if the user changes the name back to the original name?
// if a user (not the owner) adds a description, it should be changed in the original record
function zut_db_upd ($wrd_id, $wrd_name, $wrd_plural, $wrd_type, $wrd_description, $user_id, $debug) {
  zu_debug("zut_db_upd (t".$wrd_id.",".$wrd_name.",".$wrd_plural.",".substr($wrd_description,0,50).",".$wrd_type.",u".$user_id.")", $debug);
  $result = "";
  
  // read the database values to be able to check if something has been changed; done first, because it needs to be done for user and general words
  $old_name        = zut_name        ($wrd_id, $user_id, $debug-1);
  $old_plural      = zut_plural      ($wrd_id, $user_id, $debug-1);
  $old_description = zut_description ($wrd_id, $user_id, $debug-1);
  $old_type        = zut_type        ($wrd_id, $user_id, $debug-1);
  $old_type_name   = zut_type_name   ($old_type, $debug-1);
  $new_type_name   = zut_type_name   ($wrd_type, $debug-1);

  // if the name has changed, check if word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
  if ($old_name <> $wrd_name AND strtoupper($old_name) <> strtoupper($wrd_name)) {
    $id_txt = zu_sql_id($wrd_name, $user_id, $debug-1);
    if ($id_txt <> "") {
      $result = zu_sql_id_msg($id_txt, $wrd_name, $user_id, $debug-1);
    } 
  }  

  // if the check has found a problem, display it to the user
  if ($result <> "") {
    //echo $result;
  } else {  

    // update word name if needed
    if ($old_name <> $wrd_name) {
      if (zut_can_change($wrd_id, $user_id, $debug-1)) {
        if (zu_log($user_id, "update", "words", "word_name", $old_name, $wrd_name, $wrd_id, $debug-1) > 0 ) {
          $result = zu_sql_update("words", $wrd_id, "word_name", sf($wrd_name), $user_id, $debug-1);
        }
      } else {
        if (zu_log($user_id, "update", "user_words", "word_name", $old_name, $wrd_name, $wrd_id, $debug-1) > 0 ) {
          // create an entry in the user sandbox if needed
          $result = zut_db_usr_add ($wrd_id, $user_id, $debug-1);
          // update the user value
          $sql = "UPDATE user_words SET word_name = ".sf($wrd_name)." WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";";
          $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zut_db_usr_check", (new Exception)->getTraceAsString(), $debug-1);
          // check if the user sandbox is still needed for this word
          $result = zut_db_usr_check ($wrd_id, $user_id, $debug);
        }
      }
    }  
          
    // update word plural if needed
    if ($old_plural <> $wrd_plural) {
      if (zut_can_change($wrd_id, $user_id, $debug-1)) {
        if (zu_log($user_id, "update", "words", "plural", $old_plural, $wrd_plural, $wrd_id, $debug-1) > 0 ) {
          $result = zu_sql_update("words", $wrd_id, "plural", sf($wrd_plural), $user_id, $debug-1);
        }
      } else {
        if (zu_log($user_id, "update", "user_words", "plural", $old_plural, $wrd_plural, $wrd_id, $debug-1) > 0 ) {
          // create an entry in the user sandbox if needed
          $result = zut_db_usr_add ($wrd_id, $user_id, $debug-1);
          // update the user value
          $sql = "UPDATE user_words SET plural = ".sf($wrd_plural)." WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";";
          $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zut_db_usr_check", (new Exception)->getTraceAsString(), $debug-1);
          // check if the user sandbox is still needed for this word
          $result = zut_db_usr_check ($wrd_id, $user_id, $debug);
        }
      }
    }  
          
    // update word description if needed
    if ($old_description <> $wrd_description) {
      if (zut_can_change($wrd_id, $user_id, $debug-1)) {
        if (zu_log($user_id, "update", "words", "description", $old_description, $wrd_description, $wrd_id, $debug-1) > 0 ) {
          $result = zu_sql_update("words", $wrd_id, "description", sf($wrd_description), $user_id, $debug-1);
        }
      } else {
        if (zu_log($user_id, "update", "user_words", "description", $old_description, $wrd_description, $wrd_id, $debug-1) > 0 ) {
          // create an entry in the user sandbox if needed
          $result = zut_db_usr_add ($wrd_id, $user_id, $debug-1);
          // update the user value
          $sql = "UPDATE user_words SET description = ".sf($wrd_description)." WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";";
          $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zut_db_usr_check", (new Exception)->getTraceAsString(), $debug-1);
          // check if the user sandbox is still needed for this word
          $result = zut_db_usr_check ($wrd_id, $user_id, $debug);
        }
      }
    }  
          
    // update word type if needed
    if ($old_type <> $wrd_type) {
      if (zut_can_change($wrd_id, $user_id, $debug-1)) {
        if (zu_log_ref($user_id, "update", "words", "word_type_id", $old_type, $wrd_type, $old_type_name, $new_type_name, $wrd_id, $debug-1) > 0 ) {
          $result = zu_sql_update("words", $wrd_id, "word_type_id", $wrd_type, $user_id, $debug-1);
        }
      } else {
        if (zu_log_ref($user_id, "update", "user_words", "word_type_id", $old_type, $wrd_type, $old_type_name, $new_type_name, $wrd_id, $debug-1) > 0 ) {
          // create an entry in the user sandbox if needed
          $result = zut_db_usr_add ($wrd_id, $user_id, $debug-1);
          // update the user value
          $sql = "UPDATE user_words SET word_type_id = ".$wrd_type." WHERE word_id = ".$wrd_id." AND user_id = ".$user_id.";";
          $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zut_db_usr_check", (new Exception)->getTraceAsString(), $debug-1);
          // check if the user sandbox is still needed for this word
          $result = zut_db_usr_check ($wrd_id, $user_id, $debug);
        }
      }
    }  
  }

  zu_debug("zut_db_upd -> done(".$result.")", $debug-1);
  return $result;
}
/*
function zut_db_upd_usr_fld ($old_name, $fld_name, $wrd_id, $user_id, $debug) {
  zu_debug("zut_db_upd_usr_fld (".$old_name.",".$fld_name.",t".$wrd_id.",u".$user_id.")", $debug);
  $result = "";
  

  zu_debug("zut_db_upd_usr_fld -> done(".$result.")", $debug-1);
  return $result;
}
*/
// creates an sql select statment for the value_phrase_links based on an word array
function zutg_db_select_sql ($word_ids, $debug) {
  zu_debug('zutg_db_select_sql ... ', $debug);
  
  $sql_start = "SELECT l1.value_id ";
  $sql_from  = " FROM ";
  $sql_where = " WHERE ";
  $table_id = 1;
  foreach ($word_ids as $word_id) {
    if ($table_id == 1) {
      $sql_from  .= " value_phrase_links l".$table_id;
      $sql_where .= " l".$table_id.".phrase_id = ".$word_id;
    } else {
      $sql_from  .= ", value_phrase_links l".$table_id;
      $table_is_prev = $table_id - 1;
      $sql_where .= " AND l".$table_is_prev.".value_id = l".$table_id.".value_id AND l".$table_id.".phrase_id = ".$word_id;
    }
    $table_id = $table_id + 1;
  }
  
  return $sql_start.$sql_from.$sql_where." GROUP BY l1.value_id;";    
}

/* 
  ------------------------------------
  functions to be reviewed and renamed
  ------------------------------------
*/

// reads a value from the database and returns it
// the selection is based on a word / word_link list
function zut_value ($word_ids, $time_word_id, $user_id, $debug) {
  zu_debug('zut_value ('.$word_ids.','.$time_word_id.','.$user_id.')', $debug);
  $result = 0;
  // find out if a word group matches the word list
  //$phrase_group = 0;
  //$query = "SELECT phrase_group_id FROM phrase_groups WHERE word_ids = '".zut_sql_ids($word_ids, $debug)."';";
  //$sql_array = zu_sql_get($query, $debug);
  $phrase_group = zut_group_id ($word_ids, $debug-1);
  zu_debug('zut_value -> group id ('.$phrase_group.')', $debug-1);
  
  // get the value for word group
  $query = "SELECT word_value FROM `values` WHERE phrase_group_id = ".$phrase_group." AND time_word_id = ".$time_word_id." ;";
  $sql_array = zu_sql_get($query, $debug);
  $result = $sql_array[0];
  
  if ($result == '') {
    $word_lst = explode(",",$value_words);
    $word_lst[] = $time_word_id;
    $result = zuv_word_lst($word_lst, $debug);
    $value_id = zuv_word_lst_id($word_lst, $debug);
  }

  zu_debug('zut_value -> ('.$result.')', $debug-1);
  return $result;    
}

// add the group id to all values
function zut_group_review ($debug) {
  zu_debug('zut_group_review', $debug);
  $query = "SELECT value_id, phrase_id FROM value_phrase_links WHERE value_id > 0 AND phrase_id > 0 ORDER BY value_id, phrase_id;";
  $sql_result = mysql_query($query) or die('Query failed: ' . mysql_error());
  $last_value = 0;
  while ($link_row = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    if ($last_value <> $link_row[0]) {
      // save the last group
      if ($last_value > 0) {
        //echo 'regroup value '.$last_value.'->';
        //echo 'get group id for '.$word_ids.'->';
        $phrase_group = zut_group_id ($word_ids, $debug);
        //echo 'group id is '.$phrase_group.'->';
        if ($phrase_group > 0 AND $time_id > 0) {
          $sql_query = "UPDATE `values` SET phrase_group_id = ".$phrase_group.", time_word_id = ".$time_id." WHERE value_id = ".$last_value.";";
        }
        //echo $sql_query.'->';
        $update_result = mysql_query($sql_query);
      }
      //echo 'check for value '.$link_row[0].'->';
      $last_value = $link_row[0];
      if (zut_is_time($link_row[1], $debug) == false) {
        $word_ids = $link_row[1];
        $time_id = 0;
      } else {
        $word_ids = '';
        $time_id = $link_row[1];
      }
      //echo 'new ids '.$word_ids.'->';
    } else {
      if (zut_is_time($link_row[1], $debug) == false) {
        $word_ids .= ','.$link_row[1];
      } else {  
        $time_id = $link_row[1];
      }
      //echo 'ids '.$word_ids.'->';
    }
  }
  return 1;    
}



/* 
  -----------------------
  functions to be renamed
  -----------------------
*/



// creates an sql select statment for the value_phrase_links based on an word array
function zut_group_sql_select ($word_ids, $debug) {
  return zutg_db_select_sql($word_ids, $debug);
}




?>
