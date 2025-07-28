<?php

/*

    test/utils/test_api.php - quick internal check of the open api definition versus the code
    -----------------------

    to activate the yaml support on debian use
    sudo apt-get update
    sudo apt-get install php-yaml

    and if needed for the api test
    service apache2 restart


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
use html\const\paths as html_paths;

include_once paths::MODEL_LOG . 'change_log.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_LOG . 'change_log_list.php';
include_once paths::MODEL_SYSTEM . 'job.php';
include_once html_paths::HELPER . 'data_object.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_fields.php';

use cfg\component\component;
use cfg\component\component_list;
use cfg\formula\formula;
use cfg\formula\formula_list;
use cfg\group\group;
use cfg\helper\type_lists;
use cfg\language\language;
use cfg\language\language_form;
use cfg\phrase\phrase_list;
use cfg\phrase\phrase_type;
use cfg\phrase\term_list;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\system\job;
use cfg\system\sys_log_list;
use cfg\user\user;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\view\view_list;
use cfg\word\triple;
use cfg\word\word;
use cfg\word\word_list;
use html\helper\config;
use html\helper\data_object as data_object_dsp;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\word\word as word_dsp;
use shared\api;
use shared\const\triples;
use shared\const\users;
use shared\enum\change_fields;
use shared\helper\Config as shared_config;
use shared\library;
use shared\const\components;
use shared\const\formulas;
use shared\const\refs;
use shared\const\sources;
use shared\const\values;
use shared\const\views;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class api_tests
{
    // path
    const OPEN_API_PATH = 'src/main/resources/openapi/zukunft_com_api.yaml';

    const API_PATH = 'api/';
    const PHP_DEFAULT_FILENAME = 'index.php';


    /*
     * do it
     */

    /**
     * execute the API test using localhost
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'api ';
        $t->header($ts);

        $t->assert_api_get(user::class, users::SYSTEM_TEST_ID);
        $t->assert_api_get_by_text(user::class, users::SYSTEM_TEST_NAME);
        $t->assert_api_get_by_text(user::class, users::SYSTEM_TEST_EMAIL, api::URL_VAR_EMAIL);
        $t->assert_api_get(word::class);
        $t->assert_api_get_json(word::class, api::URL_VAR_WORD_ID);
        $t->assert_api_get_by_text(word::class, words::MATH);
        $t->assert_api_get(verb::class);
        $t->assert_api_get_by_text(verb::class, verbs::NOT_SET_NAME);
        $t->assert_api_get(triple::class);
        //$t->assert_api_get_by_text(triple::class, triples::TN_READ);
        //$t->assert_api_get(phrase::class);
        // the value contains only the phrase id and name in the api message because the phrase are expected to be cached in the frontend
        $t->assert_api_get(value::class, values::PI_ID);
        $t->assert_api_get(formula::class);
        $t->assert_api_get_by_text(formula::class, formulas::SCALE_TO_SEC);
        $t->assert_api_get(view::class);
        $t->assert_api_get(view::class, 1, 1);
        $t->assert_api_get_by_text(view::class, views::START_NAME);
        $t->assert_api_get(component::class);
        $t->assert_api_get_by_text(component::class, components::WORD_NAME);
        $t->assert_api_get(source::class, sources::SIB_ID);
        $t->assert_api_get_by_text(source::class, sources::SIB);
        $t->assert_api_get(ref::class, refs::PI_ID);
        $t->assert_api_get(job::class);
        $t->assert_api_get(phrase_type::class);
        $t->assert_api_get(language::class);
        $t->assert_api_get(language_form::class);


        $t->subheader($ts . 'api list');

        $t->assert_api_get_list(type_lists::class);
        $t->assert_api_get_list(word_list::class, [1, 2, words::PI_ID]);
        $t->assert_api_get_list(word_list::class, words::MATH, api::URL_VAR_PATTERN);
        $t->assert_api_get_list(phrase_list::class, [words::MATH_ID, words::CONST_ID, words::PI_SYMBOL_ID, -1, -2]);
        $t->assert_api_get_list(phrase_list::class, words::MATH, api::URL_VAR_PATTERN);
        $t->assert_api_get_list(term_list::class, [1, -1, 2, -2]);
        $t->assert_api_get_list(formula_list::class, [1]);
        $t->assert_api_get_list(view_list::class, views::START_NAME, api::URL_VAR_PATTERN);
        $t->assert_api_get_list(component_list::class, 2, 'view_id');

        $t->assert_api_chg_list(word::class,words::MATH_ID);
        $t->assert_api_chg_list(word::class,words::MATH_ID, change_fields::FLD_WORD_NAME);

        $t->assert_api_get_list(
            sys_log_list::class,
            [1, 2], 'ids',
            'sys_log_list_api',
            true);
        // $t->assert_rest(new word($usr, words::TN_READ));
        // TODO add value_list tests for prime, normal and big value tables
        // TODO add a test case for empty list, no key found, and more  values than the page size
        //$t->assert_api_get_list(value_list::class, values::PI_ID);
        // TODO add result_list tests
        // TODO add figure_list tests


        $t->subheader($ts . 'api config');

        $cfg = new config();
        $cfg->load();
        $test_name = 'the default configuration api message must at least contain the pod name';
        $t->assert($test_name, $cfg->get_by([words::POD, words::URL]), POD_NAME);

        $cfg_all = new config();
        $cfg_all->load(api::CONFIG_ALL);
        $test_name = 'there must be more configuration values than the frontend configuration values';
        // TODO activate
        //$t->assert_greater($test_name, $cfg->count(), $cfg_all->count());
        $test_name = 'the complete configuration api message must at least contain the pod name';
        // TODO activate
        //$t->assert($test_name, $cfg->get_by([words::POD, words::URL]), POD_NAME);

        // TODO get frontend configuration values and check if frontend and user config contains less values
        // TODO check if requesting an unknown config part returns an error message


        $t->subheader($ts . 'api frontend config');

        $cfg = new config();
        $cfg->load(api::CONFIG_FRONTEND);
        $test_name = 'at least one frontend configuration value must be loaded via api message';
        $t->assert_not($test_name, $cfg->count(), 0);
        $test_name = 'the frontend configuration must at least contain some user number format settings';
        // TODO activate
        // $t->assert($test_name, $cfg->get_by([words::USER, triples::NUMBER_FORMAT]), null);
        $test_name = 'the frontend configuration must at least contain the user settings for the decimal places';
        // TODO activate
        //$t->assert($test_name, $cfg->get_by([triples::NUMBER_FORMAT, triples::PERCENT_DECIMAL]), shared_config::DEFAULT_PERCENT_DECIMALS);
        $test_name = 'the frontend configuration should not contain the database block size settings';
        // TODO activate
        //$t->assert($test_name, $cfg->get_by([words::DATABASE, triples::BLOCK_SIZE]), null);


        $t->subheader($ts . 'api user config');

        $cfg = new config();
        $cfg->load(api::CONFIG_USER);
        $test_name = 'at least one frontend configuration value must be loaded via api message';
        $t->assert_not($test_name, $cfg->count(), 0);
        $test_name = 'the frontend configuration must at least contain some user number format settings';


        $t->subheader($ts . 'api id and name select');

        // load the frontend objects via api call
        $test_name = 'api id and name call of a word';
        $wrd_zh = new word_dsp();
        $wrd_zh->load_by_name(words::ZH);
        $wrd_zh->load_by_id($wrd_zh->id());
        $t->assert($test_name, $wrd_zh->name(), words::ZH);

        $test_name = 'api id and name call of a phrase';
        $phr_zh = new phrase_dsp();
        $phr_zh->load_by_name(words::ZH);
        $phr_zh->load_by_id($phr_zh->id());
        $t->assert($test_name, $phr_zh->name(), words::ZH);

    }

    /**
     * get the api message and forward it to the ui
     * TODO move all other HTML frontend tests here
     *
     * @param test_cleanup $t
     * @return void
     */
    function run_ui_test(test_cleanup $t): void
    {
        // create the stable test context that is not based on the database so that the test results rarely change
        $cfg = new data_object_dsp();
        $cfg->set_view_list($t->view_list_dsp());
        // create the test pages
        $t->assert_view(views::WORD, $t->usr1, new word($t->usr1), 1);
        $t->assert_view(views::WORD_ADD, $t->usr1, new word($t->usr1));
        $t->assert_view(views::WORD_EDIT, $t->usr1, new word($t->usr1), 1, $cfg);
        $t->assert_view(views::WORD_DEL, $t->usr1, new word($t->usr1), 1);
        $t->assert_view(views::VERB, $t->usr1, new verb(), 1);
        $t->assert_view(views::VERB_ADD, $t->usr1, new verb());
        $t->assert_view(views::VERB_EDIT, $t->usr1, new verb(), 1);
        $t->assert_view(views::VERB_DEL, $t->usr1, new verb(), 1);
        //$t->assert_view(views::TRIPLE, $t->usr1, new triple($t->usr1), 1);
        $t->assert_view(views::TRIPLE_ADD, $t->usr1, new triple($t->usr1));
        $t->assert_view(views::TRIPLE_EDIT, $t->usr1, new triple($t->usr1), 1);
        $t->assert_view(views::TRIPLE_DEL, $t->usr1, new triple($t->usr1), 1);
        //$t->assert_view(views::SOURCE, $t->usr1, new source($t->usr1), 1);
        $t->assert_view(views::SOURCE_ADD, $t->usr1, new source($t->usr1));
        $t->assert_view(views::SOURCE_EDIT, $t->usr1, new source($t->usr1), 1);
        $t->assert_view(views::SOURCE_DEL, $t->usr1, new source($t->usr1), 1);
        // TODO add:
        // REF
        $t->assert_view(views::REF_ADD, $t->usr1, new ref($t->usr1));
        // VALUE
        // GROUP
        //$t->assert_view(views::GROUP_ADD, $t->usr1, new group($t->usr1));
        // FORMULA
        $t->assert_view(views::FORMULA_ADD, $t->usr1, new formula($t->usr1));
        $t->assert_view(views::FORMULA_EDIT, $t->usr1, new formula($t->usr1), 1);
        $t->assert_view(views::FORMULA_DEL, $t->usr1, new formula($t->usr1), 1);
        // FORMULA TEST
        // RESULT
        // VIEW
        $t->assert_view(views::VIEW_ADD, $t->usr1, new view($t->usr1));
        $t->assert_view(views::VIEW_EDIT, $t->usr1, new view($t->usr1), 1);
        $t->assert_view(views::VIEW_DEL, $t->usr1, new view($t->usr1), 1);
        // COMPONENT
        $t->assert_view(views::COMPONENT_ADD, $t->usr1, new component($t->usr1));
        $t->assert_view(views::COMPONENT_EDIT, $t->usr1, new component($t->usr1), 1);
        $t->assert_view(views::COMPONENT_DEL, $t->usr1, new component($t->usr1), 1);
        // USER
        // LANGUAGE
        // SYS LOG
        // CHANGE LOG
        // IMPORT
        // EXPORT
        // PROCESS
        // FIND
        //$t->assert_view(view_shared::DSP_COMPONENT_ADD, $t->usr1, new component($t->usr1), 1);
        // TODO add the frontend reaction tests e.g. call the view.php script with the reaction to add a word


        // start the test section (ts)
        $ts = 'unit web frontend ';
        $t->header($ts);

        $html = new html_base();
        $target = htmlspecialchars(trim('<html> <head> <title>Header test (zukunft.com)</title> <link rel="stylesheet" type="text/css" href="../../../main/resources/style/style.css" /> </head> <body class="center_form">'));
        $target = htmlspecialchars(trim('<title>Header test (zukunft.com)</title>'));
        $result = htmlspecialchars(trim($html->header('Header test', 'center_form')));
        $t->dsp_contains(", dsp_header", $target, $result);

        // check if the about page contains at least some basic keywords
        // TODO activate Prio 3: $result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
        $target = 'zukunft.com AG';
        if (strpos($result, $target) > 0) {
            $result = $target;
        } else {
            $result = '';
        }
        // about does not return a page for unknown reasons at the moment
        // $t->dsp_contains(', frontend about.php '.$result.' contains at least ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        $is_connected = $t->dsp_web_test(
            'http/privacy_policy.html',
            'Swiss purpose of data protection',
            ', frontend privacy_policy.php contains at least');
        $is_connected = $t->dsp_web_test(
            'http/error_update.php?id=1',
            'not permitted',
            ', frontend error_update.php contains at least', $is_connected);
        $t->dsp_web_test(
            'http/find.php?pattern=' . words::ABB,
            words::ABB,
            ', frontend find.php contains at least', $is_connected);



    }

    /**
     * test the database update function via simulated api calls of all standard user sandbox objects
     * @return void
     */
    function test_api_write_no_rest_all(test_cleanup $t): void
    {
        $this->test_api_write_no_rest(word::class, $t->word_put_json(), $t->word_post_json(), $t);
        $this->test_api_write_no_rest(source::class, $t->source_put_json(), $t->source_post_json(), $t);
    }

    /**
     * test the database update function via real api calls for all user sandbox objects
     * @return void
     */
    function test_api_write_all(test_cleanup $t): void
    {
        $this->test_api_write(word::class, $t->word_put_json(), $t->word_post_json(), $t);
        $this->test_api_write(source::class, $t->source_put_json(), $t->source_post_json(), $t);
    }

    /**
     * test the database update function via simulated api calls for one user sandbox object
     * @param string $class the class name of the object to test
     * @param array $add_data the json that should be used to create the user sandbox object
     * @param array $upd_data the json that should be used to update the user sandbox object
     * @return void
     */
    function test_api_write_no_rest(string $class, array $add_data, array $upd_data, test_cleanup $t): void
    {
        // create a new object via api call
        $id = $t->assert_api_put_no_rest($class, $add_data);
        // check if the object has been created
        // the id is ignored in the compare because it depends on the number of rows in the database that cannot be controlled by the test
        $t->assert_api_get($class, $id, 0, $add_data, true);
        // update the previous created test object
        $id = $t->assert_api_post_no_rest($class, $id, $upd_data);
        // remove the previous created test object
        $t->assert_api_del_no_rest($class, $id);
        // check the previous created test object really has been removed
        //$t->assert_api_get($class, $id, $data, true);
    }

    /**
     * test the database update function via real api calls for one user sandbox object
     * @param string $class the class name of the object to test
     * @param array $add_data the json that should be used to create the user sandbox object
     * @param array $upd_data the json that should be used to update the user sandbox object
     * @return void
     */
    function test_api_write(string $class, array $add_data, array $upd_data, test_cleanup $t): void
    {
        // create a new source via api call
        // e.g. curl -i -X PUT -H 'Content-Type: application/json' -d '{"pod":"zukunft.com","type":"source",user_db::FLD_ID:2,"user":"zukunft.com system test","version":"0.0.3","timestamp":"2023-01-23T00:07:23+01:00","body":{"id":0,"name":"System Test Source API added","description":"System Test Source Description API","type_id":4,"url":"https:\/\/api.zukunft.com\/"}}' http://localhost/api/source/
        $id = $t->assert_api_put($class, $add_data, true);
        if ($id != 0) {
            // check if the source has been created
            $t->assert_api_get($class, $id, 0, $add_data, true);
            //$t->assert_api_post(source::class);
            $t->assert_api_del($class, $id);
        } else {
            $lib = new library();
            log_err($class . ' cannot be added via PU API call with ' . $lib->dsp_array($add_data));
        }
    }

    /*
     * TODO
     * add the word type "key"
     * "key" forces the creation of an internal value table
     *
     * add key word test
     * assume
     * ABB (Company),Employees, 2021: 15'000
     * ABBN (Ticker),Employees, 2021: 15'100
     *
     *
     * ABBN (Ticker) is ABB (Company)
     * -> ask the user which value to use for Employees, 2021
     * -> until the user has closed the open task 15'000 is used
     *
     * if Ticker is defined as a key for companies
     * -> create a normal table with a unique key
     * -> and fields like Employees (of a Company)
     *
     * the advantage compared to a classic table setup is
     * that a smooth creation and reverse is supported
     * to move the data from the word based setup to the table based setup
     * a batch job is created and once it is finished the alternative
     * access method is used
     *
     * define ISIN as a key
     * -> street number is move to new table, but not company
     *
     */

    /**
     * check if the main parts of the openapi definition matches the code
     *
     * @param test_cleanup $t
     * @return void
     */
    function run_openapi_test(test_cleanup $t): void
    {

        // init
        $t->name = 'api->';

        // start the test section (ts)
        $ts = 'unit open API definition versus the code ';
        $t->header($ts);

        $test_name = 'check if a controller for each api tag exists';
        $result = '';
        $open_api_filename = ROOT_PATH . self::OPEN_API_PATH;
        $api_def = yaml_parse_file($open_api_filename);
        if ($api_def == null) {
            log_err('OpenAPI file ' . $open_api_filename . ' missing');
        } else {
            $tags = $api_def['tags'];
            foreach ($tags as $tag) {
                $paths = $t->get_paths_of_tag($tag['name'], $api_def);
                foreach ($paths as $path) {
                    // check if at least some controller code exists for each tag
                    $filename = ROOT_PATH . self::API_PATH . $path . '/' . self::PHP_DEFAULT_FILENAME;
                    $ctrl_code = file_get_contents($filename);
                    if ($ctrl_code == null or $ctrl_code == '') {
                        if ($result != '') {
                            $result .= ', ';
                        }
                        $result .= 'api for ' . $path . ' missing';
                    }
                }
            }
        }
        $target = '';
        $t->assert($test_name, $result, $target);

        // TODO $test_name = 'check if an api tag for each controller exists';

        // the openapi internal consistency is checked via the online swagger test
    }

}