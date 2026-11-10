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
include_once paths::MODEL_HELPER . 'db_cache.php';
include_once paths::MODEL_IMPORT . 'import_file.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SERVICE . 'export' . DIRECTORY_SEPARATOR . 'json_io.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_db_load.php';
include_once test_paths::CREATE . 'unit_env.php';
include_once test_paths::UNIT . 'coding_rule_tests.php';
include_once test_paths::UNIT . 'formula_tests.php';
include_once test_paths::UNIT . 'formula_list_tests.php';
include_once test_paths::UNIT . 'result_list_tests.php';
include_once test_paths::UNIT . 'triple_tests.php';
include_once test_paths::UNIT . 'user_tests.php';
include_once test_paths::UNIT . 'word_tests.php';
include_once test_paths::UNIT_READ . 'phrase_data_read_tests.php';
include_once test_paths::UNIT_READ . 'result_list_read_tests.php';
include_once test_paths::UNIT_READ . 'triple_list_read_tests.php';
include_once test_paths::UNIT_READ . 'value_read_tests.php';
include_once test_paths::UNIT_READ . 'word_list_read_tests.php';
include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once test_paths::UNIT_WRITE . 'horizontal_write_tests.php';
include_once test_paths::UNIT_WRITE . 'all_unit_write_tests.php';
include_once test_paths::UNIT_WRITE . 'value_by_id_write_tests.php';
include_once test_paths::UNIT_WRITE . 'update_put_write_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'word_write_url_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'triple_write_url_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'formula_write_url_tests.php';
include_once test_paths::UNIT_WRITE_WORKFLOW . 'value_write_url_tests.php';
include_once test_paths::UNIT_UI . 'base_ui_tests.php';
include_once test_paths::UNIT_UI . 'change_log_ui_tests.php';
include_once test_paths::UNIT_UI . 'horizontal_ui_tests.php';
include_once test_paths::UNIT_UI . 'job_ui_tests.php';
include_once test_paths::UNIT_UI . 'localhost_ui_tests.php';
include_once test_paths::UNIT_UI . 'result_ui_tests.php';
include_once test_paths::UNIT_UI . 'system_view_ui_tests.php';
include_once test_paths::UNIT_UI . 'view_ui_tests.php';
include_once test_paths::UNIT_UI . 'word_ui_tests.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache;
use Zukunft\ZukunftCom\main\php\cfg\import\import_file;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as user_message_cfg;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\unit_env;
use Zukunft\ZukunftCom\test\php\unit\all_unit_tests;
use Zukunft\ZukunftCom\test\php\unit\change_log_tests;
use Zukunft\ZukunftCom\test\php\unit\coding_rule_tests;
use Zukunft\ZukunftCom\test\php\unit\component_link_list_tests;
use Zukunft\ZukunftCom\test\php\unit\component_link_tests;
use Zukunft\ZukunftCom\test\php\unit\component_list_tests;
use Zukunft\ZukunftCom\test\php\unit\component_tests;
use Zukunft\ZukunftCom\test\php\unit\db_cache_tests;
use Zukunft\ZukunftCom\test\php\unit\element_list_tests;
use Zukunft\ZukunftCom\test\php\unit\element_tests;
use Zukunft\ZukunftCom\test\php\unit\figure_list_tests;
use Zukunft\ZukunftCom\test\php\unit\figure_tests;
use Zukunft\ZukunftCom\test\php\unit\formula_link_tests;
use Zukunft\ZukunftCom\test\php\unit\formula_tests;
use Zukunft\ZukunftCom\test\php\unit\formula_list_tests;
use Zukunft\ZukunftCom\test\php\unit\group_list_tests;
use Zukunft\ZukunftCom\test\php\unit\group_tests;
use Zukunft\ZukunftCom\test\php\unit\job_tests;
use Zukunft\ZukunftCom\test\php\unit\phrase_list_tests;
use Zukunft\ZukunftCom\test\php\unit\phrase_tests;
use Zukunft\ZukunftCom\test\php\unit\ref_tests;
use Zukunft\ZukunftCom\test\php\unit\result_list_tests;
use Zukunft\ZukunftCom\test\php\unit\result_tests;
use Zukunft\ZukunftCom\test\php\unit\source_list_tests;
use Zukunft\ZukunftCom\test\php\unit\source_tests;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\unit\term_list_tests;
use Zukunft\ZukunftCom\test\php\unit\term_tests;
use Zukunft\ZukunftCom\test\php\unit\term_view_tests;
use Zukunft\ZukunftCom\test\php\unit\triple_list_tests;
use Zukunft\ZukunftCom\test\php\unit\triple_tests;
use Zukunft\ZukunftCom\test\php\unit\type_tests;
use Zukunft\ZukunftCom\test\php\unit\user_list_tests;
use Zukunft\ZukunftCom\test\php\unit\user_tests;
use Zukunft\ZukunftCom\test\php\unit\value_list_tests;
use Zukunft\ZukunftCom\test\php\unit\value_tests;
use Zukunft\ZukunftCom\test\php\unit\verb_tests;
use Zukunft\ZukunftCom\test\php\unit\view_list_tests;
use Zukunft\ZukunftCom\test\php\unit\view_tests;
use Zukunft\ZukunftCom\test\php\unit\word_list_tests;
use Zukunft\ZukunftCom\test\php\unit\word_tests;
use Zukunft\ZukunftCom\test\php\unit_api\api_tests;
use Zukunft\ZukunftCom\test\php\unit_read\change_log_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\component_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\component_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\element_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\formula_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\formula_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\group_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\job_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\language_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\phrase_data_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\phrase_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\phrase_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\ref_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\result_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\source_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\sys_log_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\system_views_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\term_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\term_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\triple_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\triple_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\user_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\value_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\value_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\verb_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\view_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\view_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\word_list_read_tests;
use Zukunft\ZukunftCom\test\php\unit_read\word_read_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\base_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\change_log_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\job_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\localhost_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\result_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\system_view_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\view_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\word_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_workflow\word_url_tests;
use Zukunft\ZukunftCom\test\php\unit_write_workflow\value_write_url_tests;
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
        cfg_files::NPP_CH_VOTE_FILE,
        //cfg_files::PORTFOLIO_SPLIT_CALCULATOR_FILE,
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

    // true to import the data of the start page again before its snapshot is checked
    const bool SELECTED_START_PAGE_IMPORT = false;

    // true to check and rebuild the start page snapshot
    // views_by_id/start_page/1_start_page_details.html, which localhost_ui_tests fetches from the pod
    const bool SELECTED_START_PAGE = false;

    /**
     * the data files of the start page in the import order of the db setup (sql_db):
     * the problem and solution keywords, the problem files of the ranking rows with their reasons and
     * solutions, the main reasons and the charts; all imported as the system user like the db setup does
     * @return array the json file names with the full path
     */
    private function start_page_import_files(): array
    {
        $result = [cfg_files::MESSAGE_PATH . cfg_files::START_PAGE_DATA_FILE];
        foreach (cfg_files::SAMPLE_VIEW_DATA_FILES as $filename) {
            if (str_starts_with($filename, cfg_files::DATA_START_PAGE_PATH)) {
                $result[] = $filename;
            }
        }
        $result[] = cfg_files::PROBLEM_MAIN_REASONS_FILE;
        $result[] = cfg_files::START_PAGE_CHARTS_FILE;
        return $result;
    }

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
        $tl->ui_test_cache($this->usr_dev, $this, $msg_ui);
        $u_env->init_unit_tests($this->usr1);
        $this->step('unit test env prepared');

        /*
         * unit testing - without users
         */

        // run the selected unit tests in the order of all_unit_tests::run_unit
        // the frontend with the dummy type cache that all_tests::run_all_tests hands over e.g. to word_tests
        $ui_unit = new frontend('unit tests');
        $ui_unit->load_dummy_cache_from_test_resources($msg_ui, file_get_contents(test_files::TYPE_LISTS_CACHE));
        //new lib_tests()->run(new all_tests());
        //new math_tests()->run($this);
        //new system_tests()->run($this);
        //new config_tests()->run($this);
        //new ip_range_tests()->run($this);
        new coding_rule_tests()->run($this);
        //new change_link_validation_tests()->run($this);
        //new sql_tests()->run($this);
        //new sys_log_tests()->run($this);
        // the prepared statements of the link change list by object
        new change_log_tests()->run($this);
        //new job_tests()->run($this);
        //new pod_tests()->run($this);
        //new user_tests()->run($this);
        //new user_list_tests()->run($this);
        //new base_object_tests()->run($this);
        //new sandbox_tests()->run($this);
        //new language_tests()->run($this);
        //new type_tests()->run($this);
        //new db_cache_tests()->run($this);
        //new horizontal_tests()->run($this);
        //new permission_tests()->run($this);
        //new system_view_ui_tests()->run($this);
        // assert_sql_update_user takes a sandbox_multi too, so check that it still works for a word
        new word_tests()->run($this, $ui_unit->dto->typ_lst_cache);
        //new word_list_tests()->run($this);
        //new verb_tests()->run($this);
        //new triple_tests()->run($this);
        //new triple_list_tests()->run($this);
        //new phrase_tests()->run($this);
        //new phrase_list_tests()->run($this);
        // the group save uses sandbox_multi::save_changes
        new group_tests()->run($this);
        //new group_list_tests()->run($this);
        //new term_tests()->run($this);
        //new term_list_tests()->run($this);
        //new source_tests()->run($this);
        //new source_list_tests()->run($this);
        //new ref_tests()->run($this);
        // the user row builders of sandbox_multi for the value and the time series tables
        new value_tests()->run($this);
        //new value_list_tests()->run($this);
        //new formula_tests()->run($this);
        //new formula_calc_tests()->run($this);
        //new formula_list_tests()->run($this);
        //new formula_link_tests()->run($this);
        //new element_tests()->run($this);
        //new element_list_tests()->run($this);
        //new expression_tests()->run($this);
        // the user row builders of sandbox_multi for the result tables
        new result_tests()->run($this);
        //new result_list_tests()->run($this);
        //new figure_tests()->run($this);
        //new figure_list_tests()->run($this);
        //new view_tests()->run($this);
        //new view_list_tests()->run($this);
        //new term_view_tests()->run($this);
        //new component_tests()->run($this);
        //new component_list_tests()->run($this);
        //new component_link_tests()->run($this);
        //new component_link_list_tests()->run($this);
        //new import_tests()->run($this);
        //new wikidata_tests()->run($this);
        //new db_setup_tests()->run($this);
        //new api_tests()->run_openapi_test($this);
        //new base_ui_tests()->run($this);

        // restore the global vars that may be overwritten if additional tests are activated
        $db_con = $global_db_con;

        /*
         * db testing - prepare
         */

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $app = new application();
        $db_con = $app->start("reload cache after unit testing", false, true);
        $this->step('db reopened and cache reloaded');

        // create the testing users
        $this->set_users();
        $usr = $this->usr1;
        $this->step('test users set');

        if ($usr->id > 0) {

            // a code link csv row added since the last database setup (e.g. a new component type
            // or view style) reaches the database only by the daily consistency check, so write
            // the csv rows now as the system user and reload the types that the import resolves
            global $sys;
            $sys_msg = new user_message_cfg(user::system());
            $msg = new user_message_cfg($usr);
            $db_con->db_fill_code_links($sys_msg);
            $this->step('code links filled');
            $sys->load_type_lists($db_con, $sys_msg);
            $this->step('type lists loaded');

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


            $ui = new frontend('api based ui tests');
            $ui->load_cache($msg_ui);
            $this->step('frontend cache loaded');
            // the html renderers read the type cache from the global $ui_sys; point it at the just
            // loaded frontend cache so the render does not depend on a stale cache of the unit tests
            // (same pattern as url_test_base and /http/view.php)
            global $ui_sys;
            $ui_sys = $ui->dto;
            // an empty frontend config returns the shared defaults e.g. for the date format
            $ui_sys->cfg = new config_ui();

            /*
             * prepare db testing
             */

            //$t_db->type_list_recreate($this, $usr);


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
            $imf = new import_file();
            //$test_name = 'the config.yaml is imported again';
            //$imf->import_config_yaml(user::system(), $sys_msg);
            //$this->assert_msg($test_name, $sys_msg, $this::TIMEOUT_LIMIT_IMPORT);
            //$sys_msg->reset();
            //global $cfg;
            //$cfg = new config_numbers($usr);
            //$cfg->load_cfg($sys_msg, null, $usr);
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
            if (self::SELECTED_START_PAGE_IMPORT) {
                foreach ($this->start_page_import_files() as $import_file) {
                    $test_name = 'the start page file ' . basename($import_file) . ' is imported again';
                    $imp_msg = $imf->json_file($import_file, user::system(), false);
                    $this->assert_msg($test_name, $imp_msg, $this::TIMEOUT_LIMIT_IMPORT);
                }
            }
            // a types api call during the imports may have cached the system phrases without the
            // phrases imported after it, so the pod reads the types again like after the db setup
            new db_cache(user::system())->del_types($sys_msg);
            $this->step('pod type cache cleared');


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

            // the start page with its main columns and variance fetched from the pod
            if (self::SELECTED_START_PAGE) {
                new localhost_ui_tests()->run_start_page($this);
            }


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
            //new system_read_tests()->run($this);
            //new system_views_read_tests()->run($this);
            // the start page steps up to the results of the initial effort, after which the page stops
            //$msk_tests->run_start_page_steps($this);
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
            //$msk_tests->run_portfolio_split_calculator($this);
            //$msk_tests->run_portfolio_split_calculator_diff($this);
            //$msk_tests->run_portfolio_split_calculator_cases($this);
            // the Swiss NPP vote pages, their differences to the sample, the editor preview compared with the page
            // and the sensitivity table of the pdf; the calculator editor of the PV lifetime balance as its regression
            //$msk_tests->run_npp_ch_vote($this);
            //$msk_tests->run_npp_ch_vote_editor($this);
            //$msk_tests->run_npp_sensitivity($this);
            //$msk_tests->run_calculator_editor($this);

            // the view snapshots above show only the seed data, so the test entries are created after them
            // like in test.php (all_unit_read_tests) for the read tests below
            //$t_db->create_test_db_entries($this);
            //$t_db->create_unit_test_db_entries($this);
            // the db read tests in the order of all_unit_read_tests::run_unit_db_tests
            //new sql_db_read_tests()->run($this);
            //new user_read_tests()->run($this);
            //new protection_read_tests()->run($this);
            //new share_read_tests()->run($this);
            //new horizontal_read_tests()->run($this);
            //new word_read_tests()->run($this);
            //new word_list_read_tests()->run($this);
            //new verb_read_tests()->run($this);
            //new triple_read_tests()->run($this);
            //new triple_list_read_tests()->run($this);
            //new phrase_read_tests()->run($this);
            // the phrases starting with "mathemat" without a leftover test triple
            //new phrase_list_read_tests()->run($this);
            //new group_read_tests()->run($this);
            //new term_read_tests()->run($this);
            //new term_list_read_tests()->run($this);
            //new value_read_tests()->run($this);
            // the inhabitants of all years, which needs the seed values without an exclusion of the test user
            //new value_list_read_tests()->run($this);
            //new formula_read_tests()->run($this);
            //new formula_list_read_tests()->run($this);
            //new expression_read_tests()->run($this);
            //new element_list_read_tests()->run($this);
            //new result_list_read_tests()->run($this);
            //new view_read_tests()->run($this);
            //new view_list_read_tests()->run($this);
            //new component_read_tests()->run($this);
            //new component_list_read_tests()->run($this);
            //new source_read_tests()->run($this);
            //new ref_read_tests()->run($this);
            //new language_read_tests()->run($this);
            //new change_log_read_tests()->run($this);
            //new sys_log_read_tests()->run($this);
            //new job_read_tests()->run($this);
            //new type_lists_ui_tests()->run($this, $ui);
            //new word_ui_read_tests()->run($this, $ui);
            //new start_ui_read_tests()->run($this, $ui);
            //new export_read_tests()->run($this);
            // the data of a phrase for api/phraseData, checked against the hand made sample
            //new phrase_data_read_tests()->run($this);
            //$msk_tests->run_web_frontend($this);
            //new api_tests()->run($this);
            //new graph_tests()->run($this);
            $this->cleanup($msg);


            /*
             * user interface
             */

            // the html ui tests in the order of all_ui_tests::run
            // the footer about link and the renderers whose url_old links are replaced
            new base_ui_tests()->run($this);
            //new user_ui_tests()->run($this);
            //new horizontal_ui_tests()->run($this, $ui);
            new word_ui_tests()->run($this, $ui->dto->typ_lst_cache);
            //new word_list_ui_tests()->run($this);
            //new verb_ui_tests()->run($this);
            //new verb_list_ui_tests()->run($this);
            //new triple_ui_tests()->run($this, $ui);
            //new triple_list_ui_tests()->run($this);
            //new phrase_ui_tests()->run($this);
            //new phrase_list_ui_tests()->run($this);
            //new source_ui_tests()->run($this);
            //new reference_ui_tests()->run($this);
            //new reference_list_ui_tests()->run($this);
            //new group_ui_tests()->run($this);
            //new value_ui_tests()->run($this);
            //new value_list_ui_tests()->run($this);
            //new start_page_ui_tests()->run($this);
            //new formula_ui_tests()->run($this);
            //new formula_list_ui_tests()->run($this);
            //new number_format_ui_tests()->run($this);
            new result_ui_tests()->run($this);
            //new result_list_ui_tests()->run($this);
            //new figure_ui_tests()->run($this);
            //new figure_list_ui_tests()->run($this);
            //new term_ui_tests()->run($this);
            //new term_list_ui_tests()->run($this);
            new view_ui_tests()->run($this, $ui);
            //new view_list_ui_tests()->run($this);
            //new component_ui_tests()->run($this);
            //new component_list_ui_tests()->run($this);
            //new process_catch_ui_tests()->run($this);
            //new use_case_ui_tests()->run($this);
            //new phrase_data_ui_tests()->run($this);
            //new language_ui_tests()->run($this);
            // the link change list that is now loaded with a prepared statement
            new change_log_ui_tests()->run($this);
            //new sys_log_ui_tests()->run($this);
            new job_ui_tests()->run($this);
            //new config_tree_ui_tests()->run($this);
            //new localhost_ui_tests()->run($this);
            //new start_ui_tests()->run($this);
            //new spacial_cases_ui_tests()->run($this, $ui);

            // check and update the fixed csv files
            // e.g. to have an indication which words might be missing due to the code changes
            $t_db->csv_recreate($sys_msg);

            /*
             * url
             */

            // the url snapshot tests and the same workflows as db write tests in the order of all_workflow_tests::run
            //new word_url_tests()->run($this);
            //new triple_url_tests()->run($this);
            //new formula_url_tests()->run($this);
            //new source_url_tests()->run($this);
            //new view_url_tests()->run($this);
            //new component_url_tests()->run($this);
            //new ref_url_tests()->run($this);
            //new value_url_tests()->run($this);
            //new signup_url_tests()->run($this);
            //new login_url_tests()->run($this);
            //new word_write_url_tests()->run($this);
            //new triple_write_url_tests()->run($this);
            //new formula_write_url_tests()->run($this);
            //new source_write_url_tests()->run($this);
            //new view_write_url_tests()->run($this);
            //new component_write_url_tests()->run($this);
            //new ref_write_url_tests()->run($this);
            // the value writes of the url workflows go through sandbox_multi::save_changes
            new value_write_url_tests()->run($this);
            //new signup_write_url_tests()->run($this);
            //new login_write_url_tests()->run($this);

            /*
             * db write
             */

            // cleanup - fallback delete
            //$this->cleanup_objects_ex_user();

            // run the selected db write tests
            // the change and delete of a value given only by its id, as the update put does it
            // the change and the exclusion of a value given only by its id write the user row
            new value_by_id_write_tests()->run($this);
            // the triple description change of the calculator editor and its undo
            //new update_put_write_tests()->run_use_case($this);
            // the messages of the calculator editor on the Swiss NPP vote and the PV lifetime balance and their undo,
            // which change and remove values as a user who does not own them
            new update_put_write_tests()->run_npp_vote($this);
            //new update_put_write_tests()->run_notes_and_sources($this);
            new update_put_write_tests()->run_scenarios($this);
            //new update_put_write_tests()->run_npp_links($this);
            // the test entries for the write tests like in test.php (all_unit_write_tests)
            $t_db->create_test_db_entries($this);
            //new word_write_tests()->run($this);
            //new triple_write_tests()->run($this);
            // reads the test values of create_test_values on seed words e.g. the inhabitants of the canton Zurich
            // and its cleanup loads the value link changes with the link change list
            new value_write_tests()->run($this);
            //new formula_write_tests()->run($this);
            //new job_write_tests()->run($this);
            //new job_write_tests()->run_list($this);
            //new view_write_tests()->run($this);
            // reads the seed values of 2019 and 2020 without changing them
            //new formula_trigger_tests()->run($this);
            // the db write tests in the order of all_unit_write_tests::run_db_write_tests
            //run_system_test($this);
            // the global tests and lib_tests need an all_tests object with the test users
            //$all = new all_tests();
            //$all->set_users();
            //run_user_test($all);
            //run_db_link_test($all);
            //run_sandbox_test($all);
            //new lib_tests()->run($all);
            //new user_write_tests()->run($this);
            //new user_log_write_tests()->run($this);
            //new sys_log_write_tests()->run($this);
            //new db_cache_page_write_tests()->run($this);
            //new horizontal_write_tests()->run($this);
            //$t_db->create_test_db_entries($this);

            //new word_write_tests()->run($this);
            //new word_list_write_tests()->run($this);
            //new verb_write_tests()->run($t);
            //new triple_write_tests()->run($this);
            //new phrase_write_tests()->run($t);
            //new phrase_list_write_tests()->run($t);
            // the group save uses sandbox_multi::save_changes
            new group_write_tests()->run($this);
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
            //new update_put_write_tests()->run($this);
            //run_export_test($this);
            //run_permission_test($this);
            //run_legacy_test($all);
            //run_math_test($all);
            //run_value_ui_test($this);
            //run_formula_ui_test($this);
            //run_word_ui_test($this);
            //run_word_display_test($all);
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $this);

            //$import = new import_file();
            //$import->import_test_files($usr);

            $this->cleanup($msg);
            $this->check_cleanup($msg, library::class_to_name(sandbox::class));

            /*
             * final checks
             */

            // no test row is left after the write tests, the files with not yet fixed ids and the
            // type list api message are refreshed and the html snapshots are checked like in test.php
            $t_db->csv_leftover_check($sys_msg);
            //$t_db->update_files_with_not_yet_fixed_db_id($this, $sys_msg);
            //$t_db->type_list_check($this, $sys_msg);
            // the html snapshots must not change by the replaced url_old links
            new coding_rule_tests()->run_html_snapshots($this);

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