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

include_once MODEL_LOG_PATH . 'change_log.php';
include_once MODEL_LOG_PATH . 'change_field.php';
include_once MODEL_LOG_PATH . 'change_field_list.php';
include_once MODEL_LOG_PATH . 'change_log_list.php';
include_once MODEL_SYSTEM_PATH . 'job.php';
include_once EXPORT_PATH . 'export.php';
include_once API_SYSTEM_PATH . 'type_object.php';
include_once API_PHRASE_PATH . 'phrase_type.php';
include_once API_LANGUAGE_PATH . 'language.php';
include_once API_LANGUAGE_PATH . 'language_form.php';
include_once SHARED_PATH . 'views.php';
include_once HTML_HELPER_PATH . 'data_object.php';

use api\component\component as component_api;
use api\formula\formula as formula_api;
use api\ref\ref as ref_api;
use api\ref\source as source_api;
use api\verb\verb as verb_api;
use api\view\view as view_api;
use api\word\word as word_api;
use cfg\component\component;
use cfg\component\component_list;
use cfg\formula\formula;
use cfg\formula\formula_list;
use cfg\helper\type_lists;
use cfg\system\job;
use cfg\language\language;
use cfg\language\language_form;
use cfg\log\change_field_list;
use cfg\log\change_log_list;
use cfg\phrase\phrase_list;
use cfg\phrase\phrase_type;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\sys_log_list;
use cfg\phrase\term_list;
use cfg\word\triple;
use cfg\user\user;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\view\view_list;
use cfg\word\word;
use cfg\word\word_list;
use html\phrase\phrase as phrase_dsp;
use html\word\word as word_dsp;
use html\helper\data_object as data_object_dsp;
use shared\api;
use shared\library;
use shared\views as view_shared;
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
     * @return void
     */
    function run_api_test(test_cleanup $t): void
    {

        $t->assert_api_get(user::class, user::SYSTEM_TEST_ID);
        $t->assert_api_get_by_text(user::class, user::SYSTEM_TEST_NAME);
        $t->assert_api_get_by_text(user::class, user::SYSTEM_TEST_EMAIL, api::URL_VAR_EMAIL);
        $t->assert_api_get(word::class);
        $t->assert_api_get_json(word::class, api::URL_VAR_WORD_ID);
        $t->assert_api_get_by_text(word::class, word_api::TN_READ);
        $t->assert_api_get(verb::class);
        $t->assert_api_get_by_text(verb::class, verb_api::TN_READ);
        $t->assert_api_get(triple::class);
        //$t->assert_api_get_by_text(triple::class, triple_api::TN_READ);
        //$t->assert_api_get(phrase::class);
        // the value contains only the phrase id and name in the api message because the phrase are expected to be cached in the frontend
        $t->assert_api_get(value::class, 32770);
        $t->assert_api_get(formula::class);
        $t->assert_api_get_by_text(formula::class, formula_api::TN_READ);
        $t->assert_api_get(view::class);
        $t->assert_api_get(view::class, 1, 1);
        $t->assert_api_get_by_text(view::class, view_api::TN_READ);
        $t->assert_api_get(component::class);
        $t->assert_api_get_by_text(component::class, component_api::TN_READ);
        $t->assert_api_get(source::class, source_api::TI_READ);
        $t->assert_api_get_by_text(source::class, source_api::TN_READ);
        $t->assert_api_get(ref::class, ref_api::TI_PI);
        $t->assert_api_get(job::class);
        $t->assert_api_get(phrase_type::class);
        $t->assert_api_get(language::class);
        $t->assert_api_get(language_form::class);

        $t->assert_api_get_list(type_lists::class);
        $t->assert_api_get_list(word_list::class, [1, 2, word_api::TI_PI]);
        $t->assert_api_get_list(word_list::class, word_api::TN_READ, api::URL_VAR_PATTERN);
        $t->assert_api_get_list(phrase_list::class, [1, 2, word_api::TI_PI, -1, -2]);
        $t->assert_api_get_list(phrase_list::class, word_api::TN_READ, api::URL_VAR_PATTERN);
        $t->assert_api_get_list(term_list::class, [1, -1, 2, -2]);
        $t->assert_api_get_list(formula_list::class, [1]);
        $t->assert_api_get_list(view_list::class, view_api::TN_READ, api::URL_VAR_PATTERN);
        $t->assert_api_get_list(component_list::class, 2, 'view_id');
        $t->assert_api_chg_list(
            change_log_list::class,
            api::URL_VAR_WORD_ID, 1,
            api::URL_VAR_WORD_FLD, change_field_list::FLD_WORD_NAME);
        $t->assert_api_get_list(
            sys_log_list::class,
            [1, 2], 'ids',
            'sys_log_list_api',
            true);
        // $t->assert_rest(new word($usr, word_api::TN_READ));


        // load the frontend objects via api call
        $test_name = 'api id and name call of a word';
        $wrd_zh = new word_dsp();
        $wrd_zh->load_by_name(word_api::TN_ZH);
        $wrd_zh->load_by_id($wrd_zh->id());
        $t->assert($test_name, $wrd_zh->name(), word_api::TN_ZH);

        $test_name = 'api id and name call of a phrase';
        $phr_zh = new phrase_dsp();
        $phr_zh->load_by_name(word_api::TN_ZH);
        $phr_zh->load_by_id($phr_zh->id());
        $t->assert($test_name, $phr_zh->name(), word_api::TN_ZH);

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
        $t->assert_view(view_shared::MC_WORD, $t->usr1, new word($t->usr1), 1);
        $t->assert_view(view_shared::MC_WORD_ADD, $t->usr1, new word($t->usr1));
        $t->assert_view(view_shared::MC_WORD_EDIT, $t->usr1, new word($t->usr1), 1, $cfg);
        $t->assert_view(view_shared::MC_WORD_DEL, $t->usr1, new word($t->usr1), 1);
        $t->assert_view(view_shared::MC_VERB_ADD, $t->usr1, new verb());
        $t->assert_view(view_shared::MC_VERB_EDIT, $t->usr1, new verb(), 1);
        $t->assert_view(view_shared::MC_VERB_DEL, $t->usr1, new verb(), 1);
        $t->assert_view(view_shared::MC_TRIPLE_ADD, $t->usr1, new triple($t->usr1));
        $t->assert_view(view_shared::MC_TRIPLE_EDIT, $t->usr1, new triple($t->usr1), 1);
        $t->assert_view(view_shared::MC_TRIPLE_DEL, $t->usr1, new triple($t->usr1), 1);
        $t->assert_view(view_shared::MC_SOURCE_ADD, $t->usr1, new source($t->usr1));
        $t->assert_view(view_shared::MC_SOURCE_EDIT, $t->usr1, new source($t->usr1), 1);
        $t->assert_view(view_shared::MC_SOURCE_DEL, $t->usr1, new source($t->usr1), 1);
        //$t->assert_view(view_shared::DSP_COMPONENT_ADD, $t->usr1, new component($t->usr1), 1);
        // TODO add the frontend reaction tests e.g. call the view.php script with the reaction to add a word
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
        // e.g. curl -i -X PUT -H 'Content-Type: application/json' -d '{"pod":"zukunft.com","type":"source",user::FLD_ID:2,"user":"zukunft.com system test","version":"0.0.3","timestamp":"2023-01-23T00:07:23+01:00","body":{"id":0,"name":"System Test Source API added","description":"System Test Source Description API","type_id":4,"url":"https:\/\/api.zukunft.com\/"}}' http://localhost/api/source/
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

        $t->header('Test the open API definition versus the code');

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