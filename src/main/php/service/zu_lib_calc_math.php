<?php

/*

  zu_lib_calc_math.php - ZUkunft LIBrary for CALCulating the MATHematical formula results
  --------------------

  prefix: zuc_* 

  most parts should be replaced by an r-project.org REST service call


  internal support functions that are used in other libraries such as zu_lib_calc_conv.php
  ----------------
  
  zuc_pos_function      - returns the position of the next predefined function
  zuc_get_function      - returns the next predefined function
  zuc_pos_operator      - returns the position of the next mathematical operator
  zuc_has_operator      - returns true if a text contains a mathematical operator
  zuc_get_operator      - get the left most math operator
  zuc_has_bracket       - true if the formula starts with a bracket, so that first the inner part needs to be calculated
  zuc_has_bracket_close - true if the formula starts with the closing bracket
  zuc_get_bracket       -


  math functions that actually calculate the result
  ----
  
  zuc_math          - interprets or converts a math operator condition
  zuc_math_mul      - calls zuc_math for multiplication
  zuc_math_div
  zuc_math_add
  zuc_math_sub
  zuc_math_bracket  - checks if formula contains a bracket and calculates the inner part first

  
  external functions that are used in other libraries such as zu_lib_calc_conv.php
  --------
  
  zuc_math_parse       - actually calculate the numeric result and calls the math functions; this should be replaced by R

  zuc_is_math_symbol   - true if the next symbol is a math symbol, that can be ignored if a formula should be converted to the db format
  

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


// returns the position of the corresponding separator and takes text fields and brackets into account by not splitting them
function zuc_pos_separator($formula, $separator, $start_pos)
{
    log_debug("zuc_pos_separator (" . $formula . "," . $separator . "," . $start_pos . ")");

    $pos = $start_pos;     // the search pos; was - 1 because for php the first char is 0, but in the lib it should be 1, but the is changed now
    $text_linked = False;  // do not look at text in high quotes
    $open_brackets = 0;    // number of brackets open
    $found = False;        // separator has been found

    do {
        // don't look into text that is a high quotes
        if ($open_brackets == 0) {
            if (substr($formula, $pos, strlen(ZUP_CHAR_TXT_FIELD)) == ZUP_CHAR_TXT_FIELD) {
                if ($text_linked) {
                    $text_linked = False;
                } else {
                    $text_linked = True;
                }
            }
        }

        // in case of brackets handle the inner part first
        if (!$text_linked) {
            if ($open_brackets == 0) {
                if (substr($formula, $pos, strlen($separator)) == $separator) {
                    $found = true;
                }
            }
            if (substr($formula, $pos, strlen(ZUP_CHAR_BRAKET_OPEN)) == ZUP_CHAR_BRAKET_OPEN) {
                $open_brackets = $open_brackets + 1;
            }
            if (substr($formula, $pos, strlen(ZUP_CHAR_BRAKET_CLOSE)) == ZUP_CHAR_BRAKET_CLOSE && $open_brackets > 0) {
                $open_brackets = $open_brackets - 1;
            }
        }

        if (!$found) {
            $pos = $pos + 1;
        }
    } while ($pos <= strlen($formula) && !$found);

    // if not found return -1 because the separator can also be on position 0
    if (!$found) {
        $pos = -1;
    }

    log_debug("zuc_pos_separator -> " . $pos);
    return $pos;
}


// returns the position of the next predefined function
function zuc_pos_function($formula)
{
    log_debug("zuc_pos_function (" . $formula . ")");

    // if not found return -1 because the separator can also be on position 0
    $pos = -1;

    if ($pos < 0) {
        $pos = zuc_pos_separator($formula, ZUP_FUNC_IF, 0);
    }
    if ($pos < 0) {
        $pos = zuc_pos_separator($formula, ZUP_FUNC_SUM, 0);
    }
    if ($pos < 0) {
        $pos = zuc_pos_separator($formula, ZUP_FUNC_ISNUM, 0);
    }

    log_debug("zuc_pos_function -> " . $pos);
    return $pos;
}

// return the left lost function name of the formula
function zuc_func_name($formula)
{
    log_debug("zuc_func_name (" . $formula . ")");
    $result = '';

    if (substr($formula, 0, strlen(ZUP_FUNC_IF)) == ZUP_FUNC_IF) {
        $result = ZUP_FUNC_IF;
    }
    if (substr($formula, 0, strlen(ZUP_FUNC_SUM)) == ZUP_FUNC_SUM) {
        $result = ZUP_FUNC_SUM;
    }

    // maybe later loop over all function ids
    /*$query = "SELECT formula_id FROM formulas;";
    $sql_result = mysqli_query($query) or die('Query failed: ' . mysqli_error());
    while ($formula_id = mysqli_fetch_array($sql_result, MySQLi_ASSOC) and $result == 0) {
      zu_debug("t".$formula_id[0]."=".$formula[3]);
      if ($formula_id[0] == $formula[3]) {
        $result = 1;
      }
    */
    log_debug("zuc_func_name -> " . $result);

    return $result;
}


