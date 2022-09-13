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
  
  

  
  TODO create a word object with functions .id .name
  
  
  
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/


/*
  word owner and permission functions
  ----------
*/  

// get the owner of the word; the owner is the user who created the word; the user that has first changed the word will be the owner if the original owner has exclude the word from his profile
function zut_owner($wrd_id) {
  log_debug('zut_owner (t'.$wrd_id.')');
  $user_id = zu_sql_get_value("words", "user_id", "word_id", $wrd_id);  
  return $user_id;
}

// if the word has been change by someone else than the owner the user id of the first one who has changed it, is returned
function zut_changer($wrd_id) {
  log_debug('zut_changer (t'.$wrd_id.')');
  $user_id = zu_sql_get_value("user_words", "user_id", "word_id", $wrd_id);
  return $user_id;
}

// true if the user is the owner and noone else has changed the value
function zut_can_change($wrd_id, $user_id) {
  log_debug('zut_can_change (t'.$wrd_id.',u'.$user_id.')');
  $can_change = false;
  $wrd_owner = zut_owner($wrd_id);
  if ($wrd_owner == $user_id OR $wrd_owner <= 0) {
    $wrd_user = zut_changer($wrd_id);
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
function zut_db_tree_up_level_type ($level, $word_id, $result, $link_type_id, $user_id) {
  log_debug('zut_db_tree_up_level_type('.$level.',t'.$word_id.','.$link_type_id.',u'.$user_id.')');
  $query = "   SELECT l.to_phrase_id, t.word_name " 
         . "     FROM word_links l, words t " 
         . "    WHERE l.to_phrase_id   = t.word_id " 
         . "      AND l.from_phrase_id = ".$word_id." " 
         . "      AND l.verb_id = ".$link_type_id." " 
         . " ORDER BY t.word_name;";
  $sql_result = zu_sql_get_all($query);
  while ($entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
    $sub_id = $entry[0];
    if ($sub_id > 0 AND !in_array($sub_id, $result)) {
      log_debug('zut_db_tree_up_level_type -> add '.$sub_id);
      $result[] = $sub_id;
      $result = zut_db_tree_up_level ($level + 1, $sub_id, $result);
    }
  }  
  return $result;    
}

// similar to zut_db_tree_up_level_type, but the other way round
// should be using zu_sql_word_lst_linked
function zut_db_tree_level_type ($level, $word_id, $result, $link_type_id, $user_id) {
  log_debug('zut_db_tree_level_type('.$level.',t'.$word_id.','.$link_type_id.',u'.$user_id.')');
  $query = "   SELECT l.from_phrase_id, t.word_name " 
         . "     FROM word_links l, words t " 
         . "    WHERE l.from_phrase_id   = t.word_id " 
         . "      AND l.to_phrase_id = ".$word_id." " 
         . "      AND l.verb_id = ".$link_type_id." " 
         . " ORDER BY t.word_name;";
  $sql_result = zu_sql_get_all($query);
  while ($entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
    $sub_id = $entry[0];
    if ($sub_id > 0 AND !in_array($sub_id, $result)) {
      log_debug('zut_db_tree_level_type -> add '.$sub_id);
      $result[] = $sub_id;
      $result = zut_db_tree_level ($level + 1, $sub_id, $result);
    }
  }  
  return $result;    
}

// build one level of a word tree
function zut_db_tree_up_level ($level, $word_id, $result, $link_type_id, $user_id) {
  log_debug('zut_db_tree_up_level(lev'.$level.',t'.$word_id.')');
  $loops = 0;
  do {
    $loops = $loops + 1;
    $adj_result   = zut_db_tree_up_level_type ($level, $word_id, $result, $link_type_id, $user_id);
    $added_words  = zu_lst_not_in_no_key      ($adj_result, $result);
    $result       = $adj_result;

    if ($loops >= MAX_RECURSIVE) {
      log_fatal("max number (".$loops.") of loops for word ".$word_id." reached.","zut_db_tree_up_level");
    }
  } while (!empty($added_words) AND $loops < MAX_RECURSIVE);
  return $result;    
}

// build one level of a word tree
function zut_db_tree_level ($level, $word_id, $result, $link_type_id, $user_id) {
  log_debug('zut_db_tree_level(lev'.$level.',t'.$word_id.')');
  $loops = 0;
  do {
    $loops = $loops + 1;
    $adj_result   = zut_db_tree_level_type ($level, $word_id, $result, $link_type_id, $user_id);
    $added_words  = zu_lst_not_in_no_key   ($adj_result, $result);
    $result       = $adj_result;

    if ($loops >= MAX_RECURSIVE) {
      log_fatal("max number (".$loops.") of loops for word ".$word_id." reached.","zut_db_tree_level");
    }
  } while (!empty($added_words) AND $loops < MAX_RECURSIVE);
  return $result;    
}

// returns an array of word ids, that characterises the given word e.g. for the id of "ABB Ltd." it will return the id of "Company" if the link type is "is a"
function zut_db_tree_up ($word_id, $link_type_id, $user_id) {
  log_debug('zut_db_tree_up(t'.$word_id.',l'.$link_type_id.')');
  $level = 0;
  $result = zut_db_tree_up_level ($level, $word_id, array(), $link_type_id, $user_id);

  log_debug('zut_db_tree_up -> ('.implode(",",$result).')');
  return $result;
}

// similar to zut_db_tree, but the other way round e.g. for the id of "Companies" it will return the id of "ABB Ltd." and others if the link type is "are"
function zut_db_tree ($word_id, $link_type_id, $user_id) {
  log_debug('zut_db_tree(t'.$word_id.',l'.$link_type_id.')');
  $level = 0;
  $result = zut_db_tree_level ($level, $word_id, array(), $link_type_id, $user_id);

  log_debug('zut_db_tree -> ('.implode(",",$result).')');
  return $result;
}

// id array of all words that the given word is related to e.g. for the id of "ABB Ltd." it will return the id of "Company"
function zut_ids_is ($word_id, $user_id) {
  log_debug('zut_ids_is(t'.$word_id.')');
  $link_type_id = cl(db_cl::VERB, verb::IS_A);
  $result = zut_db_tree_up ($word_id, $link_type_id, $user_id);

  log_debug('zut_ids_is -> ('.implode(",",$result).')');
  return $result;
}

// word ids that ARE of the type of the given word e.g. for "Company" it will be "ABB Ltd." and others
function zut_ids_are ($word_id, $user_id) {
  log_debug('zut_ids_are(t'.$word_id.',u'.$user_id.')');
  $link_type_id = cl(db_cl::VERB, verb::IS_A);
  $result = zut_db_tree ($word_id, $link_type_id, $user_id);

  log_debug('zut_ids_are -> ('.implode(",",$result).')');
  return $result;
}

// creates a list all parent words of the given word (foaf - friend of a friend - which means using a recursive search)
function zut_foaf_parent($word_id) {
  log_debug('zut_foaf_parent('.$word_id.')');

  $result = array();
  $parent_type = cl(db_cl::VERB, verb::IS_A);

  // find direct parent words
  $result = zu_sql_get_lst(zu_sql_words_linked($word_id, $parent_type, word_select_direction::DOWN));
  
  // find the indirect parents
  foreach (array_keys($result) AS $parent_id) {
    $foaf_words = zut_foaf_parent($parent_id);
    $result = zu_lst_merge_with_key($result, $foaf_words);
  }  
  
  log_debug('zut_foaf_parent ... done ('.implode(",",$result).')');

  return $result;
}
