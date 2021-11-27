<?php

/*

  zu_lib_word.php - old ZUkunft.com LIBrary for WORDs or terms  (just just for regression code testing)
  _______________

  prefix:
  zut_*      = Zukunft word related functions
  zutv_*     = Zukunft word link (verb) related functions
  zutg_*     = Zukunft word group related functions

  Var naming convention
  $id         - number of a database primary index
  $word_lst   - an array of words, where the array key is the database id and the array value is the unique word name
  $word_ids   - comma seperated string of word word_ids
  $word_names - comma seperated string with word string, each capsulet by highquotes
  $word_array - array of comma seperated string with word string, each capsulet by highquotes

  TODO: create an object word

  
  get functions
  ---
  
  zut_name                - simply to get the word name, if possible should not be used and an "all-in-one" query should be used for faster results
  zut_names               - to get a comma seperated word id list like 1,2,3; should be replace by lst functions
  zut_plural              - return the word name for more than one
  zut_type                - return the word type
  zut_is_name             - return the word category name based on the verb is e.g. ABB is a company, so the category for "ABB" is "company"
  zut_is_id               - similar to zut_is_name, but just returns the word id

  zut_id                  - word id for the given word string
  zutv_id                 - the verb id for the given string
  zutv_formula_id         - ??
  zut_ids
  zut_sql_ids

  zut_group_id            - return the word group (and create a new group if needed)
  zut_group_create        - zut_group_create
  
  zut_is_time             - true if the word has the type "time"
  zut_time_lst            - filter the time words out of the list of words
  zut_has_time            - true if a word lst contains a time word; maybe not really needed
  zut_time_ids            - filter the time words out of the list of words
  zut_time_useful         - create a useful list of time word
  zut_get_max_time        - get the time of the last value related to a word and assisiated to a word list
  zut_time_type_most_used - 
  zut_get_formula         -
  zut_names_to_lst        -
  zut_default_id          - if the user has given no hind at all guess an word the the user might be interested to start
  zut_select_top
  zut_is_default_word
  zut_find_missing_types
  zut_assume
  zut_keep_only_specific  - look at a word list and remove the general word, if there is a more specific word also part of the list e.g. remove "Country", but keep "Switzerland"
  

  

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

// default settings that each user can adjust for himself (with the option to reset to default or reset all th default or show the default overwrites
define("TIME_MIN_COLS", 3); // minimun number of same time type word to display in a table e.g. if at leat 3 years exist use a table to dislay
define("TIME_MAX_COLS", 10); // maximun number of same time type word to display in a table e.g. if more the 10 years exist, by default show only the lst 10 years
define("TIME_FUT_PCT", 20); // the default number of future outlook e.g. if there are 10 years of hist and 3 years of outlook display 8 years of hist and 2 years outlook

// return the word description for a comma seperated word id list like 1,2,3; the word list is used in the URL and this funktion can be used to display the words
function zut_names($word_list, $user_id)
{
    log_debug('zut_names(' . $word_list . ')');
    $word_description = "";
    $word_array = explode(",", $word_list);
    foreach ($word_array as $word_id) {
        if ($word_description == "") {
            $word_description = zut_name($word_id, $user_id);
        } else {
            $word_description = $word_description . " " . zut_name($word_id, $user_id);
        }
    }
    return $word_description;
}

// return the word type
function zut_type($wrd_id, $user_id)
{
    log_debug('zut_type (' . $wrd_id . ',u' . $user_id . ')');
    $result = null;
    if ($wrd_id > 0) {
        $wrd_del = zu_sql_get1("SELECT word_id FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND excluded = 1;");
        // only return a word if the user has not yet excluded the word
        if ($wrd_id <> $wrd_del) {
            $result = zu_sql_get1("SELECT word_type_id FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND (excluded is NULL OR excluded = 0);");
            if ($result == NULL) {
                $result = zu_sql_get_field('word', $wrd_id, 'word_type_id');
            }
        }
    }

    log_debug('zut_type (' . $wrd_id . '->' . $result . ')');
    return $result;
}

// return the word type name of a give word type id
function zut_type_name($type_id)
{
    log_debug('zut_type_name(' . $type_id . ')');
    return zu_sql_get_field('word_type', $type_id, sql_db::FLD_TYPE_NAME);
}

// return the word category name based on the verb is
function zut_is_name($id)
{
    log_debug('zut_is_name(' . $id . ')');
    $is_word = zut_is_id($id);
    $result = zu_sql_get_name('word', $is_word);
    return $result;
}

// return the word category id based on the predefined verb is
function zut_is_id($id)
{
    log_debug('zut_is_id(' . $id . ')');
    $link_id = cl(db_cl::VERB, verb::IS_A);
    $result = zu_sql_get_value_2key('word_links', 'to_phrase_id', 'from_phrase_id', $id, 'verb_id', $link_id);
    return $result;
}

// return the follow word id based on the predefined verb following
function zut_next_id($wrd_id, $user_id)
{
    log_debug('zut_next_id(' . $wrd_id . ',u' . $user_id . ')');
    $link_id = cl(db_cl::VERB, verb::DBL_FOLLOW);
    $result = zu_sql_get_value_2key('word_links', 'from_phrase_id', 'to_phrase_id', $wrd_id, 'verb_id', $link_id);
    return $result;
}

// return the prior word id based on the predefined verb following
function zut_prior_id($wrd_id, $user_id)
{
    log_debug('zut_prior_id(' . $wrd_id . ',u' . $user_id . ')');
    $link_id = cl(db_cl::VERB, verb::DBL_FOLLOW);
    $result = zu_sql_get_value_2key('word_links', 'to_phrase_id', 'from_phrase_id', $wrd_id, 'verb_id', $link_id);
    return $result;
}

// the word id for the given word string
function zut_id($wrd_name, $user_id)
{
    log_debug('zut_id(' . $wrd_name . ',u' . $user_id . ')');
    $wrd_id = 0;

    // if the user has overwritten the standard name, test this first
    if ($user_id > 0) {
        $wrd_id = zu_sql_get_id_usr('user_word', $wrd_name, $user_id);
    }
    if ($wrd_id <= 0) {
        $wrd_id = zu_sql_get_id('word', $wrd_name);
    }

    log_debug('zut_id -> (' . $wrd_id . ' for ' . $wrd_name . ')');
    return $wrd_id;
}

// the verb id for the given string
// old function, please replace with zul_id
function zutv_id($verb)
{
    log_debug('zutv_id(' . $verb . ')');
    return zu_sql_get_id('link_type', $verb);
}

// the word id for the given word string
function zutv_formula_id($verb)
{
    log_debug('zutv_formula_id(' . $verb . ')');
    return zu_sql_get_value('link_types', 'link_type_id', 'formula_name', $verb);
}

// returns an array of word ids based on a word string list like "turnover", "Nestlé"
function zut_ids($words, $user_id)
{
    log_debug('zut_ids ... words ' . $words);
    // split the word list in single words
    $word_list = explode(",", $words);
    // loop over the words and get the ids
    $word_id_list = array();
    foreach ($word_list as $word_name) {
        $word_id = zut_id(zu_str_between($word_name, '"', '"'), $user_id);
        if ($word_id > 0) {
            log_debug('zut_ids -> ' . $word_name . '=' . $word_id);
            array_push($word_id_list, $word_id);
        } else {
            log_debug('zut_ids -> no id found for ' . $word_id);
        }
    }
    return $word_id_list;
}

// get all words of one type
function zut_type_lst($wrd_type, $user_id)
{
    log_debug('zut_type_ids (' . $wrd_type . ',u' . $user_id . ')');

    $sql = "SELECT word_id, word_name FROM words WHERE word_type_id = " . $wrd_type . " ORDER BY word_name;";
    $result = zu_sql_get_lst($sql);
    return $result;
}

// true if the word id has a "is a" relation to the related word
// e.g.for the given word string
function zut_is_a($word_id, $related_word_id)
{
    log_debug('zut_is_a (' . $word_id . ',' . $related_word_id . ')');

    $result = false;
    $is_word_ids = zut_ids_is($word_id); // should be taken from the original array to increase speed
    if (in_array($related_word_id, $is_word_ids)) {
        $result = true;
    }

    log_debug('zut_is_a -> ' . zu_dsp_bool($result) . '' . $word_id);
    return $result;
}

// get all values related to the word
function zut_val_lst($wrd_id, $user_id)
{
    log_debug('zut_val_lst (' . $wrd_id . ',u' . $user_id . ')');

    $sql = "SELECT v.value_id, v.word_value FROM `values` v, value_phrase_links l WHERE l.phrase_id = " . $wrd_id . " AND l.value_id = v.value_id;";
    $result = zu_sql_get_lst($sql);

    log_debug('zut_val_lst -> ' . zu_lst_dsp($result) . '' . $wrd_id);
    return $result;
}

// returns an array of word ids based on an array of word names "turnover", "Nestlé"
function zut_array_ids($word_array, $user_id)
{
    log_debug('zut_ids ... words ' . $word_array);
    // loop over the words and get the ids
    $word_ids = array();
    foreach ($word_array as $word_name) {
        $word_id = zut_id($word_name, $user_id);
        if ($word_id > 0) {
            log_debug('zut_ids ... ' . $word_name . '=' . $word_id);
            $word_ids[] = $word_id;
        } else {
            log_debug('zut_ids ... no id found for ' . $word_id);
        }
    }
    return $word_ids;
}

// creates an SQL string to request the word group from a given word array
function zut_sql_ids($word_ids)
{
    log_debug('zut_sql_ids ... ');
    $word_list = "";
    // loop over the words and get the ids
    foreach ($word_ids as $word_id) {
        if ($word_list == "") {
            $word_list = $word_id;
        } else {
            $word_list .= "," . $word_id;
        }
    }
    return $word_list;
}

/* 
  ----------------------------
  Word group related functions
  ----------------------------
*/

