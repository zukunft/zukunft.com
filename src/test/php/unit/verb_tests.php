<?php

/*

    test/unit/verb.php - unit testing of the verb or phrase link functions
    ------------------
  

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

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\phrase;
use cfg\verb;
use cfg\verb_list;
use html\verb\verb as verb_dsp;
use shared\enum\foaf_direction;
use test\test_cleanup;

class verb_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'verb->';
        $t->resource_path = 'db/verb/';


        $t->header('verb unit tests');
        $vrb = new verb();

        $t->subheader('verb sql setup');
        $t->assert_sql_table_create($vrb);
        $t->assert_sql_index_create($vrb);

        $t->subheader('verb sql read');
        $t->assert_sql_by_id($sc, $vrb);
        $t->assert_sql_by_name($sc, $vrb);
        $t->assert_sql_by_code_id($sc, $vrb);

        $t->subheader('verb sql write');
        // TODO activate db write
        //$t->assert_sql_insert($sc, $vrb);
        // TODO activate db write
        //$t->assert_sql_update($sc, $vrb);
        // TODO activate db write
        //$t->assert_sql_delete($sc, $vrb);


        $t->subheader('Im- and Export tests');

        $vrb = new verb();
        // set the admin user if this is needed for the import e.g. for verbs
        $vrb->set_user($usr_sys);
        $json_file = 'unit/verb/is_a.json';
        $t->assert_json_file($vrb, $json_file);


        $t->subheader('HTML frontend unit tests');

        $vrb = $t->verb();
        $t->assert_api_to_dsp($vrb, new verb_dsp());


        $t->header('Unit tests of the verb list class (src/main/php/model/verb/verb_list.php)');

        $t->subheader('SQL statement tests');

        // sql to load a list with all verbs
        $vrb_lst = new verb_list($usr);
        $t->assert_sql_all($sc, $vrb_lst);

        // sql to load a verb list by phrase id and direction up
        $vrb_lst = new verb_list($usr);
        $phr = new phrase($usr);
        $phr->set_id(5);
        $this->assert_sql_by_linked_phrases($t, $db_con, $vrb_lst, $phr, foaf_direction::UP);

        // ... same for direction down
        $this->assert_sql_by_linked_phrases($t, $db_con, $vrb_lst, $phr, foaf_direction::DOWN);

    }

    /**
     * similar to $t->assert_load_sql but calling load_by_linked_phrases_sql instead of load_sql
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param verb_list $vrb_lst the verb list object used for testing
     * @param phrase $phr the phrase used for testing
     * @param foaf_direction $direction
     */
    private function assert_sql_by_linked_phrases(
        test_cleanup $t, sql_db $db_con, verb_list $vrb_lst, phrase $phr, foaf_direction $direction
    ): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $vrb_lst->load_by_linked_phrases_sql($db_con, $phr, $direction);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $vrb_lst->load_by_linked_phrases_sql($db_con, $phr, $direction);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}

