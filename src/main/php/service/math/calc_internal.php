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
  math_min      - replaces the min function by the smallest of its arguments
  math_max      - replaces the max function by the largest of its arguments
  math_one_number - replaces the functions of one number e.g. sqrt or log
  math_pow      - calculates a power

  
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

namespace Zukunft\ZukunftCom\main\php\service\math;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CALC . 'arithmetic.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\calc\arithmetic;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;

class calc_internal
{

    // interface const (to be removed, because specific functions for each part has been created)
    const string RESULT_TYPE_DB = 'db';       // returns a formula in the database format
    const string RESULT_TYPE_USER = 'user';   // returns a formula in the user format
    const string RESULT_TYPE_VALUE = 'value'; // returns a result of the formula

    // the marker of the exponent in a number in scientific notation e.g. "6.55E-5"
    const string EXPONENT_MARKER = 'E';
    // the text of a part that cannot be calculated and is already reported, so the calling parts give no number too
    const string NO_NUMBER = 'NaN';
    // a name directly before a bracket, but not the exponent of a number e.g. the "E3" of "5E3 (2)"
    const string FUNCTION_NAME_PATTERN = '/(?<![\p{L}\d_.])\p{L}[\p{L}\d_.]*(?=\s*\()/u';

    /*
     * external functions that are supposed to be called from other libraries
     */

    /**
     * actually calculate the numeric result; this should be replaced by R
     * @param string $formula the math expression with numbers
     * @param user_message $msg to report a missing math function or an expression that cannot be calculated
     * @return float|null the number or null if the expression cannot be calculated, which is reported on $msg
     */
    function parse(string $formula, user_message $msg): ?float
    {
        // never a wrong number, because the operators would else calculate without the missing function
        $missing = $this->missing_function($formula);
        if ($missing != '') {
            $msg->add(msg_id::FORMULA_FUNCTION_MISSING, [
                msg_id::VAR_NAME => $missing,
                msg_id::VAR_EXPRESSION => $formula
            ]);
            return null;
        }

        $result = $formula;

        if ($result <> "") {
            if ($result[0] == chars::CHAR_CALC) {
                $result = substr($result, 1);
            }

            // reduce sign pairs e.g. "+ -9" to "- 9" before the calculation is started
            $result = $this->combine_signs($result);

            $result = $this->math_min($result, $msg);
            log_debug("math_parse after min:" . $result);
            $result = $this->math_max($result, $msg);
            log_debug("math_parse after max:" . $result);
            $result = $this->math_one_number($result, $msg);
            log_debug("math_parse after functions of one number:" . $result);
            $result = $this->math_list($result, $msg);
            log_debug("math_parse after list functions:" . $result);
            $result = $this->math_if($result, $msg);
            log_debug("math_parse after if:" . $result);
            $result = $this->math_bracket($result, $msg);
            log_debug("math_parse after bracket:" . $result);
            // the operator with the lowest precedence splits the formula first, because each part
            // is parsed on its own: splitting at a "*" first would evaluate a "+" inside its left
            // part before the multiplication, e.g. "1 + 2 * 3" as "(1 + 2) * 3"
            $result = $this->math_add($result, $msg);
            log_debug("math_parse after add:" . $result);
            $result = $this->math_sub($result, $msg);
            log_debug("math_parse after sub:" . $result);
            $result = $this->math_mul($result, $msg);
            log_debug("math_parse after mul:" . $result);
            $result = $this->math_div($result, $msg);
            log_debug("math_parse after div:" . $result);
            $result = $this->math_pow($result, $msg);
            log_debug("math_parse after pow:" . $result);
        }

        log_debug('calculated result: "' . $result . '"');
        if (is_numeric($result)) {
            return (float)$result;
        } else {
            if ($result != self::NO_NUMBER) {
                log_err_msg('cannot parse "' . $formula . '" to number', $msg);
            }
            return null;
        }
    }

    /**
     * @param string $formula the math expression with numbers
     * @return string the first name before a bracket that is not in chars::FUNC_CALCULATED or an empty string if there is none
     */
    function missing_function(string $formula): string
    {
        $missing = array_values(array_diff($this->function_names($formula), chars::FUNC_CALCULATED));
        return $missing[0] ?? '';
    }

