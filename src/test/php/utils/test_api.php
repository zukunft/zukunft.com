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
use api\language_api;
use api\language_form_api;
use api\phrase_type_api;
use api\ref_api;
use api\source_api;
use api\system_log_api;
use api\type_api;
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
        $this->assert_api_get(word::class);
        $this->assert_api_get_json(word::class, controller::URL_VAR_WORD_ID);
        $this->assert_api_get_by_name(word::class, word_api::TN_READ);
        $this->assert_api_get(verb::class);
        $this->assert_api_get(triple::class);
        $this->assert_api_get(value::class);
        $this->assert_api_get(formula::class);
        $this->assert_api_get(view::class);
        $this->assert_api_get(view_cmp::class);
        $this->assert_api_get(source::class);
        $this->assert_api_get(ref::class);
        $this->assert_api_get(batch_job::class);
        $this->assert_api_get(phrase_type::class);
        $this->assert_api_get(language::class);
        $this->assert_api_get(language_form::class);
        $this->assert_api_get_by_name(source::class, source_api::TN_READ_API);

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
     * test the database update function via simulated api calls
     * @return void
     */
    function test_api_write_no_rest(): void
    {
        $src_id = $this->assert_api_put_no_rest(source::class);
        $this->assert_api_del_no_rest(source::class, $src_id);
    }

    /**
     * test the database update function via real api calls
     * @return void
     */
    function test_api_write(): void
    {
        // move to api write tests
        // and create write api tests without rest call
        $this->assert_api_put(source::class);
        //$this->assert_api_post(source::class);
        $this->assert_api_del(source::class);
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
    public function run_openapi_test(testing $t): void
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
        return $this->assert_api_compare($class, $actual, $filename, $contains);
    }

    /**
     * check if the REST PUT call returns the expected result
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $data the database id of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_put(string $class, array $data = []): bool
    {
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        // get default data
        if ($data == array()) {
            $data = $this->source_put_json();
        }
        $data_string = json_encode($data);
        $actual = json_decode($this->api_call("PUT", $url, $data), true);
        if ($actual == null) {
            return false;
        } else {
            return $this->assert_api_compare($class, $actual);
        }
    }

    /**
     * check if the REST DEL call returns the expected result
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $data the database id of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_del(string $class, array $data = []): bool
    {
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        // get default data
        if ($data == array()) {
            $data = $this->source_put_json();
        }
        $data_string = json_encode($data);
        $actual = json_decode($this->api_call("DELETE", $url, $data), true);
        if ($actual == null) {
            return false;
        } else {
            return $this->assert_api_compare($class, $actual);
        }
    }

    /**
     * check if the API PUT call works without the REST call
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
            $data = $this->source_put_json();
        }
        // use the controller to get the payload from the api message
        $ctrl = new controller();
        $request_body = $ctrl->check_api_msg($data);
        // apply the payload to the backend object (add switch)
        $src = new source($usr);
        $result = $src->add_from_api_msg($request_body)->get_last_message();
        // if no message should be shown to the user the adding is expected to be fine
        // so get the row id to be able to remove the test row later
        if ($result == '') {
            $result = $src->id();
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
        // apply the payload to the backend object (add switch)
        $src = new source($usr);
        $src->set_id($id);
        $result = $src->del();
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
        return $this->assert_api_compare($class, $actual, $filename, $contains);
    }

    /**
     * check if the REST GET call returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param int $id the database id of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get(string $class, int $id = 1, bool $contains = false): bool
    {
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data = array("id" => $id);
        // TODO check why for formula a double call is needed
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        return $this->assert_api_compare($class, $actual, '', $contains);
    }

    /**
     * check if the REST GET call by name returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param string $name the unique name of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_by_name(string $class, string $name = ''): bool
    {
        $url = HOST_TESTING . '/api/' . $class;
        $data = array("name" => $name);
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        return $this->assert_api_compare($class, $actual);
    }

    /**
     * check if the REST GET call of a user sandbox objects returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array $ids the database ids of the db rows that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_list(
        string $class,
        array  $ids = [1, 2],
        string $filename = '',
        bool   $contains = false): bool
    {
        $url = HOST_TESTING . '/api/' . camelize($class);
        $data = array("ids" => implode(",", $ids));
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        return $this->assert_api_compare($class, $actual, $filename, $contains);
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
        $url = HOST_TESTING . '/api/' . camelize($class);
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
     * @param string $filename to overwrite the class based filename
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @return bool true if the json has no relevant differences
     */
    function assert_api_compare(string $class, array $actual, string $filename = '', bool $contains = false): bool
    {
        $expected = json_decode($this->api_json_expected($class, $filename), true);

        // remove the change time
        if ($actual != null) {
            $actual = $this->json_remove_volatile($actual);
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
        $url = HOST_TESTING . '/api/' . $class;
        if ($class == phrase_type_api::API_NAME) {
            $url = HOST_TESTING . '/api/' . phrase_type_api::URL_NAME;
        }
        if ($class == language_form_api::API_NAME) {
            $url = HOST_TESTING . '/api/' . language_form_api::URL_NAME;
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
     * @return array a json array without volatile fields
     */
    public function json_remove_volatile(array $json): array
    {
        $json = $this->json_remove_volatile_item($json, $json, null);
        $i = 0;
        foreach ($json as $key => $item) {
            if (is_array($item)) {
                $json = $this->json_remove_volatile_item($json, $item, $i);
                $j = 0;
                foreach ($item as $sub_item) {
                    if (is_array($sub_item)) {
                        $json = $this->json_remove_volatile_item($json, $sub_item, $i, $j, $key);
                    }
                    $j++;
                }
            }
            $i++;
        }
        return $json;
    }

    private function json_remove_volatile_item(array $json, array $item, ?int $i, ?int $j = null, string $key = ''): array
    {
        $json = $this->json_remove_volatile_field($json, $item, $i, $j, $key, change_log::FLD_CHANGE_TIME);
        $json = $this->json_remove_volatile_field($json, $item, $i, $j, $key, system_log::FLD_TIME_JSON);
        $json = $this->json_remove_volatile_field($json, $item, $i, $j, $key, system_log::FLD_TIMESTAMP_JSON);
        $json = $this->json_remove_volatile_field($json, $item, $i, $j, $key, batch_job::FLD_TIME_REQUEST);
        $json = $this->json_remove_volatile_field($json, $item, $i, $j, $key, batch_job::FLD_TIME_START);
        $json = $this->json_remove_volatile_field($json, $item, $i, $j, $key, batch_job::FLD_TIME_END);
        if (array_key_exists(export::USER, $item)) {
            $actual_user = $item[export::USER];
            if ($actual_user == '::1') {
                if ($i === null) {
                    $json[export::USER] = 'zukunft.com system test';
                } else {
                    $json[$i][export::USER] = 'zukunft.com system test';
                }
            }
            if ($actual_user == '127.0.0.1') {
                if ($i === null) {
                    $json[export::USER] = 'zukunft.com system test';
                } else {
                    $json[$i][export::USER] = 'zukunft.com system test';
                }
            }
            if ($actual_user == 'zukunft.com system') {
                if ($i === null) {
                    $json[export::USER] = 'zukunft.com system test';
                } else {
                    $json[$i][export::USER] = 'zukunft.com system test';
                }
            }
            if ($actual_user == 'localhost') {
                if ($i === null) {
                    $json[export::USER] = 'zukunft.com system test';
                } else {
                    $json[$i][export::USER] = 'zukunft.com system test';
                }
            }
        }
        if (array_key_exists(export::USER_ID, $item)) {
            if ($i === null) {
                $actual_user_id = $item[export::USER_ID];
                if ($actual_user_id > 0) {
                    $json[export::USER_ID] = 4;
                }
            } else {
                $actual_user_id = $item[export::USER_ID];
                if ($actual_user_id > 0) {
                    $json[$i][export::USER_ID] = 4;
                }
            }
        }
        return $json;
    }

    private function json_remove_volatile_field(array $json, array $item, ?int $i, ?int $j, string $key, string $fld_name): array
    {
        if (array_key_exists($fld_name, $item)) {
            try {
                $actual_time = $item[$fld_name];
            } catch (Exception $e) {
                log_warning($item[$fld_name] . ' cannot be converted to a data');
                $actual_time = new DateTime('now');
            }
            $now = new DateTime('now');
            if ($actual_time < $now) {
                if ($j === null) {
                    if ($i === null) {
                        unset($json[$fld_name]);
                    } else {
                        unset($json[$i][$fld_name]);
                    }
                } else {
                    $json[$key][$j][$fld_name] = (new DateTime(system_log_api::TV_TIME))->format('Y-m-d H:i:s');
                }
            }
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