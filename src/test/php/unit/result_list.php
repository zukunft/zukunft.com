<?php

/*

    test/unit/result_list.php - unit testing of the FORMULA VALUE functions
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

namespace test;

use model\formula;
use model\phrase_group;
use model\result_list;
use model\sql_db;
use model\triple;
use model\word;
use html\result\result_list as result_list_dsp;

class result_list_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'result->';
        $t->resource_path = 'db/result/';
        $json_file = 'unit/result/result_list_import_part.json';
        $usr->set_id(1);


        $t->header('Unit tests of the result list class (src/main/php/model/formula/result_list.php)');

        $t->subheader('SQL creation tests');

        // sql to load a list of results by the formula id
        $res_lst = new result_list($usr);
        $frm = new formula($usr);
        $frm->set_id(1);
        $t->assert_load_list_sql($db_con, $res_lst, $frm);

        // sql to load a list of results by the phrase group id
        $res_lst = new result_list($usr);
        $grp = new phrase_group($usr);
        $grp->set_id(2);
        $t->assert_load_list_sql($db_con, $res_lst, $grp);

        // sql to load a list of results by the source phrase group id
        $res_lst = new result_list($usr);
        $grp = new phrase_group($usr);
        $grp->set_id(2);
        $t->assert_load_list_sql($db_con, $res_lst, $grp, true);

        // sql to load a list of results by the word id
        $res_lst = new result_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(2);
        $t->assert_load_list_sql($db_con, $res_lst, $wrd);

        // sql to load a list of results by the triple id
        $res_lst = new result_list($usr);
        $trp = new triple($usr);
        $trp->set_id(3);
        $t->assert_load_list_sql($db_con, $res_lst, $trp);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new result_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->dummy_result_list();
        $t->assert_api_to_dsp($trp_lst, new result_list_dsp());

    }

}