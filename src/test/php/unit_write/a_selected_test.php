<?php

/*

    /test/php/unit_write/a_selected_test.php - temp test code to run a selection of tests
    ----------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL . 'application.php';
include_once paths::MODEL_IMPORT . 'import_file.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_CONST . 'users.php';
include_once test_paths::CREATE . 'test_db_load.php';
include_once test_paths::CREATE . 'unit_env.php';
include_once test_paths::UNIT_READ . 'triple_list_read_tests.php';
include_once test_paths::UNIT_READ . 'value_read_tests.php';
include_once test_paths::UNIT_READ . 'word_list_read_tests.php';
include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once test_paths::UNIT_UI . 'horizontal_ui_tests.php';
include_once test_paths::UNIT_UI . 'localhost_ui_tests.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\import\import_file;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\unit_env;
use Zukunft\ZukunftCom\test\php\unit_api\api_tests;
use Zukunft\ZukunftCom\test\php\unit_read\type_lists_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_read\value_read_tests;
use Zukunft\ZukunftCom\test\php\unit_workflow\word_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class a_selected_test extends test_cleanup
{

    /**
     * run some manual selected tests for faster debugging
     */
    function run(): void
    {

        global $db_con;
        global $usr;

        // init
        $tl = new test_lib();
        $t_db = new test_db_load($this);
        $u_env = new unit_env();

        // start the test section (ts)
        $ts = 'db write job ';
        $this->header($ts);

        /*
         * unit testing - prepare
         */

        // remember the global var for restore after the unit tests
        $global_db_con = $db_con;
        $global_usr = $usr;

        // prepare for unit testing
        $db_con = $tl->unit_test_db_con();
        $this->usr1 = $tl->users_for_unit_tests();
        $u_env->init_unit_tests();

        /*
         * unit testing - without users
         */

        // run the selected unit tests
        //new system_tests()->run($this);
        //new import_tests()->run($this);
        //new formula_link_tests()->run($this);

        // restore the global vars that may be overwritten if additional tests are activated
        $db_con = $global_db_con;
        $usr = $global_usr;


        /*
         * db testing - prepare
         */

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $app = new application();
        $db_con = $app->start("reload cache after unit testing");

        // create the testing users
        $this->set_users();
        $usr = $this->usr1;

        if ($usr->id > 0) {

            /*
             * db read
             */

            // preferred tests to check upfront the words::*_ID and triples::*_ID
            //new word_list_read_tests()->run($this);
            //new triple_list_read_tests()->run($this);

            /*
             * part of system setup testing
             */

            //$sys_usr = new user;
            //$sys_usr->load_by_id(users::SYSTEM_ID);
            //$import = new import_file();
            //$import->import_config_yaml($sys_usr);


            /*
             * unit testing - with system users
             */
            //$t_db = new test_db_load($this);
            //$t_db->type_list_recreate($this, $this->usr1);


            $ui = new frontend('api based ui tests');
            $ui->load_cache();
            new type_lists_ui_tests()->run($this, $ui);

            // check and update the fixed csv files
            // e.g. to have an indication which words might be missing due to the code changes
            //$t_db->csv_recreate();

            // new horizontal_tests()->run($t);

            /*
             * prepare db testing
             */

            //$t_db->type_list_recreate($this, $usr);

            //$this->create_test_db_entries($t);

            /*
             * import
             */

            // run the selected db import tests
            /*
            $test_name = 'validate config import';
            $imf = new import_file();
            $import_result = $imf->import_config_yaml($sys_usr, true);
            $t->assert($test_name, $import_result->is_ok(), true, $t::TIMEOUT_LIMIT_IMPORT);
            */
            //new import_write_tests()->run($t);
            $imf = new import_file();
            //$imf->json_file(files::MESSAGE_PATH . files::TIME_FILE, $usr, false);
            //$this->file_import(test_files::IMPORT_TRAVEL_SCORING, $usr);
            //$this->file_import(test_files::IMPORT_CURRENCY, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::SYSTEM_VIEWS_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::UNITS_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::IP_BLACKLIST_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::TIME_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::BASE_VIEWS_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::START_PAGE_DATA_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::COMPANY_FILE, $usr);
            //$this->file_import(test_files::IMPORT_COUNTRY_ISO, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::COUNTRY_FILE, $usr);
            //$this->file_import(test_files::IMPORT_COUNTRY_ISO, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::START_PAGE_DATA_FILE, $usr);
            //$this->file_import(test_files::IMPORT_WIND_INVESTMENT, $usr);


            /*
             * ui via api
             */

            $api_test = new api_tests();
            //$api_test->run($this);

            //new localhost_ui_tests()->run($this);


            /*
             * db read
             */

            // preferred tests to check upfront the words::*_ID and triples::*_ID
            //new word_list_read_tests()->run($this);
            //new triple_list_read_tests()->run($this);
            // run the selected db read tests
            //new api_tests()->run($this);
            //new word_read_tests()->run($this);
            //new triple_read_tests()->run($this);
            //new source_read_tests()->run($this);
            new value_read_tests()->run($this);
            //new formula_read_tests()->run($this);
            //new view_read_tests()->run($this);
            //new component_read_tests()->run($this);
            //new graph_tests()->run($this);
            //new value_read_tests()->run($this);


            /*
             * user interface
             */

            //new horizontal_ui_tests()->run($this);

            /*
             * db write
             */

            // run the selected db write tests
            //new api_write_tests()->run($t);
            //new user_write_tests()->run($this);
            //new word_write_tests()->run($this);
            //new word_list_write_tests()->run($this);
            //new triple_write_tests()->run($this);
            //new group_write_tests()->run($this);
            //new source_write_tests()->run($this);
            //new ref_write_tests()->run($this);
            //new value_write_tests()->run($this);
            new formula_write_tests()->run($this);
            //new formula_link_write_tests()->run($this);
            //new expression_write_tests()->run($this);
            //new element_write_tests()->run($this);
            //new element_write_tests()->run_list($this);
            //new view_write_tests()->run($this);
            //new view_link_write_tests()->run($this);
            //new component_write_tests()->run($this);
            //new component_link_write_tests()->run($this);


            //$import = new import_file();
            //$import->import_test_files($usr);

            /*
             * url
             */

            new word_url_tests()->run($this);

        }

        /*
        global $db_con;

        // to test the database upgrade
        $db_chk = new db_check();
        $db_chk->db_upgrade_0_0_3($db_con);
        */
    }

}