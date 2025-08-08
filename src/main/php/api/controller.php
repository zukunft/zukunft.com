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
use cfg\helper\db_object_seq_id;
use cfg\ref\source;
use cfg\user\user;
use cfg\word\word;
use shared\api;
use shared\const\rest_ctrl;
use shared\library;
use shared\types\api_type;
use shared\url_var;

include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';

class controller
{

    /*
     * interface functions used by api
     */

    /**
     * return a json that has been requested by a GET request to the
     * REST controller
     *
     * @param string $api_json
     * @param string $msg
     * @return void
     */
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

    /**
     * execute an api post message to add a new object to the database
     * and return the id of the object just added to the
     * REST controller
     *
     * @param array $api_json the api json message
     * @param db_object_seq_id $dbo the database object that should be used to add the database row
     * @param user $usr the session user who has started the request
     * @param string $msg the message text collected until now
     * @return void
     */
    function post_json(
        array            $api_json,
        db_object_seq_id $dbo,
        user             $usr,
        string           $msg
    ): void
    {
        $result = ''; // reset the json message string

        $dbo->api_mapper($api_json);

        // add the db object e.g. word
        $add_result = $dbo->save();

        // if update was fine ...
        if ($add_result->is_ok()) {
            $id = $add_result->get_row_id();
            if ($id == 0) {
                $id = $dbo->id();
            }
            // TODO Prio 1 return only the id of the added word?
            $result = $dbo->api_json([api_type::HEADER], $usr);
        } else {
            // ... or in case of a problem prepare to show the message
            $msg .= $add_result->all_message_text();
        }
        // return either the api json with the id of the created db object e.g. word
        // or the message why the adding has failed
        $ctrl = new controller();
        $ctrl->curl_response($result, $msg, rest_ctrl::POST);
    }

    /**
     * execute an api put message to update an object in the database
     * and return the json of the updated database object to the
     * REST controller
     * e.g. if it is requested to update the description but the type is set already by someone else
     * the returned json contains the updated description and the updated type
     *
     * @param int $id the unique id of the db row that should be deleted of excluded
     * @param array $api_json the api json message
     * @param db_object_seq_id $dbo the database object that should be used to add the database row
     * @param user $usr the session user who has started the request
     * @param string $msg the message text collected until now
     * @return void
     */
    function put_json(
        int              $id,
        array            $api_json,
        db_object_seq_id $dbo,
        user             $usr,
        string           $msg
    ): void
    {
        $result = ''; // reset the json message string

        $dbo->api_mapper($api_json);
        $dbo->set_id($id);

        // update the db object e.g. word
        $upd_result = $dbo->save();

        // if update was fine ...
        if ($upd_result->is_ok()) {
            // TODO Prio 1 return only the id of the added word?
            $result = $dbo->api_json([api_type::HEADER], $usr);
        } else {
            // ... or in case of a problem prepare to show the message
            $msg .= $upd_result->all_message_text();
        }
        // return either the api json with the id of the created word
        // or the message why the adding of the word has failed
        $ctrl = new controller();
        $ctrl->curl_response($result, $msg, rest_ctrl::PUT, $id, $dbo);
    }

    /**
     * execute an api delete message to delete or exclude an object
     * and return the json of the excluded database object
     * or an empty json if the object has completely been deleted to the
     * REST controller
     *
     * @param int $id the unique id of the db row that should be deleted of excluded
     * @param db_object_seq_id $dbo the database object that should be used to add the database row
     * @param user $usr the session user who has started the request
     * @param string $msg the message text collected until now
     * @return void
     */
    function delete(
        int              $id,
        db_object_seq_id $dbo,
        user             $usr,
        string           $msg
    ): void
    {
        $result = ''; // reset the json message string

        if ($id > 0) {
            $dbo->load_by_id($id);

            // delete or exclude the word
            $del_result = $dbo->del();

            if ($del_result->is_ok()) {
                $result = $dbo->api_json([api_type::HEADER], $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $del_result->all_message_text();
            }
        } else {
            $lib = new library();
            $msg = $lib->class_to_name($dbo::class) . ' id is missing';
        }

        // add, update or delete the word
        $ctrl = new controller();
        $ctrl->curl_response($result, $msg, rest_ctrl::DELETE, $id, $dbo);
    }

    function not_permitted(string $msg): void
    {
        http_response_code(401);
        $this->curl_response('', $msg, rest_ctrl::GET);
    }


    /*
     * internal functions
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
        if (!headers_sent()) {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
        }

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
                array(url_var::MSG => $msg)
            );
        }
    }

    /**
     * response to post, get, put and delete requests
     *
     * @param string $api_obj the object as a json string that should be returned
     * @param string $msg the message as a json string that should be returned
     * @param string $method the curl method from the controller
     * @param int $id the id of object that should be deleted
     * @param db_object_seq_id|null $obj
     * @return void
     */
    private function curl_response(
        string                $api_obj,
        string                $msg,
        string                $method,
        int                   $id = 0,
        db_object_seq_id|null $obj = null
    ): void
    {
        // required headers
        if (!headers_sent()) {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
        }

        // method switch
        log_debug($method);
        switch ($method) {
            case rest_ctrl::PUT:
                // get json object body to put
                $request_text = file_get_contents(rest_ctrl::REQUEST_BODY_FILENAME);
                $request_json = json_decode($request_text, true);
                $request_body = $this->check_api_msg($request_json);

                // call to backend
                $result = $this->put($request_body, $obj::class);

                // return the result
                if (is_numeric($result)) {

                    // set response code - 200 OK
                    http_response_code(200);
                    echo json_encode(
                        array(url_var::ID => $result)
                    );
                } else {

                    // set response code - 400 Bad Request
                    http_response_code(400);
                    echo json_encode(
                        array(url_var::MSG => $result)
                    );
                }
                break;
            case rest_ctrl::GET:
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
                        array(url_var::MSG => $msg)
                    );
                }
                break;
            case rest_ctrl::POST:
                // return the api json or the error message
                if ($msg == '') {
                    $request_text = file_get_contents(rest_ctrl::REQUEST_BODY_FILENAME);
                    $request_json = json_decode($request_text, true);
                    $request_body = $this->check_api_msg($request_json);

                    // call to backend
                    $result = $this->post($request_body);

                    // return the result
                    if (is_numeric($result)) {
                        // set response code - 200 OK
                        http_response_code(200);
                        echo json_encode(
                            array(url_var::ID => $result)
                        );
                    } else {

                        // set response code - 400 Bad Request
                        http_response_code(400);
                        echo json_encode(
                            array(url_var::MSG => $result)
                        );
                    }
                }
                break;
            case rest_ctrl::DELETE:
                // return the api json or the error message
                if ($msg == '') {

                    if ($id > 0) {
                        $result = $obj->del();
                        if ($result->is_ok()) {
                            // set response code - 200 OK
                            http_response_code(200);
                        } else {
                            // set response code - 409 Conflict
                            http_response_code(409);

                            echo json_encode(
                                array(url_var::RESULT => $result->get_last_message())
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
                        array(url_var::MSG => $msg)
                    );
                }
                break;
            default:
                // set response code - 400 Bad Request
                http_response_code(400);
                break;
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

}