// returns true if the formula string starts with a fixed function; the formula text is always interpreted from left to right
function zuc_has_function($formula, $result_type)
{
    log_debug("zuc_has_function (" . $formula . "," . $result_type . "(");

    $result = False;

    if (zuc_func_name($formula) <> '') {
        $result = True;
    }

    return $result;
}


// returns true if a text contains a mathematical function
function zuc_has_function_pos($formula)
{
    log_debug("zuc_has_function_pos (" . $formula . ")");

    $result = False;
    $pos = zuc_pos_function($formula);
    if ($pos >= 0) {
        $result = True;
    }

    return $result;
}

// returns the next predefined function
function zuc_get_function($formula)
{
    log_debug("zuc_get_function (" . $formula . ")");

    // if not found return -1 because the separator can also be on position 0
    $result = '';

    if (substr($formula, 0, strlen(ZUP_FUNC_IF)) == ZUP_FUNC_IF) {
        $result = ZUP_FUNC_IF;
    }
    if (substr($formula, 0, strlen(ZUP_FUNC_SUM)) == ZUP_FUNC_SUM) {
        $result = ZUP_FUNC_SUM;
    }
    if (substr($formula, 0, strlen(ZUP_FUNC_ISNUM)) == ZUP_FUNC_ISNUM) {
        $result = ZUP_FUNC_ISNUM;
    }

    log_debug("zuc_get_function -> " . $result);
    return $result;
}

// returns the position of the next mathematical operator
function zuc_pos_operator($formula)
{
    log_debug("zuc_pos_operator (" . $formula . ")");

    // if not found return -1 because the separator can also be on position 0
    $next_pos = -1;

    $pos = zuc_pos_separator($formula, ZUP_OPER_ADD, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }
    $pos = zuc_pos_separator($formula, ZUP_OPER_SUB, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }
    $pos = zuc_pos_separator($formula, ZUP_OPER_MUL, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }
    $pos = zuc_pos_separator($formula, ZUP_OPER_DIV, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }

    $pos = zuc_pos_separator($formula, ZUP_OPER_AND, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }
    $pos = zuc_pos_separator($formula, ZUP_OPER_OR, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }

    log_debug("zuc_pos_operator -> " . $next_pos);
    return $next_pos;
}

// returns true if a text contains a mathematical operator
function zuc_has_operator($formula)
{
    log_debug("zuc_has_operator (" . $formula . ")");

    $result = False;
    $pos = zuc_pos_operator($formula);
    if ($pos >= 0) {
        $result = True;
    }

    return $result;
}

// get the left most math operator
function zuc_get_operator($formula)
{
    log_debug("zuc_get_operator (" . $formula . ")");

    $result = '';
    if ($formula[0] == ZUP_OPER_ADD) {
        $result = ZUP_OPER_ADD;
    } else {
        if ($formula[0] == ZUP_OPER_SUB) {
            $result = ZUP_OPER_SUB;
        } else {
            if ($formula[0] == ZUP_OPER_MUL) {
                $result = ZUP_OPER_MUL;
            } else {
                if ($formula[0] == ZUP_OPER_DIV) {
                    $result = ZUP_OPER_DIV;
                } else {
                    if ($formula[0] == ZUP_OPER_AND) {
                        $result = ZUP_OPER_AND;
                    } else {
                        if ($formula[0] == ZUP_OPER_OR) {
                            $result = ZUP_OPER_OR;
                        }
                    }
                }
            }
        }
    }
    return $result;
}

