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

use api\batch_job_api;
use api\formula_api;
use api\language_api;
use api\language_form_api;
use api\phrase_type_api;
use api\ref_api;
use api\source_api;
use api\system_log_api;
use api\triple_api;
use api\type_api;
use api\user_api;
use api\verb_api;
use api\view_api;
use api\view_cmp_api;
use api\word_api;
use cfg\language;
use cfg\language_form;
use cfg\phrase_type;
use cfg\type_object;
use controller\controller;

class test_api extends test_new_obj
{
    // path
    const TEST_ROOT_PATH = '/home/timon/git/zukunft.com/';
    const TEST_ROOT_PATH2 = '/home/timon/PhpstormProjects/zukunft.com/';
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
    function run_api_test(): void
    {

        $this->assert_api_get(user::class, 2);
        $this->assert_api_get_by_text(user::class, user::SYSTEM_TEST_NAME);
        $this->assert_api_get_by_text(user::class, user::SYSTEM_TEST_EMAIL, controller::URL_VAR_EMAIL);
        $this->assert_api_get(word::class);
        $this->assert_api_get_json(word::class, controller::URL_VAR_WORD_ID);
        $this->assert_api_get_by_text(word::class, word_api::TN_READ);
        $this->assert_api_get(verb::class);
        $this->assert_api_get_by_text(verb::class, verb_api::TN_READ);
        $this->assert_api_get(triple::class);
        //$this->assert_api_get_by_text(triple::class, triple_api::TN_READ);
        $this->assert_api_get(value::class);
        $this->assert_api_get(formula::class);
        $this->assert_api_get_by_text(formula::class, formula_api::TN_READ);
        $this->assert_api_get(view::class);
        $this->assert_api_get_by_text(view::class, view_api::TN_READ);
        $this->assert_api_get(view_cmp::class);
        $this->assert_api_get_by_text(view_cmp::class, view_cmp_api::TN_READ);
        $this->assert_api_get(source::class);
        $this->assert_api_get_by_text(source::class, source_api::TN_READ_API);
        $this->assert_api_get(ref::class);
        $this->assert_api_get(batch_job::class);
        $this->assert_api_get(phrase_type::class);
        $this->assert_api_get(language::class);
        $this->assert_api_get(language_form::class);

        $this->assert_api_get_list(type_lists::class);
        $this->assert_api_get_list(phrase_list::class);
        $this->assert_api_get_list(term_list::class, [1, -1]);
        $this->assert_api_get_list(formula_list::class, [1]);
        $this->assert_api_chg_list(
            change_log_list::class,
            controller::URL_VAR_WORD_ID, 1,
            controller::URL_VAR_WORD_FLD, change_log_field::FLD_WORD_NAME);
        $this->assert_api_get_list(
            system_log_list::class,
            [1, 2],
            'system_log_list_api',
            true);
        // $this->assert_rest(new word($usr, word_api::TN_READ));

    }

    /**
     * test the database update function via simulated api calls of all standard user sandbox objects
     * @return void
     */
    function test_api_write_no_rest_all(): void
    {
        $this->test_api_write_no_rest(word::class, $this->word_put_json(), $this->word_post_json());
        $this->test_api_write_no_rest(source::class, $this->source_put_json(), $this->source_post_json());
    }

    /**
     * test the database update function via real api calls for all user sandbox objects
     * @return void
     */
    function test_api_write_all(): void
    {
        $this->test_api_write(word::class, $this->word_put_json(), $this->word_post_json());
        $this->test_api_write(source::class, $this->source_put_json(), $this->source_post_json());
    }

    /**
     * test the database update function via simulated api calls for one user sandbox object
     * @param string $class the class name of the object to test
     * @param array $add_data the json that should be used to create the user sandbox object
     * @param array $upd_data the json that should be used to update the user sandbox object
     * @return void
     */
    function test_api_write_no_rest(string $class, array $add_data, array $upd_data): void
    {
        // create a new object via api call
        $id = $this->assert_api_put_no_rest($class, $add_data);
        // check if the object has been created
        // the id is ignored in the compare because it depends on the number of rows in the database that cannot be controlled by the test
        $this->assert_api_get($class, $id, $add_data, true);
        // update the previous created test object
        $id = $this->assert_api_post_no_rest($class, $id, $upd_data);
        // remove the previous created test object
        $this->assert_api_del_no_rest($class, $id);
        // check the previous created test object really has been removed
        //$this->assert_api_get($class, $id, $data, true);
    }

