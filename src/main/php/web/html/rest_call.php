<?php

/*

    web/html/rest_call.php - functions used by the frontend to call the backend api of zukunft.com
    ---------------------


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

namespace Zukunft\ZukunftCom\main\php\web\html;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\group_id_url;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

//include_once html_paths::API_OBJECT . 'controller.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'group_id_url.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED_TYPES . 'system_time_type.php';
include_once html_paths::USER . 'user_message.php';

class rest_call
{

    // the start of a slow answer that names the seconds of its backend steps (see controller::json_with_time)
    const string TIME_START = '{"' . json_fields::API_TIME . '":{';

    // the session cookie file used for all api calls of this process:
    // set by the test bootstrap after the admin login (test_base::api_login) so that the
    // api calls of a test run are permitted like the calls of a logged in user,
    // because a pod can block the changes of a user without login;
    // empty if the api calls are done without a session e.g. by the productive frontend
    private static string $session_cookie_file = '';

    // the anti csrf token of the session of the cookie file above, sent as a http header
    // with every api call so that the api write calls pass the csrf check of the backend
    // (see server_guard::csrf_token_valid); empty if no session is used
    private static string $session_token = '';

    /**
     * use the session of the given cookie file for all following api calls of this process
     * @param string $file the path of the session cookie file of a logged in user
     * @return void
     */
    static function set_session_cookie_file(string $file): void
    {
        self::$session_cookie_file = $file;
    }

    /**
     * send the given anti csrf token with all following api calls of this process
     * @param string $token the anti csrf token of the session of the logged in user
     * @return void
     */
    static function set_session_token(string $token): void
    {
        self::$session_token = $token;
    }

    /**
     * create the class name as used for the api
     * @param string $class the class name that should be used
     * @return string the api url
     */
    function class_to_api_name(string $class): string
    {
        $lib = new library();
        return $lib->class_to_api_route($class);
    }

    /**
     * create and execute an api call for a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param int|string $id|string the id of the database object that should be loaded
     * @param user_message $msg with the requesting user whose id is added to the url if logged in
     * @param array $data additional data that should be included in the get request
     * @return array with the body json message from the backend
     */
    function api_call_id(string $class, int|string $id, user_message $msg, array $data = []): array
    {
        return $this->api_get($class, $this->id_data($id, $data), $msg);
    }

    /**
     * add the id to the api call parameters; a group id is named in its short form like in a page
     * url (see shared/group_id_url.php), which the api of value, result and group converts back
     *
     * @param int|string $id the database id of the object to load
     * @param array $data the other parameters of the api call
     * @return array the parameters with the id
     */
    function id_data(int|string $id, array $data = []): array
    {
        $data[url_var::ID] = group_id_url::to_url($id);
        return $data;
    }

    /**
     * create and execute an api call for a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param string $name the name of the database object that should be loaded
     * @param user_message $msg with the requesting user whose id is added to the url if logged in
     * @return array with the body json message from the backend
     */
    function api_call_name(string $class, string $name, user_message $msg): array
    {
        $data = array();
        $data[url_var::NAME] = $name;
        return $this->api_get($class, $data, $msg);
    }

    /**
     * create and execute an api get call to get a json message of a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the get call
     * @param user_message $msg with the requesting user whose id is added to the url if logged in
     * @return array with the body json message from the backend
     */
    function api_get(string $class, array $data, user_message $msg): array
    {
        return $this->api_curl_call($class, $this->data_with_user($data, $msg), rest_ctrl::GET);
    }

    /**
     * add the id of the logged-in user to the url vars of an api get call,
     * so that the backend returns the sandbox of this user (see user::data_user)
     * @param array $data the url vars of the api get call
     * @param user_message $msg with the requesting user
     * @return array the url vars with the user id; unchanged if nobody is logged in
     *               or if the caller has already named a data user e.g. an admin viewing another user
     */
    function data_with_user(array $data, user_message $msg): array
    {
        $usr_id = $msg->usr?->id_for_url() ?? 0;
        if ($usr_id > 0) {
            $data[url_var::USER] ??= $usr_id;
        }
        return $data;
    }

    /**
     * create and execute an api post call add an object to the database based on the given json
     *
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the post call e.g. the json array for a new word
     * @return array with the body json message from the backend
     */
    function api_post(string $class, array $data): array
    {
        return $this->api_curl_call($class, $data, rest_ctrl::POST);
    }

    /**
     * create and execute an api put call update an object to the database based on the given json
     *
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the post call e.g. the json array for a new word
     * @return array with the body json message from the backend
     */
    function api_put(string $class, array $data): array
    {
        return $this->api_curl_call($class, $data, rest_ctrl::PUT);
    }

    /**
     * create and execute an api delete call to exclude an object from the database selected by the given json with the id
     *
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the post call e.g. the json array for a new word
     * @return array with the body json message from the backend
     */
    function api_del(string $class, array $data): array
    {
        return $this->api_curl_call($class, $data, rest_ctrl::DELETE);
    }

    /**
     * create and execute an api get, post, put or delete call for a database object
     *
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the post call e.g. the json array for a new word
     * @return array with the body json message from the backend
     */
    function api_curl_call(string $class, array $data, string $method): array
    {
        $html = new html_base();
        $ctrl = new controller();
        $api_name = $this->class_to_api_name($class);
        $url = $html->url_api($api_name, []);
        $json_str = $this->api_call($method, $url, $data);
        $json_msg = json_decode($json_str, true);
        return $ctrl->check_api_msg($json_msg);
    }

    /**
     * @param string $line one http header line of an api answer e.g. "X-Zukunft-Cache: hit"
     * @return string api::CACHE_HIT or api::CACHE_MISS if the line is the cache header, else an empty string
     */
    static function cache_state_of(string $line): string
    {
        $result = '';
        $parts = explode(':', $line, 2);
        // a header name is case-insensitive, e.g. http/2 sends it in lower case
        if (count($parts) == 2 and strcasecmp(trim($parts[0]), api::CACHE_HEADER) == 0) {
            $result = trim($parts[1]);
        }
        return $result;
    }

    /**
     * @param string $json the json answer of the backend
     * @return array the seconds per backend step that a slow answer names (see controller::json_with_time), else an empty array
     */
    static function time_steps_of(string $json): array
    {
        $result = [];
        $end = self::time_end($json);
        if ($end > 0) {
            $start = strlen(self::TIME_START) - 1;
            $result = json_decode(substr($json, $start, $end - $start + 1), true) ?? [];
        }
        return $result;
    }

    /**
     * the parts of the wait for an api answer that the frontend measures itself, so that the
     * rest of the wait is only the backend work that no answer has named
     *
     * @param float $connect the seconds until the connection to the backend has been open
     * @param float $first_byte the seconds until the first byte of the answer has been received
     * @param float $total the seconds until the whole answer has been received
     * @return array the seconds of the connect and of the transfer after the first byte, without a part that has taken no time
     */
    static function transfer_steps(float $connect, float $first_byte, float $total): array
    {
        $steps = [
            system_time_type::API_CONNECT => $connect,
            system_time_type::API_TRANSFER => $total - $first_byte,
        ];
        return array_filter($steps, fn($seconds) => $seconds > 0);
    }

    /**
     * @param float $wait the seconds that the frontend has waited for the api call
     * @param float $total the seconds of the call that curl has counted
     * @return array the seconds of the wait that curl has not counted e.g. the session cookie file, empty if none
     */
    static function curl_step(float $wait, float $total): array
    {
        $steps = [system_time_type::API_CURL => $wait - $total];
        return array_filter($steps, fn($seconds) => $seconds > 0);
    }

    /**
     * the server time of an answer split into the backend steps that a slow answer names and the
     * part that they do not cover, or named by the kind of answer if it names no steps
     *
     * @param string $json the json answer of the backend, empty if the call has failed
     * @param float $server the seconds from the open connection until the first byte of the answer
     * @return array the seconds per step of the server time
     */
    static function server_steps(string $json, float $server): array
    {
        $result = self::time_steps_of($json);
        if ($result == []) {
            $result = [self::answer_kind($json) => $server];
        } else {
            $not_measured = $server - array_sum($result);
            if ($not_measured > 0) {
                $result[system_time_type::API_NOT_MEASURED] = $not_measured;
            }
        }
        return $result;
    }

    /**
     * @param string $json a json answer of the backend that names no steps
     * @return string the step name of the kind of answer: an object names its steps if it has been slow
     *                (see controller::json_with_time), so without them it has been fast; a json list has no
     *                field for the steps, so it can have been fast or slow
     */
    private static function answer_kind(string $json): string
    {
        $result = system_time_type::API_NO_JSON;
        if (str_starts_with($json, '{')) {
            $result = system_time_type::API_FAST;
        } elseif (str_starts_with($json, '[')) {
            $result = system_time_type::API_LIST;
        }
        return $result;
    }

    /**
     * @param string $json the json answer of the backend
     * @return string the json without the seconds of the backend steps, so that a slow answer is the same as a fast one
     */
    static function without_time(string $json): string
    {
        $result = $json;
        $end = self::time_end($json);
        if ($end > 0) {
            // the comma behind the steps is dropped with them
            $result = '{' . substr($json, $end + 2);
        }
        return $result;
    }

    /**
     * @param string $json the json answer of the backend
     * @return int the position of the brace that closes the backend steps, 0 if the answer names none;
     *             the steps are one flat object, so the first closing brace of the answer ends them
     */
    private static function time_end(string $json): int
    {
        $result = 0;
        if (str_starts_with($json, self::TIME_START)) {
            $result = (int)strpos($json, '}');
        }
        return $result;
    }

    /**
     * execute an api call
     *
     * @param string $method the REST method (GET, POST, PUT or DELETE)
     * @param string $url the url that should be called
     * @param array $data the data as a json array that should be included in the call
     * @param array $extra_headers additional http headers e.g. a forward header so that
     *                             a test can simulate an external caller (see server_guard::from_own_pod)
     * @return string the result from the backend
     */
    function api_call(string $method, string $url, array $data, array $extra_headers = []): string
    {
        global $ui_sys;

        $curl = curl_init();
        $data_json = json_encode($data);

        // collect the http headers of the request so that e.g. the put content type
        // and the anti csrf token can be combined
        $headers = $extra_headers;

        switch ($method) {
            case rest_ctrl::POST:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, rest_ctrl::POST);
                break;
            case rest_ctrl::PUT:
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($data_json);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, rest_ctrl::PUT);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
                break;
            case rest_ctrl::DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, rest_ctrl::DELETE);
                $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            default:
                $url = sprintf("%s?%s", $url, http_build_query($data));

        }

        // send the anti csrf token of the session so that the api write calls pass
        // the csrf check of the backend (see server_guard::csrf_token_valid)
        if (self::$session_token != '') {
            $headers[] = api::CSRF_TOKEN_HEADER . ': ' . self::$session_token;
        }
        if ($headers != []) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        // Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // use the session of a logged in user if a session cookie file has been set
        // e.g. so that the api calls of a test run are permitted like a logged in user
        if (self::$session_cookie_file != '') {
            curl_setopt($curl, CURLOPT_COOKIEFILE, self::$session_cookie_file);
            curl_setopt($curl, CURLOPT_COOKIEJAR, self::$session_cookie_file);
        }

        // show every api call from '&debug=5' upward (url_var::DEBUG_LEVEL_API_CALL) to trace which
        // backend calls a rendering triggers, because an api call is neither a db read nor a db write
        log_debug($method . ' ' . $url, url_var::DEBUG_LEVEL_API_CALL);

        // the backend names in a header if the answer comes from the cache (see def::DEBUG_API_CACHE)
        $cache_state = '';
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, string $line) use (&$cache_state): int {
            $cache_state = self::cache_state_of($line) ?: $cache_state;
            return strlen($line);
        });

        // the wait is counted on the frontend cache, because web/ must not use the backend $sys
        $wait_start = microtime(true);
        $result = curl_exec($curl);
        $wait = microtime(true) - $wait_start;
        if ($ui_sys != null) {
            $ui_sys->api_wait += $wait;
        }
        // the whole wait is split: the curl time around the call, the connect, the server time and
        // the transfer; a slow answer names the steps of its server time and is then passed on like
        // a fast answer (see controller::json_with_time)
        $connect = curl_getinfo($curl, CURLINFO_CONNECT_TIME);
        $first_byte = curl_getinfo($curl, CURLINFO_STARTTRANSFER_TIME);
        $total = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        $answer = '';
        if (is_string($result)) {
            $answer = $result;
            $result = self::without_time($result);
        }
        $ui_sys?->add_api_steps(self::curl_step($wait, $total));
        $ui_sys?->add_api_steps(self::transfer_steps($connect, $first_byte, $total));
        $ui_sys?->add_api_steps(self::server_steps($answer, $first_byte - $connect));

        // the time shown with this message is the wait for the backend, so that the time of the next
        // message is the work of the frontend after the answer and not the api call
        log_debug('answer of ' . $method . ' ' . $url, url_var::DEBUG_LEVEL_API_CALL);

        // the header is only sent if the debug switch of the backend is on, so the message is shown at every level
        if ($cache_state != '') {
            log_debug('api cache ' . $cache_state . ' for ' . $url, url_var::DEBUG_LEVEL_SWITCHED);
        }

        if ($result === false) {
            $error = curl_error($curl);
        } else {
            $error = '';
        }

        curl_close($curl);

        if ($error != '') {
            return $error;
        } else {
            return $result;
        }
    }


}
