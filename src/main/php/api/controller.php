<?php

/*

    controller.php - the base class for API controller
    --------------

    includes all const for the API


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

use cfg\const\paths;

include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';

use cfg\helper\combine_object;
use cfg\ref\source;
use cfg\sandbox\sandbox;
use cfg\word\word;
use shared\api;

class controller
{

    /*
     * functions
     */

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
                array(api::URL_VAR_MSG => $msg)
            );
        }
    }

    /**
     * response to post, get, put and delete requests
     *
     * @param string $api_obj the object as a json string that should be returned
     * @param string $msg the message as a json string that should be returned
     * @param int $id the id of object that should be deleted
     * @param sandbox|combine_object|null $obj
     * @return void
     */
    private function curl_response(
        string                      $api_obj,
        string                      $msg,
        int                         $id = 0,
        sandbox|combine_object|null $obj = null
    ): void
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
                        array(api::URL_VAR_ID => $result)
                    );
                } else {

                    // set response code - 400 Bad Request
                    http_response_code(400);
                    echo json_encode(
                        array(api::URL_VAR_MSG => $result)
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
                        array(api::URL_VAR_MSG => $msg)
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
                    $result = $this->post($request_body);

                    // return the result
                    if (is_numeric($result)) {
                        // set response code - 200 OK
                        http_response_code(200);
                        echo json_encode(
                            array(api::URL_VAR_ID => $result)
                        );
                    } else {

                        // set response code - 400 Bad Request
                        http_response_code(400);
                        echo json_encode(
                            array(api::URL_VAR_MSG => $result)
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
                                array(api::URL_VAR_RESULT => $result->get_last_message())
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
                        array(api::URL_VAR_MSG => $msg)
                    );
                }
                break;
            default:
                // set response code - 400 Bad Request
                http_response_code(400);
                break;
        }
    }

    public function not_permitted(string $msg): void
    {
        http_response_code(401);
        $this->curl_response('', $msg);
    }

    function get_json(string $api_json, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response($api_json, $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    function get_export_json(string $json, string $msg): void
    {
        // return the api json or the error message
        if ($msg == '') {
            $this->get_response($json, $msg);
        } else {
            // tell the user e.g. that no products found
            $this->get_response('', $msg);
        }
    }

    /**
     * check if an api message is fine
     * @param array|null $api_msg the complete api message including the header and in some cases several body parts
     * @param string $body_key to select a body part of the api message
     * @return array the message body if everything has been fine or an empty array
     */
    function check_api_msg(?array $api_msg, string $body_key = api::JSON_BODY): array
    {
        $msg_ok = true;
        $body = array();
        if ($api_msg !== null) {
            // TODO check transfer time
            // TODO check if version matches
            if ($msg_ok) {
                if (array_key_exists($body_key, $api_msg)) {
                    $body = $api_msg[$body_key];
                } else {
                    // TODO activate Prio 3 next line and avoid these cases
                    // $msg_ok = false;
                    $body = $api_msg;
                    log_warning('message header missing in api message');
                }
            }
            if ($msg_ok) {
                return $body;
            } else {
                return array();
            }
        } else {
            return array();
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
