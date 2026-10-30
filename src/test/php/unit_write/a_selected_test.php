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
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

// the frontend first, because it loads the web classes in an order that defines a parent class
// before its child; entered later e.g. via test_db_load -> test_api -> change_log_list, the web
// sandbox_list_named is still being included when component_list extends it (fatal)
include_once paths::WEB . 'frontend.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL . 'application.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_IMPORT . 'import_file.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SERVICE . 'export' . DIRECTORY_SEPARATOR . 'json_io.php';
include_once paths::SHARED_CONST . 'users.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_db_load.php';
include_once test_paths::CREATE . 'unit_env.php';
include_once test_paths::UNIT . 'user_tests.php';
include_once test_paths::UNIT_READ . 'phrase_data_read_tests.php';
include_once test_paths::UNIT_READ . 'triple_list_read_tests.php';
include_once test_paths::UNIT_READ . 'value_read_tests.php';
include_once test_paths::UNIT_READ . 'word_list_read_tests.php';
include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once test_paths::UNIT_WRITE . 'horizontal_write_tests.php';
include_once test_paths::UNIT_WRITE . 'all_unit_write_tests.php';
include_once test_paths::UNIT_WRITE . 'value_by_id_write_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'word_write_url_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'triple_write_url_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'formula_write_url_tests.php';
include_once test_paths::UNIT_UI . 'horizontal_ui_tests.php';
include_once test_paths::UNIT_UI . 'localhost_ui_tests.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\import\import_file;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as user_message_cfg;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\unit_env;
use Zukunft\ZukunftCom\test\php\unit\all_unit_tests;
use Zukunft\ZukunftCom\test\php\unit_api\api_tests;
use Zukunft\ZukunftCom\test\php\unit_read\phrase_data_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\system_views_read_tests;
use Zukunft\ZukunftCom\test\php\unit_workflow\word_url_tests;
use Zukunft\ZukunftCom\test\php\unit_write_workflow\word_write_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class a_selected_test extends all_unit_tests
{

    // the system data files with code ids to load again before SELECTED_IMPORT_FILES, imported
    // as the system user like the db setup does, e.g. the calculator keywords of a use case page
    const array SELECTED_SYSTEM_IMPORT_FILES = [
        cfg_files::CALCULATOR_KEYWORDS_FILE,
    ];

    // the import json files to load for a fast single-file debugging cycle
    // comment out the entries that are not needed to import just one file at a time
    const array SELECTED_IMPORT_FILES = [
        // the use case is user data, so it is imported as the normal test user usr1 like the db setup does
        //cfg_files::PV_LIFETIME_BALANCE_FILE,
        cfg_files::PORTFOLIO_SPLIT_CALCULATOR_FILE,
        //test_files::IMPORT_PRO_CONTRA_NPP_CH,
        //test_files::IMPORT_PRO_CONTRA_NPP_CH_REPUBLIC,
        //test_files::IMPORT_COSTS_ELECTRICITY_WIKI_EN,
        //test_files::IMPORT_COSTS_ELECTRICITY_WIKI_DE,
        //files::EDUCATION_FILE,
        //files::POVERTY_FILE,
        //files::HEALTH_FILE,
        //files::POPULATION_FILE,
        //files::POPULISM_FILE,
        //files::GLOBAL_WARMING_FILE,
        //files::GDP_FILE,
        //files::ZURICH_FILE,
        //files::MESSAGE_PATH . files::TIME_FILE,
        //files::MESSAGE_PATH . files::BASE_VIEWS_FILE,
        //files::MESSAGE_PATH . files::START_PAGE_DATA_FILE,
        //files::MESSAGE_PATH . files::COMPANY_FILE,
        //files::MESSAGE_PATH . files::COUNTRY_FILE,
        //files::IMPORT_COUNTRY_ISO,
        //files::IMPORT_WIND_INVESTMENT,
    ];

    /**
     * run some manual selected tests for faster debugging
     */
    function run(): void
    {

        global $db_con;

        // init
        $tl = new test_lib();
        $t_db = new test_db_load($this);
        $u_env = new unit_env();
        $msg_ui = new user_message();

        // start the test section (ts)
        $ts = 'db write job ';
        $this->header($ts);

        /*
         * unit testing - prepare
         */

        // remember the global var for restore after the unit tests
        $global_db_con = $db_con;

        // prepare for unit testing
        $db_con = $tl->unit_test_db_con();
        $this->users_for_unit_tests();
        // set the system user like all_unit_tests->set_users does for the full test suite,
        // because e.g. the delete sql creation tests are done with the system user
        $this->usr_system = $this->user_system();
        $u_env->init_unit_tests($this->usr1);

        /*
         * unit testing - without users
         */

        // run the selected unit tests
        //new system_tests()->run($this);
        //new import_tests()->run($this);
        //new formula_link_tests()->run($this);
        //new formula_calc_tests()->run($this);
        //new api_tests()->run($this);
        //new user_tests()->run($this);

        // restore the global vars that may be overwritten if additional tests are activated
        $db_con = $global_db_con;

        /*
         * db testing - prepare
         */

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $app = new application();
        $db_con = $app->start("reload cache after unit testing", false, true);

        // create the testing users
        $this->set_users();
        $usr = $this->usr1;

        if ($usr->id > 0) {

            // a code link csv row added since the last database setup (e.g. a new component type
            // or view style) reaches the database only by the daily consistency check, so write
            // the csv rows now as the system user and reload the types that the import resolves
            global $sys;
            $sys_msg = new user_message_cfg(user::system());
            $db_con->db_fill_code_links($sys_msg);
            $sys->load_type_lists($db_con, $sys_msg);

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
            $t_db = new test_db_load($this);
            //$t_db->type_list_check($this, $this->usr1);


            $ui = new frontend('api based ui tests');
            $ui->load_cache($msg_ui);
            // the html renderers read the type cache from the global $ui_sys; point it at the just
            // loaded frontend cache so the render does not depend on a stale cache of the unit tests
            // (same pattern as url_test_base and /http/view.php)
            global $ui_sys;
            $ui_sys = $ui->dto;
            // an empty frontend config returns the shared defaults e.g. for the date format
            $ui_sys->cfg = new config_ui();
            //new type_lists_ui_tests()->run($this, $ui);

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
            // import config.yaml and the files selected in SELECTED_IMPORT_FILES again, so that a
            // changed config value e.g. the test tolerance and the changed import e.g. of the value
            // descriptions reach the database without a new setup; the global config is reloaded,
            // because the tests read it e.g. phrase_data_read_tests
            $test_name = 'the config.yaml is imported again';
            $imf = new import_file();
            $imf->import_config_yaml(user::system(), $sys_msg);
            $this->assert_msg($test_name, $sys_msg, $this::TIMEOUT_LIMIT_IMPORT);
            $sys_msg->reset();
            global $cfg;
            $cfg = new config_numbers($usr);
            $cfg->load_cfg($sys_msg, null, $usr);
            foreach (self::SELECTED_SYSTEM_IMPORT_FILES as $import_file) {
                $test_name = 'the selected system file ' . basename($import_file) . ' is imported again';
                $imp_msg = $imf->json_file($import_file, user::system(), false);
                $this->assert_msg($test_name, $imp_msg, $this::TIMEOUT_LIMIT_IMPORT);
            }
            foreach (self::SELECTED_IMPORT_FILES as $import_file) {
                $test_name = 'the selected file ' . basename($import_file) . ' is imported again';
                $imp_msg = $imf->json_file($import_file, $usr, false);
                $this->assert_msg($test_name, $imp_msg, $this::TIMEOUT_LIMIT_IMPORT);
            }


            /*
             * export
             */

            // export the data related to "nuclear" and "power" (e.g. the triple "nuclear power"
            // and its values) to a pretty json file named after the phrases, e.g. nuclear_power.json
            //new json_io()->export_to_file($usr, ['nuclear', 'power'], test_paths::EXPORT);


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
            // the system view pages by section; only the view of the use case imported above is
            // rendered into resources/web/html/use_cases/pv_lifetime_balance_no_js.html next to the
            // hand made sample, the other sections are listed to be switched on when needed
            // the data of a phrase for api/phraseData, checked against the hand made sample
            //new phrase_data_read_tests()->run($this);
            $msk_tests = new system_views_read_tests();
            //$cfg = $msk_tests->context($this);
            //$msk_tests->run_start($this);
            //$msk_tests->run_words($this, $cfg);
            //$msk_tests->run_verbs($this, $cfg);
            //$msk_tests->run_triples($this, $cfg);
            //$msk_tests->run_sources($this, $cfg);
            //$msk_tests->run_refs($this, $cfg);
            //$msk_tests->run_values($this, $cfg);
            //$msk_tests->run_formulas($this, $cfg);
            //$msk_tests->run_results($this, $cfg);
            //$msk_tests->run_views($this, $cfg);
            //$msk_tests->run_components($this, $cfg);
            //$msk_tests->run_users($this, $cfg);
            //$msk_tests->run_system_pages($this);
            //$msk_tests->run_use_cases($this, $cfg);
            // the PV page without javascript and its differences to the hand made sample as a prompt for an llm
            //$msk_tests->run_pv_lifetime_balance($this);
            //$msk_tests->run_pv_lifetime_balance_diff($this);
            // the sample and the page built by javascript with the standard, high and low input numbers
            //$msk_tests->run_pv_lifetime_balance_cases($this);
            // the portfolio split pages without and with javascript next to the hand made
            // portfolio_split_calculator_sample.html, their differences and the input cases standard, high and low
            $msk_tests->run_portfolio_split_calculator($this);
            $msk_tests->run_portfolio_split_calculator_diff($this);
            $msk_tests->run_portfolio_split_calculator_cases($this);
            //$msk_tests->run_web_frontend($this);
            //new api_tests()->run($this);
            //new word_read_tests()->run($this);
            //new triple_read_tests()->run($this);
            //new source_read_tests()->run($this);
            //new value_read_tests()->run($this);
            //new formula_read_tests()->run($this);
            //new view_read_tests()->run($this);
            //new component_read_tests()->run($this);
            //new graph_tests()->run($this);
            //new value_read_tests()->run($this);


            /*
             * user interface
             */

            //new horizontal_ui_tests()->run($this, $ui);

            /*
             * db write
             */

            // cleanup - fallback delete
            //$this->cleanup_objects_ex_user();

            // run the selected db write tests
            // the change and delete of a value given only by its id, as the update put does it
            //new value_by_id_write_tests()->run($this);
            // the triple description change of the calculator editor and its undo
            //new update_put_write_tests()->run_use_case($this);
            //run_system_test($this);
            //$t_db->create_test_db_entries($this);
            //new user_write_tests()->run($this);
            //new sys_log_write_tests()->run($this);
            //new horizontal_write_tests()->run($this);

            //new word_write_tests()->run($this);
            //new word_list_write_tests()->run($this);
            //new verb_write_tests()->run($t);
            //new triple_write_tests()->run($this);
            //new phrase_write_tests()->run($t);
            //new phrase_list_write_tests()->run($t);
            //new group_write_tests()->run($this);
            //new group_list_write_tests()->run($t);
            //new graph_tests()->run($t);
            //new term_write_tests()->run($t);
            //new term_list_tests()->run($t);
            //new source_write_tests()->run($this);
            //new ref_write_tests()->run($this);
            //new value_write_tests()->run($this);
            //new value_list_write_tests()->run($t);
            //new expression_write_tests()->run($this);
            //new element_write_tests()->run($this);
            //new element_write_tests()->run_list($this);
            //new element_group_write_tests()->run($this);
            //new formula_write_tests()->run($this);
            //new formula_write_tests()->run_list($this);
            //new formula_link_write_tests()->run($this);
            //new formula_link_write_tests()->run_list($this);
            //new formula_trigger_tests()->run($this);
            //new result_write_tests()->run($this);
            //new result_write_tests()->run_list($this);
            //new job_write_tests()->run($this);
            //new job_write_tests()->run_list($this);
            //new view_write_tests()->run($this);
            //new view_relation_write_tests()->run($this);
            //new view_link_write_tests()->run($this);
            //new component_write_tests()->run($this);
            //new component_link_write_tests()->run($this);

            // the only active main test: read each main object type via a REST api get call
            //new api_write_tests()->run($this);
            //new import_write_tests()->run($this);
            //new xbrl_write_tests()->run($this);
            //new wikidata_write_tests()->run($this);

            //$import = new import_file();
            //$import->import_test_files($usr);

            /*
             * url
             */

            //new word_url_tests()->run($this);
            //new word_write_url_tests()->run($this);
            //new triple_write_url_tests()->run($this);
            //new formula_write_url_tests()->run($this);

            // cleanup - fallback delete
            //$this->cleanup_objects();

        }

        /*
        global $db_con;

        // to test the database upgrade
        $db_chk = new db_check();
        $db_chk->db_upgrade_0_0_3($db_con);
        */
    }

}