// return the word group (and create a new group if needed)
// based on a string with the word ids
function zut_group_id($word_ids, $user_id)
{
    log_debug('zut_group_id (' . $word_ids . ',u' . $user_id . ')');
    $phrase_group = zu_sql_get_value("phrase_groups", "phrase_group_id", "word_ids", $word_ids);

    // create the word group if it is missing
    if ($phrase_group <= 0 or trim($phrase_group) == '') {
        //echo 'create new group for '.$word_ids.'->';
        $phrase_group = zut_group_create($word_ids, $user_id);
    }

    return $phrase_group;
}

// create a new word group
function zut_group_create($word_ids, $user_id)
{
    log_debug('zut_group_create ... ');

    $group_name = zut_names($word_ids, $user_id);
    log_debug('zut_group_create ... group name ' . $group_name);

    // write new group
    $sql_result = zutg_db_add($word_ids, $group_name);

    // get the id
    $phrase_group = zu_sql_get_value("phrase_groups", "phrase_group_id", "word_ids", $word_ids);

    // assign the new group to the value

    // loop over the word word_ids
    // select  all value that matches
    $query = "SELECT value_id FROM `value_phrase_links` WHERE phrase_group_id = " . $phrase_group . ";";


    return $phrase_group;
}

// if there is just one formula linked to the word, get it
function zut_formula($word_id, $user_id)
{
    log_debug('zut_formula (t' . $word_id . ',u' . $user_id . ')');

    $result = zu_sql_get_value("formula_word_links", "formula_id", "word_id", $word_id);
    return $result;
}


