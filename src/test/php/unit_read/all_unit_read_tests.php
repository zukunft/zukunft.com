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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\const\groups;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\unit\all_unit_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\start_ui_read_tests;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

include_once paths::MODEL . 'application.php';
include_once test_paths::UNIT . 'all_unit_tests.php';
include_once paths::WEB . 'frontend.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CREATE . 'test_db_load.php';

class all_unit_read_tests extends all_unit_tests
{

    function run_unit_db_tests(all_tests $t): void
    {
        global $db_con;
        global $usr;

        // init
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db read ';
        $t->header($ts);

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $app = new application();
        $db_con = $app->open_db("reload cache after unit testing");

        // create the testing users
        $t->subheader($ts . 'prepare');
        $this->set_users();
        $usr = $this->usr1;

        // check that the main database test entries are still active
        $t_db->create_test_db_entries($t);
        $t_db->create_unit_test_db_entries($t);

        // run the unit database tests
        $this->init_unit_db_tests($t);
        $this->usr1->load_usr_data();

        // do the database unit tests
        $t->subheader($ts . 'general');
        // TODO Prio 0 use $t instead of $this ?
        (new system_read_tests)->run($this);
        (new system_views_read_tests)->run($t);
        (new sql_db_read_tests)->run($this);
        (new user_read_tests)->run($this);
        (new protection_read_tests)->run($this);
        (new share_read_tests)->run($this);
        (new horizontal_read_tests)->run($this);

        $t->subheader($ts . 'objects');
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

        $t->subheader($ts . 'api based ui tests');
        global $sys;
        $ui = new frontend('api based ui tests');
        $ui->load_cache();
        (new type_lists_ui_tests)->run($t, $ui);
        (new word_ui_read_tests)->run($this, $ui);
        (new start_ui_read_tests)->run($t, $ui);

        $t->subheader($ts . 'export');
        new export_read_tests()->run($this);

        // cleanup also before testing to remove any leftovers
        $this->clean_up_unit_db_tests();

    }

    private function init_unit_db_tests(all_tests $t): void
    {
        $t_db = new test_db_load($t);
        // add functional test rows to the database for read testing e.g. exclude sandbox entries
        $t_db->test_triple(
            word_names::PI, verbs::IS, triple_names::MATH_CONST,
            triple_names::PI_NAME, triple_names::PI_NAME
        );
        $phr_grp = $t_db->add_phrase_group(array(triple_names::PI_NAME), groups::TN_READ);
        $t_db->test_value_by_phr_grp($phr_grp, values::PI_LONG);

        $t_db->test_triple(
            word_names::E_SYMBOL, verbs::ALIAS, word_names::E,
            triple_names::E, triple_names::E
        );
        $phr_grp = $t_db->add_phrase_group(array(triple_names::E), groups::TN_READ);
        $t_db->test_value_by_phr_grp($phr_grp, values::E);
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