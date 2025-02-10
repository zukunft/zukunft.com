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

namespace unit_ui;

use cfg\value\value_list;
use html\html_base;
use cfg\phrase\phrase_list;
use html\phrase\phrase_list as phrase_list_dsp;
use html\value\value_list as value_list_dsp;
use shared\types\api_type;
use test\test_cleanup;

class value_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Value list tests');

        // create a test set of phrase
        $phr_inhabitant = $t->word_inhabitant()->phrase();

        // create a test set of phrase groups
        $phr_lst_context = new phrase_list($usr);
        $phr_lst_context->add($phr_inhabitant);
        $phr_lst_context_dsp = new phrase_list_dsp($phr_lst_context->api_json());

        // create the value for the inhabitants of the city of zurich
        $val_city = $t->value_zh();
        // create the value for the inhabitants of the canton of zurich
        $val_canton = $t->value_canton();
        // create the value for the inhabitants of Switzerland
        $val_ch = $t->value_ch();

        // create the value list and the table to display the results
        // TODO link phrases
        // TODO format numbers
        // TODO use one phrase for City of Zurich
        // TODO optional "(in mio)" formatting for scale words
        // TODO move time words to column headline
        // TODO use language based plural for inhabitant
        // TODO if the row phrases have parent child relations by default display sub rows e.g. countries and cantons
        // TODO if the col phrases have parent child relations by default display sub col e.g. year and quarter by using a phrase tree object?
        // TODO add buttons to or empty cells for easy adding new related values
        $val_lst = new value_list($usr);
        $val_lst->add($val_city);
        $val_lst->add($val_canton);
        $val_lst->add($val_ch);

        // TODO add a sample to show a list of words and some values related to the words e.g. all companies with the main ratios

        $test_page = $html->text_h2('Value list display test');
        $lst_dsp = new value_list_dsp($val_lst->api_json([api_type::INCL_PHRASES]));
        $test_page .= 'without context: ' . $lst_dsp->table() . '<br>';
        // create the same table as above, but within a context
        $header_html = $phr_lst_context_dsp->headline();
        $table_html = $lst_dsp->table($phr_lst_context_dsp);
        $test_page .= 'with context: ' . $header_html . $table_html . '<br>';
        $t->html_test($test_page, 'value_list', 'value_list', $t);

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