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

namespace controller;

//include_once SERVICE_PATH . 'config.php';
//include_once SHARED_PATH . 'library.php';

use cfg\config;
use cfg\db\sql_db;
use cfg\user\user;
use DateTime;
use DateTimeInterface;
use shared\json_fields;
use shared\library;

class api_message
{

    /**
     * create and set the api message header information
     * @param sql_db $db_con the active database link to get the configuration from the database
     * @param string $class the class of the message
     * @param user|null $usr the user view that the api message should contain
     * @param array $vars the json array for the message body
     * @return array the json array including the message header
     */
    function api_header_array(
        sql_db    $db_con,
        string    $class,
        user|null $usr,
        array     $vars
    ): array
    {
        $lib = new library();
        $cfg = new config();
        $class = $lib->class_to_name($class);
        $msg = [];
        if ($db_con->connected()) {
            $msg[json_fields::POD] = $cfg->get_db(config::SITE_NAME, $db_con);
        } else {
            // for unit tests use the default pod name
            $msg[json_fields::POD] = POD_NAME;
        }
        $msg[json_fields::TYPE_NAME] = $class;
        if ($usr != null) {
            $msg[json_fields::USER_ID] = $usr->id();
            $msg[json_fields::USER_NAME] = $usr->name();
        }
        $msg[json_fields::VERSION] = PRG_VERSION;
        $msg[json_fields::TIMESTAMP] = (new DateTime())->format(DateTimeInterface::ATOM);
        $msg[json_fields::BODY] = $vars;

        return $msg;

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
