<?php

/*

    test/unit/html/value_list.php - testing of the value list html frontend functions
    -----------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class value_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;

        // init
        $html = new html_base();
        $tl = new test_lib();
        $t_wrd = new test_words($t);
        $t_val = new test_values($t);
        $ui = new frontend('unit ui html reference list');
        $dto = $tl->ui_test_cache($t->usr1, $t);
        $ui->set_cache($dto);

        // start the test section (ts)
        $ts = 'unit ui html value list ';
        $t->header($ts);

        // create a test set of phrase
        $phr_inhabitant = $t_wrd->word_inhabitant()->phrase();

        // create a test set of phrase groups
        $phr_lst_context = new phrase_list($usr);
        $phr_lst_context->add($phr_inhabitant);
        $phr_lst_context_ui = new phrase_list_ui($phr_lst_context->api_json());

        // create the value list and the table to display the results
        // TODO move the measure phrase behind the number e.g. speed of light 299'792'458 m/s instead of speed of light m/s 299'792'458
        // TODO format numbers
        // TODO use one phrase for City of Zurich
        // TODO optional "(in mio)" formatting for scale words
        // TODO move time words to column headline
        // TODO use language based plural for inhabitant
        // TODO if the row phrases have parent child relations by default display sub rows e.g. countries and cantons
        // TODO if the col phrases have parent child relations by default display sub col e.g. year and quarter by using a phrase tree object?
        // TODO add buttons to or empty cells for easy adding new related values
        $lst_zh_ui = $t_val->value_list_zh_ui();
        $lst_math_ui = $t_val->value_list_math_ui();

        // TODO add a sample to show a list of words and some values related to the words e.g. all companies with the main ratios

        $test_page = $html->text_h2('Value list display test');
        $test_page .= 'as list: ' . $html->lf() .  $lst_math_ui->list($phr_lst_context_ui) . '<br>';
        $test_page .= 'as long list: ' . $html->lf() .  $t_val->list_all_ui()->list($phr_lst_context_ui) . '<br>';
        $test_page .= 'as long list with small page: ' . $html->lf() .  $t_val->list_all_ui()->list($phr_lst_context_ui, '', '', 4) . '<br><br>';
        $test_page .= 'with units: ' . $html->lf() .  $t_val->list_all_ui()->list_unit(7) . '<br><br>';
        $test_page .= 'as table without context: ' . $lst_zh_ui->table() . '<br>';
        // create the same table as above, but within a context
        $header_html = $phr_lst_context_ui->headline();
        $table_html = $lst_zh_ui->table($phr_lst_context_ui);
        $test_page .= 'as table with context: ' . $header_html . $table_html . '<br>';
        $t->html_page_test($test_page, 'value_list', 'value_list', $t);

        $t->subheader($ts . 'user config');

        $cfg = new config($t_val->value_list_all()->api_json([api_types::INCL_PHRASES]));
        $test_name = 'a loaded config value is returned by the phrase names';
        // get_by returns the display value, so the number is rounded for the user
        $t->assert($test_name, $cfg->get_by([words::PI_SYMBOL]), round(values::PI_LONG, 2));
        $test_name = 'a missing config value returns the given default';
        $t->assert($test_name, $cfg->get_by([words::POD], 7), 7);

        $t->subheader($ts . 'sort by impact');
        $impact_lst = $t_val->value_list_zh_impact_ui();
        $impact_lst->sort_by_impact();
        $test_name = 'the value of the phrase with the highest impact is first';
        $t->assert_text_order($test_name, $impact_lst->list(), triples::COMPANY_ZURICH, triples::CITY_ZH_NAME);
        $test_name = 'sort by impact of an empty value list renders nothing';
        $t->assert($test_name, new value_list_ui()->list(), '');

        // TODO add a test that if a view contains beside the "2023 (year)"
        //      no other phrase that contains the word "2023"
        //      the "(year)" is not shown to the user, because the user will assume i

        // TODO add s test that if a view contains the word "city"
        //      or many cities and never a "Canton"
        //      and the phrase "Zurich (city)" is shown
        //      only "Zurich" without "(city)" is used
        //      because the user will assume "City of Zurich"
        //      on mouseover show the complete phrase name with the description


    }

}