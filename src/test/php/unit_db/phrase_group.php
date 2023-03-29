<?php

/*

    test/php/unit_db/phrase_group.php - test of the phrase group methods that only read from the database
    ---------------------------------
  

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

namespace test;

use api\formula_api;
use api\word_api;
use model\phrase_group;
use model\phrase_list;

class phrase_group_unit_db_tests
{
    function run(testing $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'phrase_group->';

        $t->header('Test the phrase group class (src/main/php/model/phrase_group.php)');

        // test if the phrase group links are correctly recreated when a group is updated
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_CANTON, word_api::TN_INHABITANTS));
        $grp = $phr_lst->get_grp();
        $grp_check = new phrase_group($usr);
        $grp_check->set_id($grp->id());
        $grp_check->load();
        $result = $grp_check->load_link_ids_for_testing();
        $target = $grp->phr_lst->id_lst();
        $t->dsp('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

        // second test if the phrase group links are correctly recreated when a group is updated
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_CANTON, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
        $grp = $phr_lst->get_grp();
        $grp_check = new phrase_group($usr);
        $grp_check->set_id($grp->id());
        $grp_check->load();
        $result = $grp_check->load_link_ids_for_testing();
        $target = $grp->phr_lst->id_lst();
        $t->dsp('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    }
}

