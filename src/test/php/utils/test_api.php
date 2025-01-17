<?php

/*

    test/utils/test_api.php - set of functions for testing the api
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

namespace test;

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

use api\api_message;
use api\component\component as component_api;
use api\language\language as language_api;
use api\language\language_form as language_form_api;
use api\phrase\phrase_type as phrase_type_api;
use api\ref\ref as ref_api;
use api\system\job as job_api;
use api\system\type_object as type_api;
use cfg\component\component;
use cfg\db\sql_db;
use cfg\export\export;
use cfg\formula\formula;
use cfg\helper\type_lists;
use cfg\system\job;
use cfg\language\language;
use cfg\language\language_form;
use cfg\log\change_log;
use cfg\phrase\phrase_list;
use cfg\phrase\phrase_type;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\system\sys_log;
use cfg\sys_log_list;
use cfg\phrase\term_list;
use cfg\phrase\trm_ids;
use cfg\helper\type_object;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\word\word;
use controller\controller;
use controller\system\sys_log as sys_log_api;
use DateTime;
use Exception;
use html\rest_ctrl;
use shared\api;
use shared\library;

class test_api extends create_test_objects
{
    // path
    const API_PATH = 'api';
    const JSON_EXT = '.json';

    /**
     * check if the HTML frontend object can be set based on the api json message
     * @param object $usr_obj the user sandbox object that should be tested
     */
    function assert_api_to_dsp(object $usr_obj, object $dsp_obj): bool
    {
        $class = $this->class_to_api($usr_obj::class);
        $api_obj = $usr_obj->api_obj(false);
        $api_json_msg = json_decode($api_obj->get_json(), true);
        $dsp_obj = $this->dsp_obj($usr_obj, $dsp_obj, false);
        $msg_to_backend = $dsp_obj->api_array();
        // remove the empty fields to compare the "api save" message with the "api show" message
        // the "api show" message ($api_json_msg) should not contain empty fields
        // because they are irrelevant for the user and this reduces traffic
        // the "api save" message ($msg_to_backend) should contain empty fields
        // to allow the user to remove e.g. a description and less save traffic is expected
        // TODO add a test that e.g. the description can be removed via api
        $msg_to_backend = array_filter($msg_to_backend, fn($value) => !is_null($value) && $value !== '');
        return $this->assert_api_compare($class, $api_json_msg, $msg_to_backend);
    }


    /*
     * assert api
     */

    /**
     * check if the created api json message matches the api json message from the test resources
     * the unit test should be done for all api objects
     * @param object $usr_obj the user sandbox object that should be tested
     */
    function assert_api(object $usr_obj, string $filename = '', bool $contains = false): bool
    {
        $class = $usr_obj::class;
        $class = $this->class_to_api($class);
        if ($usr_obj::class == sys_log_list::class
            or $usr_obj::class == type_lists::class) {
            $api_obj = $usr_obj->api_obj($this->usr1, false);
        } else {
            $api_obj = $usr_obj->api_obj(false);
        }
        $actual = json_decode($api_obj->get_json(), true);
        return $this->assert_api_compare($class, $actual, null, $filename, $contains);
    }

    /**
     * create the api message json body from the backend to the frontend
     * and recreate the object based on the json from the frontend and check if it matches
     * without using the real curl api
     *
     * @param object $usr_obj the user sandbox object that should be tested
     * @return bool true if the check is fine
     */
    function assert_api_json(object $usr_obj): bool
    {
        $class = $usr_obj::class;
        $class = $this->class_to_api($class);
        $test_name = $class . ' excluded returns id only api json';
        $usr_obj->exclude();
        $json_excluded = $usr_obj->api_json();
        $result = $this->assert_text_contains($test_name, $json_excluded, '"id":1,"excluded":true');
        if ($result) {
            $test_name = $class . ' reset returns empty api json';
            $usr_obj->include();
            // check that the excluded object returns a json with just the id and the excluded flag
            $json_api = $usr_obj->api_json();
            $clone_obj = clone $usr_obj;
            $clone_obj->reset();
            $json_empty = $clone_obj->api_json();
            $result = $this->assert($test_name, $json_empty, '{"id":0}');
        }
        if ($result) {
            $test_name = $class . ' fill based on api json matches original';
            $clone_obj->set_by_api_json(json_decode($json_api, true));
            $json_compare = $clone_obj->api_json();
            $result = $this->assert_json_string($test_name, $json_compare, $json_api);
        }
        return $result;
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
        $ctrl = new rest_ctrl();
        $actual = json_decode($ctrl->api_call(rest_ctrl::PUT, $url . '/', $data), true);
        $actual_text = json_encode($actual);
        $expected_raw_text = $this->file('api/' . $class . '/' . $class . '_put_response.json');
        $expected = json_decode($expected_raw_text, true);
        $expected_text = json_encode($expected);
        if ($actual == null) {
            return 0;
        } else {
            $id = 0;
            if (array_key_exists(api::URL_VAR_ID, $actual)) {
                $id = intval($actual[api::URL_VAR_ID]);
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
        $ctrl = new rest_ctrl();
        $actual = json_decode($ctrl->api_call(rest_ctrl::DELETE, $url, $data), true);
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
                $wrd->load_by_id($id);
                $result = $wrd->save_from_api_msg($request_body)->get_last_message();
                // if no message should be shown to the user the adding is expected to be fine
                // so get the row id to be able to remove the test row later
                if ($result == '') {
                    $result = $wrd->id();
                }
                break;
            case source::class:
                $src = new source($usr);
                $src->load_by_id($id);
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
        $usr_msg = new user_message();
        switch ($class) {
            case word::class:
                $wrd = new word($usr);
                $wrd->set_id($id);
                $usr_msg = $wrd->del();
                break;
            case source::class:
                $src = new source($usr);
                $src->set_id($id);
                $usr_msg = $src->del();
                break;
            default:
                log_err($class . ' not yet mapped in assert_api_del_no_rest');
        }
        // if no row id is returned report the problem
        if ($usr_msg->is_ok()) {
            return true;
        } else {
            $this->assert_fail('api write del test without REST call of ' . $class . ' failed');
            return false;
        }
    }

    /**
     * check the api message without using the real curl api
     * @param sql_db $db_con to retrive the configuration for the message header
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
        $api_msg = new api_message($db_con, $class, $this->usr1);
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
     * @param int $levels the number of children levels that should be included
     * @param ?array $expected if not null, the expected result
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get(string $class, int $id = 1, int $levels = 0, ?array $expected = null, bool $ignore_id = false): bool
    {
        // naming exception (to be removed?)
        $lib = new library();
        $ctrl = new rest_ctrl();
        $class_api = $this->class_to_api($class);
        $url = $this->class_to_url($class_api);
        if ($levels > 0) {
            $url .= '?' . api::URL_VAR_ID . '=' . $id;
            $url .= '&' . api::URL_VAR_CHILDREN . '=' . $levels;
        }
        // Check if backend is reading the id
        $data = array(api::URL_VAR_ID => $id);
        // TODO check why for formula a double call is needed
        if ($class == formula::class) {
            $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);
        }
        // TODO simulate other users
        $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);
        if ($actual == null) {
            log_err('GET api call for ' . $class_api . ' returned an empty result');
        }
        $filename = '';
        if ($class == value::class) {
            $filename = 'value_non_std';
        }
        if ($levels > 0) {
            $filename = $class_api . '_with_components';
        }
        return $this->assert_api_compare($class_api, $actual, $expected, $filename, false, $ignore_id);
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
    function assert_api_get_by_text(string $class, string $name = '', string $field = api::URL_VAR_NAME): bool
    {
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data = array($field => $name);
        $ctrl = new rest_ctrl();
        $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);
        return $this->assert_api_compare($class, $actual);
    }

    /**
     * check if the REST GET call of a user sandbox objects returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param array|string $ids the database ids of the db rows that should be used for testing
     * @param string $id_fld the field name for the object id e.g. word_id
     * @param string $filename to overwrite the class based filename to get the standard expected result
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_list(
        string       $class,
        array|string $ids = [1, 2],
        string       $id_fld = 'ids',
        string       $filename = '',
        bool         $contains = false): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        $url = HOST_TESTING . api::URL_API_PATH . $lib->camelize_ex_1($class);
        if (is_array($ids)) {
            $data = array($id_fld => implode(",", $ids));
        } else {
            $data = array($id_fld => $ids);
        }
        $ctrl = new rest_ctrl();
        $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);

        // TODO remove
        if ($class == $lib->class_to_name(phrase_list::class)) {
            if ($filename == '' and $id_fld != 'ids') {
                $filename = $class . '_without_link' . '_by_' . $id_fld;
            } else {
                $filename = $class . '_without_link';
            }
        }
        if ($class == $lib->class_to_name(term_list::class)) {
            $lst = new term_list($this->usr1);
            $lst->load_by_ids((new trm_ids($ids)));
            $result = $lst->api_obj();
            $filename = $class . '_without_link';
        }

        if ($filename == '' and $id_fld != 'ids') {
            $filename = $class . '_by_' . $id_fld;
        }

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
        $class = $lib->class_to_name($class);
        $url = HOST_TESTING . api::URL_API_PATH . $lib->camelize_ex_1($class);
        if ($fld_name != '') {
            $data = array($id_fld => $id, $fld_name => $fld_value);
        } else {
            $data = array($id_fld => $id);
        }
        $ctrl = new rest_ctrl();
        $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);

        return $this->assert_api_compare($class, $actual);
    }


    /*
     * helper for assert api
     */

    /**
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param ?array $actual the actual received json array
     * @param ?array $expected if not null, the expected result
     * @param string $filename to overwrite the class based filename to get the standard expected result
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @param bool $ignore_id true if the ids should be ignored e.g. because test records have been created
     * @return bool true if the json has no relevant differences
     */
    function assert_api_compare(
        string $class,
        ?array $actual,
        ?array $expected = null,
        string $filename = '',
        bool   $contains = false,
        bool   $ignore_id = false): bool
    {
        $lib = new library();
        $class_for_file = $this->class_without_namespace($class);
        if ($expected == null) {
            $expected = json_decode($this->api_json_expected($class_for_file, $filename), true);
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
            return $this->assert($class . ' API GET', $lib->json_contains($expected, $actual), true);
        } else {
            return $this->assert_json($class . ' API GET', $actual, $expected);
        }
    }

    /**
     * get the expected api json message of a user sandbox object
     *
     * @param string $class the class name of the object to test
     * @param string $file to overwrite the class based filename
     * @return string with the expected json message
     */
    function api_json_expected(string $class, string $file = ''): string
    {
        if ($file == '') {
            $file = $class;
        }
        $filename = self::API_PATH . DIRECTORY_SEPARATOR . $class . DIRECTORY_SEPARATOR . $file . self::JSON_EXT;
        return $this->file($filename);
    }

    /**
     * adjust the class name to the api name if they does not (yet) match
     * @param string $class the class name that should be converted
     * @return string the api name
     */
    private function class_to_api(string $class): string
    {
        $lib = new library();
        $result = $class;
        if ($class == component::class) {
            $result = component_api::API_NAME;
        }
        if ($class == ref::class) {
            $result = ref_api::API_NAME;
        }
        if ($class == job::class) {
            $result = job_api::API_NAME;
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
        return $lib->class_to_name($result);
    }

    /**
     * create the url based on the class name
     * @param string $class the class name that should be used
     * @return string the api url
     */
    private function class_to_url(string $class): string
    {
        $url = HOST_TESTING . api::URL_API_PATH . $class;
        if ($class == phrase_type_api::API_NAME) {
            $url = HOST_TESTING . api::URL_API_PATH . phrase_type_api::URL_NAME;
        }
        if ($class == language_form_api::API_NAME) {
            $url = HOST_TESTING . api::URL_API_PATH . language_form_api::URL_NAME;
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

    /*
     * helper for openapi test
     */

    public function get_paths_of_tag(string $tag, array $api_def): array
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

    /*
     * helper for api test
     */

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
        $json = $this->json_remove_volatile_time_field($json, sys_log::FLD_TIME_JSON);
        $json = $this->json_remove_volatile_time_field($json, sys_log::FLD_TIMESTAMP_JSON);
        $json = $this->json_remove_volatile_time_field($json, change_log::FLD_TIME);
        $json = $this->json_remove_volatile_time_field($json, job::FLD_TIME_REQUEST);
        $json = $this->json_remove_volatile_time_field($json, job::FLD_TIME_START);
        $json = $this->json_remove_volatile_time_field($json, job::FLD_TIME_END);

        // remove the id fields if requested
        // for tests with base load dataset the id fields should not be ignored
        // but for tests that add and remove data to table that have real data the id field should be ignored
        if ($ignore_id) {
            $json = $this->json_remove_volatile_unset_field($json, sql_db::FLD_ID);
            $json = $this->json_remove_volatile_unset_field($json, api::URL_VAR_ID);
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
                $new_value = (new DateTime(sys_log_api::TV_TIME))->format('Y-m-d H:i:s');
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

}