// return true if the word has the given type
function zut_is_type($word_id, $type)
{
    log_debug('zut_is_type (t' . $word_id . ',' . $type . ')');

    $result = false;
    $word_type = zu_sql_get_value("words", "word_type_id", "word_id", $word_id);
    if ($word_type == cl(db_cl::WORD_TYPE, $type)) {
        $result = true;
    }
    return $result;
}

// return true if the word has the type "time"
function zut_is_time($word_id)
{
    $result = zut_is_type($word_id, word_type_list::DBL_TIME);
    return $result;
}

// filter the time words out of the list of words
function zut_time_lst($word_lst)
{
    log_debug('zut_time_lst(' . zu_lst_dsp($word_lst) . ')');

    $result = array();
    $time_type = cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME);
    // loop over the word ids and add only the time ids to the result array
    foreach (array_keys($word_lst) as $word_id) {
        $word_type = $word_lst[$word_id][1];
        if ($word_type == $time_type) {
            $result[$word_id] = $word_lst[$word_id];
        }
    }
    log_debug('zut_time_lst ... done (' . zu_lst_dsp($result) . ')');
    return $result;
}

// filter the time words out of a list of word ids
function zut_time_ids($word_ids)
{
    log_debug('zut_time_ids(' . implode(",", $word_ids) . ')');

    $result = array();
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_ids as $word_id) {
        if (zut_is_time($word_id)) {
            $result[] = $word_id;
        }
    }
    log_debug('zut_time_ids -> done');
    return $result;
}

