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

// returns a positive word id if the formula string starts with a word in the database reference format; maybe not needed any more, because ...
function zuc_get_word($formula)
{
    log_debug("zuc_get_word (" . $formula . ")");
    $result = 0;

    if (substr($formula, 0, strlen(expression::MAKER_WORD_START)) == expression::MAKER_WORD_START) {
        $result = zu_str_right_of($formula, expression::MAKER_WORD_START);
        $result = zu_str_left_of($result, expression::MAKER_WORD_END);
    }

    log_debug("zuc_get_word ... done (" . $result . ")");
    return $result;
}

// returns a positive word id if the formula string starts with a word; maybe not needed any more
function zuc_has_word($formula)
{
    log_debug("zuc_has_word (" . $formula . ")");
    $result = 0;

    if (substr($formula, 0, strlen(expression::MAKER_WORD_START)) == expression::MAKER_WORD_START) {
        $result = zu_str_right_of($formula, expression::MAKER_WORD_START);
        $result = zu_str_left_of($result, expression::MAKER_WORD_END);
    }

    log_debug("zuc_has_word ... done (" . $result . ")");
    return $result;
}

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


// returns the verb id if the left most part of the formula references to a verb
function zuc_get_verb($formula, $word_array, $time_word_id)
{
    log_debug("zuc_get_verb(" . $formula . "," . dsp_array($word_array) . "," . $time_word_id . ")");
    $result = 0;

    if (substr($formula, 0, strlen(expression::MAKER_TRIPLE_START)) == expression::MAKER_TRIPLE_START) {
        $result = zu_str_right_of($formula, expression::MAKER_TRIPLE_START);
        $result = zu_str_left_of($result, expression::MAKER_TRIPLE_END);
    }

    log_debug("zuc_get_verb ... done (" . $result . ")");
    return $result;
}

// returns a list of words that are linked to any word of the word array with the given verb
// e.g. get all country differentiators of Sales (where "Sales" is the word array and "differentiators" is the verb
function zuc_get_verb_words($verb_id, $word_array, $time_word_id, $user_id)
{
    log_debug("zuc_get_verb_words(" . $verb_id . ":" . dsp_array($word_array) . "," . $time_word_id . ")");
    $result = array();

    // list all words that are linked to the verb e.g. country can be the differentiator for Sales, so Sales would be the result
    $verb_words = zu_sql_word_ids_linked($word_array, $verb_id, verb::DIRECTION_UP);
    log_debug("zuc_get_verb_words -> verb words " . dsp_array($verb_words));
    // list all foaf of the verb
    $is_a_type = cl(db_cl::VERB, verb::IS_A);
    $word_ids = zut_array_ids($verb_words, $user_id);
    foreach ($word_ids as $word_id) {
        log_debug("zuc_get_verb_words -> add word id " . $word_id . " to result");
        $foaf_words = zu_sql_get_lst(zu_sql_words_linked($word_id, $is_a_type, verb::DIRECTION_UP));
        log_debug("zuc_get_verb_words -> add word id " . $word_id . " to result, which has foaf words " . dsp_array($foaf_words) . " (" . dsp_array(array_keys($foaf_words)) . "..");
        // combine so add Sales to the selection words and the countries to the result words
        //$result = array_merge($result, $foaf_words);
        $result = zu_lst_merge_with_key($result, $foaf_words);
    }
    log_debug("zuc_get_verb_words -> done (" . dsp_array($result) . ")");

    return $result;
}