// get the next math operator
function zuc_get_operator_pos($formula)
{
    log_debug("zuc_get_operator_pos (" . $formula . ")");

    $result = '';
    $pos = zuc_pos_operator($formula);
    if ($formula[$pos] == ZUP_OPER_ADD) {
        $result = ZUP_OPER_ADD;
    } else {
        if ($formula[$pos] == ZUP_OPER_SUB) {
            $result = ZUP_OPER_SUB;
        } else {
            if ($formula[$pos] == ZUP_OPER_MUL) {
                $result = ZUP_OPER_MUL;
            } else {
                if ($formula[$pos] == ZUP_OPER_DIV) {
                    $result = ZUP_OPER_DIV;
                } else {
                    if ($formula[$pos] == ZUP_OPER_AND) {
                        $result = ZUP_OPER_AND;
                    } else {
                        if ($formula[$pos] == ZUP_OPER_OR) {
                            $result = ZUP_OPER_OR;
                        }
                    }
                }
            }
        }
    }
    return $result;
}

// returns the position of the next mathematical operator
function zuc_pos_bracket($formula)
{
    log_debug("zuc_pos_bracket (" . $formula . ")");

    // if not found return -1 because the separator can also be on position 0
    $next_pos = -1;

    $pos = zuc_pos_separator($formula, ZUP_CHAR_BRAKET_OPEN, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }
    $pos = zuc_pos_separator($formula, ZUP_CHAR_BRAKET_CLOSE, 0);
    if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
        $next_pos = $pos;
    }

    log_debug("zuc_pos_bracket -> " . $next_pos);
    return $next_pos;
}

// returns the next bracket
function zuc_get_bracket($formula)
{
    log_debug("zuc_get_bracket (" . $formula . ")");

    // if not found return -1 because the separator can also be on position 0
    $result = '';

    if (substr($formula, 0, strlen(ZUP_CHAR_BRAKET_OPEN)) == ZUP_CHAR_BRAKET_OPEN) {
        $result = ZUP_CHAR_BRAKET_OPEN;
    }
    if (substr($formula, 0, strlen(ZUP_CHAR_BRAKET_CLOSE)) == ZUP_CHAR_BRAKET_CLOSE) {
        $result = ZUP_CHAR_BRAKET_CLOSE;
    }

    log_debug("zuc_get_bracket -> " . $result);
    return $result;
}

// returns true if the formula starts with a bracket, so that first the inner part needs to be calculated
function zuc_has_bracket($formula)
{
    log_debug("zuc_has_bracket (" . $formula . ")");

    $result = false;
    if (substr($formula, 0, strlen(ZUP_CHAR_BRAKET_OPEN)) == ZUP_CHAR_BRAKET_OPEN) {
        $result = True;
    }
    return $result;
}

// true if the formula starts with the closing bracket
function zuc_has_bracket_close($formula)
{
    log_debug("zuc_has_bracket_close (" . $formula . ")");

    $result = false;
    if (substr($formula, 0, strlen(ZUP_CHAR_BRAKET_CLOSE)) == ZUP_CHAR_BRAKET_CLOSE) {
        $result = True;
    }
    return $result;
}


/*
  math functions
  --------------
*/

// interprets or converts a math operator condition
// this could should be replaced by R-project.org later
function zuc_math($formula, $operator, $word_array, $time_phr)
{
    $result = $formula;

    // check the parameters
    if ($formula == '') {
        log_warning('Formula is missing in zuc_math', 'zuc_math');
    } elseif ($operator == '') {
        log_warning('Operator is missing in zuc_math', 'zuc_math');
    } else {

        if (isset($time_phr)) {
            $time_word_id = $time_phr->id;
        } else {
            $time_word_id = 0;
        }
        if ($word_array == null) {
            log_warning('Words are missing in zuc_math', 'zuc_math');
        } else {
            if (gettype($word_array) == 'string') {
                $word_array = array($word_array);
            }
            log_debug("zuc_math (" . $formula . "," . $operator . "," . implode(",", $word_array) . "," . $time_word_id . ")");
        }


        $pos = zuc_pos_separator($result, $operator, strlen($operator));
        // echo $formula.": ".$pos." of ".$operator."<br>";
        // echo substr($result, $pos - 1, 1)."<br>";
        // if ($pos > 1 && zuc_has_operator(substr($result, $pos - 1, 1)) == false) {
        if ($pos > 0) {
            $part_l = zu_str_left($result, $pos);
            log_debug("zuc_math -> left part of " . $operator . ": " . $part_l . ".");
            $part_l = zuc_math_parse($part_l, $word_array, $time_phr);
            log_debug("zuc_math -> left part result " . $part_l . ": ");
            $part_r = zu_str_right($result, ($pos + 1) * -1);
            log_debug("zuc_math -> right part " . $operator . ": " . $part_r . ".");
            $part_r = zuc_math_parse($part_r, $word_array, $time_phr);
            log_debug("zuc_math -> right part result " . $part_r . ": ");

            $result_l = floatval($part_l);
            $result_r = floatval($part_r);

            //echo "calc op ".$operator."<br>";
            switch ($operator) {
                case ZUP_OPER_MUL:
                    $result = $result_l * $result_r;
                    break;
                case ZUP_OPER_DIV:
                    if ($result_r <> 0) {
                        log_debug("zuc_math -> result " . $result_l . " / " . $result_r);
                        $result = $result_l / $result_r;
                    } else {
                        $result = 0;
                    }
                    break;
                case ZUP_OPER_ADD:
                    $result = $result_l + $result_r;
                    break;
                case ZUP_OPER_SUB:
                    log_debug("zuc_math -> result " . $result_l . " / " . $result_r);
                    $result = $result_l - $result_r;
                    break;
            }
        } else {
            if ($pos == 0) {
                $part_r = zu_str_right($result, $pos * -1);
                $part_r = zuc_math_parse($part_r, $word_array, $time_phr);
                $result = $operator + $part_r;
            }
        }
    }

    return $result;
}

