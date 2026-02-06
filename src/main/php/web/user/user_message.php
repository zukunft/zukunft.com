<?php

/*

    web/user/user_message.php - messages created by the frontend for the user
    -------------------------


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

namespace Zukunft\ZukunftCom\main\php\web\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class user_message extends Message
{

    // array of the messages that should be shown to the user to explain the result of a process
    private array $txt;
    // the prime database row that has caused the user message
    private int|string $db_row_id;

    public ?user $usr;

    // a list of solutions suggested by the program
    //private user_actions $actions;

    /**
     * assumes that normally everything is fine
     * @param string $txt an initial message text
     *                         if this text is not empty it is assumed that something went wrong
     */
    function __construct(?user $usr = null, string $txt = '')
    {
        parent::__construct();
        $this->txt = [];
        if ($txt !== '') {
            $this->txt[] = $txt;
            $this->set_not_ok();
        }
        $this->db_row_id = 0;
        $this->usr = $usr;
    }


    /*
     * api
     */

    /**
     * create a json array to send the messages to the frontend
     * TODO Prio 1 move the text messages to id message and include it in the json
     * TODO Prio 2 add the solution with the prepared job id
     * @return array with the messages
     */
    function api_array(): array
    {
        $vars = array();
        $msg_lst = [];
        foreach ($this->msg_id_lst as $id_msg) {
            $msg_lst[] = $id_msg;
        }
        $vars[json_fields::USER_MESSAGES] = $msg_lst;
        $var_lst = [];
        foreach ($this->msg_var_lst as $var_msg) {
            $var_lst[] = $var_msg;
        }
        $vars[json_fields::USER_MESSAGES_WITH_VARS] = $var_lst;
        $vars[json_fields::USER_MESSAGES_STATUS] = $this->msg_status;
        if ($this->usr != null) {
            $vars[json_fields::USER] = $this->usr->api_array();
        }
        return $vars;
    }

    /**
     * @return string the json message to the backend as a string
     */
    function api_json(): string
    {
        return json_encode($this->api_array());
    }

    /**
     * fill the vars with this database message object based on the given api json array
     * @param array $api_json the api array with the frontend message
     */
    function api_mapper(array $api_json): void
    {
        if (array_key_exists(json_fields::USER_MESSAGES, $api_json)) {
            $msg_lst = $api_json[json_fields::USER_MESSAGES];
            foreach ($msg_lst as $id_msg) {
                $this->msg_id_lst[] = $id_msg;
            }
        }
        if (array_key_exists(json_fields::USER_MESSAGES_WITH_VARS, $api_json)) {
            $var_lst = $api_json[json_fields::USER_MESSAGES_WITH_VARS];
            foreach ($var_lst as $var_msg) {
                $this->msg_var_lst[] = $var_msg;
            }
        }
        if (array_key_exists(json_fields::USER_MESSAGES_STATUS, $api_json)) {
            $this->msg_status = $api_json[json_fields::USER_MESSAGES_STATUS];
        }
        if (array_key_exists(json_fields::USER, $api_json)) {
            $usr = new user();
            $usr_msg = new user_message();
            $usr->api_mapper($api_json[json_fields::USER],$usr_msg);
            if ($usr_msg->is_ok()) {
                $this->usr = $usr;
            }
        }
    }


    /*
     * add
     */

    /**
     * add a message id
     * to offer the user to see more details without a retry
     * more than one message id can be added to a user message result.
     * the message id is translated to the user interface language at the latest possible moment
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_id(?msg_id $msg_id): void
    {
        if ($msg_id != null) {
            // do not repeat the same text more than once
            if (!in_array($msg_id, $this->msg_id_lst)) {
                $this->msg_id_lst[] = $msg_id;
            }
            // if a message text is added it is expected that the result was not ok, but other statuus are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * add a error message with variables
     * and add the translated message to the log so that the admin can also see it
     * TODO Prio 3 check if the causing user is added to the log
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_err_with_vars(?msg_id $msg_id, array $var_lst): void
    {
        $this->add($msg_id, $var_lst, true);
        $msg = $this->get_last_message_translated();
        log_err($msg);
    }

    /**
     * add a message classified as an error
     * @param string $txt the explanation that should be shown to the user
     * @return void
     */
    function add_error_text(string $txt): void
    {
        if ($txt != '') {
            $this->add_message_text($txt);
            $this->set_error();
        }
    }

    /**
     * add a message classified as a warning
     * @param string $txt the explanation that should be shown to the user
     * @return void
     */
    function add_warning(string $txt): void
    {
        if ($txt != '') {
            $this->add_message_text($txt);
            $this->set_warning();
        }
    }

    /**
     * to offer the user to see more details without a retry,
     * more than one message text can be added to a user message result
     *
     * @param string $txt the message text to add
     * @return void is never expected to fail
     */
    function add_message(string $txt): void
    {
        if ($txt != '') {
            $this->add_message_text($txt);
            // if a message text is added it is expected that the result was not ok, but other statuus are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    public function add_message_text(string $txt): void
    {
        // do not repeat the same text more than once
        if (!in_array($txt, $this->txt)) {
            $this->txt[] = $txt;
        }
    }

    /**
     * combine the given message with this message
     *
     * @param user_message|Message $msg_to_add a message of which all parameters should be added to this message
     * @return void is never expected to fail
     */
    function merge(user_message|Message $msg_to_add): void
    {
        foreach ($msg_to_add->get_all_messages() as $msg_text) {
            $this->add_message_text($msg_text);
        }
        foreach ($msg_to_add->get_all_var_messages() as $msg_var) {
            $this->add($msg_var[0], $msg_var[1], $msg_to_add->is_ok());
        }
        $this->combine_status($msg_to_add);
    }


    /*
     * get
     */

    /**
     * simple return the message text
     * @param int $pos used to get another message than the main message
     * @return string simple the message text
     */
    function get_message(int $pos = 1): string
    {
        // the first message should have the position 1 not 0 like in php array
        $pos = $pos - 1;
        if (count($this->txt) > $pos and $pos >= 0) {
            return $this->txt[$pos];
        } else {
            return '';
        }
    }

    /**
     * @return string with the latest added message
     */
    function get_last_message(): string
    {
        return $this->get_message(count($this->txt));
    }

    /**
     * @return int|string the main database row to which this user message is related
     */
    function get_row_id(): int|string
    {
        return $this->db_row_id;
    }


    /*
     * internal
     */

    /**
     * TODO should pick the last either from msg_var_lst or msg_id_lst
     * @return string with the latest added message translated to the user language
     */
    function get_last_message_translated(): string
    {
        return $this->get_message_translated(count($this->msg_var_lst));
    }

    /**
     * @return array with all the text messages
     */
    protected function get_all_messages(): array
    {
        return $this->txt;
    }

    /**
     * @return array with all the translatable messages with vars
     */
    protected function get_all_var_messages(): array
    {
        return $this->msg_var_lst;
    }

    /**
     * combine the status of two user messages and assume the worst
     * @param user_message $msg_to_add the user messages that should be combined with this user message
     * @return void
     */
    private function combine_status(user_message $msg_to_add): void
    {
        if (!$msg_to_add->is_ok()) {
            $this->msg_status = msg_id::NOK;
        }
    }

}