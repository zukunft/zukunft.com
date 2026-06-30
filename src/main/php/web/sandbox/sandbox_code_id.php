<?php

/*

    web/sandbox/sandbox_code_id.php - extends the superclass for named html objects with the type id
    -------------------------------


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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'sandbox_typed.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

class sandbox_code_id extends sandbox_typed
{

    // the code_id to use single objects with predefined functionality also in the frontend
    public ?string $code_id = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->code_id = $json_array[json_fields::CODE_ID];
        } else {
            $this->code_id = null;
        }
        return $msg->is_ok();
    }


    /**
     * besides the base checks the phrase type may only be changed by a user that is allowed to set
     * the type; if an ip-only or name-only user actually changes it a warning is shown the usual way
     * and the change is not confirmed (mirrors the backend can_set_type_id permission)
     *
     * @param user_message $usr_msg with the requesting user and to enrich with a warning per invalid field
     * @param string $action the crud action of the change; a delete needs no type
     * @param array $url_array the pending change url with the new phrase type and its '8'-prefixed old value
     * @return bool true if the entered data can be confirmed
     */
    function input_valid(user_message $usr_msg, string $action = '', array $url_array = []): bool
    {
        $result = parent::input_valid($usr_msg, $action, $url_array);
        if ($action != url_var::CRUD_DELETE) {
            $old = $url_array[url_var::PRE . url_var::PHRASE_TYPE] ?? null;
            $new = $url_array[url_var::PHRASE_TYPE] ?? null;
            if ($new != $old) {
                $usr = $usr_msg->usr;
                if ($usr == null or !$usr->can_set_type_id()) {
                    $usr_msg->add_warning_with_vars(msg_id::TYPE_CHANGE_NOT_ALLOWED, [
                        msg_id::VAR_CLASS_NAME => library::class_to_name_translated($this::class)
                    ]);
                    $result = false;
                }
            }
        }
        return $result;
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * the code id is included in the message only to fill up backend object but never to change the code_id via ui
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::CODE_ID] = $this->code_id;
        return $vars;
    }

}


