<?php

/*

    test/php/unit/calc_internal.php - Test the internal math function
    ----------------------

    the internal math function should be replaced by REST R-Project calls


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CALC . 'arithmetic.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\service\math\calc_internal;
use Zukunft\ZukunftCom\main\php\shared\calc\arithmetic;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

global $db_con;

class math_tests
{

    /**
     * the shared calculation of a text with numbers, which the frontend uses to repeat the
     * calculation of a result with the values of a page (formula_calc)
     */
    function run_arithmetic(test_cleanup $t): void
    {
        $ts = 'unit arithmetic ';
        $t->header($ts);
        $calc = new arithmetic();
        $cases = [
            ['2 + 3 * 4', 14.0],
            ['( 2 + 3 ) * 4', 20.0],
            ['-2 * 3', -6.0],
            ['2 - -3', 5.0],
            ['7 / 2', 3.5],
            ['1575.000000000000 * 30', 47250.0],
            ['( 1575 + 2223.77 ) / 4500', 0.84417111111111],
            // the direct use of a month: the smaller of the PV production and the day load
            ['min( 207.4 ; 450 * 0.35 )', 157.5],
            ['2 * min( 3 ; 1 + 1 )', 4.0],
            ['min( 5 ; -2 ; 3 )', -2.0],
            ['min( min( 4 ; 9 ) ; 6 ) - 1', 3.0],
            ['( min( 1 ; 2 ) ) * 3', 3.0],
            ['min( 7 )', 7.0],
            ['max( 207.4 ; 450 * 0.35 )', 207.4],
            ['2 * max( 3 ; 1 + 1 )', 6.0],
            ['max( -5 ; -2 ; -3 )', -2.0],
            ['max( min( 4 ; 9 ) ; 6 ) - 1', 5.0],
            ['( max( 1 ; 2 ) ) * 3', 6.0],
            ['max( 7 )', 7.0],
        ];
        foreach ($cases as [$txt, $expected]) {
            $t->assert($ts . '"' . $txt . '"', round($calc->calc($txt) ?? 0, 10), round($expected, 10));
        }
        // negative: a text that is no calculation gives no number instead of a wrong one
        $invalid = ['', '2 +', '2 x 3', '( 2 + 3', '1 / 0', '2 3',
            'min( 1 ; 2', 'min 1', 'min( ; 1 )', 'max( 1 ; 2', 'max 1', 'max( ; 1 )', 'round( 1 ; 2 )', '1 ; 2'];
        foreach ($invalid as $txt) {
            $t->assert_null($ts . '"' . $txt . '" is no calculation', $calc->calc($txt));
        }
    }
    function run(test_cleanup $t): void
    {
        $this->run_arithmetic($t);

        // start the test section (ts)
        $ts = 'unit math ';
        $t->header($ts);

        // init
        $calc = new calc_internal();
        $msg = new user_message();

        // test bracket finding
        $math_text = "(2 - 1) * 2";
        $result = $calc->has_bracket($math_text);
        // TODO Prio 2 speed up
        $t->assert($ts . 'has bracket in "' . $math_text . '"', $result, true, $t::TIMEOUT_LIMIT_CALC);

        // test bracket execute
        $math_text = "(3 - 1) * 2";
        $result = $calc->math_bracket($math_text, $msg);
        $t->assert($ts . 'execute bracket in "' . $math_text . '"', $result, "2 * 2");

        // test simple calc
        $t->assert($ts . 'calc plus "2 + 2"', $calc->parse("2 + 2", $msg), 4);
        $t->assert($ts . 'calc minus "3 - 1"', $calc->parse("3 - 1", $msg), 2);
        $t->assert($ts . 'calc mul "3 * 2"', $calc->parse("3 * 2", $msg), 6);
        $t->assert($ts . 'calc div "4 / 2"', $calc->parse("4 / 2", $msg), 2);

        // test add/minus bracket rules
        $math_text = "(-10744--10744)/-10744";
        $target = 0;
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'test add bracket rule with "' . $math_text . '"', $result, $target);

        // test multiply bracket rules
        $math_text = "(2 - 1) * 2";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'test multiply bracket rule with "' . $math_text . '"', $result, 2);

        // test the sign combiner that reduces "+ -" to "-" and "- -" to "+" before the calculation
        $t->assert($ts . 'calc plus a negative "2 + -1"', $calc->parse("2 + -1", $msg), 1);
        $t->assert($ts . 'calc minus a negative "2 - -1"', $calc->parse("2 - -1", $msg), 3);

        // test a sum with a negative term as produced by a formula like
        // "GDP" = "consumption" + "investment" + "government spending" + "net exports"
        $math_text = "18799600000000 + 4945100000000 + 4562600000000 + -945300000000";
        $target = 27362000000000;
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'test plus minus sign rule with "' . $math_text . '"', $result, $target);

        // test left-associative chained subtraction: "10 - 3 - 2" = (10 - 3) - 2 = 5, not 10 - (3 - 2) = 9
        $t->assert($ts . 'calc chained minus "10 - 3 - 2"', $calc->parse("10 - 3 - 2", $msg), 5);

        // test left-associative chained division: "100 / 5 / 2" = (100 / 5) / 2 = 10, not 100 / (5 / 2) = 40
        $t->assert($ts . 'calc chained div "100 / 5 / 2"', $calc->parse("100 / 5 / 2", $msg), 10);

        // test a sum with two negative terms (e.g. several cost/benefit adders) that becomes a
        // chained subtraction after the sign combiner: 0.1625 + 0.0208 - 0.003 - 0.02 + 0.005 + 0.015 = 0.1803
        $math_text = "0.1625 + 0.0208 + -0.003 + -0.02 + 0.005 + 0.015";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'calc sum with two negative terms "' . $math_text . '"', round((float)$result, 4), 0.1803);

        // test a signed number after a leading blank as produced by the calc validation
        // substitution (e.g. " -8 / 52" from "percent" = "treatment effect" / "control group"):
        // the blank before the sign is not a left operand, so the sign stays with the number
        // and no "cannot parse" error is logged
        $math_text = " -8 / 52";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'calc leading minus "' . $math_text . '"', round((float)$result, 4), -0.1538);

        // php writes a small intermediate result in scientific notation, so the sign of the
        // exponent is part of the number and never split off as an operator
        $math_text = "6.5502183406112E-5 * 2";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'calc exponent sign "' . $math_text . '"', round((float)$result, 8), 0.00013100);
        // negative: a sign after a number in scientific notation is still an operator if a
        // blank separates it from the number
        $math_text = "5E3 - 1";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'calc minus after exponent "' . $math_text . '"', $result, 4999);

        // the operator precedence without brackets: a multiplication binds stronger than an
        // addition on either side, e.g. the variance of a sum "0.09 + 0.16 + 2 * 0.024"
        $t->assert($ts . 'calc mul before add "2 * 3 + 4"', $calc->parse("2 * 3 + 4", $msg), 10);
        $t->assert($ts . 'calc add before mul "1 + 2 * 3"', $calc->parse("1 + 2 * 3", $msg), 7);
        $t->assert($ts . 'calc sub with mul "10 - 2 * 3"', $calc->parse("10 - 2 * 3", $msg), 4);
        $t->assert($ts . 'calc mixed "1 + 2 * 3 - 4 / 2"', $calc->parse("1 + 2 * 3 - 4 / 2", $msg), 5);
        $math_text = "0.09 + 0.16 + 2 * 0.024";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'calc variance of a sum "' . $math_text . '"', round((float)$result, 4), 0.298);
        // a sign directly after an operator belongs to the number, so it is no subtraction
        $t->assert($ts . 'calc div by a negative "6 / -2"', $calc->parse("6 / -2", $msg), -3);
        $t->assert($ts . 'calc mul with a negative "2 * -3 + 1"', $calc->parse("2 * -3 + 1", $msg), -5);
        $math_text = " +8 / 2";
        $result = $calc->parse($math_text, $msg);
        $t->assert($ts . 'calc leading plus "' . $math_text . '"', $result, 4);

        // the min function e.g. for the battery charge of a month: the smallest of the surplus,
        // one battery cycle per day and the night load divided by the round-trip efficiency
        $math_text = "min(1003.24-270;10*31;(450-270)/0.9)";
        $t->assert($ts . 'calc min "' . $math_text . '"', $calc->parse($math_text, $msg), 200);
        $math_text = "2 * min(3; 1 + 1)";
        $t->assert($ts . 'calc min in a product "' . $math_text . '"', $calc->parse($math_text, $msg), 4);
        $math_text = "5 - min(5;-2;3)";
        $t->assert($ts . 'calc minus a negative min "' . $math_text . '"', $calc->parse($math_text, $msg), 7);
        $math_text = "min(min(4;9);6) - 1";
        $t->assert($ts . 'calc nested min "' . $math_text . '"', $calc->parse($math_text, $msg), 3);
        $math_text = "(min(1;2))*3";
        $t->assert($ts . 'calc min in a bracket "' . $math_text . '"', $calc->parse($math_text, $msg), 3);
        $math_text = "min(1;2) + min(3;4)";
        $t->assert($ts . 'calc two min "' . $math_text . '"', $calc->parse($math_text, $msg), 4);
        // the formula editor writes a blank between the tokens
        $math_text = "min ( 207.4 ; 157.5 )";
        $t->assert($ts . 'calc min with blanks "' . $math_text . '"', $calc->parse($math_text, $msg), 157.5);
        // a min of one argument is the argument, not a split at the separator
        $math_text = "min(4 + 1)";
        $t->assert($ts . 'calc min of one argument "' . $math_text . '"', $calc->parse($math_text, $msg), 5);
        // negative: a min without its brackets stays unchanged instead of becoming a wrong number
        $math_text = "min(1;2";
        $t->assert($ts . 'no min without closing bracket "' . $math_text . '"', $calc->math_min($math_text, $msg), $math_text);
        $math_text = "min 1";
        $t->assert($ts . 'no min without open bracket "' . $math_text . '"', $calc->math_min($math_text, $msg), $math_text);
        // negative: an argument that is no number logs an expected error and informs the user
        $math_text = "min(1;abc)";
        $num_txt = $this->without_error_count(fn() => $calc->math_min($math_text, $msg));
        $t->assert($ts . 'no min of a text "' . $math_text . '"', $num_txt, calc_internal::NO_NUMBER);
        $t->assert_false($ts . 'the user is told about the text in "' . $math_text . '"', $msg->is_ok());
        $msg->reset();
        $math_text = "min(;1)";
        $num_txt = $this->without_error_count(fn() => $calc->math_min($math_text, $msg));
        $t->assert($ts . 'no min with an empty argument "' . $math_text . '"', $num_txt, calc_internal::NO_NUMBER);
        $msg->reset();

        // the max function: the largest of its arguments
        $math_text = "max(1003.24-270;10*31;(450-270)/0.9)";
        $t->assert($ts . 'calc max "' . $math_text . '"', round((float)$calc->parse($math_text, $msg), 2), 733.24);
        $math_text = "2 * max(3; 1 + 1)";
        $t->assert($ts . 'calc max in a product "' . $math_text . '"', $calc->parse($math_text, $msg), 6);
        $math_text = "5 - max(-5;-2;-3)";
        $t->assert($ts . 'calc minus a negative max "' . $math_text . '"', $calc->parse($math_text, $msg), 7);
        $math_text = "max(max(4;9);6) - 1";
        $t->assert($ts . 'calc nested max "' . $math_text . '"', $calc->parse($math_text, $msg), 8);
        $math_text = "(max(1;2))*3";
        $t->assert($ts . 'calc max in a bracket "' . $math_text . '"', $calc->parse($math_text, $msg), 6);
        $math_text = "max(1;2) + max(3;4)";
        $t->assert($ts . 'calc two max "' . $math_text . '"', $calc->parse($math_text, $msg), 6);
        $math_text = "max ( 207.4 ; 157.5 )";
        $t->assert($ts . 'calc max with blanks "' . $math_text . '"', $calc->parse($math_text, $msg), 207.4);
        $math_text = "max(4 + 1)";
        $t->assert($ts . 'calc max of one argument "' . $math_text . '"', $calc->parse($math_text, $msg), 5);
        // a limit from below and above, each function inside the other
        $math_text = "max(0; min(7; 3 + 1)) + min(9; max(1; 2))";
        $t->assert($ts . 'calc max and min "' . $math_text . '"', $calc->parse($math_text, $msg), 6);
        // negative: a max without its brackets stays unchanged instead of becoming a wrong number
        $math_text = "max(1;2";
        $t->assert($ts . 'no max without closing bracket "' . $math_text . '"', $calc->math_max($math_text, $msg), $math_text);
        $math_text = "max 1";
        $t->assert($ts . 'no max without open bracket "' . $math_text . '"', $calc->math_max($math_text, $msg), $math_text);
        // negative: an argument that is no number logs an expected error and informs the user
        $math_text = "max(1;abc)";
        $num_txt = $this->without_error_count(fn() => $calc->math_max($math_text, $msg));
        $t->assert($ts . 'no max of a text "' . $math_text . '"', $num_txt, calc_internal::NO_NUMBER);
        $t->assert_false($ts . 'the user is told about the text in "' . $math_text . '"', $msg->is_ok());
        $msg->reset();
        $math_text = "max(;1)";
        $num_txt = $this->without_error_count(fn() => $calc->math_max($math_text, $msg));
        $t->assert($ts . 'no max with an empty argument "' . $math_text . '"', $num_txt, calc_internal::NO_NUMBER);
        $msg->reset();

        // a function that the calculation does not have is named to report it to the user
        $math_text = "round(2.6) * 3";
        $t->assert($ts . 'missing function "' . $math_text . '"', $calc->missing_function($math_text), 'round');
        $math_text = "10 - min(1;sqrt(4))";
        $t->assert($ts . 'missing function in an argument "' . $math_text . '"', $calc->missing_function($math_text), 'sqrt');
        $math_text = "sum(1;2)";
        $t->assert($ts . 'sum is not calculated yet "' . $math_text . '"', $calc->missing_function($math_text), chars::FUNC_SUM);
        $math_text = 'min( sqrt( 4 ) ; "log(2)" ) * ( 1 + 2 )';
        $t->assert($ts . 'function names without a text and a bracket "' . $math_text . '"', $calc->function_names($math_text), ['min', 'sqrt']);
        $t->assert($ts . 'without the text "' . $math_text . '"', $calc->without_text($math_text), 'min( sqrt( 4 ) ;  ) * ( 1 + 2 )');
        // negative: a function that the calculation has, a bracket and a text are no missing function
        $math_text = "if(1) + min(1;2) * max ( 3;4 )";
        $t->assert($ts . 'no missing function "' . $math_text . '"', $calc->missing_function($math_text), '');
        $math_text = "2 * (3 + 1) + 5E3 * (2)";
        $t->assert($ts . 'no missing function for a bracket "' . $math_text . '"', $calc->missing_function($math_text), '');
        $math_text = '"sqrt(4)" + 1';
        $t->assert($ts . 'no missing function in a text "' . $math_text . '"', $calc->missing_function($math_text), '');
        // negative: never a wrong number, and the user is told which function is missing
        $math_text = "10 - sqrt(4)";
        $t->assert_null($ts . 'no number for a missing function "' . $math_text . '"', $calc->parse($math_text, $msg));
        $t->assert_text_contains($ts . 'the user is told about the function in "' . $math_text . '"', $msg->text(), 'sqrt');
        $msg->reset();
        $math_text = "round(2.6) * 3";
        $t->assert_null($ts . 'no number for a missing function "' . $math_text . '"', $calc->parse($math_text, $msg));
        $t->assert_text_contains($ts . 'the user is told about the function in "' . $math_text . '"', $msg->text(), 'round');
        $msg->reset();
        // negative: a part that cannot be calculated is never counted as zero, each of these logs an expected error
        $math_text = "abc + 1";
        $num = $this->without_error_count(fn() => $calc->parse($math_text, $msg));
        $t->assert_null($ts . 'no number for a text "' . $math_text . '"', $num);
        $t->assert_false($ts . 'the user is told about the text in "' . $math_text . '"', $msg->is_ok());
        $msg->reset();
        $math_text = "(abc + 1) + 3";
        $num = $this->without_error_count(fn() => $calc->parse($math_text, $msg));
        $t->assert_null($ts . 'no number for a text in a bracket "' . $math_text . '"', $num);
        $msg->reset();
        $math_text = "min(1;abc) + 1";
        $num = $this->without_error_count(fn() => $calc->parse($math_text, $msg));
        $t->assert_null($ts . 'no number for a text in an argument "' . $math_text . '"', $num);
        $msg->reset();
        // negative: a division by zero is no number and the user is told which part divides by zero
        $math_text = "1 / 0";
        $t->assert_null($ts . 'no number for a division by zero "' . $math_text . '"', $calc->parse($math_text, $msg));
        $t->assert_text_contains($ts . 'the user is told about "' . $math_text . '"', $msg->text(), $math_text);
        $msg->reset();
        $math_text = "(1 / 0) + 1";
        $t->assert_null($ts . 'no number for a division by zero in a bracket "' . $math_text . '"', $calc->parse($math_text, $msg));
        $msg->reset();
        $math_text = "5 / (2 - 2)";
        $t->assert_null($ts . 'no number for a calculated zero "' . $math_text . '"', $calc->parse($math_text, $msg));
        $msg->reset();
        // a zero above the line is a normal number
        $math_text = "0 / 5";
        $t->assert($ts . 'calc zero divided "' . $math_text . '"', $calc->parse($math_text, $msg), 0);
        $t->assert_true($ts . 'no message for "' . $math_text . '"', $msg->is_ok());
        // the if function with two conditions gives the number behind the bracket if the condition is true
        $math_text = "if(1&1) 5";
        $t->assert($ts . 'calc if and "' . $math_text . '"', $calc->parse($math_text, $msg), 5);
        $math_text = "if(1|0) 5";
        $t->assert($ts . 'calc if or "' . $math_text . '"', $calc->parse($math_text, $msg), 5);
        // negative: a false condition gives no result, which is normal and no error
        $math_text = "if(1&0) 5";
        $t->assert_null($ts . 'no result for a false and "' . $math_text . '"', $calc->parse($math_text, $msg));
        $math_text = "if(0|0) 5";
        $t->assert_null($ts . 'no result for a false or "' . $math_text . '"', $calc->parse($math_text, $msg));
        $t->assert_true($ts . 'no message for a false condition "' . $math_text . '"', $msg->is_ok());
        // negative: a text in the condition or behind it gives no number, each of these logs an expected error
        $math_text = "if(abc&1) 5";
        $num = $this->without_error_count(fn() => $calc->parse($math_text, $msg));
        $t->assert_null($ts . 'no number for a text in the condition "' . $math_text . '"', $num);
        $t->assert_false($ts . 'the user is told about the text in "' . $math_text . '"', $msg->is_ok());
        $msg->reset();
        $math_text = "if(1&1) abc";
        $num = $this->without_error_count(fn() => $calc->parse($math_text, $msg));
        $t->assert_null($ts . 'no number for a text behind the condition "' . $math_text . '"', $num);
        $msg->reset();
        // the if function with one condition and with the then and else part
        $math_text = "if(1) 5";
        $t->assert($ts . 'calc if with one condition "' . $math_text . '"', $calc->parse($math_text, $msg), 5);
        $math_text = "2 + if(1&1) 5";
        $t->assert($ts . 'calc keeps the part before if "' . $math_text . '"', $calc->parse($math_text, $msg), 7);
        $math_text = "if(0 = 0 ; 10 - 2 ; 4)";
        $t->assert($ts . 'calc if then "' . $math_text . '"', $calc->parse($math_text, $msg), 8);
        $math_text = "if(1 = 0 ; 10 - 2 ; 4) * 2 + 1";
        $t->assert($ts . 'calc if else in a sum "' . $math_text . '"', $calc->parse($math_text, $msg), 9);
        $math_text = "(if(1;2;3)) * 2";
        $t->assert($ts . 'calc if in a bracket "' . $math_text . '"', $calc->parse($math_text, $msg), 4);
        $math_text = "if(0 = 0 ; 3 ; 5 / 0)";
        $t->assert($ts . 'the part that is not chosen is not calculated "' . $math_text . '"', $calc->parse($math_text, $msg), 3);
        $t->assert_true($ts . 'no message for "' . $math_text . '"', $msg->is_ok());
        // negative: a false condition without an else part gives no result, which is no error
        $math_text = "if(0) 5";
        $t->assert_null($ts . 'no result for a false condition "' . $math_text . '"', $calc->parse($math_text, $msg));
        $t->assert_true($ts . 'no message for a false condition "' . $math_text . '"', $msg->is_ok());
        // negative: a text is neither true nor false, also not for or, this logs an expected error
        $math_text = "if(abc|1) 5";
        $num = $this->without_error_count(fn() => $calc->parse($math_text, $msg));
        $t->assert_null($ts . 'no number for a text in an or "' . $math_text . '"', $num);
        $t->assert_false($ts . 'the user is told about the text in "' . $math_text . '"', $msg->is_ok());
        $msg->reset();
        // negative: a calculation without a missing function reports nothing to the user
        $math_text = "min(1;2) + 1";
        $t->assert($ts . 'a number for "' . $math_text . '"', $calc->parse($math_text, $msg), 2);
        $t->assert_true($ts . 'no message for "' . $math_text . '"', $msg->is_ok());

    }

    /**
     * a negative test that logs an error on purpose, which would else hide a real error in the
     * number of internal errors of the test run; any other error of the test run is still counted
     *
     * @param callable $call the calculation that is expected to log an error
     * @return mixed the result of the calculation
     */
    private function without_error_count(callable $call): mixed
    {
        global $sys;
        $errors = $sys?->errors;
        $result = $call();
        if ($sys !== null) {
            $sys->errors = $errors;
        }
        return $result;
    }

}