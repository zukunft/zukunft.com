<?php

/*

    test/php/unit_write/phrase_group_list_tests.php - write test PHRASE GROUP LISTS to the database and check the results
    -----------------------------------------------
  

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

namespace unit_write;

use cfg\group\group_list;
use cfg\phrase\phrase_list;
use cfg\word\word_list;
use shared\const\words;
use test\test_cleanup;

class group_list_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the phrase group list class (src/main/php/model/phrase/phrase_group_list.php)');

        // define some phrase groups for testing

        // Switzerland inhabitants
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO));
        $country_grp = $phr_lst->get_grp_id();

        // Canton of Zurich inhabitants
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::ZH, words::CANTON, words::INHABITANTS, words::MIO));
        $canton_grp = $phr_lst->get_grp_id();

        // City of Zurich inhabitants
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::ZH, words::CITY, words::INHABITANTS, words::MIO));
        $city_grp = $phr_lst->get_grp_id();

        // test add a phrase group to a phrase group list
        $grp_lst = new group_list($usr);
        $grp_lst->add($country_grp);
        $grp_lst->add($canton_grp);
        $grp_lst->add($city_grp);
        $result = $grp_lst->name();
        $target = words::INHABITANTS . ','
            . words::MIO . ','
            . words::CH
            . ' and '
            . words::CANTON . ','
            . words::INHABITANTS . ','
            . words::MIO . ','
            . words::ZH
            . ' and '
            . words::CITY . ','
            . words::INHABITANTS . ','
            . words::MIO . ','
            . words::ZH;
        $t->display('phrase_group_list->add of ' . $country_grp->dsp_id() . ', ' . $country_grp->dsp_id() . ', ' . $city_grp->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);


        // test getting the common phrases of several group
        $grp_lst = new group_list($usr);

        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO));
        $country_grp = $wrd_lst->get_grp();
        $grp_lst->add($country_grp);

        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(words::ZH, words::CANTON, words::INHABITANTS, words::MIO));
        $canton_grp = $wrd_lst->get_grp();
        $grp_lst->add($canton_grp);

        $phr_lst = $grp_lst->common_phrases();
        $result = $phr_lst->dsp_name();
        $target = '"' . words::INHABITANTS . '","' . words::MIO . '"';
        $t->display('phrase_group_list->common_phrases of ' . $grp_lst->dsp_id(), $target, $result);

    }

}