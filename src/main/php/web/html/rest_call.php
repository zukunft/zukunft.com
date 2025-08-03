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

namespace html;

use cfg\const\paths;

//include_once paths::API_OBJECT . 'controller.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'library.php';

use controller\controller;
use shared\api;
use shared\const\rest_ctrl;
use shared\library;

class rest_call
{

    /**
     * create the class name as used for the api
     * @param string $class the class name that should be used
     * @return string the api url
     */
    function class_to_api_name(string $class): string
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        return $lib->camelize_ex_1($class);
    }

    /**
     * create and execute an api call for a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param int|string $id|string the id of the database object that should be loaded
     * @param array $data additional data that should be included in the get request
     * @return array with the body json message from the backend
     */
    function api_call_id(string $class, int|string $id, array $data = []): array
    {
        $data[api::URL_VAR_ID] = $id;
        return $this->api_get($class, $data);
    }

    /**
     * create and execute an api call for a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param string $name the name of the database object that should be loaded
     * @return array with the body json message from the backend
     */
    function api_call_name(string $class, string $name): array
    {
        $data = array();
        $data[api::URL_VAR_NAME] = $name;
        return $this->api_get($class, $data);
    }

    /**
     * create and execute an api get call to get a json message of a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the get call
     * @return array with the body json message from the backend
     */
    function api_get(string $class, array $data): array
    {
        return $this->api_curl_call($class, $data, rest_ctrl::GET);
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
     * execute an api call
     *
     * @param string $method the REST method (GET, POST, PUT or DELETE)
     * @param string $url the url that should be called
     * @param array $data the data as a json array that should be included in the call
     * @return string the result from the backend
     */
    function api_call(string $method, string $url, array $data): string
    {
        $curl = curl_init();
        $data_json = json_encode($data);

        switch ($method) {
            case rest_ctrl::POST:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, rest_ctrl::POST);
                break;
            case rest_ctrl::PUT:
                curl_setopt($curl,
                    CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json', 'Content-Length: ' . strlen($data_json)));
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

        // Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($curl);

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

    function request_json(): array
    {
        $request_text = file_get_contents(rest_ctrl::REQUEST_BODY_FILENAME);
        return json_decode($request_text, true);
    }

}
