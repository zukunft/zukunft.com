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

use api\list_api;
use api\user_sandbox_api;

class controller
{

    // the parameter names used in the url
    CONST URL_VAR_DEBUG = 'debug';
    CONST URL_VAR_WORD = 'words';

    // used for the change log
    CONST URL_VAR_WORD_ID = 'word_id';
    CONST URL_VAR_WORD_FLD = 'word_field';

    // path parameters
    CONST PATH_API_REDIRECT = '/../../'; // get from the __DIR__ to the php root path
    CONST PATH_MAIN_LIB = 'src/main/php/zu_lib.php'; // the main php library the contains all other paths

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
                array("message" => $msg)
            );
        }
    }

    /**
     * encode an user sandbox object for the frontend api
     * and response to a get request
     *
     * @param user_sandbox_api $api_obj the object that should be encoded
     * @param string $msg if filled the message that should be shown to the user instead of the object
     * @return void
     */
    function get(user_sandbox_api $api_obj, string $msg): void
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
}
