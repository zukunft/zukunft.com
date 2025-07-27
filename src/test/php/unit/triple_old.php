<?php

/*

  test/unit/triple.php - unit testing of the word link / triple functions
  -----------------------
  

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

namespace unit;

use cfg\const\paths;

include_once paths::SHARED_CONST . 'triples.php';

// TODO combine with triple_unit_test

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use shared\const\triples;
use test\test_cleanup;

class triple_old
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';

        // start the test section (ts)
        $ts = 'unit triple ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement');

        // sql to load a triple by id
        $trp = new triple($usr);
        $trp->set_id(1);
        $t->assert_sql_by_obj_vars($db_con, $trp);
        $t->assert_sql_standard($sc, $trp);

        // sql to load a triple by name
        $trp = new triple($usr);
        $trp->set_name(triples::COMPANY_ZURICH);
        $t->assert_sql_by_obj_vars($db_con, $trp);
        $t->assert_sql_standard($sc, $trp);

        // sql to load a triple by link ids
        $trp = new triple($usr);
        $wrd_from = new word($usr);
        $wrd_from->set_id(2);
        $vrb = new verb();
        $vrb->set_id(3);
        $wrd_to = new word($usr);
        $wrd_to->set_id(4);
        $trp->set_from($wrd_from->phrase());
        $trp->set_verb($vrb);
        $trp->set_to($wrd_to->phrase());
        $t->assert_sql_by_obj_vars($db_con, $trp);
        $t->assert_sql_standard($sc, $trp);
        $trp->set_id(5);
        $t->assert_sql_not_changed($sc, $trp);
        $t->assert_sql_user_changes($sc, $trp);

        // sql to check the usage of a triple

        $t->subheader($ts . 'im- and export');
        $json_file = 'unit/triple/pi.json';
        $t->assert_json_file(new triple($usr), $json_file);
    }

}