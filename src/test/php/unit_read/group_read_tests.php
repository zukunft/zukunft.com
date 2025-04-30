<?php

/*

    test/php/unit_read/group_tests.php - test of the phrase group methods that only read from the database
    ----------------------------------
  

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

namespace unit_read;

use cfg\group\group;
use cfg\phrase\phrase_list;
use cfg\word\word_list;
use shared\const\words;
use test\test_cleanup;

class group_read_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'phrase_group->';

        $t->header('group db read tests');

        $t->subheader('load');

        $test_name = 'group by word names';
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names($t->words_canton_zh_inhabitants());
        $test_name .= ' for ' . $wrd_lst->dsp_id();
        $phr_grp = new group($usr);
        $phr_grp->load_by_phr_lst($wrd_lst->phrase_list());
        $result = $phr_grp->id();
        $target = 0;
        if ($result > 0) {
            $target = $result;
        }
        $t->assert($test_name, $result, $target);

        $test_name = 'test if the phrase group links are correctly recreated when a group is updated';
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names([words::ZH, words::CANTON, words::INHABITANTS]);
        $test_name .= ' for phrases ' . $phr_lst->dsp_id();
        $grp = $phr_lst->get_grp_id();
        $grp_check = new group($t->usr1);
        $grp_check->set_id($grp->id());
        $result = $grp_check->load_link_ids_for_testing();
        $target = $grp->phrase_list()->id_lst();
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_PAGE);

        $test_name = 'second test if the phrase group links are correctly recreated when a group is updated';
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(array(words::ZH, words::CANTON, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $test_name .= ' for phrases ' . $phr_lst->dsp_id();
        $grp = $phr_lst->get_grp_id();
        $grp_check = new group($t->usr1);
        $grp_check->set_id($grp->id());
        $result = $grp_check->load_link_ids_for_testing();
        $target = $grp->phrase_list()->id_lst();
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_PAGE);

    }
}

