<?php

/*

  zu_lib_calc.php - old Zukunft formula Parser  (just just for regression code testing)
  ---------------

  prefix: zuc_* 

  A two step approach is used:
  1. replace words, verbs and formula links with numeric values
  2. calculate the math result

  formulas are interpretet from left to right
  
  
  convertion support functions that look at the complete formula
  ------------------
  
  zuc_pos_seperator    - returns the position of the corresponding seperator and takes brakets into account
  zuc_pos_word         - get the position of the word id in the database reference format
  zuc_pos_link         - get the position of the verb id in the database reference format
  zuc_pos_formula      - get the position of the formula id in the database reference format
  zuc_has_words        - returns true if the formula string contains a word link in the database reference format
  zuc_has_links        - returns true if the formula string contains a verb link in the database reference format
  zuc_has_formulas     - returns true if the formula string contains a formula link in the database reference format
  zuc_has_refs         - returns true if the formula string contains a word, verb or formula link in the database reference format
  
  
  convertion support functions that look at the left part
  ------------------
  
  zuc_func_name        - return the left lost function name of the formula
  zuc_has_function     - returns true if the formula string starts with a fixed function
  zuc_get_word         - returns true if the formula string with a word link in the database reference format

  
  information and detection functions
  -------------------------
  
  zuc_is_text_only         - true if the remaining formula part is only text
  zuc_has_word             - returns true if the formula string with a word link in the database reference format

  
  value replace functions
  -------------
  
  zuc_2db            - start to replace all word, verb and formula names with database IDs
  zuc_2db_part       - replace all word, verb and formula names with database IDs in the given formula part
  zuc_2num           - start to replace all database IDs with database values
  zuc_2num_part      - replace all database IDs with database values in the given formula part
  zuc_2val           - calculate the numeric result (replace this with an R project call!)
  zuc_2val_part      - 

  old combined function that should not be used any more
  zuc_parse          - parses a zukunft.com formula and returns the converted result
  zuc_part           - parses a formula and returns the converted result

  
  
  functions to review
  -------------------
  
  zuc_is_date - ???
  zuc_get_var - ???


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


// interface const (to be removed, because specific functions for each part has been created)
define('ZUP_RESULT_TYPE_DB', 'db');    // returns a formula in the database format
define('ZUP_RESULT_TYPE_USER', 'user');  // returns a formula in the user format
define('ZUP_RESULT_TYPE_VALUE', 'value'); // returns a result of the formula


/* sample

original request: formula: increase, words: "Nestlé", "turnover"
formula "increase": (next[] - last[]) / last[]
formula "next": needs time jump value[is time jump for 
so                       -> next["time jump"->,         "follower of"->"Now"]
1. find_missing_word_types: next["time jump"->"Company","follower of"->"Now"]
2. calc word:               next["YoY",                 "follower of"->"Now"]
3. calc word:               next["YoY",                 "follower of"->"This Year"]
4. calc word:               next["YoY",                 "follower of"->"2013"]
5. calc word:               next["YoY",                 "2014"]


parse

1. get inner part
2. do fixed functions
3. add word
4. get values

rules:
a verb cannot have the same name as a word
a formula cannot have the same name as a word
if needed just add "(formula)"



next needs time jump -> value[is time jump for->:time_word]
this year: 
2013 is vorgänger von 2014 
time jump for company is "YoY" -> word link table
next 

formula types: calc:

syntax: function_name["link_type->word_type:word_name"]
or: word_from>word_link:word_to e.g. “>is a:Company” lists all companies
or: word[condition_formula] e.g. “GAAP[>is:US]” use GAAP only for US based companies
or: word1 || word2 e.g. “GAAP[>is:US]” use word1 or word2 (maybe replace “||” with “or” for users)
or: -word e.g. “-word” remove the word from the context

samples: next["->Timejump"] means next needs a time jump word
db format: {f2}[->{}]

Four steps to get the result
1. replace functions
2. complete words
3. get values
4. send calc to octave


zu_calc("increase("Nestlé", "turnover")")
  -> find_missing_word_types("Nestlé", "turnover") with formula "increase" (zu_word_find_missing_types ($word_array, $formula_id))
    -> increase needs time_jump word_type
  -> assume_missing words("Nestlé", "turnover") with word_type "time_jump"
      -> get time_jump for "Nestlé", "turnover"
        -> add_word "YoY" linked to "Company" linked to "Nestlé"
        -> result increase("Nestlé", "turnover", "YoY")
-> zu_calc("next(Nestlé, turnover, YoY)")
   -> increase formula: (next() - last()) / last()
   -> add_word("Next Year")
      -> zu_calc("get_value(Nestlé, turnover, YoY, Next Year)")

Sample 2
zu_calc("countryweight("Nestlé")")
-> formula = '="Country" "differentiator"/"differentiator total"' // convert predefined formula "differentiator total" 
-> "differentiator total" = '=sum("differentiator")' // convert predefined formula "differentiator total" 
-> call 'get words("Nestlé" "Country" "differentiator")', which returns a list of words ergo the result will be a list
-> call 'get values("Nestlé" "Country" "differentiator")', which returns a list of values
-> call function sum to get the sum of the differentiators
-> calc the percent result for each value
      
      
      
Parser

The persing is done in two steps:

1. add default words and replace the words with value
2. calc the result

Do automatic caching of the results if needed

*/


