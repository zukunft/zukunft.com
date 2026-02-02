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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;


include_once paths::MODEL_LOG . 'change_log.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_LOG . 'change_log_list.php';
include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'job_db.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once test_paths::UTILS . 'test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\trm_ids;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\system\job_db;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use DateTime;
use Exception;

class test_api extends test_base
{
    // path
    const string API_PATH = 'api';
    const string JSON_EXT = '.json';
    // an api json message for an empty object
    const string JSON_ID_ONLY = '{"id":0}';
    // an export json message for an empty object
    const string JSON_NAME_ONLY = '{"name":""}';
    // an export json message for an empty array object e.g.
    const string JSON_ARRAY_ONLY = '[]';
    // part of a json if the object with id 1 is excluded
    const string JSON_PART_ID_EXCLUDED = '"id":1,"excluded":true';
    // part of a json if the object is excluded
    const string JSON_PART_EXCLUDED = '"excluded":true,';

    /**
     * check if the HTML frontend object can be set based on the api json message
     * @param object $usr_obj the user sandbox object that should be tested
     * @param object $dsp_obj the display object used to create the api message to the backend
     * @param array $api_types to check the different message type e.g.to test if an excluded object can be reactivated
     * @return bool true if the test has been successful
     */
    function assert_api_to_ui(object $usr_obj, object $dsp_obj, array $api_types = []): bool
    {
        $lib = new library();
        $usr_msg_ui = new user_message_ui();
        $class = $this->class_to_api($usr_obj::class);
        $api_types[] = api_types::TEST_MODE;
        $msg_to_frontend = $usr_obj->api_json($api_types);
        $dsp_obj->set_from_json($msg_to_frontend, $usr_msg_ui);
        $array_to_backend = $dsp_obj->api_array($api_types);
        // remove the empty fields to compare the "api save" message with the "api show" message
        // the "api show" message ($msg_to_frontend) should not contain empty fields
        // because they are irrelevant for the user and this reduces traffic
        // the "api save" message ($array_to_backend) should contain empty fields
        // to allow the user to remove e.g. a description and less save traffic is expected
        // TODO add a test that e.g. the description can be removed via api
        $array_to_backend = $lib->array_filter_r($array_to_backend, fn($value) => is_null($value) || $value === '');
        $array_to_frontend = json_decode($msg_to_frontend, true);
        $array_to_frontend = $this->json_remove_fields_only_to_ui($array_to_frontend);
        // and also remove fields of linked objects because each object is updated by its own
        // whereas the object to the frontend are sometimes combined to reduce traffic
        // e.g. the components are included in the view
        if ($usr_obj::class == component_link::class) {
            $array_to_frontend = $this->json_remove_component_fields($array_to_frontend);
        }
        return $this->assert_api_compare($class, $array_to_frontend, $array_to_backend);
    }


    /*
     * assert api
     */

