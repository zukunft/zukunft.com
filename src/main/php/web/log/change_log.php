<?php

/*

    web/user/user_log.php - the common change log object for the frontend API
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

namespace html\log;

include_once WEB_SANDBOX_PATH . 'sandbox.php';
include_once WEB_USER_PATH . 'user.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_PATH . 'json_fields.php';

use html\sandbox\sandbox;
use html\user\user as user;
use html\user\user_message;
use shared\json_fields;
use DateTime;
use Exception;

class change_log extends sandbox
{


    /*
     * object vars
     */

    public ?user $usr = null;  // the user who has done the change
    public ?int $action_id = null;   // database id for the change type (add, change or del)
    public ?int $table_id = null;    // database id of the table used to get the name from the preloaded hash
    public ?int $field_id = null;    // database id of the table used to get the name from the preloaded hash
    public ?int $row_id = null;      // prime database key of the row that has been changed
    public DateTime $change_time;    // the time of the change


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(json_fields::CHANGE_TIME, $json_array)) {
            try {
                $this->change_time = new DateTime($json_array[json_fields::CHANGE_TIME]);
            } catch (Exception $e) {
                $usr_msg = $json_array[json_fields::CHANGE_TIME]
                    . ' has wrong change time format because ' . $e->getMessage();
            }
        } else {
            $this->change_time = new DateTime();
        }
        if (array_key_exists(json_fields::ACTION_ID, $json_array)) {
            $this->action_id = $json_array[json_fields::ACTION_ID];
        } else {
            $this->action_id = null;
        }
        if (array_key_exists(json_fields::TABLE_ID, $json_array)) {
            $this->table_id = $json_array[json_fields::TABLE_ID];
        } else {
            $this->table_id = null;
        }
        if (array_key_exists(json_fields::FIELD_ID, $json_array)) {
            $this->field_id = $json_array[json_fields::FIELD_ID];
        } else {
            $this->field_id = null;
        }
        if (array_key_exists(json_fields::ROW_ID, $json_array)) {
            $this->row_id = $json_array[json_fields::ROW_ID];
        } else {
            $this->row_id = null;
        }
        return $usr_msg;
    }

}