    /**
     * @param string $formula a math expression
     * @return array every name before a bracket outside a text in the order of the expression
     */
    function function_names(string $formula): array
    {
        preg_match_all(self::FUNCTION_NAME_PATTERN, $this->without_text($formula), $found);
        return $found[0];
    }

    /**
     * @param string $formula a math expression
     * @return string the expression without the texts in high quotes e.g. without the phrase names
     */
    function without_text(string $formula): string
    {
        $txt_field = chars::TXT_FIELD . '[^' . chars::TXT_FIELD . ']*' . chars::TXT_FIELD;
        return preg_replace('/' . $txt_field . '/', '', $formula);
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
                if (substr($formula, $pos, strlen(chars::TXT_FIELD)) == chars::TXT_FIELD) {
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
                    if (substr($formula, $pos, strlen($separator)) == $separator
                        and !$this->is_sign($formula, $pos)) {
                        $found = true;
                    }
                }
                if (substr($formula, $pos, strlen(chars::BRACKET_OPEN)) == chars::BRACKET_OPEN) {
                    $open_brackets = $open_brackets + 1;
                }
                if (substr($formula, $pos, strlen(chars::BRACKET_CLOSE)) == chars::BRACKET_CLOSE && $open_brackets > 0) {
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
     * like pos_separator, but returns the position of the LAST top-level separator instead of the first
     * needed so that left-associative operators (subtraction, division) are evaluated correctly:
     * "a - b - c" must be read as "(a - b) - c", which means splitting at the last "-", not the first
     * (splitting at the first "-" would wrongly compute "a - (b - c)")
     * @returns int the position of the last separator at bracket level 0, or -1 if not found
     */
    private function pos_separator_last(string $formula, string $separator, int $start_pos): int
    {
        $pos = $start_pos;
        $text_linked = False;  // do not look at text in high quotes
        $open_brackets = 0;    // number of brackets open
        $last = -1;            // position of the last separator found

        while ($pos <= strlen($formula)) {
            // don't look into text that is in high quotes
            if ($open_brackets == 0) {
                if (substr($formula, $pos, strlen(chars::TXT_FIELD)) == chars::TXT_FIELD) {
                    if ($text_linked) {
                        $text_linked = False;
                    } else {
                        $text_linked = True;
                    }
                }
            }

            // remember the separator only at the top bracket level and outside text
            if (!$text_linked) {
                if ($open_brackets == 0) {
                    if (substr($formula, $pos, strlen($separator)) == $separator
                        and !$this->is_sign($formula, $pos)) {
                        $last = $pos;
                    }
                }
                if (substr($formula, $pos, strlen(chars::BRACKET_OPEN)) == chars::BRACKET_OPEN) {
                    $open_brackets = $open_brackets + 1;
                }
                if (substr($formula, $pos, strlen(chars::BRACKET_CLOSE)) == chars::BRACKET_CLOSE && $open_brackets > 0) {
                    $open_brackets = $open_brackets - 1;
                }
            }

            $pos = $pos + 1;
        }

        log_debug($last);
        return $last;
    }

    /**
     * true if the "+" or "-" at the given position belongs to a number and is not an operator:
     * the sign of a number at the start of the formula or after another operator or an open
     * bracket (e.g. the "-" of "6 / -2"), and the exponent sign of a number in scientific
     * notation (e.g. the "-" of "6.5502183406112E-5", which php writes for a small intermediate
     * result); a sign that no number follows (e.g. a lone "-" or "- log(x)") stays an operator,
     * so that the db format conversion still detects it as a math symbol
     *
     * @param string $formula the formula text
     * @param int $pos the position of a separator in the formula
     * @return bool true if the separator at the position is the sign of a number
     */
    private function is_sign(string $formula, int $pos): bool
    {
        $result = false;
        $char = substr($formula, $pos, 1);
        if ($char == chars::ADD or $char == chars::SUB) {
            // the chars next to the sign that are not blanks
            $prev = substr(rtrim(substr($formula, 0, $pos)), -1);
            $next = substr(ltrim(substr($formula, $pos + 1)), 0, 1);
            $number_follows = (ctype_digit($next) or $next == chars::DECIMAL_POINT);
            $is_start = ($prev == '');
            $after_operator = in_array($prev, [chars::ADD, chars::SUB, chars::MUL, chars::DIV, chars::POW, chars::BRACKET_OPEN]);
            // brackets around the whole condition, because "or" binds weaker than the assignment
            $result = ((($is_start or $after_operator) and $number_follows)
                or $this->is_exponent_sign($formula, $pos));
        }
        return $result;
    }

    /**
     * @param string $formula the formula text
     * @param int $pos the position of a "+" or "-" in the formula
     * @return bool true if the sign is the exponent sign of a number in scientific notation
     */
    private function is_exponent_sign(string $formula, int $pos): bool
    {
        $result = false;
        if ($pos >= 2) {
            $marker = strtoupper(substr($formula, $pos - 1, 1));
            $before = substr($formula, $pos - 2, 1);
            $after = substr($formula, $pos + 1, 1);
            $result = ($marker == self::EXPONENT_MARKER and ctype_digit($before) and ctype_digit($after));
        }
        return $result;
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
            $pos = $this->pos_separator($formula, chars::FUNC_IF, 0);
        }
        if ($pos < 0) {
            $pos = $this->pos_separator($formula, chars::FUNC_SUM, 0);
        }
        if ($pos < 0) {
            $pos = $this->pos_separator($formula, chars::FUNC_IS_NUM, 0);
        }
        if ($pos < 0) {
            $pos = $this->pos_separator($formula, chars::FUNC_MIN, 0);
        }
        if ($pos < 0) {
            $pos = $this->pos_separator($formula, chars::FUNC_MAX, 0);
        }
        foreach ([...chars::FUNC_ONE_NUMBER, ...chars::FUNC_LIST] as $name) {
            if ($pos < 0) {
                $pos = $this->pos_separator($formula, $name, 0);
            }
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

        if (str_starts_with($formula, chars::FUNC_IF)) {
            $result = chars::FUNC_IF;
        }
        if (str_starts_with($formula, chars::FUNC_SUM)) {
            $result = chars::FUNC_SUM;
        }
        if (str_starts_with($formula, chars::FUNC_IS_NUM)) {
            $result = chars::FUNC_IS_NUM;
        }
        if (str_starts_with($formula, chars::FUNC_MIN)) {
            $result = chars::FUNC_MIN;
        }
        if (str_starts_with($formula, chars::FUNC_MAX)) {
            $result = chars::FUNC_MAX;
        }
        foreach ([...chars::FUNC_ONE_NUMBER, ...chars::FUNC_LIST] as $name) {
            if (str_starts_with($formula, $name)) {
                $result = $name;
            }
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

        $pos = $this->pos_separator($formula, chars::ADD, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, chars::SUB, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, chars::MUL, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, chars::DIV, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, chars::POW, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }

        $pos = $this->pos_separator($formula, chars::AND, 0);
        if ($pos >= 0 and ($pos < $next_pos or $next_pos < 0)) {
            $next_pos = $pos;
        }
        $pos = $this->pos_separator($formula, chars::OR, 0);
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
        if ($formula[0] == chars::ADD) {
            $result = chars::ADD;
        } else {
            if ($formula[0] == chars::SUB) {
                $result = chars::SUB;
            } else {
                if ($formula[0] == chars::MUL) {
                    $result = chars::MUL;
                } else {
                    if ($formula[0] == chars::DIV) {
                        $result = chars::DIV;
                    } else {
                        if ($formula[0] == chars::AND) {
                            $result = chars::AND;
                        } else {
                            if ($formula[0] == chars::OR) {
                                $result = chars::OR;
                            }
                        }
                    }
                }
            }
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

        if (str_starts_with($formula, chars::BRACKET_OPEN)) {
            $result = chars::BRACKET_OPEN;
        }
        if (str_starts_with($formula, chars::BRACKET_CLOSE)) {
            $result = chars::BRACKET_CLOSE;
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

        if (str_starts_with($formula, chars::BRACKET_OPEN)) {
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
        if (str_starts_with($formula, chars::BRACKET_CLOSE)) {
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
        if (str_starts_with($formula, chars::FORMULA_START)) {
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
        if ($formula[0] == chars::TXT_FIELD && substr($formula, -1) == chars::TXT_FIELD) {
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

        $calc = new calc_internal();

        $pos = $calc->pos_separator($formula, chars::WORD_START, 0,);
        $end = $calc->pos_separator($formula, chars::WORD_END, $pos);
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
    private function calc(string $formula, $operator, user_message $msg): string
    {
        $result = $formula;

        $lib = new library();

        // check the parameters
        if ($formula == '') {
            log_warning('Formula is missing in math', 'math');
        } elseif ($operator == '') {
            log_warning('Operator is missing in math', 'math');
        } else {


            // subtraction and division are left-associative ("a - b - c" = "(a - b) - c"),
            // so split at the last operator and recurse on the left; addition and
            // multiplication are associative, so the first operator is fine and kept as before
            // TODO Prio 1 review
            if ($operator == chars::SUB or $operator == chars::DIV) {
                $pos = $this->pos_separator_last($result, $operator, strlen($operator));
            } else {
                $pos = $this->pos_separator($result, $operator, strlen($operator));
            }
            // echo $formula.": ".$pos." of ".$operator."<br>";
            // echo substr($result, $pos - 1, 1)."<br>";
            // if ($pos > 1 && has_operator(substr($result, $pos - 1, 1)) == false) {
            if ($pos > 0) {
                $part_l = $lib->str_left($result, $pos);
                log_debug("left part of " . $operator . ": " . $part_l . ".");
                if (trim($part_l) == '' and ($operator == chars::SUB or $operator == chars::ADD)) {
                    // TODO Prio 1 review and avoid this exception
                    // a blank before a sign (e.g. the " -8" in " -8 / 52" created by the calc
                    // validation substitution) is not a left operand: the sign belongs to the
                    // number, so use zero instead of parsing the blank as a number
                    $part_l = 0.0;
                } else {
                    $part_l = $this->parse($part_l, $msg);
                }
                log_debug("left part result " . $part_l . ": ");
                $part_r = $lib->str_right($result, ($pos + 1) * -1);
                log_debug("right part " . $operator . ": " . $part_r . ".");
                $part_r = $this->parse($part_r, $msg);
                log_debug("right part result " . $part_r . ": ");

                $result_l = floatval($part_l);
                $result_r = floatval($part_r);

                //echo "calc op ".$operator."<br>";
                switch ($operator) {
                    case chars::MUL:
                        $result = $result_l * $result_r;
                        break;
                    case chars::DIV:
                        if ($result_r <> 0) {
                            log_debug("result " . $result_l . " / " . $result_r);
                            $result = $result_l / $result_r;
                        } else {
                            $result = self::NO_NUMBER;
                            // a part without a number is already reported and is no zero
                            if ($part_r !== null) {
                                $msg->add(msg_id::FORMULA_DIVISION_BY_ZERO, [msg_id::VAR_EXPRESSION => $formula]);
                            }
                        }
                        break;
                    case chars::ADD:
                        $result = $result_l + $result_r;
                        break;
                    case chars::SUB:
                        log_debug("result " . $result_l . " / " . $result_r);
                        $result = $result_l - $result_r;
                        break;
                    case chars::POW:
                        // a part without a number is already reported and is no zero
                        if ($part_l !== null and $part_r !== null) {
                            $result = $this->power($result_l, $result_r, $formula, $msg);
                        }
                        break;
                }
                // floatval would count a part without a number as zero
                if ($part_l === null or $part_r === null) {
                    $result = self::NO_NUMBER;
                }
            } else {
                if ($pos == 0) {
                    $part_r = $lib->str_right($result, $pos * -1);
                    $part_r = $this->parse($part_r, $msg);
                    $result = $operator . $part_r;
                    if ($part_r === null) {
                        $result = self::NO_NUMBER;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_mul(string $formula, user_message $msg): string
    {
        return $this->calc($formula, chars::MUL, $msg);
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_div(string $formula, user_message $msg): string
    {
        return $this->calc($formula, chars::DIV, $msg);
    }

    /**
     * splits at the first operator, so that a power is calculated from right to left;
     * a number written before a power keeps its sign, because it may be a value e.g. -2 ^ 2 is 4
     * @returns string interprets or converts a math operator condition
     */
    private function math_pow(string $formula, user_message $msg): string
    {
        return $this->calc($formula, chars::POW, $msg);
    }

    /**
     * @param float $base the number that is raised
     * @param float $exponent the power
     * @param string $formula the expression to name in the message
     * @param user_message $msg to report a power without a number e.g. the root of a negative number
     * @return string the number or NO_NUMBER
     */
    private function power(float $base, float $exponent, string $formula, user_message $msg): string
    {
        return $this->number_or_report(arithmetic::power_of($base, $exponent), chars::POW, $formula, $msg);
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_add(string $formula, user_message $msg): string
    {
        return $this->calc($formula, chars::ADD, $msg);
    }

    /**
     * @returns string interprets or converts a math operator condition
     */
    private function math_sub(string $formula, user_message $msg): string
    {
        return $this->calc($formula, chars::SUB, $msg);
    }

    /**
     * combine a binary operator directly followed by the sign of the next number into a single operator
     * so that e.g. "4 + -9" is calculated as "4 - 9" and "4 - -9" as "4 + 9"
     * this runs before the calculation is started, because a sum formula with a negative input
     * (e.g. GDP = consumption + investment + government spending + net exports) produces "+ -" pairs
     * @param string $formula the math expression that may contain a "+ -", "- -", "+ +" or "- +" sign pair
     * @returns string the expression with each sign pair reduced to a single "+" or "-" operator
     */
    private function combine_signs(string $formula): string
    {
        $result = preg_replace_callback(
            '/\s*([+\-])\s*([+\-])\s*(?=\d)/',
            function (array $match): string {
                $sign = ($match[1] === $match[2]) ? chars::ADD : chars::SUB;
                return ' ' . $sign . ' ';
            },
            $formula
        );
        return $result ?? $formula;
    }

    /**
     * checks if formula contains a bracket and calculates the inner part first
     */
    function math_bracket(string $formula, user_message $msg): string
    {
        $result = $formula;

        $lib = new library();

        // get the position of the next bracket
        $inner_start_pos = $this->pos_separator($result, chars::BRACKET_OPEN, 0);
        // if there is a bracket ...
        if ($inner_start_pos >= 0) {
            // ... and a closing bracket ...
            $inner_end_pos = $this->pos_separator($result, chars::BRACKET_CLOSE, $inner_start_pos + 1);

            // ... separate the formula

            // get the left part, but don't get the result of the left part because this can cause loops
            $left_part = substr($result, 0, $inner_start_pos);
            log_debug("left_part " . $left_part);

            $inner_part = substr($result, $inner_start_pos + 1, $inner_end_pos - $inner_start_pos - 1);
            log_debug("inner_part " . $inner_part);

            // get the right part, but don't get the result of the right part because will be done by the calling function
            $right_part = $lib->str_right_of($result, $left_part . chars::BRACKET_OPEN . $inner_part . chars::BRACKET_CLOSE);
            log_debug("right_part " . $right_part);

            // ... and something needs to be calculated e.g. also the min function of "(min(1;2)) * 3"
            // or a number needs its brackets removed e.g. a value in "(-3) ^ 2"
            $is_num = is_numeric(trim($inner_part));
            if ($this->has_operator($inner_part) or $this->has_function_pos($inner_part) or $is_num) {

                // calculate the inner part
                $inner_part = $this->parse($inner_part, $msg);
                log_debug("inner_part result " . $inner_part);

                // combine the result
                $result = $left_part . $inner_part . $right_part;
                if ($inner_part === null) {
                    $result = self::NO_NUMBER;
                }
            }
        }

        log_debug("done " . $result);
        return $result;
    }

    /**
     * replace the min functions outside a bracket by the smallest of their arguments,
     * e.g. "2 * min(3; 1 + 1)" by "2 * 2"; a min inside a bracket or an argument is replaced
     * when the bracket or the argument is parsed
     * @param string $formula the math expression with numbers that may contain the min function
     * @param user_message $msg to report an argument that cannot be calculated
     * @return string the expression with the min function replaced, unchanged if it has none or its closing bracket is missing, or NO_NUMBER if an argument has no number
     */
    function math_min(string $formula, user_message $msg): string
    {
        return $this->replace_function($formula, chars::FUNC_MIN, min(...), $msg);
    }

    /**
     * replace the max functions outside a bracket by the largest of their arguments,
     * in the same way as math_min
     * @param string $formula the math expression with numbers that may contain the max function
     * @param user_message $msg to report an argument that cannot be calculated
     * @return string the expression with the max function replaced, unchanged if it has none or its closing bracket is missing, or NO_NUMBER if an argument has no number
     */
    function math_max(string $formula, user_message $msg): string
    {
        return $this->replace_function($formula, chars::FUNC_MAX, max(...), $msg);
    }

    /**
     * replace the functions of one number outside a bracket e.g. "2 * sqrt(9)" by "2 * 3";
     * a function inside a bracket or an argument is replaced when that is parsed
     * @param string $formula the math expression with numbers
     * @param user_message $msg to report a function without a number e.g. the logarithm of zero
     * @return string the expression with the functions replaced or NO_NUMBER if one has no number
     */
    private function math_one_number(string $formula, user_message $msg): string
    {
        $result = $formula;
        foreach (chars::FUNC_ONE_NUMBER as $name) {
            $pick = fn(array $nums) => $this->one_number($name, $nums, $formula, $msg);
            $result = $this->replace_function($result, $name, $pick, $msg);
        }
        return $result;
    }

    /**
     * replace the list functions outside a bracket e.g. "sum(1;2) * 2" by "3 * 2"; the formula
     * engine has written all numbers of a list as the arguments (formula::fill_list_element_group)
     * @param string $formula the math expression with numbers
     * @param user_message $msg to report an argument that cannot be calculated
     * @return string the expression with the functions replaced or NO_NUMBER if one has no number
     */
    function math_list(string $formula, user_message $msg): string
    {
        $result = $formula;
        foreach (chars::FUNC_LIST as $name) {
            $pick = fn(array $nums) => arithmetic::list_number($name, $nums);
            $result = $this->replace_function($result, $name, $pick, $msg);
        }
        return $result;
    }

    /**
     * @param string $name the name of the function e.g. "sqrt"
     * @param array $nums the numbers of the arguments, which must be exactly one
     * @param string $formula the expression to name in the message
     * @param user_message $msg to report a function without a number
     * @return string the number or NO_NUMBER
     */
    private function one_number(string $name, array $nums, string $formula, user_message $msg): string
    {
        $num = NAN;
        if (count($nums) == 1) {
            $num = arithmetic::one_number($name, $nums[0]);
        }
        return $this->number_or_report($num, $name, $formula, $msg);
    }

    /**
     * @param float $num the calculated number, which can be infinite or not a number e.g. of a logarithm of zero
     * @param string $name the name of the function or the operator
     * @param string $formula the expression to name in the message
     * @param user_message $msg to report a calculation without a number
     * @return string the number or NO_NUMBER, which is reported, because it is never a wrong number
     */
    private function number_or_report(float $num, string $name, string $formula, user_message $msg): string
    {
        $result = (string)$num;
        if (!is_finite($num)) {
            $msg->add(msg_id::FORMULA_FUNCTION_NO_NUMBER, [
                msg_id::VAR_NAME => $name,
                msg_id::VAR_EXPRESSION => $formula
            ]);
            $result = self::NO_NUMBER;
        }
        return $result;
    }

    /**
     * @param string $formula the math expression with numbers that may contain the function
     * @param string $name the name of the function e.g. "min"
     * @param callable $pick returns the number of the function from the array of its argument numbers
     * @param user_message $msg to report an argument that cannot be calculated
     * @return string the expression with every function outside a bracket replaced, unchanged if it has none or its closing bracket is missing, or NO_NUMBER if one has no number
     */
    private function replace_function(string $formula, string $name, callable $pick, user_message $msg): string
    {
        $result = $formula;
        // every call, because the brackets of a later call would else be calculated like a normal bracket
        do {
            $before = $result;
            $result = $this->replace_first_function($before, $name, $pick, $msg);
        } while ($result != $before and $result != self::NO_NUMBER);
        return $result;
    }

    /**
     * @param string $formula the math expression with numbers that may contain the function
     * @param string $name the name of the function e.g. "min"
     * @param callable $pick returns the number of the function from the array of its argument numbers
     * @param user_message $msg to report an argument that cannot be calculated
     * @return string the expression with the first function outside a bracket replaced, unchanged if it has none or its closing bracket is missing, or NO_NUMBER if it has no number
     */
    private function replace_first_function(string $formula, string $name, callable $pick, user_message $msg): string
    {
        $result = $formula;
        $name_start = $this->pos_separator($result, $name, 0);
        $open = $this->function_bracket($result, $name, $name_start);
        if ($open >= 0) {
            $args_start = $open + 1;
            $args_end = $this->pos_separator($result, chars::BRACKET_CLOSE, $args_start);
            if ($args_end >= $args_start) {
                $args = substr($result, $args_start, $args_end - $args_start);
                $nums = $this->function_arguments($args, $msg);
                // an argument that is no number is never replaced by a wrong number
                $num_txt = self::NO_NUMBER;
                if (!in_array(null, $nums, true)) {
                    $num_txt = (string)$pick($nums);
                }
                $result = self::NO_NUMBER;
                if ($num_txt != self::NO_NUMBER) {
                    $result = substr($formula, 0, $name_start) . $num_txt . substr($formula, $args_end + 1);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $formula the math expression with numbers
     * @param string $name the name of the function e.g. "min"
     * @param int $name_start the position of the function name or -1 if the expression has none
     * @return int the position of the open bracket of the function or -1 if more than blanks follow the name e.g. "min (" of the formula editor is a call
     */
    private function function_bracket(string $formula, string $name, int $name_start): int
    {
        $result = -1;
        if ($name_start >= 0) {
            $name_end = $name_start + strlen($name);
            $open = $this->pos_separator($formula, chars::BRACKET_OPEN, $name_end);
            $between = substr($formula, $name_end, max($open - $name_end, 0));
            if ($open >= 0 and trim($between) == '') {
                $result = $open;
            }
        }
        return $result;
    }

    /**
     * @param string $args the arguments of a function e.g. "3; 1 + 1"
     * @param user_message $msg to report an argument that cannot be calculated
     * @return array the number of each argument; an argument in brackets or with a function is split only at its own level
     */
    private function function_arguments(string $args, user_message $msg): array
    {
        $result = [];
        foreach ($this->function_argument_texts($args) as $arg) {
            $result[] = $this->parse($arg, $msg);
        }
        return $result;
    }

    /**
     * @param string $args the arguments of a function e.g. "3; 1 + 1"
     * @return array the text of each argument, split only at its own level
     */
    private function function_argument_texts(string $args): array
    {
        $result = [];
        $pos = $this->pos_separator($args, chars::FUNC_ARG_SEPARATOR, 0);
        while ($pos >= 0) {
            $result[] = substr($args, 0, $pos);
            $args = substr($args, $pos + 1);
            $pos = $this->pos_separator($args, chars::FUNC_ARG_SEPARATOR, 0);
        }
        $result[] = $args;
        return $result;
    }

    /**
     * replace the first if function outside a bracket, either "if( condition ; then ; else )"
     * or "if( condition ) then", where the text behind the bracket is the then part
     * @param string $formula the math expression with numbers that may contain the if function
     * @param user_message $msg to report a part that cannot be calculated
     * @return string the expression with the function replaced, unchanged if it has none or is no valid call, or NO_NUMBER if it has no number
     */
    private function math_if(string $formula, user_message $msg): string
    {
        $result = $formula;
        // every call, because the brackets of a later call would else be calculated like a normal bracket
        do {
            $before = $result;
            $result = $this->math_first_if($before, $msg);
        } while ($result != $before and $result != self::NO_NUMBER);
        return $result;
    }

    /**
     * @param string $formula the math expression with numbers that may contain the if function
     * @param user_message $msg to report a part that cannot be calculated
     * @return string the expression with the first function replaced, unchanged if it has none or is no valid call, or NO_NUMBER if it has no number
     */
    private function math_first_if(string $formula, user_message $msg): string
    {
        $result = $formula;
        $if_start = $this->pos_separator($result, chars::FUNC_IF, 0);
        $open = $this->function_bracket($result, chars::FUNC_IF, $if_start);
        if ($open >= 0) {
            $close = $this->pos_separator($result, chars::BRACKET_CLOSE, $open + 1);
            if ($close > $open) {
                $args = $this->function_argument_texts(substr($result, $open + 1, $close - $open - 1));
                $before = substr($result, 0, $if_start);
                $after = substr($result, $close + 1);
                if (count($args) == 3) {
                    $num = $this->if_number($args[0], $args[1], $args[2], $msg);
                    $result = $before . $num . $after;
                } elseif (count($args) == 1) {
                    $num = $this->if_number($args[0], $after, null, $msg);
                    $result = $before . $num;
                } else {
                    $num = '';
                }
                if ($num == self::NO_NUMBER) {
                    $result = self::NO_NUMBER;
                }
            }
        }
        log_debug("done " . $result);
        return $result;
    }

    /**
     * only the chosen part is calculated, because the condition often guards the other part e.g. against a division by zero
     * @param string $condition the text of the condition e.g. "1 = 0" or "1 & 0"
     * @param string $then the text that is calculated if the condition is true
     * @param string|null $else the text that is calculated if the condition is false, null for no result without an error
     * @param user_message $msg to report a part that cannot be calculated
     * @return string the number or NO_NUMBER
     */
    private function if_number(string $condition, string $then, ?string $else, user_message $msg): string
    {
        $result = self::NO_NUMBER;
        $is_true = $this->condition_is_true($condition, $msg);
        $chosen = $is_true ? $then : $else;
        if ($is_true !== null and $chosen !== null) {
            $result = (string)($this->parse($chosen, $msg) ?? self::NO_NUMBER);
        }
        return $result;
    }

    /**
     * @param string $condition two conditions joined by chars::OR or chars::AND, two sums compared by "=" or a sum that is true if it is not zero
     * @param user_message $msg to report a part that cannot be calculated
     * @return bool|null the result or null if a part cannot be calculated, because a missing part is neither true nor false
     */
    private function condition_is_true(string $condition, user_message $msg): ?bool
    {
        $or = $this->pos_separator($condition, chars::OR, 0);
        $and = $this->pos_separator($condition, chars::AND, 0);
        $equal = $this->pos_separator($condition, chars::CHAR_CALC, 0);
        if ($or >= 0 or $and >= 0) {
            $pos = $or >= 0 ? $or : $and;
            $left = $this->condition_is_true(substr($condition, 0, $pos), $msg);
            $right = $this->condition_is_true(substr($condition, $pos + 1), $msg);
            if ($left === null or $right === null) {
                $result = null;
            } else {
                $result = $or >= 0 ? ($left or $right) : ($left and $right);
            }
        } elseif ($equal >= 0) {
            $left = $this->parse(substr($condition, 0, $equal), $msg);
            $right = $this->parse(substr($condition, $equal + 1), $msg);
            $result = ($left === null or $right === null) ? null : $left == $right;
        } else {
            $num = $this->parse($condition, $msg);
            $result = $num === null ? null : $num != 0;
        }
        return $result;
    }


    /**
     * @returns bool true if the next symbol is a math symbol (that can be ignored if a formula should be converted to the db format)
     */
    private function is_math_symbol(string $formula): bool
    {
        log_debug($formula);
        $lib = new library();
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
        log_debug($lib->dsp_bool($result));
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
        $lib = new library();
        if ($this->is_math_symbol($formula)) {
            log_debug("math");
            $result = True;
        } else {
            $result = $this->next_char_is_num($formula);
        }
        log_debug($lib->dsp_bool($result));
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