// exclude the time words from a list of word ids
function zut_ids_ex_time($word_ids, $user_id)
{
    log_debug('zut_ids_ex_time(' . implode(",", $word_ids) . ',u' . $user_id . ')');

    $result = array();
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_ids as $word_id) {
        if (!zut_is_time($word_id)) {
            $result[] = $word_id;
        }
    }
    log_debug('zut_ids_ex_time -> done');
    return $result;
}

// filter the first / best time word out of a list of word ids
// includes the user id, because potentially the user could change the type
function zut_time_id($word_ids, $user_id)
{
    log_debug('zut_time_id(' . implode(",", $word_ids) . ',u' . $user_id . ')');

    $result = null;
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_ids as $word_id) {
        if (zut_is_time($word_id)) {
            $result = $word_id;
        }
    }
    log_debug('zut_time_id -> done');
    return $result;
}

// true if a word lst contains a time word
function zut_has_time($word_lst)
{
    log_debug('zut_has_time(' . implode(",", $word_lst) . ')');

    $result = false;
    // loop over the word ids and add only the time ids to the result array
    foreach (array_keys($word_lst) as $word_id) {
        if ($result == false) {
            if (zut_is_time($word_id)) {
                $result = true;
            }
        }
    }
    log_debug('zut_has_time ... done (' . zu_dsp_bool($result) . ')');
    return $result;
}

// create a useful list of time word
function zut_time_useful($word_lst)
{
    log_debug('zut_time_useful(' . zu_lst_dsp($word_lst) . ')');

    //$result = zu_lst_to_flat_lst($word_lst);
    $result = $word_lst;
    asort($result);
    // sort
    //print_r($word_lst);

    // get the most ofter time type e.g. years if the list contains more than 5 years
    //$type_most_used = zut_time_type_most_used ($word_lst);

    // if nothing special is defined try to select 20 % outlook to the future
    // get latest time without estimate
    // check the number of none estimate results
    // if the hist is longer than it should be dfine the start word
    // fill from the start word the default number of words


    log_debug('zut_time_useful -> (' . zu_lst_dsp($result) . ')');
    return $result;
}

// get the most useful time for the given words
function zut_assume_time($word_ids, $user_id)
{
    // fix wrd_ids if needed
    $word_ids = zu_ids_not_empty($word_ids);

    log_debug('zut_assume_time(' . implode(",", $word_ids) . ',u' . $user_id . ')');

    $word_lst = zu_sql_wrd_ids_to_lst($word_ids, $user_id);
    if (zut_has_time($word_lst)) {
        $time_word_lst = zut_time_lst($word_lst);
        $time_word_ids = array_keys($time_word_lst);
        // shortcut, replace with a most_useful function
        $result = $time_word_ids[0];
    } else {
        //$result = zut_get_max_time($word_ids[0], $word_ids, $user_id);
        $result = zut_get_max_time_all($word_ids[0], $word_ids, $user_id);
    }

    log_debug('zut_assume_time -> time used "' . zut_name($result, $user_id) . '" (' . $result . ')');
    return $result;
}

// get the time of the last value related to a word and assisiated to a word list
function zut_get_max_time($word_id, $word_lst, $user_id)
{
    log_debug('zut_get_max_time(' . $word_id . ',' . implode(",", $word_lst) . ',' . $user_id . ')');

    $result = 0;

    // get all values related to the selectiong word, because this is probably strongest selection and to save time reduce the number of records asap
    $value_lst = zu_sql_word_values($word_id, $user_id);

    if (sizeof($value_lst) > 0) {

        // get all words related to the value list
        $all_word_lst = zu_sql_value_lst_words($value_lst, $user_id);

        // get the time words
        $time_lst = zut_time_lst($all_word_lst);

        // get the most usefult (last) time words (replace by a "followed by" sorted list
        arsort($time_lst);
        $time_keys = array_keys($time_lst);
        $result = $time_keys[0];
    }

    log_debug('zut_get_max_time ... done (' . $result . ')');
    return $result;
}