    /**
     * check if the created api json message matches the api json message from the test resources
     * the unit test should be done for all api objects
     * @param object $usr_obj the user sandbox object that should be tested
     * @param string $filename the exceptional filename that overwrites the generated filename
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @return bool true if everything is fine
     */
    function assert_api(
        object              $usr_obj,
        string              $filename = '',
        api_type_list|array $typ_lst = [],
        bool                $contains = false
    ): bool
    {
        // check and norm the parameters
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $typ_lst->add(api_types::TEST_MODE);
        $class = $this->class_to_api($usr_obj::class);

        // create the api json message and revert it to an array for better compare
        $actual = json_decode($usr_obj->api_json($typ_lst, $this->usr1), true);

        return $this->assert_api_compare($class, $actual, null, $filename, '', $contains);
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
        $class_api = $this->class_to_api($class);
        $usr_msg = new user_message($usr_obj->get_user());

        // is excluded api json empty?
        $test_name = $class_api . ' excluded json is empty';
        $usr_obj->exclude();
        $json_excluded = $usr_obj->api_json();
        $json_excluded_full = $usr_obj->api_json([api_types::WITH_EXCLUDED]);
        $target = test_api::JSON_ARRAY_ONLY;
        $result = $this->assert_text_contains($test_name, $json_excluded, $target);
        // is excluded api json only the id if requested?
        if ($result) {
            $test_name = $class_api . ' excluded json can be only id';
            $json_excluded_id = $usr_obj->api_json([api_types::WITH_EXCLUDED_ID]);
            $target = self::JSON_PART_ID_EXCLUDED;
            $result = $this->assert_text_contains($test_name, $json_excluded_id, $target);
        }
        // is excluded api json filled if requested?
        if ($result) {
            $test_name = $class_api . ' excluded json can be complete';
            $target = self::JSON_PART_EXCLUDED;
            $result = $this->assert_text_contains($test_name, $json_excluded_full, $target);
            $json_excluded_full = str_replace($target, '', $json_excluded_full);
        }
        if ($result) {
            $test_name = $class_api . ' reset returns empty api json';
            $usr_obj->include();
            // check that the excluded object returns a json with just the id and the excluded flag
            $json_api = $usr_obj->api_json();
            $target = self::JSON_ID_ONLY;
            if ($usr_obj::class == value::class) {
                $clone_obj = $usr_obj->clone_all();
            } elseif ($usr_obj::class == element::class) {
                $target = self::JSON_ARRAY_ONLY;
                $clone_obj = $usr_obj->clone_all();
            } else {
                $clone_obj = clone $usr_obj;
            }
            $clone_obj->reset();
            $json_empty = $clone_obj->api_json();
            $result = $this->assert($test_name, $json_empty, $target);
        }

        // does frontend and backend api json match?
        $test_name = $class_api . ' fill based on api json matches original';
        if ($result) {
            $clone_obj->api_mapper(json_decode($json_api, true), $usr_msg);
            $json_compare = json_encode($this->json_remove_fields_only_to_ui(json_decode($clone_obj->api_json(), true)));
            $json_api_ex = json_encode($this->json_remove_fields_only_to_ui(json_decode($json_api, true)));
            $result = $this->assert_json_string($test_name, $json_compare, $json_api_ex);
        }
        // does the remaining part of the full excluded api json match the normal api json
        if ($result) {
            $result = $this->assert_json_string($test_name, $json_excluded_full, $json_api);
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
    function assert_api_put(
        string       $class,
        test_cleanup $t,
        array        $data = [],
        bool         $ignore_id = false
    ): int
    {
        $t_db = new test_db_load($t);
        // get default data
        if ($data == array()) {
            $data = $t_db->source_put_json();
        }
        // naming exception (to be removed?)
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data_string = json_encode($data);
        $ctrl = new rest_call();
        $actual = json_decode($ctrl->api_call(rest_ctrl::PUT, $url . '/', $data), true);
        $actual_text = json_encode($actual);
        $expected_raw_text = $this->file('api/' . $class . '/' . $class . '_put_response.json');
        $expected = json_decode($expected_raw_text, true);
        $expected_text = json_encode($expected);
        if ($actual == null) {
            return 0;
        } else {
            $id = 0;
            if (array_key_exists(url_var::ID, $actual)) {
                $id = intval($actual[url_var::ID]);
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
        $ctrl = new rest_call();
        $actual = json_decode($ctrl->api_call(rest_ctrl::DELETE, $url, $data), true);
        if ($actual == null) {
            return false;
        } else {
            return $this->assert_api_compare($class, $actual);
        }
    }

    /**
     * check if the API PUT or POST call without the REST call adds or updates the user sandbox object
     * similar to assert_api_put or assert_api_post but without the need for a local webserver
     *
     * @param sandbox $sbx the sandbox object that should be tested
     * @param int $id the id of the object that should be updated
     * @param array $data the database id of the db row that should be used for testing
     * @param user_message $usr_msg to collect the messages for the user
     * @return int the id of the created db row
     */
    function assert_api_no_rest(sandbox $sbx, int $id, array $data, user_message $usr_msg): int
    {
        // check input values
        if ($data == []) {
            log_err('Data for ' . $sbx::class . ' missing in assert_api_no_rest');
        }
        // use the controller to get the payload from the api message
        $ctrl = new controller();
        $request_body = $ctrl->check_api_msg($data);
        // load the object before the update
        if ($id != 0) {
            $sbx->load_by_id($id);
        }
        // apply the payload to the backend object (add switch)
        $sbx->api_mapper($request_body, $usr_msg);
        if ($usr_msg->is_ok()) {
            $sbx->save($usr_msg);
        }
        // if no row id is returned report the problem
        if ($usr_msg->is_ok()) {
            return $usr_msg->get_row_id();
        } else {
            $this->assert_fail('api write test without REST call of ' . $sbx::class . ' failed');
            return 0;
        }
    }

    /**
     * check if the API DEL call works without the REST call
     * similar to assert_api_del but without the need for a local webserver
     *
     * @param string $class the class name of the object to test
     * @param int $id the id of the object that should be updated
     * @return bool if the object has been deleted or excluded
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
                $wrd->id = $id;
                $wrd->del($usr_msg);
                break;
            case source::class:
                $src = new source($usr);
                $src->id = $id;
                $src->del($usr_msg);
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
     * @param sql_db $db_con to retrieve the configuration for the message header
     * @param object $usr_obj the user sandbox object that should be tested
     * @param string $filename to overwrite the filename of the expected json message based on the usr_obj
     * @param bool $contains set to true if the actual message is expected to contain more than the expected message
     * @return bool true if the check is fine
     */
    function assert_api_msg(sql_db $db_con, object $usr_obj, string $filename = '', bool $contains = false): bool
    {
        $class = $usr_obj::class;
        $class = $this->class_to_api($class);
        $api_msg = $usr_obj->api_json([api_types::HEADER], $this->usr1);
        $actual = json_decode($api_msg, true);
        return $this->assert_api_compare($class, $actual, null, $filename, '', $contains);
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
    function assert_api_get(
        string $class,
        int    $id = 1,
        int    $levels = 0,
        ?array $expected = null,
        bool   $ignore_id = false
    ): bool
    {
        // naming exception (to be removed?)
        $lib = new library();
        $ctrl = new rest_call();
        $class_api = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        if ($levels > 0) {
            $url .= '?' . url_var::ID . '=' . $id;
            $url .= '&' . url_var::LEVELS . '=' . $levels;
        }
        // Check if backend is reading the id
        $data = array(url_var::ID => $id);
        // TODO move this exception to the api_par_lst
        if ($class == value::class) {
            $data[url_var::WITH_PHRASES] = url_var::TRUE;
        }
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
            $filename = $class_api . '_with_component_id';
        }
        return $this->assert_api_compare($class_api, $actual, $expected, $filename, '', false, $ignore_id);
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
    function assert_api_get_by_text(string $class, string $name = '', string $field = url_var::NAME): bool
    {
        $class = $this->class_to_api($class);
        $url = $this->class_to_url($class);
        $data = array($field => $name);
        $ctrl = new rest_call();
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
        string       $id_fld = url_var::ID_LST,
        string       $filename = '',
        bool         $contains = false): bool
    {
        $lib = new library();
        $url_map = new url_mapper();
        $usr_msg = new user_message_ui();
        $class = $lib->class_to_name($class);
        $url = api::HOST_TESTING . url_var::API_PATH . $lib->camelize_ex_1($class);
        if (is_array($ids)) {
            $data = array($id_fld => implode(",", $ids));
        } else {
            $data = array($id_fld => $ids);
        }
        $ctrl = new rest_call();
        $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);

        // TODO Prio 0 remove
        if ($class == $lib->class_to_name(phrase_list::class)) {
            if ($filename == '' and $id_fld != url_var::ID_LST) {
                $file_by_name = $url_map->name_to_human($id_fld, $usr_msg);
                $filename = $class . '_without_link' . '_by_' . $file_by_name;
            } else {
                $filename = $class . '_without_link';
            }
        }

        if ($filename == '' and $id_fld != url_var::ID_LST) {
            $file_by_name = $url_map->name_to_human($id_fld, $usr_msg);
            $filename = $class . '_by_' . $file_by_name;
        }

        return $this->assert_api_compare($class, $actual, null, $filename, '', $contains);
    }

    /**
     * check if the REST GET call of user changes returns the expected JSON message
     * for testing the local deployments needs to be updated using an external script
     *
     * @param string $class the class name of the object to test
     * @param int|string $id the database id of the object to which the changes should be listed
     * @param string $fld the url api field name to select only some changes e.g. 'word_field'
     * @param user|null $usr to select only the changes of this user
     * @param int $limit to set a page size that is different from the default page size
     * @param int $page offset the number of pages
     * @return bool true if the json has no relevant differences
     */
    function assert_api_chg_list(
        string     $class,
        int|string $id = 1,
        string     $fld = '',
        user|null  $usr = null,
        int        $limit = 0,
        int        $page = 0
    ): bool
    {
        $log_lst = new change_log_list_ui();
        $json = $log_lst->load_api_by_object_field($class, $id, $fld, $usr, $limit, $page);
        $actual = json_decode($json, true);

        $lib = new library();
        $log_class = $lib->class_to_name(change_log_list::class);
        $filename = $log_class;
        $class = $lib->class_to_api_name($class);
        if ($class != '') {
            $filename .= '_' . $class;
        }
        if ($id != 0) {
            $filename .= '_' . $id;
        }
        if ($fld != '') {
            $filename .= '_' . $fld;
        }
        if ($usr != null) {
            $filename .= '_u' . $usr->id;
        }
        if ($page != 0) {
            $filename .= '_p' . $page;
        }
        if ($limit != 0) {
            $filename .= '_l' . $limit;
        }

        return $this->assert_api_compare($class, $actual, null, $filename, change_log_list::class);
    }

    /**
     * check if the REST POST call returns a JSON message with the id of the object just added
     * for testing the local deployments needs to be updated using an external script
     * TODO Prio 1 add user_message as parameter
     *
     * @param string $class the class name of the object to test
     * @return bool true if the json has no relevant differences
     */
    function assert_api_post(
        string       $class,
        test_cleanup $t
    ): bool
    {
        $lib = new library();
        $t_map = new test_mappers($t);
        $usr_msg_ui = new user_message_ui();
        $test_name = 'add new ' . $lib->class_to_name($class) . ' via api post call';

        $dbo = $t_map->class_to_add_object($class);
        $name = $dbo->name();
        $dbo_ui = $t_map->class_to_ui_object($class);
        $dbo_ui->set_from_json($dbo->api_json(), $usr_msg_ui);
        //$add_result = $dbo_ui->add_via_api();

        // TODO Prio 1 remove reloading and use $add_result instead
        $dbo->load_by_name($name);
        return $this->assert_greater_zero($test_name, $dbo->id());

        //return $this->assert_greater_zero($test_name, $add_result->get_row_id());
    }

    /**
     * check if the REST POST call returns a JSON message with the id of the object just added
     * for testing the local deployments needs to be updated using an external script
     * TODO Prio 1 add user_message as parameter
     *
     * @param string $class the class name of the object to test
     * @return bool true if the json has no relevant differences
     */
    function assert_api_post_direct(
        string       $class,
        user         $usr,
        test_cleanup $t,
        string       $msg = ''
    ): bool
    {
        $lib = new library();
        $ctrl = new controller();
        $t_map = new test_mappers($t);
        $usr_msg_ui = new user_message_ui();

        $test_name = 'add new ' . $lib->class_to_name($class) . ' by simulation the post call';

        $dbo = $t_map->class_to_add_object($class);
        $dbo_ui = $t_map->class_to_ui_object($class);
        $dbo_ui->set_from_json($dbo->api_json(), $usr_msg_ui);
        // replacement for the api call
        $name = $dbo->name();
        $ctrl->post_json($dbo_ui->api_array(), $dbo, $usr, $msg);
        $dbo->load_by_name($name);

        return $this->assert_greater_zero($test_name, $dbo->id());
    }

    /**
     * check if the REST DELETE call returns an empty JSON message if the excusion has been successful
     * for testing the local deployments needs to be updated using an external script
     * TODO Prio 1 add user_message as parameter
     *
     * @param string $class the class name of the object to test
     * @return bool true if the json has no relevant differences
     */
    function assert_api_del_direct(
        string       $class,
        user         $usr,
        test_cleanup $t,
        string       $msg = ''
    ): bool
    {
        $lib = new library();
        $ctrl = new controller();
        $t_map = new test_mappers($t);
        $usr_msg_ui = new user_message_ui();

        $test_name = 'del new ' . $lib->class_to_name($class) . ' by simulation the delete call';

        $dbo = $t_map->class_to_add_object($class);
        $dbo->load_by_name($dbo->name());
        $dbo_ui = $t_map->class_to_ui_object($class);
        $dbo_ui->set_from_json($dbo->api_json(), $usr_msg_ui);
        $ctrl->delete($dbo_ui->id(), $dbo, $usr, $msg);

        $dbo->load_by_name($dbo->name());
        return $this->assert($test_name, $dbo->id(), 0);
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
        string $class_for_file = '',
        bool   $contains = false,
        bool   $ignore_id = false): bool
    {
        $lib = new library();
        if ($class_for_file == '') {
            $class_for_file = $class;
        }
        $class_for_file = $this->class_without_namespace($class_for_file);
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
        if ($class == ref::class) {
            $result = json_fields::REFERENCE;
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
        $lib = new library();
        if ($class == ref::class) {
            $class = url_var::REF_API;
        }
        $url_class = $lib->camelize_ex_1($lib->class_to_name($class));
        return api::HOST_TESTING . url_var::API_PATH . $url_class;
    }

    /**
     * create the put json message based on the class name
     * @param string $class the class name that should be used
     * @return string the api url
     */
    private function class_to_put_msg(
        string       $class,
        test_cleanup $t
    ): array
    {
        $t_db = new test_db_load($t);
        $put_msg = array();
        switch ($class) {
            case source::class:
                $put_msg = $t_db->source_put_json();
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
        $json = $this->json_remove_volatile_time_field($json, job_db::FLD_TIME_REQUEST);
        $json = $this->json_remove_volatile_time_field($json, job_db::FLD_TIME_START);
        $json = $this->json_remove_volatile_time_field($json, job_db::FLD_TIME_END);

        // remove the id fields if requested
        // for tests with base load dataset the id fields should not be ignored
        // but for tests that add and remove data to table that have real data the id field should be ignored
        if ($ignore_id) {
            $json = $this->json_remove_volatile_unset_field($json, sql_db::FLD_ID);
            $json = $this->json_remove_volatile_unset_field($json, url_var::ID);
        }

        // replace any local test username with the standard test username
        if (array_key_exists(json_fields::USER_NAME, $json)) {
            $actual_user = $json[json_fields::USER_NAME];
            if ($actual_user == '::1'
                or $actual_user == '127.0.0.1'
                or 'zukunft.com system'
                or 'localhost') {
                $new_value = users::SYSTEM_TEST_NAME;
                $json = $this->json_remove_volatile_replace_field($json, json_fields::USER_NAME, $new_value);
            }
        }

        // replace any local test user id with the standard test user id
        if (array_key_exists(json_fields::USER_ID, $json)) {
            $user_id = $json[json_fields::USER_ID];
            if ($user_id >= 0) {
                $user_id = users::SYSTEM_TEST_ID;
            }
            $json = $this->json_remove_volatile_replace_int_field($json, json_fields::USER_ID, $user_id);
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
                $new_value = (new DateTime(sys_log_tests::TV_TIME))->format('Y-m-d H:i:s');
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