<?php

/*

    web/sandbox/combine_object.php - parent object to combine two or four sandbox web objects
    ------------------------------

    e.g. to combine value and result to figure
    or word and triple to phrase
    or word, triple, verb and formula to term

    TODO use it for phrase
    TODO use it for term


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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'CombineObject.php';

use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;

class combine_object extends CombineObject
{

    /*
     * construct and map
     */

    /**
     * the html display object are always filled base on the api message
     * TODO Prio 1 add user_message as parameter
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        $msg = new user_message(); // a buffer of this constructor, see the TODO above to take the caller's
        parent::__construct(new word());
        if ($api_json != null) {
            $this->set_from_json($api_json, $msg);
        } else {
            $this->set_obj(new word());
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of this combine frontend object bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function set_from_json(string $json_api_msg, user_message $msg): bool
    {
        return $this->api_mapper(json_decode($json_api_msg, true), $msg);
    }

    /**
     * set the vars of this combine frontend object bases on the api json array
     * dummy function that should be overwritten by the child object
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        $msg->add_err_with_vars(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'api_mapper',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $msg->is_ok();
    }


    /*
     * load
     */

    /**
     * load the combine object e.g. phrase by id via api
     * TODO Prio 1 add user_message as parameter
     * @param int $id
     * @param user_message $msg to collect the load warnings for the user
     * @return bool
     */
    function load_by_id(int $id, user_message $msg): bool
    {
        $result = false;

        $api = new rest_call();
        $json_body = $api->api_call_id($this::class, $id);
        if ($json_body) {
            $this->api_mapper($json_body, $msg);
            $result = true;
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string the unique id fields
     */
    function dsp_id(): string
    {
        if ($this->obj() != null) {
            return $this->obj()->dsp_id() . ' as term';
        } else {
            return 'term with null object';
        }
    }

}
