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

namespace unit\html;

use api\phrase\group as group_api;
use api\phrase\phrase_list as phrase_list_api;
use api\value\value as value_api;
use api\value\value_list as value_list_api;
use html\html_base;
use cfg\phrase_list;
use html\phrase\phrase_list as phrase_list_dsp;
use html\value\value_list as value_list_dsp;
use test\test_cleanup;

class value_list
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Value list tests');

        // create a test set of phrase
        $phr_zh = $t->word_zh()->phrase();
        $phr_city = $t->word_city()->phrase();
        $phr_canton = $t->word_canton()->phrase();
        $phr_ch = $t->word_city()->phrase();
        $phr_inhabitant = $t->word_inhabitant()->phrase();
        $phr_2019 = $t->word_2019()->phrase();
        $phr_mio = $t->word_mio()->phrase();

        // create a test set of phrase groups
        $grp_id = 1;
        $phr_lst_city = new phrase_list($usr);
        $phr_lst_city->add($phr_zh);
        $phr_lst_city->add($phr_city);
        $phr_lst_city->add($phr_inhabitant);
        $phr_lst_city->add($phr_2019);
        $phr_grp_city = $phr_lst_city->get_grp_id(false);
        $phr_grp_city->set_id($grp_id);
        $grp_id++;
        $phr_lst_canton = new phrase_list($usr);
        $phr_lst_canton->add($phr_zh);
        $phr_lst_canton->add($phr_canton);
        $phr_lst_canton->add($phr_inhabitant);
        $phr_lst_canton->add($phr_mio);
        $phr_lst_canton->add($phr_2019);
        $phr_grp_canton = $phr_lst_canton->get_grp_id(false);
        $phr_grp_canton->set_id($grp_id);
        $grp_id++;
        $phr_lst_ch = new phrase_list($usr);
        $phr_lst_ch->add($phr_ch);
        $phr_lst_ch->add($phr_mio);
        $phr_lst_ch->add($phr_inhabitant);
        $phr_lst_ch->add($phr_2019);
        $phr_grp_ch = $phr_lst_ch->get_grp_id(false);
        $phr_grp_ch->set_id($grp_id);
        $phr_lst_context = new phrase_list($usr);
        $phr_lst_context->add($phr_inhabitant);
        $phr_lst_context_dsp = new phrase_list_dsp($phr_lst_context->api_json());

        // create the value for the inhabitants of the city of zurich
        $val_id = 1;
        $val_city = new value_api($val_id); $val_id++;
        $val_city->set_grp($phr_grp_city->api_obj());
        $val_city->set_number(value_api::TV_CITY_ZH_INHABITANTS_2019);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value_api($val_id); $val_id++;
        $val_canton->set_grp($phr_grp_canton->api_obj());
        $val_canton->set_number(value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value_api($val_id);
        $val_ch->set_grp($phr_grp_ch->api_obj());
        $val_ch->set_number(value_api::TV_CH_INHABITANTS_2019_IN_MIO);

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
        $val_lst = new value_list_api();
        $val_lst->add($val_city);
        $val_lst->add($val_canton);
        $val_lst->add($val_ch);

        // TODO add a sample to show a list of words and some values related to the words e.g. all companies with the main ratios

        $test_page = $html->text_h2('Value list display test');
        $lst_dsp = new value_list_dsp($val_lst->get_json());
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