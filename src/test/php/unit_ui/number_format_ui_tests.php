<?php

/*

    test/php/unit_ui/number_format_ui_tests.php - the unit and the decimals of a group of numbers
    -------------------------------------------


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'number_format.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\helper\number_format;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class number_format_ui_tests
{
    // the target digits of the default tier (config.yaml number format > digits > default)
    const int DIGITS = 4;

    function run(test_cleanup $t): void
    {
        $t_frm = new test_formulas($t);
        $t_phr = new test_phrases($t);
        $msg_ui = new user_message_ui();

        // start the test section (ts)
        $ts = 'unit ui number format ';
        $t->header($ts);

        $phr_lst = $t_phr->phrase_list_mass_ui();
        $kg = $phr_lst->get_by_name(word_names::KG, $msg_ui);
        $tonne = $phr_lst->get_by_name(word_names::TONNE, $msg_ui);

        $t->subheader($ts . 'larger unit');

        // the largest number has more digits than the target, so the column is shown in tonne
        $test_name = 'a column of large kg numbers is shown in tonne';
        $fmt = new number_format($t_frm->formula_list_mass_ui(), $phr_lst);
        $fmt->group([10482.2, 2000, 113805.2, -2948.83], $kg, self::DIGITS, $msg_ui);
        $unit_name = $fmt->unit->name();
        $t->assert($test_name, $unit_name, word_names::TONNE);
        $test_name = '... with the decimals that the largest number leaves to the target digits';
        $t->assert($test_name, $fmt->decimals, 1);
        $test_name = '... so that a round number shows the decimals of the column';
        $t->assert($test_name, $fmt->text(2000), '2.0');
        $test_name = '... and a negative number keeps its sign';
        $t->assert($test_name, $fmt->text(-2948.83), '-2.9');

        // negative: a column whose largest number fits the target digits keeps the unit
        $test_name = 'a column of small kg numbers stays in kg';
        $fmt->group([349.41, 66.67, 3793.51], $kg, self::DIGITS, $msg_ui);
        $unit_name = $fmt->unit->name();
        $t->assert($test_name, $unit_name, word_names::KG);
        $test_name = '... without decimals, because the largest number has the target digits';
        $t->assert($test_name, $fmt->text(66.67), '67');

        $t->subheader($ts . 'smaller unit');

        // a number below one is shown in the smaller unit as long as it keeps the target digits
        $test_name = 'a column below one tonne is shown in kg';
        $fmt->group([0.123456], $tonne, self::DIGITS, $msg_ui);
        $unit_name = $fmt->unit->name();
        $t->assert($test_name, $unit_name, word_names::KG);
        $test_name = '... with the decimals of the target digits';
        $t->assert($test_name, $fmt->text(0.123456), '123.5');

        $t->subheader($ts . 'no conversion');

        // negative: without a formula the unit is kept and only the decimals are decided
        $test_name = 'a unit without a conversion formula is kept';
        $no_cnv = new number_format(new formula_list_ui(), $phr_lst);
        $no_cnv->group([248880, 8296], $kg, self::DIGITS, $msg_ui);
        $unit_name = $no_cnv->unit->name();
        $t->assert($test_name, $unit_name, word_names::KG);
        $test_name = '... and the number keeps all its integer digits';
        $t->assert($test_name, $no_cnv->text(248880), "248'880");

        // negative: a column whose header names the unit is never converted
        $test_name = 'a group without a unit shares only the decimals';
        $fmt->group([1119.96, 49.9], null, self::DIGITS, $msg_ui);
        $t->assert($test_name, $fmt->text(49.9), '50');

        $test_name = 'a group of zeros has no decimals';
        $fmt->group([0], $kg, self::DIGITS, $msg_ui);
        $t->assert($test_name, $fmt->text(0), '0');
    }

}
