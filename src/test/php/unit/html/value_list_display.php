<?php

/*

    test/unit/value_list_display.php - TESTing of the VALUE LIST DISPLAY functions
    --------------------------------
  

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

namespace test\html;

use api\phrase_api;
use api\phrase_group_api;
use api\phrase_list_api;
use api\word_api;
use html\html_base;
use test\testing;
use test\value_api;

class value_list_display_unit_tests
{
    function run(testing $t): void
    {
        $html = new html_base();

        $t->subheader('Value list tests');

        // create a test set of phrase
        $phr_id = 1;
        $phr_zh = new phrase_api($phr_id, word_api::TN_ZH); $phr_id++;
        $phr_city = new phrase_api($phr_id, word_api::TN_CITY); $phr_id++;
        $phr_canton = new phrase_api($phr_id, word_api::TN_CANTON); $phr_id++;
        $phr_ch = new phrase_api($phr_id, word_api::TN_CH); $phr_id++;
        $phr_inhabitant = new phrase_api($phr_id, word_api::TN_INHABITANT); $phr_id++;
        $phr_2019 = new phrase_api($phr_id, word_api::TN_2019); $phr_id++;
        $phr_mio = new phrase_api($phr_id, word_api::TN_MIO_SHORT);

        // create a test set of phrase groups
        $grp_id = 1;
        $phr_grp_city = new phrase_group_api($grp_id); $grp_id++;
        $phr_grp_city->add($phr_zh);
        $phr_grp_city->add($phr_city);
        $phr_grp_city->add($phr_inhabitant);
        $phr_grp_city->add($phr_2019);
        $phr_grp_canton = new phrase_group_api($grp_id); $grp_id++;
        $phr_grp_canton->add($phr_zh);
        $phr_grp_canton->add($phr_canton);
        $phr_grp_canton->add($phr_inhabitant);
        $phr_grp_canton->add($phr_mio);
        $phr_grp_canton->add($phr_2019);
        $phr_grp_ch = new phrase_group_api($grp_id);
        $phr_grp_ch->add($phr_ch);
        $phr_grp_ch->add($phr_mio);
        $phr_grp_ch->add($phr_inhabitant);
        $phr_grp_ch->add($phr_2019);
        $phr_lst_context = new phrase_list_api();
        $phr_lst_context->add($phr_inhabitant);

        // create the value for the inhabitants of the city of zurich
        $val_id = 1;
        $val_city = new \api\value_api($val_id); $val_id++;
        $val_city->set_grp($phr_grp_city);
        $val_city->set_number(value_api::TV_CITY_ZH_INHABITANTS_2019);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new \api\value_api($val_id); $val_id++;
        $val_canton->set_grp($phr_grp_canton);
        $val_canton->set_number(value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // create the value for the inhabitants of Switzerland
        $val_ch = new \api\value_api($val_id);
        $val_ch->set_grp($phr_grp_ch);
        $val_ch->set_number(value_api::TV_CH_INHABITANTS_2019_IN_MIO);

        // create the value list and the table to display the results
        // TODO link phrases
        // TODO format numbers
        // TODO use one phrase for City of Zurich
        // TODO optional "(in mio)" formatting for scale words
        // TODO move time words to column headline
        // TODO use language based plural for inhabitant
        $val_lst = new \api\value_list_api();
        $val_lst->add($val_city);
        $val_lst->add($val_canton);
        $val_lst->add($val_ch);
        $t->html_test($val_lst->dsp_obj()->table(), 'table_value', $t);

        // create the same table as above, but within a context
        $t->html_test(
            $phr_lst_context->dsp_obj()->headline()
            . $val_lst->dsp_obj()->table($phr_lst_context),
            'table_value_context', $t);

    }

}