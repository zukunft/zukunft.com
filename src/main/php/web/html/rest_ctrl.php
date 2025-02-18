<?php

/*

    web/html/api_const.php - constants used for the backend to frontend api of zukunft.com
    ----------------------


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

//include_once API_OBJECT_PATH . 'controller.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';

use controller\controller;
use shared\api;
use shared\library;

class rest_ctrl
{

    // methods used
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    // url path of the api
    const PATH = 'api/';

    // url path to the fixed views
    const PATH_FIXED = '/http/';
    const URL_MAIN_SCRIPT = 'view';

    // url extension of the fixed views
    const EXT = '.php';

    // classes used to allow renaming of the API name independent of the class name
    const WORD = 'word';
    const VERB = 'verb';
    const TRIPLE = 'triple';
    const VALUE = 'value';
    const FORMULA = 'formula';
    const VIEW = 'view';
    const LINK = 'link';
    const SOURCE = 'source';
    const LANGUAGE = 'language';

    // class extensions of all possible the fixed views
    const CREATE = '_add';
    const UPDATE = '_edit';
    const REMOVE = '_del';
    const LIST = '';
    const SEARCH = 'find';

    // special api function independent of a class
    const LOGIN_RESET = 'login_reset';
    const ERROR_UPDATE = 'error_update';
    const URL_ABOUT = 'about';

    // view parameter names
    const PAR_VIEW_VERBS = 'verbs';  // to select the verbs that should be display
    const PAR_LOG_STATUS = 'status'; // to set the status of a log entry
    const PAR_VIEW_SOURCES = 'sources';  // to select the formulas that should be display
    const PAR_VIEW_LANGUAGES = 'languages';  // to select the formulas that should be display
    const PAR_VIEW_NEW_ID = 'new_id'; // if the user has changed the view for this word, save it
    const PAR_VIEW_ID = 'view'; // if the user has selected a special view, use it

    // classes used
    const CLASS_FORM_ROW = 'form-row';

    // to be reviewed
    const VALUE_EDIT = 'value_edit';
    const RESULT_EDIT = 'result_edit';

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
     * create an execute an api call for a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param int $id the id of the database object that should be loaded
     * @param array $data additional data that should be included in the get request
     * @return array with the body json message from the backend
     */
    function api_call_id(string $class, int $id, array $data = []): array
    {
        $data[api::URL_VAR_ID] = $id;
        return $this->api_get($class, $data);
    }

    /**
     * create an execute an api call for a database object
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
     * create and execute an api call for a database object
     * by id
     * @param string $class the frontend class name that should be loaded
     * @param array $data with the parameter for the get call
     * @return array with the body json message from the backend
     */
    function api_get(string $class, array $data): array
    {
        $html = new html_base();
        $ctrl = new controller();
        $api_name = $this->class_to_api_name($class);
        $url = $html->url_api($api_name, []);
        $json_str = $this->api_call(self::GET, $url, $data);
        $jsom_msg = json_decode($json_str, true);
        return $ctrl->check_api_msg($jsom_msg);
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
            case self::POST:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::POST);
                break;
            case self::PUT:
                curl_setopt($curl,
                    CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json', 'Content-Length: ' . strlen($data_json)));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::PUT);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
                break;
            case self::DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::DELETE);
                $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            default:
                $url = sprintf("%s?%s", $url, http_build_query($data));

        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

}