// interprets or converts a math operator condition
function zuc_math_mul($formula, $word_array, $time_phr)
{
    return zuc_math($formula, ZUP_OPER_MUL, $word_array, $time_phr);
}

// interprets or converts a math operator condition
function zuc_math_div($formula, $word_array, $time_phr)
{
    return zuc_math($formula, ZUP_OPER_DIV, $word_array, $time_phr);
}

// interprets or converts a math operator condition
function zuc_math_add($formula, $word_array, $time_phr)
{
    return zuc_math($formula, ZUP_OPER_ADD, $word_array, $time_phr);
}

// interprets or converts a math operator condition
function zuc_math_sub($formula, $word_array, $time_phr)
{
    return zuc_math($formula, ZUP_OPER_SUB, $word_array, $time_phr);
}

// checks if formula contains a bracket and calculates the inner part first
// why is $word_array and $time_word_id needed? this should have been converted to numbers earlier
function zuc_math_bracket($formula, $word_array, $time_phr)
{
    if (isset($time_phr)) {
        $time_word_id = $time_phr->id;
    } else {
        $time_word_id = 0;
    }
    if ($word_array == null) {
        log_warning('Words are missing in zuc_math', 'zuc_math');
    } else {
        if (gettype($word_array) == 'string') {
            $word_array = array($word_array);
        }
        log_debug("zuc_math_bracket (" . $formula . "," . implode(",", $word_array) . "," . $time_word_id . ")");
    }

    $result = $formula;

    // get the position of the next bracket
    $inner_start_pos = zuc_pos_separator($result, ZUP_CHAR_BRAKET_OPEN, 0);
    // if there is a bracket ...
    if ($inner_start_pos >= 0) {
        // ... and a closing bracket ...
        $inner_end_pos = zuc_pos_separator($result, ZUP_CHAR_BRAKET_CLOSE, $inner_start_pos + 1);

        // ... separate the formula

        // get the left part, but don't get the result of the left part because this can cause loops
        $left_part = substr($result, 0, $inner_start_pos);
        log_debug("zuc_math_bracket -> left_part " . $left_part);

        $inner_part = substr($result, $inner_start_pos + 1, $inner_end_pos - $inner_start_pos - 1);
        log_debug("zuc_math_bracket -> inner_part " . $inner_part);

        // get the right part, but don't get the result of the right part because will be done by the calling function
        $right_part = zu_str_right_of($result, $left_part . ZUP_CHAR_BRAKET_OPEN . $inner_part . ZUP_CHAR_BRAKET_CLOSE);
        log_debug("zuc_math_bracket -> right_part " . $right_part);

        // ... and something needs to be calculated
        if (zuc_has_operator($inner_part)) {

            // calculate the inner part
            $inner_part = zuc_math_parse($inner_part, $word_array, $time_phr);
            log_debug("zuc_math_bracket -> inner_part result " . $inner_part);

            // combine the result
            $result = $left_part . $inner_part . $right_part;
        }
    }

    log_debug("zuc_math_bracket -> done (" . $result . ")");
    return $result;
}

