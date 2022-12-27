<?php

/*

  test/unit_db/user_log.php - database unit testing of the user log functions
  -------------------------


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

use api\triple_api;
use api\word_api;

class change_log_unit_db_tests
{

    function run(testing $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'user log read db->';

        $t->header('Unit database tests of the user log classes (src/main/php/model/log/* and src/main/php/model/user/log_*)');

        $t->subheader('Load user log tests');

        // prepare the objects for the tests
        $wrd = $t->dummy_word();
        $trp = $t->dummy_triple();

        // check if loading the changes technically works
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_wrd($wrd, change_log_field::FLD_WORD_NAME);
        $t->assert('word view change', $result, true);

        // check if the first entry is the adding of the word name
        $first_change = $lst->lst()[0];
        $t->assert('first word is adding', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, word_api::TN_READ);

        // ... same for triples
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_trp($trp, change_log_field::FLD_TRIPLE_NAME);
        $t->assert('triple view change', $result, true);

        // check if the first entry is the adding of the triple name
        $first_change = $lst->lst()[0];
        $t->assert('first triple is adding', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, triple_api::TN_READ_NAME);


        $t->subheader('API unit db tests');

        $wrd = new word($usr);
        $wrd->load_by_id(1);
        $log_lst = new change_log_list();
        $log_lst->load_by_fld_of_wrd($wrd, change_log_field::FLD_WORD_NAME);
        $t->assert_api($log_lst);

    }

}