    /**
     * test the database update function via real api calls for one user sandbox object
     * @param string $class the class name of the object to test
     * @param array $add_data the json that should be used to create the user sandbox object
     * @param array $upd_data the json that should be used to update the user sandbox object
     * @return void
     */
    function test_api_write(string $class, array $add_data, array $upd_data): void
    {
        // create a new source via api call
        // e.g. curl -i -X PUT -H 'Content-Type: application/json' -d '{"pod":"zukunft.com","type":"source","user_id":2,"user":"zukunft.com system test","version":"0.0.3","timestamp":"2023-01-23T00:07:23+01:00","body":{"id":0,"name":"System Test Source API added","description":"System Test Source Description API","type_id":4,"url":"https:\/\/api.zukunft.com\/"}}' http://localhost/api/source/
        $id = $this->assert_api_put($class, $add_data, true);
        if ($id != 0) {
            // check if the source has been created
            $this->assert_api_get($class, $id, $add_data, true);
            //$this->assert_api_post(source::class);
            $this->assert_api_del($class, $id);
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
     * -> streetnumber is move to new table, but not company
     *
     */

    /**
     * check if the main parts of the openapi definition matches the code
     *
     * @param testing $t
     * @return void
     */
    function run_openapi_test(testing $t): void
    {

        // init
        $t->name = 'api->';

        $t->header('Test the open API definition versus the code');

        $test_name = 'check if a controller for each api tag exists';
        $result = '';
        $open_api_filename = self::TEST_ROOT_PATH . self::OPEN_API_PATH;
        if (!file_exists($open_api_filename)) {
            $open_api_filename = self::TEST_ROOT_PATH2 . self::OPEN_API_PATH;
        }
        $api_def = yaml_parse_file($open_api_filename);
        if ($api_def == null) {
            log_err('OpenAPI file ' . $open_api_filename . ' missing');
        } else {
            $tags = $api_def['tags'];
            foreach ($tags as $tag) {
                $paths = $this->get_paths_of_tag($tag['name'], $api_def);
                foreach ($paths as $path) {
                    // check if at least some controller code exists for each tag
                    $filename = self::TEST_ROOT_PATH . self::API_PATH . $path . '/' . self::PHP_DEFAULT_FILENAME;
                    if (!file_exists($filename)) {
                        $filename = self::TEST_ROOT_PATH2 . self::API_PATH . $path . '/' . self::PHP_DEFAULT_FILENAME;
                    }
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


    /*
     * assert api
     */

    /**
     * @param object $usr_obj the user sandbox object that should be tested
     */
    function assert_api(object $usr_obj, string $filename = '', bool $contains = false): bool
    {
        $class = $usr_obj::class;
        $class = $this->class_to_api($class);
        $api_obj = $usr_obj->api_obj();
        $actual = json_decode(json_encode($api_obj), true);
        return $this->assert_api_compare($class, $actual, null, $filename, $contains);
    }

    /**
     * check if the REST PUT call returns the expected result
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $data the database id of the db row that should be used for testing
     * @return int the id of the added user sandbox object
     */
    function assert_api_put(string $class, array $data = [], bool $ignore_id = false): int
    {
        // get default data
        if ($data == array()) {
            $data = $this->source_put_json();
        }
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data_string = json_encode($data);
        $actual = json_decode($this->api_call("PUT", $url . '/', $data), true);
        $actual_text = json_encode($actual);
        $expected_raw_text = $this->file('api/' . $class . '/' . $class . '_put_response.json');
        $expected = json_decode($expected_raw_text, true);
        $expected_text = json_encode($expected);
        if ($actual == null) {
            return 0;
        } else {
            $id = 0;
            if (array_key_exists(controller::URL_VAR_ID, $actual)) {
                $id = intval($actual[controller::URL_VAR_ID]);
            } else {
                log_err('PUT api call is expected to return the id of the added record, but it returns: ' . $actual_text);
            }

            // remove the volatile id if requested
            if ($expected != null) {
                $expected = $this->json_remove_volatile($expected, $ignore_id);
                // if there is no expected result beside the volatile values switch off the compare
                if ($expected == null) {
                    $expected = $actual;
                }
            }
            if ($actual != null) {
                $actual = $this->json_remove_volatile($actual, $ignore_id);
                // if there is no actual result beside the volatile values switch off the compare
                if ($actual == null) {
                    $actual = $expected;
                }
            } else {
                log_err('PUT API call for ' . $class . ' returned an empty result');
            }

            if ($this->assert_api_compare($class, $actual, $expected)) {
                return $id;
            } else {
                return 0;
            }
        }
    }

    /**
     * check if the REST DEL call returns the expected result
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param int $id the database id of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_del(string $class, int $id = 0): bool
    {
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data = array("id" => $id);
        $actual = json_decode($this->api_call("DELETE", $url, $data), true);
        if ($actual == null) {
            return false;
        } else {
            return $this->assert_api_compare($class, $actual);
        }
    }

    /**
     * check if the API PUT call without the REST call adds the user sandbox object
     * similar to assert_api_put but without the need for a local webserver
     *
     * @param string $class the class name of the object to test
     * @param array $data the database id of the db row that should be used for testing
     * @return int the id of the created db row
     */
    function assert_api_put_no_rest(string $class, array $data = []): int
    {
        global $usr;

        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        // get default data
        if ($data == array()) {
            log_err('Data for ' . $class . ' missing in assert_api_put_no_rest');
        }
        // use the controller to get the payload from the api message
        $ctrl = new controller();
        $request_body = $ctrl->check_api_msg($data);
        // apply the payload to the backend object (add switch)
        $result = 0;
        switch ($class) {
            case word::class:
                $wrd = new word($usr);
                $result = $wrd->save_from_api_msg($request_body)->get_last_message();
                // if no message should be shown to the user the adding is expected to be fine
                // so get the row id to be able to remove the test row later
                if ($result == '') {
                    $result = $wrd->id();
                }
                break;
            case source::class:
                $src = new source($usr);
                $result = $src->save_from_api_msg($request_body)->get_last_message();
                // if no message should be shown to the user the adding is expected to be fine
                // so get the row id to be able to remove the test row later
                if ($result == '') {
                    $result = $src->id();
                }
                break;
            default:
                log_err($class . ' not yet mapped in assert_api_put_no_rest');
        }
        // if no row id is returned report the problem
        if ($result == null or $result <= 0) {
            $this->assert_fail('api write test without REST call of ' . $class . ' failed');
            return 0;
        } else {
            return $result;
        }
    }

    /**
     * check if the API POST call without the REST call updates the user sandbox object
     * similar to assert_api_put but without the need for a local webserver
     *
     * @param string $class the class name of the object to test
     * @param array $data the database id of the db row that should be used for testing
     * @return int the id of the created db row
     */
    function assert_api_post_no_rest(string $class, int $id, array $data = []): int
    {
        global $usr;

        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        // get default data
        if ($data == array()) {
            log_err('Data for ' . $class . ' missing in assert_api_put_no_rest');
        }
        // use the controller to get the payload from the api message
        $ctrl = new controller();
        $request_body = $ctrl->check_api_msg($data);
        // apply the payload to the backend object (add switch)
        $result = 0;
        switch ($class) {
            case word::class:
                $wrd = new word($usr);
                $wrd->load_by_id($id, word::class);
                $result = $wrd->save_from_api_msg($request_body)->get_last_message();
                // if no message should be shown to the user the adding is expected to be fine
                // so get the row id to be able to remove the test row later
                if ($result == '') {
                    $result = $wrd->id();
                }
                break;
            case source::class:
                $src = new source($usr);
                $src->load_by_id($id, source::class);
                $result = $src->save_from_api_msg($request_body)->get_last_message();
                // if no message should be shown to the user the adding is expected to be fine
                // so get the row id to be able to remove the test row later
                if ($result == '') {
                    $result = $src->id();
                }
                break;
            default:
                log_err($class . ' not yet mapped in assert_api_put_no_rest');
        }
        // if no row id is returned report the problem
        if ($result == null or $result <= 0) {
            $this->assert_fail('api write test without REST call of ' . $class . ' failed');
            return 0;
        } else {
            return $result;
        }
    }

    /**
     * check if the API DEL call works without the REST call
     * similar to assert_api_del but without the need for a local webserver
     *
     * @param string $class the class name of the object to test
     * @param array $data the database id of the db row that should be used for testing
     * @return int the id of the created db row
     */
    function assert_api_del_no_rest(string $class, int $id): bool
    {
        global $usr;

        // naming exception (to be removed?)
        $class = $this->class_to_api($class);

        // apply the payload to the backend object (add more switches)
        $result = new user_message();
        switch ($class) {
            case word::class:
                $wrd = new word($usr);
                $wrd->set_id($id);
                $result = $wrd->del();
                break;
            case source::class:
                $src = new source($usr);
                $src->set_id($id);
                $result = $src->del();
                break;
            default:
                log_err($class . ' not yet mapped in assert_api_del_no_rest');
        }
        // if no row id is returned report the problem
        if ($result->is_ok()) {
            return true;
        } else {
            $this->assert_fail('api write del test without REST call of ' . $class . ' failed');
            return false;
        }
    }

    /**
     * check the api message without using the real curl api
     * @param object $usr_obj the user sandbox object that should be tested
     * @param string $filename to overwrite the filename of the expected json message based on the usr_obj
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @return bool true if the check is fine
     */
    function assert_api_msg(sql_db $db_con, object $usr_obj, string $filename = '', bool $contains = false): bool
    {
        $class = $usr_obj::class;
        $class = $this->class_to_api($class);
        $api_obj = $usr_obj->api_obj();
        $api_msg = new api_message($db_con, $class);
        $api_msg->add_body($api_obj);
        $actual = json_decode(json_encode($api_msg), true);
        return $this->assert_api_compare($class, $actual, null, $filename, $contains);
    }

    /**
     * check if the REST GET call returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param int $id the database id of the db row that should be used for testing
     * @param ?array $expected if not null, the expected result
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get(string $class, int $id = 1, ?array $expected = null, bool $ignore_id = false): bool
    {
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data = array(controller::URL_VAR_ID => $id);
        // TODO check why for formula a double call is needed
        if ($class == formula::class) {
            $actual = json_decode($this->api_call("GET", $url, $data), true);
        }
        // TODO simulate other users
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        if ($actual == null) {
            log_err('GET api call for ' . $class . ' returned an empty result');
        }
        $filename = '';
        if ($class == value::class) {
            $filename = "value_non_std";
        }
        return $this->assert_api_compare($class, $actual, $expected, $filename, false, $ignore_id);
    }

    /**
     * check if the REST GET call by name returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param string $name the unique name (or any other unique text) of the db row that should be used for testing
     * @param string $field the URL field name of the unique text
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_by_text(string $class, string $name = '', string $field = controller::URL_VAR_NAME): bool
    {
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data = array($field => $name);
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        return $this->assert_api_compare($class, $actual);
    }

    /**
     * check if the REST GET call of a user sandbox objects returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $ids the database ids of the db rows that should be used for testing
     * @param string $filename to overwrite the class based filename to get the standard expected result
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_list(
        string $class,
        array  $ids = [1, 2],
        string $filename = '',
        bool   $contains = false): bool
    {
        $lib = new library();
        $url = HOST_TESTING . controller::URL_API_PATH . $lib->camelize_ex_1($class);
        $data = array("ids" => implode(",", $ids));
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        return $this->assert_api_compare($class, $actual, null, $filename, $contains);
    }

    /**
     * check if the REST GET call of user changes returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param string $id_fld the field name for the object id e.g. word_id
     * @param int $id the database id of the object to which the changes should be listed
     * @param string $fld_name the url api field name to select only some changes e.g. 'word_field'
     * @param string $fld_value the database field name to select only some changes e.g. 'view_id'
     * @return bool true if the json has no relevant differences
     */
    function assert_api_chg_list(string $class, string $id_fld = '', int $id = 1, string $fld_name = '', string $fld_value = ''): bool
    {
        $lib = new library();
        $url = HOST_TESTING . controller::URL_API_PATH . $lib->camelize_ex_1($class);
        if ($fld_name != '') {
            $data = array($id_fld => $id, $fld_name => $fld_value);
        } else {
            $data = array($id_fld => $id);
        }
        $actual = json_decode($this->api_call("GET", $url, $data), true);

        return $this->assert_api_compare($class, $actual);
    }


    /*
     * helper for assert api
     */

    /**
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $actual the actual received json array
     * @param ?array $expected if not null, the expected result
     * @param string $filename to overwrite the class based filename to get the standard expected result
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return bool true if the json has no relevant differences
     */
    function assert_api_compare(
        string $class,
        array  $actual,
        ?array $expected = null,
        string $filename = '',
        bool   $contains = false,
        bool   $ignore_id = false): bool
    {
        if ($expected == null) {
            $expected = json_decode($this->api_json_expected($class, $filename), true);
        }

        // remove the change time
        if ($actual != null) {
            $actual = $this->json_remove_volatile($actual, $ignore_id);
        }
        if ($expected != null) {
            $expected = $this->json_remove_volatile($expected, $ignore_id);
        }

        // TODO remove, for faster debugging only
        $json_actual = json_encode($actual);
        $json_expected = json_encode($expected);
        if ($contains) {
            return $this->assert($class . ' API GET', json_contains($expected, $actual), true);
        } else {
            return $this->assert($class . ' API GET', json_is_similar($expected, $actual), true);
        }
    }

    /**
     * get the expected api json message of a user sandbox object
     *
     * @param string $class the class name of the object to test
     * @param string $file to overwrite the class based filename
     * @return string with the expected json message
     */
    private function api_json_expected(string $class, string $file = ''): string
    {
        if ($file == '') {
            $file = $class;
        }
        return $this->file('api/' . $class . '/' . $file . '.json');
    }

    /**
     * adjust the class name to the api name if they does not (yet) match
     * @param string $class the class name that should be converted
     * @return string the api name
     */
    private function class_to_api(string $class): string
    {
        $result = $class;
        if ($class == view_cmp::class) {
            $result = view_cmp_api::API_NAME;
        }
        if ($class == ref::class) {
            $result = ref_api::API_NAME;
        }
        if ($class == batch_job::class) {
            $result = batch_job_api::API_NAME;
        }
        if ($class == type_object::class) {
            $result = type_api::API_NAME;
        }
        if ($class == phrase_type::class) {
            $result = phrase_type_api::API_NAME;
        }
        if ($class == language::class) {
            $result = language_api::API_NAME;
        }
        if ($class == language_form::class) {
            $result = language_form_api::API_NAME;
        }
        return $result;
    }

    /**
     * create the url based on the class name
     * @param string $class the class name that should be used
     * @return string the api url
     */
    private function class_to_url(string $class): string
    {
        $url = HOST_TESTING . controller::URL_API_PATH . $class;
        if ($class == phrase_type_api::API_NAME) {
            $url = HOST_TESTING . controller::URL_API_PATH . phrase_type_api::URL_NAME;
        }
        if ($class == language_form_api::API_NAME) {
            $url = HOST_TESTING . controller::URL_API_PATH . language_form_api::URL_NAME;
        }
        return $url;
    }

    /**
     * create the put json message based on the class name
     * @param string $class the class name that should be used
     * @return string the api url
     */
    private function class_to_put_msg(string $class): array
    {
        $put_msg = array();
        switch ($class) {
            case source::class:
                $put_msg = $this->source_put_json();
                break;
            default:
                break;
        }
        return $put_msg;
    }

    /**
     * remove all volatile fields from a given json array
     *
     * @param array $json a json array with volatile fields
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return array a json array without volatile fields
     */
    function json_remove_volatile(array $json, bool $ignore_id = false): array
    {
        return $this->json_remove_volatile_level($json, $ignore_id);
    }

    /**
     * remove the volatile fields from the current level of a given json array
     *
     * @param array $json a json array with volatile fields
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return array a json array without volatile fields
     */
    private function json_remove_volatile_level(array $json, bool $ignore_id): array
    {
        // remove the volatile fields from this level
        $json = $this->json_remove_volatile_item($json, $ignore_id);
        foreach ($json as $key => $item) {
            if (is_array($item)) {
                // remove the volatile fields from the next level
                $json[$key] = $this->json_remove_volatile_level($item, $ignore_id);
            }
        }
        return $json;
    }

    /**
     * remove a time value and key from a json that should not be used for a compare
     *
     * @param array $json a json array with volatile fields
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return array the main json without the volatile id fields
     */
    private function json_remove_volatile_item(array $json, bool $ignore_id): array
    {
        // remove or replace the volatile time fields
        $json = $this->json_remove_volatile_time_field($json, system_log::FLD_TIME_JSON);
        $json = $this->json_remove_volatile_time_field($json, system_log::FLD_TIMESTAMP_JSON);
        $json = $this->json_remove_volatile_time_field($json, change_log::FLD_CHANGE_TIME);
        $json = $this->json_remove_volatile_time_field($json, batch_job::FLD_TIME_REQUEST);
        $json = $this->json_remove_volatile_time_field($json, batch_job::FLD_TIME_START);
        $json = $this->json_remove_volatile_time_field($json, batch_job::FLD_TIME_END);

        // remove the id fields if requested
        // for tests with base load dataset the id fields should not be ignored
        // but for tests that add and remove data to table that have real data the id field should be ignored
        if ($ignore_id) {
            $json = $this->json_remove_volatile_unset_field($json, sql_db::FLD_ID);
            $json = $this->json_remove_volatile_unset_field($json, controller::URL_VAR_ID);
        }

        // replace any local test username with the standard test username
        if (array_key_exists(export::USER, $json)) {
            $actual_user = $json[export::USER];
            if ($actual_user == '::1'
                or $actual_user == '127.0.0.1'
                or 'zukunft.com system'
                or 'localhost') {
                $new_value = user::SYSTEM_TEST_NAME;
                $json = $this->json_remove_volatile_replace_field($json, export::USER, $new_value);
            }
        }

        // replace any local test user id with the standard test user id
        if (array_key_exists(export::USER_ID, $json)) {
            $user_id = $json[export::USER_ID];
            if ($user_id >= 0) {
                $user_id = user::SYSTEM_TEST_ID;
            }
            $json = $this->json_remove_volatile_replace_int_field($json, export::USER_ID, $user_id);
        }
        return $json;
    }

    /**
     * remove a time value and key from a json that should not be used for a compare
     *
     * @param array $json a json array with volatile fields
     * @param string $fld_name the field name, that should be removed
     * @return array the main json without the volatile id fields
     */
    private function json_remove_volatile_time_field(array $json, string $fld_name): array
    {
        if (array_key_exists($fld_name, $json)) {
            try {
                $actual_time = $json[$fld_name];
            } catch (Exception $e) {
                log_warning($item[$fld_name] . ' cannot be converted to a data');
                $actual_time = new DateTime('now');
            }
            $now = new DateTime('now');
            // at the moment just a fixed number of levels allowed
            if ($actual_time < $now) {
                $json = $this->json_remove_volatile_unset_field($json, $fld_name);
                unset($json[$fld_name]);
            } else {
                $new_value = (new DateTime(system_log_api::TV_TIME))->format('Y-m-d H:i:s');
                $json = $this->json_remove_volatile_replace_field($json, $fld_name, $new_value);
            }
        }
        return $json;
    }

    /**
     * remove a value and key from a json that should not be used for a compare
     *
     * @param array $json a json array with volatile fields
     * @param string $fld_name the field name, that should be removed
     * @return array the main json without the volatile id fields
     */
    private function json_remove_volatile_unset_field(
        array  $json,
        string $fld_name): array
    {
        if (array_key_exists($fld_name, $json)) {
            unset($json[$fld_name]);
        }
        return $json;
    }

    /**
     * remove a value and key from a json that should not be used for a compare
     *
     * @param array $json a json array with volatile fields
     * @param string $fld_name the field name, that should be removed
     * @param string $new_value the new field value that the field should have
     * @return array the main json without the volatile id fields
     */
    private function json_remove_volatile_replace_field(
        array  $json,
        string $fld_name,
        string $new_value): array
    {
        if (array_key_exists($fld_name, $json)) {
            $json[$fld_name] = $new_value;
        }
        return $json;
    }

    /**
     * remove a value and key from a json that should not be used for a compare
     *
     * @param array $json a json array with volatile fields
     * @param string $fld_name the field name, that should be removed
     * @param int $new_value the new field value that the field should have
     * @return array the main json without the volatile id fields
     */
    private function json_remove_volatile_replace_int_field(
        array  $json,
        string $fld_name,
        int    $new_value): array
    {
        if (array_key_exists($fld_name, $json)) {
            $json[$fld_name] = $new_value;
        }
        return $json;
    }

    /*
     * helper for openapi test
     */

    private function get_paths_of_tag(string $tag, array $api_def): array
    {
        $lib = new library();
        $paths = [];
        $api_paths = $api_def['paths'];
        foreach ($api_paths as $path_key => $path) {
            $path_name = $lib->str_right_of($path_key, '/');
            if (str_contains($path_name, '/')) {
                $path_name = $lib->str_left_of($path_name, '/');
            }
            if (array_key_exists('post', $path)) {
                $path_posts = $path['post'];
                if (array_key_exists('tags', $path_posts)) {
                    $path_tags = $path_posts['tags'];
                    foreach ($path_tags as $path_tag) {
                        if ($path_tag == $tag) {
                            if (!in_array($path_name, $paths)) {
                                $paths[] = $path_name;
                            }
                        }
                    }
                }
            }
        }

        return $paths;
    }
}