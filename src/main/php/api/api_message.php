<?php

/*

    api/api_message.php - the JSON header object for the frontend API
    -------------------

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

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

//include_once paths::SERVICE . 'config.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use DateTime;
use DateTimeInterface;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class api_message
{

    /**
     * create the api json message string of this combine object for the frontend
     * @param string $pod_name the site_name or the pod name that has created this message
     * @param string $class the class of the message
     * @param array $vars the json array for the message body
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|user_ui|null $usr the user for whom the api message should be created which can differ from the session user
     * @returns string the api json message for the object as a string
     */
    function api_json(
        string              $pod_name,
        string              $class,
        array               $vars,
        api_type_list|array $typ_lst = [],
        user|user_ui|null   $usr = null
    ): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        // null values are not needed in the api message to the frontend
        // but in the api message to the backend null values are relevant
        // e.g. to remove empty string overwrites
        $vars = array_filter($vars, fn($value) => !is_null($value) && $value !== '');

        // add header if requested
        if ($typ_lst->use_header()) {
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($pod_name, $class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }

    /**
     * create and set the api message header information
     * @param string $pod_name the site_name or the pod name that has created this message
     * @param string $class the class of the message
     * @param user|user_ui|null $usr the user view that the api message should contain
     * @param array $vars the json array for the message body
     * @return array the json array including the message header
     */
    function api_header_array(
        string            $pod_name,
        string            $class,
        user|user_ui|null $usr,
        array             $vars
    ): array
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        $msg = [];
        $msg[json_fields::POD] = $pod_name;
        $msg[json_fields::TYPE_NAME] = $class;
        if ($usr != null) {
            $msg[json_fields::USER_ID] = $usr->id();
            $msg[json_fields::USER_NAME] = $usr->name_or_null();
        }
        $msg[json_fields::VERSION] = def::PRG_VERSION;
        $msg[json_fields::TIMESTAMP] = new DateTime()->format(DateTimeInterface::ATOM);
        $msg[json_fields::BODY] = $vars;

        return $msg;

    }

    // TODO call once in global $sys
    function api_site_name(sql_db $db_con): string
    {
        $cfg = new config();
        // for unit tests use the default pod name
        $site_name = def::POD_NAME;
        if ($db_con->connected()) {
            $site_name = $cfg->get_db(config::SITE_NAME, $db_con);
            // TODO remove this fallback case
            if ($site_name == '') {
                $site_name = def::POD_NAME;
            }
        }
        return $site_name;
    }

    /**
     * validate the message using the header and return the message body json array
     * @param array $json_array a json message array with or without message header
     * @return array the body of the json message or a user message if the message is not valid
     */
    function validate(array $json_array): array
    {
        $body = $json_array;
        if (key_exists(json_fields::POD, $json_array)
            and key_exists(json_fields::BODY, $json_array)) {
            $body = $json_array[json_fields::BODY];
        }
        return $body;
    }

}