// 
function zuc_math_if($formula, $word_array, $time_phr)
{
    if (isset($time_phr)) {
        $time_word_id = $time_phr->id;
    } else {
        $time_word_id = 0;
    }
    if ($word_array == null) {
        log_warning('Words are missing in zuc_math', 'zuc_math');
    } else {
        if (gettype($word_array) == 'string') {
            $word_array = array($word_array);
        }
        log_debug("zuc_math_if (" . $formula . "," . implode(",", $word_array) . "," . $time_word_id . ")");
    }

    $result = $formula;

    // get the position of the next bracket
    log_debug("zuc_math_if -> separate ");
    $if_start_pos = zuc_pos_separator($result, ZUP_FUNC_IF, 0);
    $inner_start_pos = zuc_pos_separator($result, ZUP_CHAR_BRAKET_OPEN, 0);
    log_debug("zuc_math_if -> separate ");
    // if there is a bracket ...
    if ($if_start_pos >= 0 and $inner_start_pos >= 0 and $if_start_pos < $inner_start_pos) {
        // ... and a closing bracket ...
        $inner_end_pos = zuc_pos_separator($result, ZUP_CHAR_BRAKET_CLOSE, $inner_start_pos + 1);

        // ... separate the formula

        // get the left part, but don't get the result of the left part because this can cause loops
        $left_part = substr($result, 0, $inner_start_pos);
        log_debug("zuc_math_if -> left_part " . $left_part);

        $inner_part = substr($result, $inner_start_pos + 1, $inner_end_pos - $inner_start_pos - 1);
        log_debug('zuc_math_if -> inner_part "' . $inner_part . '"');

        // get the right part, but don't get the result of the right part because will be done by the calling function
        $right_part = zu_str_right_of($result, $left_part . ZUP_CHAR_BRAKET_OPEN . $inner_part . ZUP_CHAR_BRAKET_CLOSE);
        log_debug("zuc_math_if -> right_part " . $right_part);

        // ... and something needs to be looked at
        if (zuc_has_operator($inner_part) or zuc_has_function_pos($inner_part)) {

            // depending on the operator split the inner part if needed
            $operator = zuc_get_operator_pos($inner_part);
            log_debug('zuc_math_if -> operator "' . $operator . '" in "' . $inner_part . '"');
            if ($operator == ZUP_OPER_AND or $operator == ZUP_OPER_OR) {
                $result = Null; // by default no result
                $inner_left_part = zu_str_left_of($inner_part, $operator);
                $inner_right_part = zu_str_right_of($inner_part, $operator);
                $inner_left_part = zuc_math_parse($inner_left_part, $word_array, $time_phr);
                $inner_right_part = zuc_math_parse($inner_right_part, $word_array, $time_phr);
                if ($operator == ZUP_OPER_AND) {
                    if ($inner_left_part == True and $inner_right_part == True) {
                        log_debug('if: get logical result for "' . $inner_part . '" is "true"');
                        $result = zuc_math_parse($right_part, $word_array, $time_phr);
                    }
                }
                if ($operator == ZUP_OPER_OR) {
                    if ($inner_left_part == True or $inner_right_part == True) {
                        log_debug('if: get logical result for "' . $inner_part . '" is "true"');
                        $result = zuc_math_parse($right_part, $word_array, $time_phr);
                    }
                }
            } else {
                // calculate the inner part
                $inner_part_result = zuc_math_parse($inner_part, $word_array, $time_phr);
                log_debug("zuc_math_if -> inner_part result " . $inner_part);
                log_debug('if: get logical result for "' . $inner_part . '" is "' . $inner_part_result . '"');
                // combine the result
                $result = $left_part . $inner_part_result . $right_part;
            }
        }
    }

    log_debug("zuc_math_if ... done (" . $result . ")");
    return $result;
}


/*
  external functions that are supposed to be called from other libraries
  ------------------
*/

