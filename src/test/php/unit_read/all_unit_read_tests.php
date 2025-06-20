<?php

/*

    test/php/unit/test_unit_db.php - add tests to the unit test that read only from the database in a useful order
    ------------------------------
    
    the zukunft.com unit tests should test all class methods, that can be tested without writing to the database


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

include_once WEB_PATH . 'frontend.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';

use html\types\type_lists as type_list_dsp;
use shared\const\groups;
use shared\const\triples;
use shared\const\values;
use shared\const\words;
use shared\types\verbs;
use test\all_tests;
use unit\all_unit_tests;
use unit\api_tests;
use unit_ui\all_ui_tests;

class all_unit_read_tests extends all_unit_tests
{

    function run_unit_db_tests(all_tests $t): void
    {
        global $db_con;
        global $usr;

        $this->header('Start the zukunft.com unit database read only tests');

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $db_con = prg_restart("reload cache after unit testing");

        // create the testing users
        $this->set_users();
        $usr = $this->usr1;

        // check that the main database test entries are still active
        $this->create_test_db_entries($t);

        // run the unit database tests
        $this->init_unit_db_tests();
        $this->usr1->load_usr_data();

        // do the database unit tests
        (new system_read_tests)->run($this);
        (new sql_db_read_tests)->run($this);
        (new user_read_tests)->run($this);
        (new protection_read_tests)->run($this);
        (new share_read_tests)->run($this);
        (new word_read_tests)->run($this);
        (new word_list_read_tests)->run($this);
        (new verb_read_tests)->run($this);
        (new triple_read_tests)->run($this);
        (new triple_list_read_tests)->run($this);
        (new phrase_read_tests)->run($this);
        (new phrase_list_read_tests)->run($this);
        (new group_read_tests)->run($this);
        (new term_read_tests)->run($this);
        (new term_list_read_tests)->run($this);
        (new value_read_tests)->run($this);
        (new value_list_read_tests)->run($this);
        (new formula_read_tests)->run($this);
        (new formula_list_read_tests)->run($this);
        (new expression_read_tests)->run($this);
        (new element_list_read_tests)->run($this);
        (new view_read_tests)->run($this);
        (new view_list_read_tests)->run($this);
        (new component_read_tests)->run($this);
        (new component_list_read_tests)->run($this);
        (new source_read_tests)->run($this);
        (new ref_read_tests)->run($this);
        (new language_read_tests)->run($this);
        (new change_log_read_tests)->run($this);
        (new sys_log_read_tests)->run($this);
        (new job_read_tests)->run($this);

        // load the types from the api message
        $api_msg = $this->type_lists_api($this->usr1);
        new type_list_dsp($api_msg);

        $api_test = new api_tests();
        $api_test->run($this);

        // test all system views
        $api_test->run_ui_test($this);

        (new export_read_tests())->run($this);

        // cleanup also before testing to remove any leftovers
        $this->clean_up_unit_db_tests();

    }

    private function init_unit_db_tests(): void
    {
        // add functional test rows to the database for read testing e.g. exclude sandbox entries
        $this->test_triple(
            triples::PI, verbs::IS, words::MATH,
            triples::PI_NAME, triples::PI_NAME
        );
        $phr_grp = $this->add_phrase_group(array(triples::PI_NAME), groups::TN_READ);
        $this->test_value_by_phr_grp($phr_grp, values::PI_LONG);

        $this->test_triple(
            triples::E, verbs::IS, words::MATH,
            triples::E, triples::E
        );
        $phr_grp = $this->add_phrase_group(array(triples::E), groups::TN_READ);
        $this->test_value_by_phr_grp($phr_grp, values::E);
    }

    /**
     * remove the database test rows created by init_unit_db_tests
     * to have a clean database without test rows
     * @return void
     */
    private function clean_up_unit_db_tests(): void
    {
        //$this->del_triple_by_name(triples::TN_READ_NAME);
        //$phr_grp = $this->load_phrase_group_by_name(groups::TN_READ);
        //$this->del_value_by_phr_grp($phr_grp);
        //$this->del_phrase_group(groups::TN_READ);
    }

}