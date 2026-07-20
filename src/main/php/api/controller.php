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

namespace Zukunft\ZukunftCom\main\php\api;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'server_guard.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

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
        if (!$this->change_permitted($usr, $msg)) {
            return;
        }

        $result = ''; // reset the json message string
        $usr_msg = new user_message($usr);

        $dbo->api_mapper($api_json, $usr_msg);

        // add the db object e.g. word
        $dbo->save($usr_msg);

        // if update was fine ...
        if ($usr_msg->is_ok()) {
            $id = $usr_msg->get_row_id();
            if ($id == 0) {
                $id = $dbo->id();
            }
            // TODO Prio 1 return only the id of the added word?
            $result = $dbo->api_json([api_types::HEADER], $usr);
        } else {
            // ... or in case of a problem prepare to show the message
            $msg .= $usr_msg->all_message_text();
        }
        // return either the api json with the id of the created db object e.g. word
        // or the message why the adding has failed
        $ctrl = new controller();
        $ctrl->curl_response($result, $msg, rest_ctrl::POST, 0, $dbo);
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
        if (!$this->change_permitted($usr, $msg)) {
            return;
        }

        $result = ''; // reset the json message string
        $usr_msg = new user_message($usr);

        $dbo->api_mapper($api_json, $usr_msg);
        $dbo->id = $id;

        // update the db object e.g. word
        $dbo->save($usr_msg);

        // if update was fine ...
        if ($usr_msg->is_ok()) {
            // TODO Prio 1 return only the id of the added word?
            $result = $dbo->api_json([api_types::HEADER], $usr);
        } else {
            // ... or in case of a problem prepare to show the message
            $msg .= $usr_msg->all_message_text();
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
        if (!$this->change_permitted($usr, $msg)) {
            return;
        }

        $result = ''; // reset the json message string
        $usr_msg = new user_message($usr);

        if ($id > 0) {
            $dbo->load_by_id($id);

            // delete or exclude the word
            $dbo->del($usr_msg);

            if ($usr_msg->is_ok()) {
                $result = $dbo->api_json([api_types::HEADER], $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $usr_msg->all_message_text();
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
        $this->set_response_code(401);
        $this->curl_response('', $msg, rest_ctrl::GET);
    }

    /**
     * true if the current write request (post / put / delete) may proceed, guarding both against a
     * cross-site request (csrf) and against a user who is not permitted to change data in this pod:
     * the origin check fails closed for a forged cross-origin browser request; the is_blocked check
     * is the same as in the model (sandbox->save and sandbox->del) but done before the api json is
     * mapped, so a user without login gets a clear rejection instead of a change that fails later
     *
     * @param user $usr the session user who has started the request
     * @param string $msg the message text collected until now
     * @return bool false if the write is a suspected csrf or the user may not change data in this pod
     */
    private function change_permitted(user $usr, string $msg): bool
    {
        $permitted = true;

        // reject a cross-site write before touching the database: a browser sends an Origin (or a
        // Referer) whose host must match this pod's host, so a forged request from another site is
        // stopped; a same-origin call or a non-browser server-to-server call (no such header) passes
        if (!server_guard::same_origin()) {
            $permitted = false;
            $usr_msg = new user_message($usr);
            $usr_msg->add(msg_id::CHANGE_BLOCKED_CROSS_ORIGIN, []);
            $this->not_permitted($msg . $usr_msg->all_message_text());
        } elseif (!server_guard::csrf_token_valid()) {
            // require the per-session csrf token in the X-CSRF-Token header (synchronizer token,
            // closes the same_origin both-headers-absent fail-open); a forged cross-site write
            // cannot supply it. see server_guard::csrf_token_valid
            $permitted = false;
            $usr_msg = new user_message($usr);
            $usr_msg->add(msg_id::CHANGE_BLOCKED_CROSS_ORIGIN, []);
            $this->not_permitted($msg . $usr_msg->all_message_text());
        } elseif ($usr->is_blocked()) {
            $permitted = false;
            // tell the user why the change has been rejected and how to solve it
            $usr_msg = new user_message($usr);
            $usr_msg->add(msg_id::CHANGE_BLOCKED_FOR_IP_USER, []);
            $this->not_permitted($msg . $usr_msg->all_message_text());
        }

        return $permitted;
    }


    /*
     * internal functions
     */

    /**
     * set the http response code only if the response headers have not been sent yet
     * during the test run the log writer may already have emitted output (e.g. a test header),
     * which sends the headers, so setting the response code afterwards would raise a php warning
     * (Cannot set response code - headers already sent); this guard matches the header() calls
     * that are already protected the same way
     * @param int $code the http response code e.g. 200 or 400
     * @return void
     */
    private function set_response_code(int $code): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }
    }

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
            header("Content-Type: application/json; charset=" . def::ENCODING);
            header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
        }

        // return the api json or the error message
        if ($msg == '') {

            // set response code - 200 OK
            $this->set_response_code(200);

            // return e.g. the word object
            echo $api_obj;

        } else {

            // set response code - 400 Bad Request
            $this->set_response_code(400);

            // tell the user no products found
            echo json_encode(
                array(json_fields::MSG => $msg)
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
            header("Content-Type: application/json; charset=" . def::ENCODING);
            header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
        }

        // method switch. put_json / delete have already run change_permitted and saved / deleted the
        // object before calling this, so here only format the response - never map+save or delete
        // again (the previous re-execution here was a double write / double delete)
        log_debug($method);
        switch ($method) {
            case rest_ctrl::PUT:
                if ($msg == '') {
                    if (!headers_sent()) {
                        $this->set_response_code(200);
                    }
                    echo json_encode(
                        array(url_var::ID => $obj?->id() ?? $id)
                    );
                } else {
                    if (!headers_sent()) {
                        $this->set_response_code(400);
                    }
                    echo json_encode(
                        array(json_fields::MSG => $msg)
                    );
                }
                break;
            case rest_ctrl::GET:
                // return the api json or the error message
                if ($msg == '') {

                    // set response code - 200 OK
                    if (!headers_sent()) {
                        $this->set_response_code(200);
                    }

                    // return e.g. the word object
                    echo $api_obj;

                } else {

                    // set response code - 400 Bad Request
                    if (!headers_sent()) {
                        $this->set_response_code(400);
                    }

                    // tell the user no object found
                    echo json_encode(
                        array(json_fields::MSG => $msg)
                    );
                }
                break;
            case rest_ctrl::POST:
                // post_json has already saved the new object; return its id or the error message
                if ($msg == '') {
                    if (!headers_sent()) {
                        $this->set_response_code(200);
                    }
                    echo json_encode(
                        array(url_var::ID => $obj?->id() ?? $id)
                    );
                } else {
                    if (!headers_sent()) {
                        $this->set_response_code(400);
                    }
                    echo json_encode(
                        array(json_fields::MSG => $msg)
                    );
                }
                break;
            case rest_ctrl::DELETE:
                if ($msg == '') {
                    if (!headers_sent()) {
                        $this->set_response_code(200);
                    }
                    echo json_encode(
                        array(url_var::ID => $id)
                    );
                } elseif ($id > 0) {
                    // delete() ran with an id but del() failed, e.g. the object is still in use -
                    // a conflict (409), not a bad request; the object was not re-deleted here
                    if (!headers_sent()) {
                        $this->set_response_code(409);
                    }
                    echo json_encode(
                        array(json_fields::MSG => $msg)
                    );
                } else {
                    // no id was given (400 bad request)
                    if (!headers_sent()) {
                        $this->set_response_code(400);
                    }
                    echo json_encode(
                        array(json_fields::MSG => $msg)
                    );
                }
                break;
            default:
                // set response code - 400 Bad Request
                if (!headers_sent()) {
                    $this->set_response_code(400);
                }
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
                    // TODO Prio 3 activate next line and avoid these cases
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


}