// get the time of the last value related to a word and assisiated to a word list
function zut_get_max_time_all($word_id, $word_lst, $user_id)
{
    log_debug('zut_get_max_time_all(' . $word_id . ',' . implode(",", $word_lst) . ',' . $user_id . ')');

    $result = 0;

    // get all values related to the selectiong word, because this is probably strongest selection and to save time reduce the number of records asap
    $value_in = zuv_of_wrd_ids($word_lst, $user_id);
    $value_lst = array();
    $value_lst[$value_in['id']] = $value_in['num'];
    log_debug('zut_get_max_time_all -> (' . implode(",", $value_lst) . ')');

    if (sizeof($value_lst) > 0) {

        // get all words related to the value list
        $all_word_lst = zu_sql_value_lst_words($value_lst, $user_id);

        // get the time words
        $time_lst = zut_time_lst($all_word_lst);

        // get the most usefult (last) time words (replace by a "followed by" sorted list
        arsort($time_lst);
        $time_keys = array_keys($time_lst);
        $result = $time_keys[0];
    }

    log_debug('zut_get_max_time_all ... done (' . $result . ')');
    return $result;
}

// get the most ofter time type e.g. years if the list contains more than 5 years
function zut_time_type_most_used($word_lst)
{
    log_debug('zut_time_type_most_used(' . $word_lst . ')');

    // get the most ofter time type e.g. years if the list contains more than 5 years
    // if nothing special is defined try to select 20 % outlokk to the future
    $result = $word_lst->lst[0];

    return $result;
}

// true if a word lst contains a scaling word
function zut_has_scaling($word_ids)
{
    log_debug('zut_has_scaling (' . implode(",", $word_ids) . ')');
    global $debug;

    $result = false;
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_ids as $word_id) {
        if ($result == false) {
            if (zut_is_type($word_id, word_type_list::DBL_SCALING, $debug - 1)
                or zut_is_type($word_id, word_type_list::DBL_SCALING_HIDDEN)) {
                $result = true;
            }
        }
    }
    log_debug('zut_has_scaling ... done (' . zu_dsp_bool($result) . ')');
    return $result;
}

// get the (first) scaling words of the word lst
function zut_scale_lst($word_lst)
{
    log_debug('zut_scale_lst(' . zu_lst_dsp($word_lst) . ')');

    $result = array();
    $scale_type = cl(db_cl::WORD_TYPE, word_type_list::DBL_SCALING);
    $scale_type_hidden = cl(db_cl::WORD_TYPE, word_type_list::DBL_SCALING_HIDDEN);
    // loop over the word ids and add only the time ids to the result array
    foreach (array_keys($word_lst) as $word_id) {
        $word_type = $word_lst[$word_id][1];
        if ($word_type == $scale_type or $word_type == $scale_type_hidden) {
            $result[$word_id] = $word_lst[$word_id];
        }
    }
    log_debug('zut_scale_lst ... done (' . zu_lst_dsp($result) . ')');
    return $result;
}

// get the (last) scaling word of the word id list
function zut_scale_id($wrd_ids, $user_id)
{
    log_debug('zut_scale_id (' . implode(",", $wrd_ids) . ',u' . $user_id . ')');

    $result = -1;
    $scale_type = cl(db_cl::WORD_TYPE, word_type_list::DBL_SCALING);
    $scale_type_hidden = cl(db_cl::WORD_TYPE, word_type_list::DBL_SCALING_HIDDEN);
    // loop over the word ids and add only the time ids to the result array
    foreach ($wrd_ids as $word_id) {
        $word_type = zut_type($word_id, $user_id);
        if ($word_type == $scale_type or $word_type == $scale_type_hidden) {
            $result = $word_id;
        }
    }
    log_debug('zut_scale_id ... done (' . $result . ')');
    return $result;
}