/*
  conversion support functions that look at the complete formula
  ------------------                            --------
*/
// returns the position of the word id in the database reference format
function zuc_pos_word($formula): int
{
    log_debug("zuc_pos_word (" . $formula . ")");
    $result = -1;

    $calc = new math();

    $pos = $calc->pos_separator($formula, expression::MAKER_WORD_START, 0,);
    $end = $calc->pos_separator($formula, expression::MAKER_WORD_END, $pos);
    if ($pos >= 0 and $end > $pos) {
        $result = $pos;
    }

    log_debug("zuc_pos_word ->  (" . $result . ")");
    return $result;
}

// returns the position of the verb id in the database reference format
function zuc_pos_link($formula): int
{
    log_debug("zuc_pos_link (" . $formula . ")");
    $result = -1;

    $calc = new math();

    $pos = $calc->pos_separator($formula, expression::MAKER_TRIPLE_START, 0,);
    $end = $calc->pos_separator($formula, expression::MAKER_TRIPLE_END, $pos);
    if ($pos >= 0 and $end > $pos) {
        $result = $pos;
    }

    log_debug("zuc_pos_link ->  (" . $result . ")");
    return $result;
}

// returns the position of the formula id in the database reference format
function zuc_pos_formula($formula)
{
    log_debug("zuc_pos_formula (" . $formula . ")");
    $result = -1;

    $calc = new math();

    $pos = $calc->pos_separator($formula, expression::MAKER_FORMULA_START, 0,);
    $end = $calc->pos_separator($formula, expression::MAKER_FORMULA_END, $pos);
    if ($pos >= 0 and $end > $pos) {
        $result = $pos;
    }

    log_debug("zuc_pos_formula -> (" . $result . ")");
    return $result;
}

// returns true if the formula contains a word link
function zuc_has_words($formula): bool
{
    log_debug("zuc_has_words (" . $formula . ")");
    $result = false;

    if (zuc_pos_word($formula) >= 0) {
        $result = true;
    }

    log_debug("zuc_has_words -> (" . zu_dsp_bool($result) . ")");
    return $result;
}

// returns true if the formula contains a verb link
function zuc_has_links($formula)
{
    log_debug("zuc_has_links (" . $formula . ")");
    $result = false;

    if (zuc_pos_link($formula) >= 0) {
        $result = true;
    }

    log_debug("zuc_has_links -> (" . zu_dsp_bool($result) . ")");
    return $result;
}

// returns true if the formula contains a formula link
function zuc_has_formulas($formula)
{
    log_debug("zuc_has_formulas (" . $formula . ")");
    $result = false;

    if (zuc_pos_formula($formula) >= 0) {
        $result = true;
    }

    log_debug("zuc_has_formulas -> (" . zu_dsp_bool($result) . ")");
    return $result;
}

