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

use Zukunft\ZukunftCom\main\php\service\math\calc_internal;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

global $db_con;

class math_tests
{
    function run(test_cleanup $t): void
    {
        // start the test section (ts)
        $ts = 'unit math ';
        $t->header($ts);

        // init
        $calc = new calc_internal();

        // test bracket finding
        $math_text = "(2 - 1) * 2";
        $result = $calc->has_bracket($math_text);
        // TODO speed up
        $t->assert($ts . 'has bracket in "' . $math_text . '"', $result, true, $t::TIMEOUT_LIMIT_CALC);

        // test bracket execute
        $math_text = "(3 - 1) * 2";
        $result = $calc->math_bracket($math_text);
        $t->assert($ts . 'execute bracket in "' . $math_text . '"', $result, "2 * 2");

        // test simple calc
        $t->assert($ts . 'calc plus "2 + 2"', $calc->parse("2 + 2"), 4);
        $t->assert($ts . 'calc minus "3 - 1"', $calc->parse("3 - 1"), 2);
        $t->assert($ts . 'calc mul "3 * 2"', $calc->parse("3 * 2"), 6);
        $t->assert($ts . 'calc div "4 / 2"', $calc->parse("4 / 2"), 2);

        // test add/minus bracket rules
        $math_text = "(-10744--10744)/-10744";
        $target = 0;
        $result = $calc->parse($math_text);
        $t->assert($ts . 'test add bracket rule with "' . $math_text . '"', $result, $target);

        // test multiply bracket rules
        $math_text = "(2 - 1) * 2";
        $result = $calc->parse($math_text);
        $t->assert($ts . 'test multiply bracket rule with "' . $math_text . '"', $result, 2);

        // test the sign combiner that reduces "+ -" to "-" and "- -" to "+" before the calculation
        $t->assert($ts . 'calc plus a negative "2 + -1"', $calc->parse("2 + -1"), 1);
        $t->assert($ts . 'calc minus a negative "2 - -1"', $calc->parse("2 - -1"), 3);

        // test a sum with a negative term as produced by a formula like
        // "GDP" = "consumption" + "investment" + "government spending" + "net exports"
        $math_text = "18799600000000 + 4945100000000 + 4562600000000 + -945300000000";
        $target = 27362000000000;
        $result = $calc->parse($math_text);
        $t->assert($ts . 'test plus minus sign rule with "' . $math_text . '"', $result, $target);

    }

}