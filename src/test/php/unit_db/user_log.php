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

class user_log_unit_db_tests
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
        $result = $lst->load_by_dsp_of_wrd($wrd);
        $t->assert('word view change', $result, false);

        // ... and check if at least the most critical is loaded
        //$result = cl(db_cl::VIEW_TYPE, view_type::DEFAULT);
        //$t->assert('check type' . view_type::DEFAULT, $result, 1);


    }

}

