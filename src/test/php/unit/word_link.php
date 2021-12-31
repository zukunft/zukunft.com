<?php

/*

  test/unit/word_link.php - unit testing of the word link / triple functions
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class word_link_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/pi.json';
        $usr->id = 1;

        $t->header('Unit tests of the word class (src/main/php/model/word/word_link.php)');


        $t->subheader('SQL statement tests');

        // sql to load a triple by id
        $trp = new word_link($usr);
        $trp->id = 1;
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);

        // sql to load a triple by name
        $trp = new word_link($usr);
        $trp->name = phrase::TN_ZH_COMPANY;
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);

        // sql to load a triple by link ids
        $trp = new word_link($usr);
        $wrd_from = new word($usr);
        $wrd_from->id = 2;
        $vrb = new verb($usr);
        $vrb->id = 3;
        $wrd_to = new word($usr);
        $wrd_to->id = 4;
        $trp->from = $wrd_from->phrase();
        $trp->verb = $vrb;
        $trp->to = $wrd_to->phrase();
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new word_link($usr), $json_file);
    }

}