// actually calculate the numeric result; this should be replaced by R
function zuc_math_parse($formula, $word_array, $time_phr)
{
    if (isset($time_phr)) {
        $time_word_id = $time_phr->id;
    } else {
        $time_word_id = 0;
    }
    log_debug('calculate (by calling R): "' . $formula . '"');
    if ($word_array == null) {
        log_warning('Words are missing in zuc_math', 'zuc_math');
    } else {
        if (gettype($word_array) == 'string') {
            $word_array = array($word_array);
        }
        log_debug("zuc_math_parse (" . $formula . "," . implode(",", $word_array) . "," . $time_word_id . ")");
    }

    $result = $formula;

    if ($result <> "") {
        if ($result[0] == ZUP_CHAR_CALC) {
            $result = substr($result, 1);
        }

        $result = zuc_math_if($result, $word_array, $time_phr);
        log_debug("zuc_math_parse after if:" . $result);
        $result = zuc_math_bracket($result, $word_array, $time_phr);
        log_debug("zuc_math_parse after bracket:" . $result);
        $result = zuc_math_mul($result, $word_array, $time_phr);
        log_debug("zuc_math_parse after mul:" . $result);
        $result = zuc_math_div($result, $word_array, $time_phr);
        log_debug("zuc_math_parse after div:" . $result);
        $result = zuc_math_add($result, $word_array, $time_phr);
        log_debug("zuc_math_parse after add:" . $result);
        $result = zuc_math_sub($result, $word_array, $time_phr);
        log_debug("zuc_math_parse after sub:" . $result);
    }

    log_debug('calculated result: "' . $result . '"');
    return $result;
}

// returns true if the next symbol is a math symbol (that can be ignored if a formula should be converted to the db format)
function zuc_is_math_symbol($formula)
{
    log_debug("zuc_is_math_symbol (" . $formula . ")");
    $result = false;
    if (zuc_has_operator($formula[0])) {
        log_debug("zuc_is_math_symbol -> operator");
        $result = True;
    } else {
        if (zuc_has_bracket($formula[0])) {
            log_debug("zuc_is_math_symbol -> bracket");
            $result = True;
        } else {
            if (zuc_has_bracket_close($formula[0])) {
                log_debug("zuc_is_math_symbol -> close");
                $result = True;
            } else {
                if (zuc_pos_function($formula) == 0) {
                    log_debug("zuc_is_math_symbol -> func");
                    $result = True;
                }
            }
        }
    }
    log_debug("zuc_is_math_symbol ... (" . zu_dsp_bool($result) . ")");
    return $result;
}

// true if the first char of the formula is a number
function zuc_next_char_is_num($formula)
{
    $result = false;
    if ($formula[0] == "0" or
        $formula[0] == "1" or
        $formula[0] == "2" or
        $formula[0] == "3" or
        $formula[0] == "4" or
        $formula[0] == "5" or
        $formula[0] == "6" or
        $formula[0] == "7" or
        $formula[0] == "8" or
        $formula[0] == "9") {
        $result = True;
    }
    return $result;
}


function zuc_is_math_symbol_or_num($formula)
{
    log_debug("zuc_is_math_symbol_or_num (" . $formula . ")");
    $result = false;
    if (zuc_is_math_symbol($formula)) {
        log_debug("zuc_is_math_symbol_or_num -> math");
        $result = True;
    } else {
        $result = zuc_next_char_is_num($formula);
    }
    log_debug("zuc_is_math_symbol_or_num ... (" . zu_dsp_bool($result) . ")");
    return $result;
}

// returns the position of the next math symbol
function zuc_pos_math_symbol($formula)
{
    log_debug("zuc_pos_math_symbol (" . $formula . ")");

    $result = -1;
    $pos = zuc_pos_operator($formula);
    if ($pos >= 0 and ($pos < $result or $result == -1)) {
        $result = $pos;
    }
    $pos = zuc_pos_function($formula);
    if ($pos >= 0 and ($pos < $result or $result == -1)) {
        $result = $pos;
    }
    $pos = zuc_pos_bracket($formula);
    if ($pos >= 0 and ($pos < $result or $result == -1)) {
        $result = $pos;
    }

    log_debug("zuc_pos_math_symbol ... (" . $result . ")");
    return $result;
}

// returns the next math symbol or number
function zuc_get_math_symbol($formula)
{
    log_debug("zuc_get_math_symbol (" . $formula . ")");

    $result = "";
    if ($result == '') {
        $result = zuc_get_operator($formula);
    }
    if ($result == '') {
        $result = zuc_get_function($formula);
    }
    if ($result == '') {
        $result = zuc_get_bracket($formula);
    }
    /*  if ($result == '') {
        if ($formula[0] >= 0 AND $formula[0] <= 9) {
           $result = $formula[0];
        }
      }  */

    log_debug("zuc_get_math_symbol -> (" . $result . ")");
    return $result;
}
