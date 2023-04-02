<?php

/*

    controller.php - the base class for API controller
    --------------

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

namespace controller;

include_once API_PATH . 'message_header.php';
include_once API_SYSTEM_PATH . 'type_lists.php';
include_once API_SANDBOX_PATH . 'combine_object.php';
include_once API_SANDBOX_PATH . 'list.php';
include_once API_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_WORD_PATH . 'word.php';

use api_message;
use api\combine_object_api;
use api\list_api;
use api\type_lists_api;
use api\sandbox_api;
use model\sandbox;
use model\source;
use model\word;

class controller
{

    // the parameter names used in the url or in th result json
    const URL_API_PATH = 'api/';
    const URL_VAR_ID = 'id'; // the internal database id that should never be shown to the user
    const URL_VAR_ID_LST = 'ids'; // a comma seperated list of internal database ids
    const URL_VAR_NAME = 'name'; // the unique name of a term, view, component, user, source, language or type
    const URL_VAR_DEBUG = 'debug'; // to force the output of debug messages
    const URL_VAR_CODE_ID = 'code_id';
    const URL_VAR_WORD = 'words';
    const URL_VAR_MSG = 'message';
    const URL_VAR_RESULT = 'result';
    const URL_VAR_EMAIL = 'email';

    // used for the change log
    const URL_VAR_WORD_ID = 'word_id';
    const URL_VAR_WORD_FLD = 'word_field';

    // field names of the api json messages
    const API_FLD_ID = 'id';  // the json field name in the api json message which is supposed to be the same as the var $id
    const API_FLD_NAME = 'name';
    const API_FLD_DESCRIPTION = 'description';
    // the json field name in the api json message which is supposed to contain the code id of an object type
    // e.g. for the word api message it contains the id of the phrase type
    const API_FLD_TYPE = 'type';
    // the json field name in the api json message which is supposed to contain the database id of an object type
    // e.g. for the word api message it contains the id of the phrase type
    const API_FLD_TYPE_ID = 'type_id';
    const API_FLD_PHRASES = 'phrases';

    // path parameters
    const PATH_API_REDIRECT = '/../../'; // get from the __DIR__ to the php root path
    const PATH_MAIN_LIB = 'src/main/php/zu_lib.php'; // the main php library the contains all other paths

    /**
     * response to a get request
     *
     * @param string $api_obj the object as a json string that should be returned
     * @param string $msg the message as a json string that should be returned
     * @return void
     */
    private function get_response(string $api_obj, string $msg): void
    {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET");

        // return the api json or the error message
        if ($msg == '') {

            // set response code - 200 OK
            http_response_code(200);

            // return e.g. the word object
            echo $api_obj;

        } else {

            // set response code - 400 Bad Request
            http_response_code(400);

            // tell the user no products found
            echo json_encode(
                array(self::URL_VAR_MSG => $msg)
            );
        }
    }

    /**
     * response to post, get, put and delete requests
     *
     * @param string $api_obj the object as a json string that should be returned
     * @param string $msg the message as a json string that should be returned
     * @param int $id the id of object that should be deleted
     * @return void
     */
    private function curl_response(string $api_obj, string $msg, int $id = 0, ?sandbox $obj = null): void
    {
        // required headers
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST,GET,PUT,DELETE");

        // method switch
        $method = $_SERVER['REQUEST_METHOD'];
        log_debug($method);
        switch ($method) {
            case 'PUT':
                // get json object body to put
                $request_text = file_get_contents('php://input');
                $request_json = json_decode($request_text, true);
                $request_body = $this->check_api_msg($request_json);

                // call to backend
                $result = $this->put($request_body, $obj::class);

                // return the result
                if (is_numeric($result)) {

                    // set response code - 200 OK
                    http_response_code(200);
                    echo json_encode(
                        array(self::URL_VAR_ID => $result)
                    );
                } else {

                    // set response code - 400 Bad Request
                    http_response_code(400);
                    echo json_encode(
                        array(self::URL_VAR_MSG => $result)
                    );
                }
                break;
            case 'GET':
                // return the api json or the error message
                if ($msg == '') {

                    // set response code - 200 OK
                    http_response_code(200);

                    // return e.g. the word object
                    echo $api_obj;

                } else {

                    // set response code - 400 Bad Request
                    http_response_code(400);

                    // tell the user no object found
                    echo json_encode(
                        array(self::URL_VAR_MSG => $msg)
                    );
                }
                break;
            case 'POST':
                // return the api json or the error message
                if ($msg == '') {
                    $request_text = file_get_contents('php://input');
                    $request_json = json_decode($request_text, true);
                    $request_body = $this->check_api_msg($request_json);

                    // call to backend
                    $result = $this->post($request_body, $obj::class);

                    // return the result
                    if (is_numeric($result)) {
                        // set response code - 200 OK
                        http_response_code(200);
                        echo json_encode(
                            array(self::URL_VAR_ID => $result)
                        );
                    } else {

                        // set response code - 400 Bad Request
                        http_response_code(400);
                        echo json_encode(
                            array(self::URL_VAR_MSG => $result)
                        );
                    }
                }
                break;
            case 'DELETE':
                // return the api json or the error message
                if ($msg == '') {

                    if ($id > 0) {
                        $result = $obj->del($id);
                        if ($result->is_ok()) {
                            // set response code - 200 OK
                            http_response_code(200);
                        } else {
                            // set response code - 409 Conflict
                            http_response_code(409);

                            echo json_encode(
                                array(self::URL_VAR_RESULT => $result->get_last_message())
                            );
                        }
                    }
                } else {

                    // set response code - 400 Bad Request
                    http_response_code(400);
                    // set response code - 410 Gone
                    // http_response_code(410);
                    // set response code - 403 Forbidden
                    // http_response_code(403);

                    // tell the user no products found
                    echo json_encode(
                        array(self::URL_VAR_MSG => $msg)
                    );
                }
                break;
            default:
                // set response code - 400 Bad Request
                http_response_code(400);
                break;
        }
    }

    public
    function not_permitted(string $msg): void
    {
        http_response_code(401);
        $this->curl_response('', $msg);
    }

    /**
     * encode an user sandbox object for the frontend api
     * and response to a get request
     *
     * @param sandbox_api|combine_object_api $api_obj the object that should be encoded
     * @param string $msg if filled the message that should be shown to the user instead of the object
     * @return void
     */
    function get(sandbox_api|combine_object_api $api_obj, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response(json_encode($api_obj), $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    function get_list(list_api $api_obj, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response(json_encode($api_obj), $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    function get_api_msg(api_message $api_obj, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response(json_encode($api_obj), $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    function get_types(type_lists_api $api_obj, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response(json_encode($api_obj), $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    function get_export(object $api_obj, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response(json_encode($api_obj), $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    public
    function check_api_msg(array $api_msg): array
    {
        $msg_ok = true;
        $body = array();
        // TODO check transfer time
        // TODO check if version matches
        if ($msg_ok) {
            if (array_key_exists('body', $api_msg)) {
                $body = $api_msg['body'];
            } else {
                $msg_ok = false;
            }
        }
        if ($msg_ok) {
            return $body;
        } else {
            return array();
        }
    }

    /**
     * encode a user sandbox object for the frontend api
     * and response to curl requests
     *
     * @param api_message $api_msg the object that should be encoded
     * @param string $msg if filled the message that should be shown to the user instead of the object
     * @param int $id
     * @return void
     */
    function curl(api_message $api_msg, string $msg, int $id, sandbox $obj): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->curl_response(json_encode($api_msg), $msg, $id, $obj);
        } else {
            // tell the user e.g. that no products found
            $this->curl_response('', $msg, $id, $obj);
        }
    }

    function put(array $request, string $class): string
    {
        global $usr;
        $result = '';
        switch ($class) {
            case word::class:
                $wrd = new word($usr);
                $result = $wrd->save_from_api_msg($request)->get_last_message();
                if ($result == '') {
                    $result = $wrd->id();
                }
                break;
            case source::class:
                $src = new source($usr);
                $result = $src->save_from_api_msg($request)->get_last_message();
                if ($result == '') {
                    $result = $src->id();
                }
                break;
        }
        return $result;
    }

    function post(array $request): string
    {
        return 'post';
    }

    function delete(array $request): string
    {
        return 'delete';
    }

}
