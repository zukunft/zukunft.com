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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/


// interface const (to be removed, because specific functions for each part has been created)
define('ZUP_RESULT_TYPE_DB', 'db');    // returns a formula in the database format
define('ZUP_RESULT_TYPE_USER', 'user');  // returns a formula in the user format
define('ZUP_RESULT_TYPE_VALUE', 'value'); // returns a result of the formula




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

    $pos = $calc->pos_separator($formula, expression::WORD_START, 0,);
    $end = $calc->pos_separator($formula, expression::WORD_END, $pos);
    if ($pos >= 0 and $end > $pos) {
        $result = $pos;
    }

    log_debug("zuc_pos_word ->  (" . $result . ")");
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
    if (substr($formula, 0, strlen(expression::FORMULA_START)) == expression::FORMULA_START) {
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
