<?php

/*

  internal_calc.php - internal zukunft math class to calculate formula results
  -----------------

  most parts should be replaced by an r-project.org REST service call


  internal support functions that are used in other libraries such as zu_lib_calc_conv.php
  ----------------
  
  pos_function      - returns the position of the next predefined function
  get_function      - returns the next predefined function
  pos_operator      - returns the position of the next mathematical operator
  has_operator      - returns true if a text contains a mathematical operator
  get_operator      - get the left most math operator
  has_bracket       - true if the formula starts with a bracket, so that first the inner part needs to be calculated
  has_bracket_close - true if the formula starts with the closing bracket
  get_bracket       -


  math functions that actually calculate the result
  ----
  
  math          - interprets or converts a math operator condition
  math_mul      - calls math for multiplication
  math_div
  math_add
  math_sub
  math_bracket  - checks if formula contains a bracket and calculates the inner part first

  
  external functions that are used in other libraries such as zu_lib_calc_conv.php
  --------
  
  math_parse       - actually calculate the numeric result and calls the math functions; this should be replaced by R

  is_math_symbol   - true if the next symbol is a math symbol, that can be ignored if a formula should be converted to the db format
  

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

include_once SHARED_PATH . 'library.php';

use cfg\formula\expression;
use shared\library;

class math
{

    // interface const (to be removed, because specific functions for each part has been created)
    const RESULT_TYPE_DB = 'db';       // returns a formula in the database format
    const RESULT_TYPE_USER = 'user';   // returns a formula in the user format
    const RESULT_TYPE_VALUE = 'value'; // returns a result of the formula

    /*
     * external functions that are supposed to be called from other libraries
     */

    /**
     * actually calculate the numeric result; this should be replaced by R
     */
    function parse(string $formula): ?float
    {
        $result = $formula;

        if ($result <> "") {
            if ($result[0] == expression::CHAR_CALC) {
                $result = substr($result, 1);
            }

            $result = $this->math_if($result);
            log_debug("math_parse after if:" . $result);
            $result = $this->math_bracket($result);
            log_debug("math_parse after bracket:" . $result);
            $result = $this->math_mul($result);
            log_debug("math_parse after mul:" . $result);
            $result = $this->math_div($result);
            log_debug("math_parse after div:" . $result);
            $result = $this->math_add($result);
            log_debug("math_parse after add:" . $result);
            $result = $this->math_sub($result);
            log_debug("math_parse after sub:" . $result);
        }

        log_debug('calculated result: "' . $result . '"');
        if (is_numeric($result)) {
            return (float)$result;
        } else {
            log_err('cannot parse "' . $formula . '" to number');
            return null;
        }
    }

    /*
     * internal functions
     */

    /**
     * @returns int the position of the corresponding separator and takes text fields and brackets into account by not splitting them
     */
    function pos_separator(string $formula, string $separator, int $start_pos): int
    {
        log_debug("pos_separator (" . $formula . "," . $separator . "," . $start_pos . ")");

        $pos = $start_pos;     // the search pos; was - 1 because for php the first char is 0, but in the lib it should be 1, but the is changed now
        $text_linked = False;  // do not look at text in high quotes
        $open_brackets = 0;    // number of brackets open
        $found = False;        // separator has been found

        do {
            // don't look into text that is a high quotes
            if ($open_brackets == 0) {
                if (substr($formula, $pos, strlen(expression::TXT_FIELD)) == expression::TXT_FIELD) {
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
                if (substr($formula, $pos, strlen(expression::BRACKET_OPEN)) == expression::BRACKET_OPEN) {
                    $open_brackets = $open_brackets + 1;
                }
                if (substr($formula, $pos, strlen(expression::BRACKET_CLOSE)) == expression::BRACKET_CLOSE && $open_brackets > 0) {
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

        log_debug($pos);
        return $pos;
    }


    /**
     * @returns int the position of the next predefined function
     */
    private function pos_function(string $formula): int
    {
        log_debug($formula);

        // if not found return -1 because the separator can also be on position 0
        $pos = -1;

        if ($pos < 0) {
            $pos = $this->pos_separator($formula, expression::FUNC_IF, 0);
        }
        if ($pos < 0) {
            $pos = $this->pos_separator($formula, expression::FUNC_SUM, 0);
        }
        if ($pos < 0) {
            $pos = $this->pos_separator($formula, expression::FUNC_IS_NUM, 0);
        }

        log_debug($pos);
        return $pos;
    }

    /**
     * @returns bool true if a text contains a mathematical function
     */
    private function has_function_pos(string $formula): bool
    {
        log_debug($formula);

        $result = False;
        $pos = $this->pos_function($formula);
        if ($pos >= 0) {
            $result = True;
        }

        return $result;
    }

    /**
     * @returns string the next predefined function
     */
    private function get_function(string $formula): string
    {
        log_debug("get_function (" . $formula . ")");

        // if not found return -1 because the separator can also be on position 0
        $result = '';

        if (str_starts_with($formula, expression::FUNC_IF)) {
            $result = expression::FUNC_IF;
        }
        if (str_starts_with($formula, expression::FUNC_SUM)) {
            $result = expression::FUNC_SUM;
        }
        if (str_starts_with($formula, expression::FUNC_IS_NUM)) {
            $result = expression::FUNC_IS_NUM;
        }

        log_debug($result);
        return $result;
    }

    /**
     * @returns int the position of the next mathematical operator
     */
    private function pos_operator(string $formula): int
    {
        log_debug("pos_operator (" . $formula . ")");

        // if not found return -1 because the separator can also be on position 0
        $next_pos = -1;

        $pos = $this->pos_separator($formula, expression::ADD, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, expression::SUB, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, expression::MUL, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, expression::DIV, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }

        $pos = $this->pos_separator($formula, expression::AND, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, expression::OR, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }

        log_debug($next_pos);
        return $next_pos;
    }

    /**
     * @returns bool true if a text contains a mathematical operator
     */
    private function has_operator(string $formula): bool
    {
        log_debug($formula);

        $result = False;
        $pos = $this->pos_operator($formula);
        if ($pos >= 0) {
            $result = True;
        }

        return $result;
    }

    /**
     * @return string get the left most math operator
     */
    private function get_operator(string $formula): string
    {
        log_debug($formula);

        $result = '';
        if ($formula[0] == expression::ADD) {
            $result = expression::ADD;
        } else {
            if ($formula[0] == expression::SUB) {
                $result = expression::SUB;
            } else {
                if ($formula[0] == expression::MUL) {
                    $result = expression::MUL;
                } else {
                    if ($formula[0] == expression::DIV) {
                        $result = expression::DIV;
                    } else {
                        if ($formula[0] == expression::AND) {
                            $result = expression::AND;
                        } else {
                            if ($formula[0] == expression::OR) {
                                $result = expression::OR;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return string get the next math operator
     */
    private function get_operator_pos(string $formula): string
    {
        log_debug($formula);

        $result = '';
        $pos = $this->pos_operator($formula);
        if ($formula[$pos] == expression::ADD) {
            $result = expression::ADD;
        } elseif ($formula[$pos] == expression::SUB) {
            $result = expression::SUB;
        } elseif ($formula[$pos] == expression::MUL) {
            $result = expression::MUL;
        } elseif ($formula[$pos] == expression::DIV) {
            $result = expression::DIV;
        } elseif ($formula[$pos] == expression::AND) {
            $result = expression::AND;
        } elseif ($formula[$pos] == expression::OR) {
            $result = expression::OR;
        }
        return $result;
    }

    /**
     * @returns string the next bracket
     */
    private function get_bracket(string $formula): string
    {
        log_debug($formula);

        // if not found return -1 because the separator can also be on position 0
        $result = '';

        if (str_starts_with($formula, expression::BRACKET_OPEN)) {
            $result = expression::BRACKET_OPEN;
        }
        if (str_starts_with($formula, expression::BRACKET_CLOSE)) {
            $result = expression::BRACKET_CLOSE;
        }

        log_debug( $result);
        return $result;
    }

    /**
     * @returns bool true if the formula starts with a bracket, so that first the inner part needs to be calculated
     */
    function has_bracket(string $formula): bool
    {
        log_debug($formula);

        if (str_starts_with($formula, expression::BRACKET_OPEN)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @returns bool true if the formula starts with the closing bracket
     */
    private function has_bracket_close(string $formula): bool
    {
        if (str_starts_with($formula, expression::BRACKET_CLOSE)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @returns bool true if the formula starts with a bracket, so that first the inner part needs to be calculated
     */
    function has_formula(string $formula): bool
    {
        if (str_starts_with($formula, expression::FORMULA_START)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @returns bool true if the remaining formula part is only text, do not parse it any more
     */
    function is_text_only(string $formula): bool
    {
        if ($formula[0] == expression::TXT_FIELD && substr($formula, -1) == expression::TXT_FIELD) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @returns bool true if the remaining formula part is only a date
     */
    function is_date(string $formula): bool
    {
        $date = date_parse($formula);
        if (checkdate($date["month"], $date["day"], $date["year"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @returns int the position of the word id in the database reference format
     */
    function pos_word(string $formula): int
    {
        $result = -1;

        $calc = new math();

        $pos = $calc->pos_separator($formula, expression::WORD_START, 0,);
        $end = $calc->pos_separator($formula, expression::WORD_END, $pos);
        if ($pos >= 0 and $end > $pos) {
            $result = $pos;
        }

        log_debug($result);
        return $result;
    }

    /*
     * math functions
     */

    /**
     * interprets or converts a math operator condition
     * this could and should be replaced by R-project.org later
     * @returns string with the result of the formula that can be converted into a number
     */
    private function calc(string $formula, $operator): string
    {
        $result = $formula;

        $lib = new library();

        // check the parameters
        if ($formula == '') {
            log_warning('Formula is missing in math', 'math');
        } elseif ($operator == '') {
            log_warning('Operator is missing in math', 'math');
        } else {


            $pos = $this->pos_separator($result, $operator, strlen($operator));
            // echo $formula.": ".$pos." of ".$operator."<br>";
            // echo substr($result, $pos - 1, 1)."<br>";
            // if ($pos > 1 && has_operator(substr($result, $pos - 1, 1)) == false) {
            if ($pos > 0) {
                $part_l = $lib->str_left($result, $pos);
                log_debug("left part of " . $operator . ": " . $part_l . ".");
                $part_l = $this->parse($part_l);
                log_debug("left part result " . $part_l . ": ");
                $part_r = $lib->str_right($result, ($pos + 1) * -1);
                log_debug("right part " . $operator . ": " . $part_r . ".");
                $part_r = $this->parse($part_r);
                log_debug("right part result " . $part_r . ": ");

                $result_l = floatval($part_l);
                $result_r = floatval($part_r);

                //echo "calc op ".$operator."<br>";
                switch ($operator) {
                    case expression::MUL:
                        $result = $result_l * $result_r;
                        break;
                    case expression::DIV:
                        if ($result_r <> 0) {
                            log_debug("result " . $result_l . " / " . $result_r);
                            $result = $result_l / $result_r;
                        } else {
                            $result = 0;
                        }
                        break;
                    case expression::ADD:
                        $result = $result_l + $result_r;
                        break;
                    case expression::SUB:
                        log_debug("result " . $result_l . " / " . $result_r);
                        $result = $result_l - $result_r;
                        break;
                }
            } else {
                if ($pos == 0) {
                    $part_r = $lib->str_right($result, $pos * -1);
                    $part_r = $this->parse($part_r);
                    $result = $operator . $part_r;
                }
            }
        }

        return $result;
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_mul(string $formula): string
    {
        return $this->calc($formula, expression::MUL);
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_div(string $formula): string
    {
        return $this->calc($formula, expression::DIV);
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_add(string $formula): string
    {
        return $this->calc($formula, expression::ADD);
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_sub(string $formula): string
    {
        return $this->calc($formula, expression::SUB);
    }

    /**
     * checks if formula contains a bracket and calculates the inner part first
     */
    function math_bracket(string $formula): string
    {
        $result = $formula;

        $lib = new library();

        // get the position of the next bracket
        $inner_start_pos = $this->pos_separator($result, expression::BRACKET_OPEN, 0);
        // if there is a bracket ...
        if ($inner_start_pos >= 0) {
            // ... and a closing bracket ...
            $inner_end_pos = $this->pos_separator($result, expression::BRACKET_CLOSE, $inner_start_pos + 1);

            // ... separate the formula

            // get the left part, but don't get the result of the left part because this can cause loops
            $left_part = substr($result, 0, $inner_start_pos);
            log_debug("left_part " . $left_part);

            $inner_part = substr($result, $inner_start_pos + 1, $inner_end_pos - $inner_start_pos - 1);
            log_debug("inner_part " . $inner_part);

            // get the right part, but don't get the result of the right part because will be done by the calling function
            $right_part = $lib->str_right_of($result, $left_part . expression::BRACKET_OPEN . $inner_part . expression::BRACKET_CLOSE);
            log_debug("right_part " . $right_part);

            // ... and something needs to be calculated
            if ($this->has_operator($inner_part)) {

                // calculate the inner part
                $inner_part = $this->parse($inner_part);
                log_debug("inner_part result " . $inner_part);

                // combine the result
                $result = $left_part . $inner_part . $right_part;
            }
        }

        log_debug("done " . $result);
        return $result;
    }

    /**
     * @param string $formula
     * @return string
     */
    private function math_if(string $formula): string
    {
        $result = $formula;

        $lib = new library();

        // get the position of the next bracket
        log_debug("separate ");
        $if_start_pos = $this->pos_separator($result, expression::FUNC_IF, 0);
        $inner_start_pos = $this->pos_separator($result, expression::BRACKET_OPEN, 0);
        log_debug("separate ");
        // if there is a bracket ...
        if ($if_start_pos >= 0 and $inner_start_pos >= 0 and $if_start_pos < $inner_start_pos) {
            // ... and a closing bracket ...
            $inner_end_pos = $this->pos_separator($result, expression::BRACKET_CLOSE, $inner_start_pos + 1);

            // ... separate the formula

            // get the left part, but don't get the result of the left part because this can cause loops
            $left_part = substr($result, 0, $inner_start_pos);
            log_debug("left_part " . $left_part);

            $inner_part = substr($result, $inner_start_pos + 1, $inner_end_pos - $inner_start_pos - 1);
            log_debug('inner_part "' . $inner_part . '"');

            // get the right part, but don't get the result of the right part because will be done by the calling function
            $right_part = $lib->str_right_of($result, $left_part . expression::BRACKET_OPEN . $inner_part . expression::BRACKET_CLOSE);
            log_debug("right_part " . $right_part);

            // ... and something needs to be looked at
            if ($this->has_operator($inner_part) or $this->has_function_pos($inner_part)) {

                // depending on the operator split the inner part if needed
                $operator = $this->get_operator_pos($inner_part);
                log_debug('operator "' . $operator . '" in "' . $inner_part . '"');
                if ($operator == expression::AND or $operator == expression::OR) {
                    $result = null; // by default no result
                    $inner_left_part = $lib->str_left_of($inner_part, $operator);
                    $inner_right_part = $lib->str_right_of($inner_part, $operator);
                    $inner_left_part = $this->parse($inner_left_part);
                    $inner_right_part = $this->parse($inner_right_part);
                    if ($operator == expression::AND) {
                        if ($inner_left_part and $inner_right_part) {
                            log_debug('if: get logical result for "' . $inner_part . '" is "true"');
                            $result = $this->parse($right_part);
                        }
                    }
                    if ($operator == expression::OR) {
                        if ($inner_left_part or $inner_right_part) {
                            log_debug('if: get logical result for "' . $inner_part . '" is "true"');
                            $result = $this->parse($right_part);
                        }
                    }
                } else {
                    // calculate the inner part
                    $inner_part_result = $this->parse($inner_part);
                    log_debug("inner_part result " . $inner_part);
                    log_debug('if: get logical result for "' . $inner_part . '" is "' . $inner_part_result . '"');
                    // combine the result
                    $result = $left_part . $inner_part_result . $right_part;
                }
            }
        }

        log_debug("done " . $result);
        return $result;
    }


    /**
     * @returns bool true if the next symbol is a math symbol (that can be ignored if a formula should be converted to the db format)
     */
    private function is_math_symbol(string $formula): bool
    {
        log_debug($formula);
        $result = false;
        if ($this->has_operator($formula[0])) {
            log_debug("operator");
            $result = True;
        } else {
            if ($this->has_bracket($formula[0])) {
                log_debug("bracket");
                $result = True;
            } else {
                if ($this->has_bracket_close($formula[0])) {
                    log_debug("close");
                    $result = True;
                } else {
                    if ($this->pos_function($formula) == 0) {
                        log_debug("func");
                        $result = True;
                    }
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @returns bool true if the first char of the formula is a number
     */
    private function next_char_is_num(string $formula): bool
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


    function is_math_symbol_or_num(string $formula): bool
    {
        log_debug($formula);
        if ($this->is_math_symbol($formula)) {
            log_debug("math");
            $result = True;
        } else {
            $result = $this->next_char_is_num($formula);
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * returns the next math symbol or number
     */
    function get_math_symbol(string $formula): string
    {
        log_debug($formula);

        $result = $this->get_operator($formula);
        if ($result == '') {
            $result = $this->get_function($formula);
        }
        if ($result == '') {
            $result = $this->get_bracket($formula);
        }
        /*  if ($result == '') {
            if ($formula[0] >= 0 AND $formula[0] <= 9) {
               $result = $formula[0];
            }
          }  */

        log_debug($result);
        return $result;
    }

}