<?php

/*

    test/php/unit_write/phrase_group_list.php - write test PHRASE GROUP LISTS to the database and check the results
    -----------------------------------------
  

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

namespace test\write;

use api\word_api;
use model\phrase_group_list;
use model\phrase_list;
use model\word_list;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_PAGE;

class phrase_group_list_test
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the phrase group list class (src/main/php/model/phrase/phrase_group_list.php)');

        // define some phrase groups for testing

        // Switzerland inhabitants
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $country_grp = $phr_lst->get_grp();

        // Canton of Zurich inhabitants
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_CANTON, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $canton_grp = $phr_lst->get_grp();

        // City of Zurich inhabitants
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_CITY, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $city_grp = $phr_lst->get_grp();

        // test add a phrase group to a phrase group list
        $grp_lst = new phrase_group_list($usr);
        $grp_lst->add($country_grp);
        $grp_lst->add($canton_grp);
        $grp_lst->add($city_grp);
        $result = $grp_lst->name();
        $target = word_api::TN_CH . ',' . word_api::TN_INHABITANTS . ',' . word_api::TN_MIO .
            ' and ' . word_api::TN_CANTON . ',' . word_api::TN_ZH . ',' . word_api::TN_INHABITANTS . ',' . word_api::TN_MIO .
            ' and ' . word_api::TN_CITY . ',' . word_api::TN_ZH . ',' . word_api::TN_INHABITANTS . ',' . word_api::TN_MIO;
        $t->display('phrase_group_list->add of ' . $country_grp->dsp_id() . ', ' . $country_grp->dsp_id() . ', ' . $city_grp->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);


        // test getting the common phrases of several group
        $grp_lst = new phrase_group_list($usr);

        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $country_grp = $wrd_lst->get_grp();
        $grp_lst->add($country_grp);

        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_CANTON, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $canton_grp = $wrd_lst->get_grp();
        $grp_lst->add($canton_grp);

        $phr_lst = $grp_lst->common_phrases();
        $result = $phr_lst->dsp_name();
        $target = '"' . word_api::TN_INHABITANTS . '","' . word_api::TN_MIO . '"';
        $t->display('phrase_group_list->common_phrases of ' . $grp_lst->dsp_id(), $target, $result);

    }

}