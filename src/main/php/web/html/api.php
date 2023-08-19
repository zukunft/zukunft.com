<?php

/*

    web\html\api_const.php - constants used for the backend to frontend api of zukunft.com
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

use cfg\library;
use controller\controller;

class api
{

    // methods used
    const GET = 'GET';

    // TODO to be move to the environment variables as defined in appication.yaml
    // the url of the backend
    const HOST_BACKEND = 'http://localhost/';

    // url path of the api
    const PATH = 'api/';

    // url path to the fixed views
    const PATH_FIXED = '/http/';

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

    // view parameter names
    const PAR_VIEW_WORDS = 'words';  // to select the words that should be display
    const PAR_VIEW_TRIPLES = 'triples';  // to select the triple that should be display
    const PAR_VIEW_FORMULAS = 'formulas';  // to select the formulas that should be display
    const PAR_VIEW_VERBS = 'verbs';  // to select the verbs that should be display
    const PAR_LOG_STATUS = 'status'; // to set the status of a log entry
    const PAR_VIEW_SOURCES = 'sources';  // to select the formulas that should be display
    const PAR_VIEW_LANGUAGES = 'languages';  // to select the formulas that should be display

    // styles used
    const STYLE_GREY = 'grey';
    const STYLE_GLYPH = 'glyphicon glyphicon-pencil';
    const STYLE_USER = 'user_specific';
    const STYLE_RIGHT = 'right_ref';

    // classes used
    const CLASS_FORM_ROW = 'form-row';
    const CLASS_COL_4 = 'col-sm-4';

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
     * @return array with the body json message from the backend
     */
    function api_call_id(string $class, int $id): array
    {
        $data = array();
        $data[controller::URL_VAR_ID] = $id;
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
        $data[controller::URL_VAR_NAME] = $name;
        return $this->api_get($class, $data);
    }

    /**
     * create an execute an api call for a database object
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

    function api_call(string $method, string $url, array $data): string
    {
        $curl = curl_init();
        $data_json = json_encode($data);


        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                break;
            case "PUT":
                curl_setopt($curl,
                    CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json', 'Content-Length: ' . strlen($data_json)));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
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