function zut_get_formula($word_names)
{
    log_debug('zut_get_formula(' . implode(",", $word_names) . ')');

    $result = '';
    //$word_array = explode(",",$word_names);
    $word_array = $word_names;
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_array as $word_name) {
        $formula_id = zuf_id($word_name);
        log_debug('zut_get_formula -> "' . $word_name . '" (id ' . $formula_id . ')');
        if ($formula_id > 0) {
            $result = $word_name;
        }
    }
    log_debug('zut_get_formula ... "' . $result . '" ');
    return $result;
}

// convert an array of word names to an array, where the array key is the database ID
function zut_names_to_lst($word_names, $user_id)
{
    log_debug('zut_names_to_lst(' . implode(",", $word_names) . ')');

    $result = array();
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_names as $word_name) {
        $word_id = zut_id($word_name, $user_id);
        log_debug('zut_names_to_lst -> "' . $word_name . '" (id ' . $word_id . ')');
        if ($word_id > 0) {
            $result[$word_id] = $word_name;
        }
    }
    log_debug('zut_names_to_lst ... "' . implode(",", $result) . '" ');
    return $result;
}


// returns an array with only the time word
/* function zut_time_ids ($word_ids) {
  // split the word list in single words
  $word_list = "";
  // loop over the words and get the ids
  // $word_id_list = array();
  foreach ($word_ids as $word_id) {
    if ($word_list == "") { 
      $word_list = $word_id;
    } else {
      $word_list = $word_list.",".$word_id;
    }
  }
  
  return $word_list;

} 

*/


// returns an array with only the time word
/* function zut_non_time_ids ($word_ids) {
  // split the word list in single words
  $word_list = "";
  // loop over the words and get the ids
  // $word_id_list = array();
  foreach ($word_ids as $word_id) {
    if ($word_list == "") { 
      $word_list = $word_id;
    } else {
      $word_list = $word_list.",".$word_id;
    }
  }
  
  return $word_list;

} */


/*

Default functions - assuming the best guess

*/

// if the user has given no hind at all guess an word the the user might be interested to start
function zut_default_id($user_id)
{
    log_debug('zut_default_id(' . $user_id . ')');
    $result = zu_sql_get_value("users", "last_word_id", "user_id", $user_id);
    if ($result <= 0) {
        $result = 1; // if nothing is know, start with the first word
    }
    log_debug('zut_default_id->' . $result . ')');
    return $result;
}

/*

Functions to be reviewed

*/


// selects out of a word list the most importand word
// e.g. given the word list "Turnover, Nestlé, 2014, GAAP", "Turnover" and "Nestlé" is selected, 
// because "2014" is the default time word for a company 
// and "GAAP" is the default Accounting word for a company 
function zut_select_top($debug)
{
}

// returns true if the test_word is the default type word for the word list
// e.g. for the word list "Turnover, Nestlé" the default "time_word" is is the actual year
function zut_is_default_word($word_list, $word_type, $test_word)
{
}

// returns an array of the missing word types
// e.g. ("Nestlé", "turnover") with formula "increase" returns "time_jump" is missing
function zut_find_missing_types($word_array, $formula_id)
{
    // get needed word type
    // get word types existing
    // find missing
    $result = zu_sql_get_value_2key("formula_word_type_links", "word_type_id", "formula_id", $formula_id, "link_type_id", 5, 0);
    return $result;
}

// returns the "nearest" word of a given type related to a word array
// e.g. ("Nestlé", "turnover") with word_type "time_jump" gets nothing on the first level
// but it finds "YoY" related to Company linked to Nestlé
function zut_assume($word_array, $word_type_id)
{
    $result = "YoY";
    return $result;
}

// look at a word list and remove the general word, if there is a more specific word also part of the list e.g. remove "Country", but keep "Switzerland"
function zut_keep_only_specific($word_array)
{
    log_debug('zut_keep_only_specific(' . implode(",", $word_array) . ')');

    $result = $word_array;
    foreach ($word_array as $word_id) {
        $word_types = zut_foaf_parent($word_id);
        log_debug('zut_keep_only_specific -> ' . $word_id . ' is of type (' . implode(",", $word_types) . ')');
        $result = zu_lst_not_in_no_key($result, array_keys($word_types));
    }

    log_debug('zut_keep_only_specific -> (' . implode(",", $result) . ')');

    return $result;
}


?>