// replace the verb related word with the used word
function zuc_get_verb_word_array($formula, $word_array, $time_word_id)
{
    log_debug("zuc_get_verb_word_array (" . $formula . "," . dsp_array($word_array) . "," . $time_word_id . ")");
    $result = array();

    if (substr($formula, 0, strlen(expression::MAKER_TRIPLE_START)) == expression::MAKER_TRIPLE_START) {
        log_debug("zuc_get_verb_word_array -> found");
        $verb_id = zu_str_right_of($formula, expression::MAKER_WORD_START);
        $verb_id = zu_str_left_of($verb_id, expression::MAKER_WORD_END);
        // list all words that are linked to the verb e.g. country can be the differentiator for Sales, so Sales would be the result
        $verb_words = zu_sql_word_ids_linked($word_array, $verb_id, verb::DIRECTION_DOWN);
        log_debug("zuc_get_verb_word_array -> verb words " . dsp_array($verb_words));
        $word_array[] = $verb_words[0];
        log_debug("zuc_get_verb_word_array ... done (" . $word_array . ")");
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

// replaces the left most formula call with the result of the formula
// maybe rename to zuc_get_formula_result
function zuc_frm_val($formula, $word_array, $time_word_id, $user_id)
{
    log_debug("zuc_frm_val (" . $formula . "," . dsp_array($word_array) . "," . $time_word_id . ")");

    $result = $formula;
    $result_user = 0;

    // get the formula id
    $formula_id = strval(zu_str_between($result, expression::MAKER_FORMULA_START, expression::MAKER_FORMULA_END));
    log_debug("zuc_frm_val -> id " . $formula_id);

    // get what is around the formula id
    $formula_id_txt = expression::MAKER_FORMULA_START . $formula_id . expression::MAKER_FORMULA_END;
    $part_l = zu_str_left_of($result, $formula_id_txt);
    $part_r = zu_str_right_of($result, $part_l . $formula_id_txt);

    // get any addition words if needed
    if ($part_r <> "") {
        log_debug("zuc_frm_val -> r part " . $part_r . ".");
        $new_word_id = zuf_2num_get_word($part_r);
        if ($new_word_id > 0) {
            // seperate words from right part and add the words to the word array
            $words_to_add = zu_str_left_of($part_r, ZUP_CHAR_WORDS_END);
            $words_to_add = zu_str_right_of($words_to_add, ZUP_CHAR_WORDS_START);
            //$word_add_array = strsplit;
            $words_to_add = zu_str_left_of($part_r, expression::MAKER_WORD_END);
            $words_to_add = zu_str_right_of($words_to_add, expression::MAKER_WORD_START);
            log_debug("zuc_frm_val -> add words " . $words_to_add);
            $time_word_id_received = strval($words_to_add);
            if ($time_word_id_received > 0) {
                //array_push($word_array, $time_word_id);
                $time_word_id = $time_word_id_received;
                log_debug("zuc_frm_val -> add word " . $time_word_id);
            }
            $part_r = zu_str_right_of($part_r, ZUP_CHAR_WORDS_END);
        }
    }

    // get the formula result
    $formula_text = zuf_text($formula_id);
    log_debug("zuc_frm_val -> get result for " . $formula_text);
    if (zuf_is_special($formula_id, $user_id)) {
        $in_result = zuf_special_result($formula_id, $word_array, $time_word_id, $user_id);
        $formula_result = $in_result[0];
        if ($in_result[1] > 0) {
            $result_user = $in_result[1];
        }
    } else {
        $in_result = zuf_2num($formula_id, $formula_text, $word_array, $time_word_id, $user_id);
        $formula_result = $in_result[0];
        if ($in_result[1] > 0) {
            $result_user = $in_result[1];
        }
    }
    log_debug("zuc_frm_val -> " . $formula_result);
    $in_result = zuf_2num_part($part_l . $formula_result . $part_r, $word_array, $time_word_id, $result_user, $user_id);
    $result = $in_result[0];
    if ($in_result[1] > 0) {
        $result_user = $in_result[1];
    }
    log_debug("zuc_frm_val -> incl. left and right part " . $result);

    // temp combine the result with the user info
    $out_result = array();
    $out_result[] = $result;
    $out_result[] = $result_user;

    return $out_result;
}


/*
// split a formula into a function and its parameters
// returns an array where the first entry is the function name and the last entry is the formula part after the function
function zuc_parameter($formula, $max_parameters) {
                                            
  $parameters = array();
  $num_parameters = 0;
  $pos = 0;
  
  $func_txt = "";
  $func_len = 0;
  $sep_len = 0;
  
  $start_pos = 0;
  $end_func = 0;
  $end_pos = 0;
  
  $parameter_text = "";
  $rest_text = "";
  
  $func_txt = zu_str_left_of($formula, xfp_char_braket_open)
  If xfp_is_function(func_txt) Then
  
    parameters = bsi_array_add(parameters, func_txt)
  
    func_txt = func_txt + xfp_char_braket_open
    func_len = Len(func_txt) + 1 ' +1 because in most cases the first char AFTER the func is needed
    sep_len = Len(xfp_char_seperator)
  
    If bsi_left(formula, func_len - 1) = func_txt _
    And xfp_closing_braket(formula, func_len) > 0 Then
      
      start_pos = func_len
      pos = 1
      Do While pos <= max_parameters
        end_func = xfp_pos_seperator(formula, xfp_char_braket_close, start_pos)
        end_pos = xfp_pos_seperator(formula, xfp_char_seperator, start_pos)
        ' stop if ")" is reached
        If end_func <= end_pos And end_func > 0 Then
          pos = max_parameters
        }
        If pos = max_parameters Then
          end_pos = end_func
        }
        If end_pos > 0 Then
          parameter_text = bsi_mid(formula, start_pos, end_pos - (start_pos))
'          parameter_text = xfp_parse_part(parameter_text, result_type)
          parameters = bsi_array_add(parameters, parameter_text)
          num_parameters = num_parameters + 1
          start_pos = end_pos + Len(xfp_char_seperator)
'         Else
'          bsi_cfg_report_error "In formula " + formula + " are less parameter than expected (" + Format(max_parameters) + ").", bsi_prg_name, 0
        }
        pos = pos + 1
      Loop
                        
      If end_pos < Len(formula) Then
        rest_text = bsi_right(formula, Len(formula) - end_pos)
        parameters = bsi_array_add(parameters, rest_text)
      }
      
      parameters = bsi_array_add(parameters, num_parameters) ' the last is always the number of real parameters (without rest)
      
    }
  }
  
  xfp_parameter = parameters
End Function



// interprets or converts a function with variable number of parameters
function zuc_get_result($function_name, $formula, $result_type As String, $min_par, $max_par) {
                
  $result = "";
  $parameters = array();
  $par_size = 0; ' number of real parameters
    
  $parameters = xfp_parameter(formula, max_par)
  
  If parameters(0) = function_name Then
    ' get parameters and parse them
    par_size = parameters(UBound(parameters))
    
    ' create result depending on the requested format
    If result_type = xfp_result_type_db_value Then
      If par_size >= min_par And par_size <= max_par Then
        result = xfp_get_result_value(function_name, par_size, parameters)
       Else
        bsi_log bsi_prg_name, "Wrong number of parameters in function >" + parameters(0) + "<. Given >" + format(par_size) + "< expected between >" + format(min_par) + "< and >" + format(max_par) + "<.", bsi_log_status_error, 0
        result = ""
      }
     Else
      result = xfp_par_to_text(parameters, result_type)
    }
  }
  
  xfp_get_result_min_max = result
End Function


*/


/*


  // if return type = value



// returns true if the next suggested parse is math based
function zuc_is_math ($formula, $formula_type, $result_type, $word_array, $time_word_id) {
  $result = false;
  if (xfp_has_operator($formula) || substr($formula, 1) == ZUC_CHAR_BRAKET_OPEN) {
    $result = true;
  }
  returns $result;
}
*/

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

// ????
function zuc_get_var($formula)
{
    log_debug("zuc_get_var (" . $formula . ")");

    $result = $formula;

    // if value is numeric, just return it
    if (!is_numeric($result) && !zuc_is_date($result)) {
        // if value is quoted text, just return the text without quotes
        if (zuc_is_text_only($result)) {
            $result = substr($result, 1, strlen($result) - 2);
        } else {
            // if it is a formula, execute the formula
            /*
            // check for chains
            if (InStr($result, zuc_char_range) > 0 _
            And InStr($result, zuc_char_range) < Len($result) - 1) {
              $range_name_from = "";
              $range_name_to = "";

              range_name_from = bsi_str_left_of(bsi_secure_value($result, "String"), zuc_char_range)
              range_name_to = bsi_str_right_of(bsi_secure_value($result, "String"), zuc_char_range)

              $result = format(bsi_secure_value(bsi_rv(range_name_from), "String"))
              $result = $result + zuc_char_range
              $result = $result + format(bsi_secure_value(bsi_rv(range_name_to), "String"))
            } else {
              // else it is a var name; in this case return the value
              $result = bsi_rv($result)
            } */
        }
    }
    return $result;
}

/*
// parses a formula and returns the converted result e.g. convert the word names to db symbols ("Sales" to "{t6}") or the other way round
function zuc_part ($formula, $result_type, $word_array, $time_word_id, $user_id) {
  zu_debug("zuc_part (".$formula.",".$result_type.",".implode(",",$word_array).",".$time_word_id.",".$user_id.")");

  $result = trim($formula);
  
  if (zuc_is_text_only($result) == 0 And $result <> "") {
    $new_word_id = zuc_has_words($result, $result_type);
    if ($new_word_id > 0) {
      zu_debug("zuc_part -> words");
      $word_array[] = $new_word_id; 
      $result = zu_str_right_of($result, expression::MAKER_WORD_START.$new_word_id.expression::MAKER_WORD_END);
      // if a verb or other word follows combine the words
      if (zuc_get_verb($result, $result_type, $word_array, $time_word_id) > 0 || zuc_has_words($result, $result_type)) {
        zu_debug("zuc_part -> combined words ".implode(",",$word_array));
        $result = zuc_part($result, $result_type, $word_array, $time_word_id, $user_id);
      } else {
        // other wise return the value
        zu_debug("zuc_part -> get word value ".implode(",",$word_array));
        $value_words   = $word_array;
        $value_words[] = $time_word_id;
        $result = zuv_word_lst($value_words).zuc_part($result, $result_type, $word_array, $time_word_id, $user_id);
      }
    } else {
      // if verbs returns a list of words, the result should also be a list of results
      // if there is more than one array, all combinations of results should be returned
      $verb_id = zuc_get_verb($result, $result_type, $word_array, $time_word_id);
      if ($verb_id > 0) {
        zu_debug("zuc_part -> verbs");
        $right_part = zu_str_right_of($result, expression::MAKER_LINK_START.$verb_id.expression::MAKER_LINK_END);
        $right_value = zuc_part($right_part, $result_type, $word_array, $time_word_id, $user_id);

        $new_verb_ids = zuc_get_verb_words($result, $result_type, $word_array, $time_word_id, $user_id);
        zu_debug("zuc_part -> add verbs ".implode(",",$new_verb_ids));
        //$word_array = zuc_get_verb_word_array($result, $result_type, $word_array, $time_word_id);
        if (is_array($new_verb_ids)) {
          if (sizeof($new_verb_ids) > 1) {
            zu_debug("zuc_part -> array values ".implode(",",$new_verb_ids));
            $result = array();
            foreach (array_keys($new_verb_ids) AS $verb_id) {
              $value_words   = $word_array;
              $value_words[] = $time_word_id;
              $value_words[] = $verb_id;
              zu_debug("zuc_part -> value words ".implode(",",$value_words));
              $result_val = zuv_word_lst($value_words);
              zu_debug("zuc_part -> value ".$result_val);
              if ($result_val <> '') {
                $row_result = array();
                $row_result[] = $result_val.$right_value;
                $row_result[] = $new_verb_ids[$verb_id];
                //$row_result[] = $verb_id;
                $result[] = $row_result;
              }
              zu_debug("zuc_part -> ".implode(",",$result));
            }
          } else {
            // single array
            zu_debug("zuc_part -> single array ".$new_verb_ids);
            $value_words   = $word_array;
            $value_words[] = $time_word_id;
            $value_words[] = $new_verb_ids[0];
            $result = zuv_word_lst($value_words).$right_value;
          }
        } else {
          // single 
          $value_words   = $word_array;
          $value_words[] = $time_word_id;
          $value_words[] = $new_verb_ids;
          zu_debug("zuc_part -> value ".$new_verb_ids);
          $result = zuv_word_lst($value_words).$right_value;
        }
      } else {
        if (zuc_has_formula($result) == true) {
          zu_debug("zuc_part -> formula");
          $result = zuc_frm_val ($result, $word_array, $time_word_id, $user_id);
        } else {
          zu_debug("zuc_part -> no formula ".$result." has opp ".zuc_has_operator($result));
          if (zuc_has_function($result) == true) {
            zu_debug("zuc_part -> function");
            //result = xfp_get_result_min_max(xfp_func_if, used_formula, result_type, 2, 3)
            $result = "function";
          } else {
            zu_debug("zuc_part -> no function");
            if (zuc_has_operator($result) || zuc_has_braket($result)) {
              zu_debug("zuc_part -> math");
              if ($result_type == ZUP_RESULT_TYPE_VALUE) {
                $r_oper = zuc_get_operator($result);
                if ($r_oper <> "") {
                  $right_part = zu_str_right_of($result, $r_oper); 
                  $result = $r_oper.zuc_part($right_part, $result_type, $word_array, $time_word_id, $user_id);
                }
              }
            
            //} else {
             // if (zuc_get_logical(used_formula) <> "") {
             //   $result = zuc_logical(used_formula, result_type) 
            } else {
              zu_debug("zuc_part -> value");
              switch ($result_type) {
              case (ZUP_RESULT_TYPE_USER):
                // only replace real var names, but not simple numbers
                $result = $formula;
                if (!is_numeric(result)) {
                  $result = zuc_get_var_name($formula);
                } 
              case (ZUP_RESULT_TYPE_VALUE):
                //$result = zuc_get_var($formula);
                $result = $formula;
                if (is_array($result)) {
                  foreach (array_keys($result) AS $r_key) {
                    zu_debug("zuc_part -> calc row ".$result[$r_key][0]);
                    $result[$r_key][0] = zuc_math_parse($result[$r_key][0], $word_array, $time_word_id);
                    zu_debug("zuc_part -> calc result ".$result[$r_key][0]);
                  }
                } else {    
                  $result = zuc_math_parse($result, $word_array, $time_word_id);
                } 
              } 
            }
          }
        }
      }  
    }
    //result = zuc_set_var($formula);
  }
  
  // to avoid loops
  if ($result == $formula AND $result <> "") {
    zu_fatal("stopped to avoid loops with ".$formula.".","zuc_part");
    $result = "";
  }
  
  zu_debug("zuc_part ... done (".$result.")");

  return $result;
}
*/

// parses a zukunft.com formula and returns the converted result
function zuc_parse($formula, $result_type, $word_array, $time_word_id, $user_id)
{
    log_debug("zuc_parse (" . $formula . " target \"" . $result_type . "\" with words " . dsp_array($word_array) . ":" . $time_word_id . ")");

    $result = $formula;

    // get main time word because most values are related to a timestamp
    if ($time_word_id <= 0) {
        if (zut_has_time($word_array)) {
            $time_word_lst = zut_time_lst($word_array);
            // shortcut, replace with a most_useful function
            $time_word_id = $time_word_lst[0];
        } else {
            $time_word_id = zut_get_max_time($word_array[0], $word_array, $user_id);
        }
    }

    // check if formula is an id ???
    if (is_numeric($result)) {
        if ($result == 2) {
            log_debug("zuc_parse -> get value from db " . $result);
            $result = zut_value($word_array, $time_word_id);
        } else {
            log_debug("zuc_parse -> get from db " . $result);
            $result = zuf_text($result);
            log_debug("zuc_parse -> read from db " . $result);
        }
    }

    //
    if ($result[0] == ZUP_CHAR_CALC) {
        $part = substr($result, 1);
        /*    switch ($result_type) {
              Case ZUP_RESULT_TYPE_DB:
                $var_nbr = 1;
                $result = ZUP_CHAR_CALC . zuc_part($part, $result_type, $word_array, $time_word_id, $user_id);
                echo 'db:'.$result.'<br>';
              Case ZUP_RESULT_TYPE_USER:
                $result = ZUP_CHAR_CALC . zuc_part($part, $result_type, $word_array, $time_word_id, $user_id);
                echo 'user:'.$result.'<br>';
              Case ZUP_RESULT_TYPE_VALUE:
                $result =                 zuc_part($part, $result_type, $word_array, $time_word_id, $user_id);
                echo 'val:'.$result.'<br>'; */
        if ($result_type == ZUP_RESULT_TYPE_USER) {
            $result = ZUP_CHAR_CALC . zuc_part($part, $result_type, $word_array, $time_word_id, $user_id);
        } else {
            if ($result_type == ZUP_RESULT_TYPE_VALUE) {
                $result = zuc_part($part, $result_type, $word_array, $time_word_id, $user_id);
                if (is_array($result)) {
                    foreach (array_keys($result) as $r_key) {
                        log_debug("zuc_parse -> calc row " . $result[$r_key][0]);
                        $result[$r_key][0] = zuc_math_parse($result[$r_key][0], $word_array, $time_word_id);
                        log_debug("zuc_parse -> calc result " . $result[$r_key][0]);
                    }
                } else {
                    $result = zuc_math_parse($result, $word_array, $time_word_id);
                }
            }
        }
    }

    // to display the result check the result format
    // for that, get the formula id
    // get the format_word_id of the formula
    $format_word_id = 19;

    // format the result, if needed
    $result = zuv_dsp($result, $format_word_id);

    return $result;
}

/*
Sample: 
update "Sales" "water" "annual growth rate"
-> get the formulas where any of the value words is used (zuv_frm_lst )
-> formula "yearly forecast "estimate" "next" = "this" * (1 + "annual growth rate")" because "water" OR "annual growth rate" used
-> get the list of words of the updated value not used in the formula e.g. "Sales" "Water" ($val_wrd_ids_ex_frm_wrd)
-> get all values linked to the word list e.g. "Sales" AND "Water" (zuv_lst_of_wrd_ids -> $val_lst_of_wrd_ids)
-> get the word list for each value excluding the word used in the formula e.g. "Nestlé" "Sales" "Water" "2016" and  "Nestlé" "Sales" "Water" "2017" ($val_wrd_lst_ex_frm_wrd)
-> calculate the formula result for each word list (zuc_frm)
-> return the list of formula results e.g. "Nestlé" "Sales" "Water" "2018" "estimate" that have been updated or created ($frm_result_upd_lst)
-> r) check in which formula the formula results are used
-> formula "yearly forecast "estimate" "next" = "this" * (1 + "annual growth rate"), because the formula is linked to year and 2018 is a Year
-> calculate the formula result for each word list of the formula result
-> return the list of formula results e.g. "Nestlé" "Sales" "Water" "2019" "estimate" 
-> repeat at r)
*/

// returns a list of all formula results that needs to be updated the numbers in $val_ids_updated has been updated
function zuc_upd_val_lst($val_ids_updated, $upd_usr)
{
    log_debug('zuc_upd_val_lst(v' . dsp_array($val_ids_updated) . ',u' . $upd_usr . ')');
    // 1. get all formulas where the value is used
    // include the category words, because a formula linked to "Year" is inheritent to e.g. "2016"
    $val_wrd_lst = zuv_ids_wrd_lst_incl_cat($val_ids_updated, $upd_usr);

    $elm_type_id = formula_element_type::WORD;
    $sql = "SELECT formula_id 
            FROM formula_elements
           WHERE formula_element_type_id = " . $elm_type_id . "
             AND ref_id IN (" . sql_array(array_keys($val_wrd_lst)) . ");";
    $frm_ids = zu_sql_get_ids($sql);
    log_debug('zuc_upd_val_lst -> formulas (' . dsp_array($frm_ids) . ')');
    // 2. update the formulas
    $fv_lst = zuc_val_frm_upd($val_wrd_lst, $frm_ids, $upd_usr, "");
    // 3. if the result has changed repeat with 1.

    // TODO check that the same value is not updated twice
    $calc_layer = 1;
    while (!empty($fv_lst) and $calc_layer < ZUC_MAX_CALC_LAYERS) {
        $fv_next_layer = array();
        foreach ($fv_lst as $fv) {
            $fv_next_layer = array_merge($fv_next_layer, $fv->update_depending());
        }
        $fv_lst = $fv_next_layer;
        $calc_layer++;
    }

    /*  $sql_result = zuf_wrd_lst ($frm_ids_updated, $usr_id);
        zu_debug('zuc_upd_lst -> number of formulas '. mysqli_num_rows ($sql_result));
        while ($frm_row = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
          zu_debug('zuc_upd_lst -> formula '.$frm_row['formula_name'].' ('.$frm_row['resolved_text'].') linked to '.zut_name($frm_row['word_id'], */
}

// returns a list of all formula results that needs to be updated the words in $wrd_ids_updated has been updated
// if a word link has been updated, maybe both words needs to be checked
function zuc_upd_wrd_lst($wrd_ids_updated)
{
}

// returns a list of all formula results that needs to be updated the verbs / word links in $lnk_ids_updated has been updated
function zuc_upd_lnk_lst($lnk_ids_updated)
{
}

// returns a list of all formula results that may needs to be updated if a list of values has been updated
// $val_ids_updated - values that has been updated
// $frm_ids_updated - formulas that needs to be checked for update as checked by the calling function
function zuc_val_upd_frm_result_lst($val_ids_updated, $frm_ids_updated, $upd_usr_id)
{
    // loop over the formulas and check if any updated value has been used
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


// returns a list of all formula results that may needs to be updated if a formula is updated for one user
function zuc_upd_lst_usr($val_wrd_lst, $frm_ids_updated, $usr_id, $last_msg_time, $collect_pos)
{
    log_debug('zuc_upd_lst_usr(' . dsp_array($val_wrd_lst) . ',f' . dsp_array($frm_ids_updated) . ',u' . $usr_id . ')');
    $result = array();

    // loop over the word categories assigned to the formulas
    // get the words where the formula is used including the based on the assigned word e.g. Company or year
    $sql_result = zuf_wrd_lst($frm_ids_updated, $usr_id);
    log_debug('zuc_upd_lst_usr -> number of formula assigned words ' . mysqli_num_rows($sql_result));
    while ($frm_row = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
        log_debug('zuc_upd_lst_usr -> formula ' . $frm_row['formula_name'] . ' (' . $frm_row['resolved_text'] . ') linked to ' . zut_name($frm_row['word_id'], $usr_id));

        // show the user the progress every two seconds
        $last_msg_time = zuc_upd_lst_msg($last_msg_time, $collect_pos, mysqli_num_rows($sql_result), $frm_row);
        $collect_pos++;

        // also use the formula for all related words e.g. if the formula should be used for "Company" use it also for "ABB"
        $is_word_ids = zut_ids_are($frm_row['word_id'], $usr_id); // should later be taken from the original array to increase speed

        // include also the main word in the testing
        $is_word_ids[] = $frm_row['word_id'];

        // filter the words if just a value has been updated
        if (!empty($val_wrd_lst)) {
            log_debug('zuc_upd_lst_usr -> update related words (' . dsp_array($val_wrd_lst) . ')');
            $used_word_ids = array_intersect($is_word_ids, array_keys($val_wrd_lst));
            log_debug('zuc_upd_lst_usr -> needed words (' . dsp_array($used_word_ids) . ' instead of ' . dsp_array($is_word_ids) . ')');
        } else {
            $used_word_ids = $is_word_ids;
        }

        // loop over the words assigned to the formulas
        foreach ($used_word_ids as $wrd_id) {
            log_debug('zuc_upd_lst_usr -> sub word ' . zut_name($wrd_id, $usr_id) . ' (' . $wrd_id . ')');
            $special_frm_wrd_ids = array();

            if (zuf_has_verb($frm_row['formula_text'], $usr_id)) {
                // special case
                log_debug('zuc_upd_lst_usr -> formula has verb (' . $frm_row['formula_text'] . ')');
            } else {

                // include all results of the underlying formulas
                $all_frm_ids = zuf_frm_ids($frm_row['formula_text'], $usr_id);

                // get fixed / special formulas
                $frm_ids = array();
                foreach ($all_frm_ids as $chk_frm_id) {
                    if (zuf_is_special($chk_frm_id, $usr_id)) {
                        $special_frm_wrd_ids = zuc_upd_lst_frm_special($frm_row[formula::FLD_ID], $frm_row['formula_text'], $usr_id, $special_frm_wrd_ids);

                        //get all values related to the words
                    } else {
                        $frm_ids[] = $chk_frm_id;
                    }
                }

                // include the results of the underlying formulas, but only the once related to one of the words assigned to the formula
                $result_fv = zuc_upd_lst_fv($val_wrd_lst, $wrd_id, $frm_ids, $frm_row, $usr_id);
                $result = array_merge($result, $result_fv);

                // get all values related to assigned word and to the formula words
                // and based on this value get the unique word list
                $frm_wrd_ids = zuf_wrd_ids($frm_row['formula_text'], $usr_id);
                log_debug('zuc_upd_lst_usr -> frm_wrd_ids1 (' . dsp_array($frm_wrd_ids) . ')');

                // add word words for the special formulas
                $frm_wrd_ids = array_unique(array_merge($frm_wrd_ids, $special_frm_wrd_ids));
                log_debug('zuc_upd_lst_usr -> frm_wrd_ids2 (' . dsp_array($frm_wrd_ids) . ')');

                $result_val = zuc_upd_lst_val($wrd_id, $frm_wrd_ids, $frm_row, $usr_id);
                $result = array_merge($result, $result_val);
            }
        }
    }

    //print_r($result);
    log_debug('zuc_upd_lst_usr -> (' . dsp_count($result) . ')');
    return $result;
}

// get the calculation requests if a formula has been updated
// returns a list of all formula results that may needs to be updated if a formula is updated
// $frm_ids_updated - formulas that needs to be checked for update
function zuc_upd_lst($frm_ids_updated, $upd_usr_id)
{
    log_debug('zuc_upd_lst(' . dsp_array($frm_ids_updated) . ',u' . $upd_usr_id . ')');
    // to inform the user about the progress
    $last_msg_time = time(); // the start time
    $collect_pos = 0;        // to calculate the progress in percent

    $result = array();

    // loop over the users: first calculate the standard values for all user and than the user specific values
    $usr_lst = zuu_active_lst();
    // add a dummy user to calculate the standard results within the same loop
    $usr_lst[0] = "dummy user to calculate the base value for all users";
    // to calculate the base value first
    ksort($usr_lst);

    log_debug('zuc_upd_lst -> active users (' . dsp_array($usr_lst) . ')');
    foreach (array_keys($usr_lst) as $usr_id) {
        if ($usr_id == 0 or $upd_usr_id == 0 or $upd_usr_id == $usr_id) {
            log_debug('zuc_upd_lst -> user (' . $usr_lst[$usr_id] . ')');

            $result = zuc_upd_lst_usr(array(), $frm_ids_updated, $usr_id, $last_msg_time, $collect_pos);
        }
    }

    //flush();
    log_debug('zuc_upd_lst -> (' . dsp_count($result) . ')');
    return $result;
}

/*
// calculate the result for one formula for one user
// and save the result in the database
// the formula text is a parameter to save time
// always returns an array of formula values
// TODO: check if calculation is really needed
function zuc_frm($frm_id, $frm_text, $wrd_ids, $time_word_id, $user_id) {
  zu_debug('zuc_frm (f'.$frm_id.','.$frm_text.',t'.implode(",",$wrd_ids).',u'.$user_id.')');

  $result = true;
  $result_user = 0;
  
  // check if an update of the result is needed
  
  // $needs_update = true;
  // if (zuf_has_verb ($frm_text, $user_id)) {
  //   $needs_update = true; // this case will be checked later
  // } else {
  //   $frm_wrd_ids = zuf_wrd_ids($frm_text, $user_id);
  // } 

  // get the word id of the formula name to add the word to the result 
  // e.g. if the formula "countryweight" is calculated the word "countryweight" should be added to the result values
  // to be done by the calling function
  $frm_name = zuf_name($frm_id, $user_id);
  $frm_wrd_id = zut_id($frm_name, $user_id);
  
  // find the position of the formula indicator "="
  // use the part left of it to add the words to the result
  $wrd_txt_4_result = zu_str_left_of($frm_text, ZUP_CHAR_CALC);
  if ($wrd_txt_4_result <> "") {
    $frm_result_wrd_ids = zuf_wrd_ids($frm_text, $user_id);
  }

  $result = zuf_2num ($frm_id, $frm_text, $wrd_ids, 0, $user_id);
  $val_result = $result[0];
  if ($result[1] > 0) {
    $result_user = $result[1];
  }
  if (is_array($val_result)) {
    $result_txt = '';
    foreach (array_keys($val_result) AS $r_key) {
      $fv = New formula_value;
      $fv->frm_id = $frm_id;
      $fv->value = $val_result[$r_key][0];
      $fv->usr_id = $result_user;
      $result_txt = $val_result[$r_key][0].' ('.$val_result[$r_key][1].'), ';
      $row_wrd_id = zut_id($val_result[$r_key][1], $user_id);
      // TODO
      // calculate the standard value for all users and check for each user if there could be a different result
      $result_wrd_ids = $wrd_ids;
      $result_wrd_ids[] = $frm_wrd_id;
      $result_wrd_ids[] = $wrd_id;
      $result_wrd_ids[] = $row_wrd_id;
      if ($wrd_txt_4_result <> "") {
        foreach ($frm_result_wrd_ids AS $frm_result_wrd_id) {
          $result_wrd_ids[] = $frm_result_wrd_id;
        }
      }
      $result_wrd_ids = array_unique($result_wrd_ids);
      asort($result_wrd_ids);
      $fv->wrd_ids = $result_wrd_ids;
      // if no time is specified, save the result twice: once with the assumed time and once without time as general result
      // or the time given is matching the default time
      $result_default_time = zut_assume_time($result_wrd_ids, $user_id); // must be the same function called used in zuf_2num
      $fv->time_id = $time_word_id;
      if ($time_word_id <= 0 OR $time_word_id == $result_default_time) {
        $fv->time_id = 0;
        $frm_result_id = $fv->save();
        //$frm_result_id_no_time = zuf_db_save_result($frm_id, $result_wrd_ids, 0, $value, $result_user);
        $time_word_id = $result_default_time;
      }  
      $result_wrd_ids = array_diff($result_wrd_ids, array($time_word_id));
      $fv->time_id = $time_word_id;
      $frm_result_id = $fv->save();
      //$frm_result_id = zuf_db_save_result($frm_id, $result_wrd_ids, $time_word_id, $value, $result_user);
      zu_debug("zuc_frm -> array result ".$result_txt." (".implode(",",$result_wrd_ids).")");
      // return the result id only if it has been updated
      if ($frm_result_id > 0) {
        $result[2] = $frm_result_id;
      } else {
        if ($frm_result_id_no_time > 0) {
          $result[2] = $frm_result_id_no_time;
        }
      }
      $result[3] = $result_wrd_ids;
    }
  } else {  
    zu_debug('zuc_frm -> result "'.$val_result.'" for formula "'.$frm_name.'" ('.$frm_wrd_id.')');
    if (is_numeric($val_result)) {
      $result_wrd_ids = $wrd_ids;
      $result_wrd_ids[] = $frm_wrd_id;
      if ($wrd_txt_4_result <> "") {
        foreach ($frm_result_wrd_ids AS $frm_result_wrd_id) {
          $result_wrd_ids[] = $frm_result_wrd_id;
        }
      }
      $result_wrd_ids = array_unique($result_wrd_ids);
      asort($result_wrd_ids);
      $result_default_time = zut_assume_time($result_wrd_ids, $user_id); // must be the same function called used in zuf_2num
      $fv = New formula_value;
      $fv->frm_id = $frm_id;
      $fv->wrd_ids = $result_wrd_ids;
      $fv->value = $val_result;
      $fv->usr_id = $result_user;
      if ($time_word_id <= 0 OR $time_word_id == $result_default_time) {
        $fv->time_id = 0;
        $frm_result_id = $fv->save();
        //$frm_result_id_no_time = zuf_db_save_result($frm_id, $result_wrd_ids, 0, $val_result, $result_user);
        $time_word_id = $result_default_time;
      }  
      $result_wrd_ids = array_diff($result_wrd_ids, array($time_word_id));
      $fv->time_id = $time_word_id;
      $frm_result_id = $fv->save();
      //$frm_result_id = zuf_db_save_result($frm_id, $result_wrd_ids, $time_word_id, $val_result, $result_user);
      // return the result id only if it has been updated
      if ($frm_result_id > 0) {
        $result[2] = $frm_result_id;
      } else {
        if ($frm_result_id_no_time > 0) {
          $result[2] = $frm_result_id_no_time;
        }
      }
      $result[3] = $result_wrd_ids;
    }
  }
  
  return $result;
}
*/
// update all needed formula values if a list of values has been updated
// similar to zuc_frm_upd below, but with a filter on the values
// $val_wrd_lst - list of words that is related to the value update; only results linked to these word needs to be updated
// returns a list of formula results that needs to be updated
function zuc_val_frm_upd($val_wrd_lst, $frm_ids, $usr, $back)
{
    log_debug("zuc_val_frm_upd (t" . dsp_array($val_wrd_lst) . ",f" . dsp_array($frm_ids) . ",u" . $usr . ")");
    $result = array();

    //ob_implicit_flush(true);
    //ob_end_flush();

    $last_msg_time = time(); // the start time
    $collect_pos = 0;        // to calculate the progress in percent

    $frm_upd_lst = zuc_upd_lst_usr($val_wrd_lst, $frm_ids, $usr, $last_msg_time, $collect_pos);
    $calc_pos = 0;
    $last_msg_time = time();
    foreach ($frm_upd_lst as $r) {
        //zu_debug("calculate ".round($calc_pct,2)."% (".$r['frm_name']." for ".implode(",",$wrd_names).") = ".$r[$r_key][0]);
        $frm = new formula($usr);
        $frm->id = $r['frm_id'];
        $frm->ref_text = $r['frm_text'];
        $fv_lst = $frm->calc($r['wrd_ids'], $back);
        $result = array_merge($result, $fv_lst);
        //$in_result = $frm->calc($r['wrd_ids'], 0);
        //$in_result = zuc_frm($r['frm_id'], $r['frm_text'], $r['wrd_ids'], 0, $r['usr_id']);
        //$val_result = $in_result[0];

        // show the user the progress every two seconds
        if ($last_msg_time + UI_MIN_RESPONSE_TIME < time()) {
            $calc_pct = ($calc_pos / sizeof($frm_upd_lst)) * 100;

            $wrd_names = array();
            foreach ($r['wrd_ids'] as $wrd_id) {
                $wrd_names[] = zut_name($wrd_id);
            }

            $fv = $fv_lst[0];
            $val_result = $fv->value;
            if (is_array($val_result)) {
                foreach (array_keys($val_result) as $r_key) {
                    echo "calculate " . round($calc_pct, 2) . "% (" . $r['frm_name'] . " for " . dsp_array($wrd_names) . ") = " . $val_result[$r_key][0] . "<br>";
                }
            } else {
                echo "calculate " . round($calc_pct, 2) . "% (" . $r['frm_name'] . " for " . dsp_array($wrd_names) . ") = " . $val_result . "<br>";
            }
            $last_msg_time = time();
            //ob_flush();
            //flush();
        }
        $calc_pos++;
    }
    //ob_end_flush();

    //$result .= zuh_go_back($back_link, $usr_id);
    return $result;
}

// if a list of formulas needs to updated the results, calculate all the depending values
function zuc_frm_upd($frm_ids_updated, $usr, $back)
{
    ob_implicit_flush(true);
    ob_end_flush();

    $frm_upd_lst = zuc_upd_lst($frm_ids_updated, $usr);
    $calc_pos = 0;
    $last_msg_time = time();
    foreach ($frm_upd_lst as $r) {
        $frm = new formula($usr);
        $frm->id = $r['frm_id'];
        //$frm->ref_text = $r['frm_text'];
        $frm->load();
        log_debug('zuc_frm_upd -> (' . $frm->name . ' - ' . $frm->id . ')');
        $wrd_lst = new word_list($usr);
        $wrd_lst->ids = $r['wrd_ids'];
        $wrd_lst->load();
        $fv_lst = $frm->calc($wrd_lst, $back);
        log_debug('zuc_frm_upd -> done (' . $frm->name . ' - ' . $frm->id . ')');
        $fv = $fv_lst[0];
        $val_result = $fv->value;
        //$in_result = $frm->calc($r['wrd_ids'], 0);
        //$in_result = zuc_frm($r['frm_id'], $r['frm_text'], $r['wrd_ids'], 0, $r['usr_id']);
        //$val_result = $in_result[0];

        // show the user the progress every two seconds
        if ($last_msg_time + UI_MIN_RESPONSE_TIME < time()) {
            $calc_pct = ($calc_pos / sizeof($frm_upd_lst)) * 100;

            $wrd_names = array();
            foreach ($r['wrd_ids'] as $wrd_id) {
                $wrd_names[] = zut_name($wrd_id);
            }

            if (is_array()) {
                foreach (array_keys($val_result) as $r_key) {
                    echo "calculate " . round($calc_pct, 2) . "% (" . $r['frm_name'] . " for " . dsp_array($wrd_names) . ") = " . $val_result[$r_key][0] . "<br>";
                }
            } else {
                echo "calculate " . round($calc_pct, 2) . "% (" . $r['frm_name'] . " for " . dsp_array($wrd_names) . ") = " . $val_result . "<br>";
            }
            $last_msg_time = time();
            ob_flush();
            flush();
        }
        $calc_pos++;
    }
    ob_end_flush();

    $result = zuh_go_back($back, $usr);
}


// recalculate all formula results in a batch run, which means that no direct messages to the user will be send
// to be splitted into two parts:
// 1. build the calculation list
// 2. Execute the calculation list
function zuc_batch_all($back)
{
    log_debug('zuc_batch_all()');

    zuf_check();

    $user_id = zuu_id();

    // result counter to limit the time without interaction
    $result_nbr = 0;

    // loop over the users: first calculate the standard values for all user and than the user specific values

    // loop over the formulas
    $frm_ids_updated = array();
    $sql_result = zuf_wrd_lst($frm_ids_updated, $user_id);
    // get the words where the formula is used including the based on the assigned word e.g. Company or year
    while ($frm_row = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
        $frm_id = $frm_row[formula::FLD_ID];
        log_debug('zuc_batch_all -> formula (' . $frm_row['resolved_text'] . ' (' . $frm_id . '), word ' . zut_name($frm_row['word_id'], $user_id) . ' (' . $frm_row['word_id'] . ')');
        $frm_wrd_id = zut_id($frm_row['formula_name'], $user_id);
        if ($frm_wrd_id <= 0) {
            log_err("Name for " . $frm_row['formula_name'] . " is missing. Please run the database consistency check.", "zut_db_add");
        }
        $is_word_ids = zut_ids_are($frm_row['word_id'], $user_id); // should be taken from the original array to increase speed
        foreach ($is_word_ids as $wrd_id) {
            log_debug('zuc_batch_all -> sub word ' . zut_name($wrd_id, $user_id) . ' (' . $wrd_id . ')');
            if ($result_nbr < 1000) {
                $wrd_ids = array();
                $wrd_ids[] = $wrd_id;
                $frm = new formula($user_id);
                $frm->id = $frm_id;
                $frm->ref_text = $frm_row['formula_text'];
                //$frm->usr = $usr;
                $frm->calc($wrd_ids, $back);
                //zuc_frm($frm_id, $frm_row['formula_text'], $wrd_ids, $user_id);
                $result_nbr++;
            }
        }
    }
    // filter the values by the words used on the right side to increase speed
    // calculate the numeric result for each value
    // add the words from the left side of the formula to the result
    // check if the result need to be updated
    // solve the dependencies
}

?>
