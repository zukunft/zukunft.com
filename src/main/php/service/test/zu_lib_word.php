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

// default settings that each user can adjust for himself (with the option to reset to default or reset all th default or show the default overwrites
use cfg\phrase_type;

define("TIME_MIN_COLS", 3); // minimun number of same time type word to display in a table e.g. if at leat 3 years exist use a table to dislay
define("TIME_MAX_COLS", 10); // maximun number of same time type word to display in a table e.g. if more the 10 years exist, by default show only the lst 10 years
define("TIME_FUT_PCT", 20); // the default number of future outlook e.g. if there are 10 years of hist and 3 years of outlook display 8 years of hist and 2 years outlook


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


/* 
  ----------------------------
  Word group related functions
  ----------------------------
*/


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
    $result = zut_is_type($word_id, phrase_type::TIME);
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



// true if a word lst contains a scaling word
function zut_has_scaling($word_ids)
{
    log_debug('zut_has_scaling (' . implode(",", $word_ids) . ')');
    global $debug;

    $result = false;
    // loop over the word ids and add only the time ids to the result array
    foreach ($word_ids as $word_id) {
        if ($result == false) {
            if (zut_is_type($word_id, phrase_type::SCALING, $debug - 1)
                or zut_is_type($word_id, phrase_type::SCALING_HIDDEN)) {
                $result = true;
            }
        }
    }
    log_debug('zut_has_scaling ... done (' . zu_dsp_bool($result) . ')');
    return $result;
}


// get the (last) scaling word of the word id list
function zut_scale_id($wrd_ids, $user_id)
{
    log_debug('zut_scale_id (' . implode(",", $wrd_ids) . ',u' . $user_id . ')');

    $result = -1;
    $scale_type = cl(db_cl::WORD_TYPE, phrase_type::SCALING);
    $scale_type_hidden = cl(db_cl::WORD_TYPE, phrase_type::SCALING_HIDDEN);
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