// returns true if the formula contains a word, verb or formula link
function zuc_has_refs($formula)
{
    log_debug("zuc_has_refs (" . $formula . ")");
    $result = false;

    if (zuc_has_words($formula)
        or zuc_has_links($formula)
        or zuc_has_formulas($formula)) {
        $result = true;
    }

    log_debug("zuc_has_refs -> (" . zu_dsp_bool($result) . ")");
    return $result;
}


/*
  convertion support functions that look at the left part of the formula
  ------------------                            ---------
*/


// if the remaining formula part is only text, do not parse it any more
function zuc_is_text_only($formula)
{
    log_debug("zuc_is_text_only (" . $formula . ")");

    $result = false;
    // if value is quoted text, just return the text without quotes
    if ($formula[0] == ZUP_CHAR_TXT_FIELD && substr($formula, -1) == ZUP_CHAR_TXT_FIELD) {
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}






// returns true if the formula string starts with a formula saved in the database
function zuc_has_formula($formula)
{
    log_debug("zuc_has_formula (" . $formula . ")");

    $result = False;

    // zu_debug(" -> ".substr($formula, 0, strlen(expression::MAKER_FORMULA_START))." = ".expression::MAKER_FORMULA_START."?");
    if (substr($formula, 0, strlen(expression::MAKER_FORMULA_START)) == expression::MAKER_FORMULA_START) {
        log_debug("zuc_has_formula -> found");
        $result = True;
    }

    log_debug("zuc_has_formula ... done (" . zu_dsp_bool($result) . ")");
    return $result;
}



// ????
function zuc_is_date($datetext)
{
    log_debug("zuc_is_date (" . $datetext . ")");

    $result = false;

    $date = date_parse($datetext);
    if (checkdate($date["month"], $date["day"], $date["year"])) {
        $result = true;
    }

    return $result;
}



// include the results of the underlying formulas, but only the once related to one of the words assigned to the formula
// e.g. if the "Earnings per Share" is used in the formula and the results for ABB should be updated, 
// one calculation request for "Earnings per Share" "ABB" "2016" and one for "Earnings per Share" "ABB" "2017" may be needed
function zuc_upd_lst_fv($val_wrd_lst, $wrd_id, $frm_ids, $frm_row, $usr_id)
{
    log_debug('zuc_upd_lst_fv(vt' . dsp_array($val_wrd_lst) . ',t' . $wrd_id . ',' . dsp_array($frm_ids) . ',u' . $usr_id . ')');
    global $debug;

    $result = array();

    $frm_val_lst = zuvc_frm_related_grp_wrds($val_wrd_lst, $wrd_id, $frm_ids, $usr_id);

    foreach (array_keys($frm_val_lst) as $frm_val_id) {
        /* maybe use for debugging */
        if ($debug > 0) {
            $debug_txt = "";
            $debug_wrd_ids = $frm_val_lst[$frm_val_id][1];
            foreach ($debug_wrd_ids as $debug_wrd_id) {
                $debug_txt .= ", " . zut_name($debug_wrd_id);
            }
        }
        log_debug('zuc_upd_lst_fv -> calc ' . $frm_row['formula_name'] . ' for ' . zut_name($wrd_id, $usr_id) . ' (' . $wrd_id . ') based of a formula result' . $debug_txt);

        // get the group words
        $wrd_ids = $frm_val_lst[$frm_val_id][1];
        // add the formula assigned word if needed
        if (!in_array($wrd_id, $wrd_ids)) {
            $wrd_ids[] = $wrd_id;
        }

        // build the single calculation request
        $calc_row = array();
        $calc_row['usr_id'] = $usr_id;
        $calc_row['frm_id'] = $frm_row[formula::FLD_ID];
        $calc_row['frm_name'] = $frm_row['formula_name'];
        $calc_row['frm_text'] = $frm_row['formula_text'];
        $calc_row['wrd_ids'] = $wrd_ids;
        $result[] = $calc_row;
    }

    log_debug('zuc_upd_lst_fv -> (' . dsp_count($result) . ')');
    return $result;
}

// get all values related to assigned word and to the formula words 
// and request on formula result for each word group
// e.g. the formula is assigned to Company and the operating income formula result should be calulated
//      so Sales and Cost are words of the formula
//      if Sales and Cost for 2016 and 2017 and EUR and CHF are in the database
// TODO: check if a value is used in the formula
//       exclude the time word and if needed loop over the time words
//       if the value has been update, create a calculation request
function zuc_upd_lst_val($wrd_id, $frm_wrd_ids, $frm_row, $usr_id)
{
    log_debug('zuc_upd_lst_val(t' . $wrd_id . ',' . dsp_array($frm_wrd_ids) . ',u' . $usr_id . ')');

    global $debug;

    $result = array();

    $value_lst = zuv_frm_related_grp_wrds($wrd_id, $frm_wrd_ids, $usr_id);

    foreach (array_keys($value_lst) as $val_id) {
        /* maybe use for debugging */
        if ($debug > 0) {
            $debug_txt = "";
            $debug_wrd_ids = $value_lst[$val_id][1];
            foreach ($debug_wrd_ids as $debug_wrd_id) {
                $debug_txt .= ", " . zut_name($debug_wrd_id);
            }
        }
        log_debug('zuc_upd_lst -> calc ' . $frm_row['formula_name'] . ' for ' . zut_name($wrd_id, $usr_id) . ' (' . $wrd_id . ')' . $debug_txt);

        // get the group words
        $wrd_ids = $value_lst[$val_id][1];
        // add the formula assigned word if needed
        if (!in_array($wrd_id, $wrd_ids)) {
            $wrd_ids[] = $wrd_id;
        }

        // build the single calculation request
        $calc_row = array();
        $calc_row['usr_id'] = $usr_id;
        $calc_row['frm_id'] = $frm_row[formula::FLD_ID];
        $calc_row['frm_name'] = $frm_row['formula_name'];
        $calc_row['frm_text'] = $frm_row['formula_text'];
        $calc_row['wrd_ids'] = $wrd_ids;
        $result[] = $calc_row;
    }

    log_debug('zuc_upd_lst_val -> (' . dsp_count($result) . ')');
    return $result;
}

// update the progress bar for the user
function zuc_upd_lst_msg($last_msg_time, $pos, $total, $frm_row)
{
    // show the user the progress every two seconds
    if ($last_msg_time + UI_MIN_RESPONSE_TIME < time()) {
        $calc_pct = ($pos / $total) * 100;
        echo "calculate collect " . round($calc_pct, 2) . "% (" . $frm_row['resolved_text'];
        //echo "calculate collect ".round($calc_pct,2)."% (".$frm_row['resolved_text']." for ".$usr_lst[$usr_id].")<br>";
        $last_msg_time = time();
        flush();
    }

    return $last_msg_time;
}

// like zuf_frm_ids, but also including the special formulas
function zuc_upd_lst_frm_special($frm_id, $frm_text, $usr_id, $wrd_id)
{
    log_debug('zuc_upd_lst_frm_special (f' . $frm_id . ',' . $frm_text . ',u' . $usr_id . ',t' . $wrd_id . ')');

    // handle the special for id
    // get all words assigned to the formula e.g. if the "increase" formula is linked to "Year", get the word id for "Year"
    //$special_frm_wrd_lst = zuf_linked_wrd_lst($chk_frm_id, $usr_id);
    $special_frm_wrd_lst = zuf_linked_wrd_lst($frm_id, $usr_id);
    $special_frm_wrd_ids = array_keys($special_frm_wrd_lst);
    // include all is a words
    foreach ($special_frm_wrd_ids as $special_frm_wrd_id) {
        $new_special_frm_wrd_ids = zut_ids_are($special_frm_wrd_id, $usr_id);
        $special_frm_wrd_ids = array_unique(array_merge($special_frm_wrd_ids, $new_special_frm_wrd_ids));
    }

    log_debug('zuc_upd_lst_frm_special -> (' . dsp_array($special_frm_wrd_ids) . ')');
    return $special_frm_wrd_ids;
}


