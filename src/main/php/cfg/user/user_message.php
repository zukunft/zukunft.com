<?php

/*

    model/user/user_message.php - a complex object that functions can return
    ---------------------------

    class function are should return on of
    1. boolean if a failure does not need any user action
    2. string if the user just needs to be informed about the result
    3. this user_message if a decision is expected by the user

    beside the user relevant return value, the admin user communication is done
    1. via log_debug, _info, _warning and _error message written directly from th function to the log table
    2. via exception if the calling function needs to be informed and the calling function needs to decide the next steps

    the main functions are:
         - add:            to add a translatable message to the user with parameters
         - add_text:       to add a translatable message to the user without parameters
         - add_admin:      to add a non translatable message to the admin
         - add_develop:    to add a non translatable message for developers
         - merge (ex add): to merge two messages into one

    TODO Prio 2 once all messages are in line with the standard rename to message $msg
    TODO Prio 2 add an int value for the expected specificity of the message to show the most specific messages first because they are expected to be the root cause


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

namespace Zukunft\ZukunftCom\main\php\cfg\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class user_message
{

    // the user who has started the process
    // and who should see the problem descriptions
    // and the suggested solutions
    public user|null $usr;
    private int $msg_status;
    private int|null $checksum = null;

    // the start time for longer processes
    public ?float $start_time;

    // array of the information only messages that should be shown to the user
    // to explain the result of a process
    private array $info_text;

    // array of the messages that should be shown to the user
    // to explain the result of a process
    private array $msg_text;

    // array of the messages types that might have more than one entry
    // so each array entry is an array with the message keys
    // e.g. "missing phrase" is the type and "energy" is the entry if the phrase with the name "energy" is missing
    private array $typ_lst;

    // array of the messages id that should be shown to the user
    // in the language of the user frontend
    // to explain the result of a process
    private array $msg_id_lst;
    // array of an array of a message id
    // and a list of vars that should be added at the translated messages text
    // at the predefined and language specific place
    private array $msg_var_lst;

    // the prime database row that has caused the user message
    private int|string $db_row_id;
    // list of database names and id used for inserting a list
    private array $db_row_id_lst;
    // true an object has been added that might have objects depending on this object
    // e.g. if a triple has been added more word or triples needs to be added
    private bool $added_depending = false;
    // to trace to progress
    private string $url;

    // a list of solutions suggested by the program
    //private user_actions $actions;

    /**
     * assumes that normally everything is fine
     * @param user|null $usr the user for whom the messages should be created
     * @param string $msg_text an initial message text
     *                         if this text is not empty it is assumed that something went wrong
     */
    function __construct(?user $usr = null, string $msg_text = '')
    {
        $this->reset();
        $this->usr = $usr;
        if ($msg_text == '') {
            $this->msg_status = msg_id::OK;
        } else {
            $this->msg_text[] = $msg_text;
            $this->msg_status = msg_id::NOK;
        }
    }

    function reset(bool $keep_usr = false): void
    {
        if (!$keep_usr) {
            $this->usr = new user();
        }
        $this->info_text = [];
        $this->msg_text = [];
        $this->msg_status = msg_id::OK;
        $this->start_time = null;
        $this->db_row_id = 0;
        $this->db_row_id_lst = [];
        $this->added_depending = false;
        $this->msg_id_lst = [];
        $this->msg_var_lst = [];
        $this->typ_lst = [];
    }

    /**
     * create user message list without errors but with the original user
     * @return user_message empty but with the same user
     */
    function clone_reset(): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->usr = $this->usr;
        return $usr_msg;
    }


    /*
     * set and get
     */

    /**
     * set the status to not ok
     * @return void
     */
    function set_not_ok(): void
    {
        $this->msg_status = msg_id::NOK;

    }

    /**
     * set the status to ok
     * @return void
     */
    function set_ok(): void
    {
        $this->msg_status = msg_id::OK;

    }

    /**
     * set the status to warning
     * @return void
     */
    function set_warning(): void
    {
        $this->msg_status = msg_id::WARNING;

    }

    /**
     * set the main database row to which this user message is related
     * @param int|string $id the prime database index value
     * @return void
     */
    function set_db_row_id(int|string $id): void
    {
        $this->db_row_id = $id;
    }

    /**
     * @param string $url the url used to trace to progress
     * @return void
     */
    function set_url(string $url): void
    {
        $this->url = $url;
    }

    function url(): string
    {
        return $this->url;
    }

    /**
     * set a simple checksum e.g. for a fast import validation
     * @param int $checksum a simple checksum for fast validation
     * @return void
     */
    function set_checksum(int $checksum): void
    {
        $this->checksum = $checksum;
    }

    /**
     * @return int|null the number of values that have been expected to be imported
     */
    function checksum(): int|null
    {
        return $this->checksum;
    }

    function db_row_id_lst(): array
    {
        return $this->db_row_id_lst;
    }

    function added_depending(): bool
    {
        return $this->added_depending;
    }


    /*
     * api
     */

    /**
     * create a json array to send the messages to the frontend
     * TODO Prio 0 move the message status to a shared const object
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
            $vars[json_fields::USER] = $this->usr->api_json_array(new api_type_list([]));
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
            $usr->api_mapper($api_json[json_fields::USER], $usr_msg);
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
     * to offer the user to see more details without retry
     * more than one message id can be added to a user message result
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
     * add a message id for info only
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_info_id(?msg_id $msg_id): void
    {
        if ($msg_id != null) {
            // do not repeat the same text more than once
            if (!in_array($msg_id, $this->msg_id_lst)) {
                $this->msg_id_lst[] = $msg_id;
            }
        }
    }

    /**
     * add an info message id and a list of related variables
     * to offer the user to see more details without retry
     * more than one message id can be added to a user message result
     * the message id is translated to the user interface language at the latest possible moment
     * the vars are expected to be in the target language already
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_info_with_vars(?msg_id $msg_id, array $var_lst): void
    {
        $this->add_id_with_vars($msg_id, $var_lst, true);
    }

    /**
     * add a warning message with variables
     * and add the translated message to the log so that the admin can also see it
     * TODO Prio 3 check if the causing user is added to the log
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_warning_with_vars(?msg_id $msg_id, array $var_lst): void
    {
        $this->add_id_with_vars($msg_id, $var_lst, true);
        $msg = $this->get_last_message_translated();
        log_warning($msg);
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
        $this->add_id_with_vars($msg_id, $var_lst, true);
        $msg = $this->get_last_message_translated();
        log_err($msg);
    }

    /**
     * add a message id and a list of related variables
     * to offer the user to see more details without retry
     * more than one message id can be added to a user message result
     * the message id is translated to the user interface language at the latest possible moment
     * the vars are expected to be in the target language already
     *
     * the function just adds the message and sets the status to fail
     * without writing to the log
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_id_with_vars(?msg_id $msg_id, array $var_lst, bool $ok = false): void
    {
        if ($msg_id != null) {
            $key_lst = [];
            foreach ($this->msg_var_lst as $msg_row) {

                $key_lst[] = $msg_row[0]->name . ':' . implode(",", $msg_row[1]);
            }

            // check the var list
            foreach ($var_lst as $var) {
                if (is_array($var)) {
                    log_warning('var ' . implode(",", $var) . 'is an array');
                }
            }

            // do not repeat the same text more than once
            if (!in_array($msg_id->name . ':' . implode(",", $var_lst),
                $key_lst)) {
                $this->msg_var_lst[] = [$msg_id, $var_lst];
            }
            // if a message text is added it is expected that the result was not ok, but other statuus are not changed
            if ($this->is_ok() and !$ok) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * to offer the user to see more details without retry
     * more than one message text can be added to a user message result
     *
     * @param string $msg_text the message text to add
     * @param string $type the message type to group the messages
     * @return void is never expected to fail
     */
    function add_type_message(string $msg_text, string $type): void
    {
        if ($msg_text != '') {
            // find the next key
            if (in_array($type, array_keys($this->typ_lst))) {
                $sub_lst = $this->typ_lst[$type];
                // do not repeat the same text more than once
                if (!in_array($msg_text, $sub_lst)) {
                    $sub_lst[] = $msg_text;
                    $this->typ_lst[$type] = $sub_lst;
                }
            } else {
                $this->typ_lst[$type] = [$msg_text];
            }
            // if a message text is added it is expected that the result was not ok, but other statuus are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * to offer the user to see more details without retry
     * more than one message text can be added to a user message result
     * TODO replace with add_id add_id_with_vars
     *
     * @param string $msg_text the message text to add
     * @return void is never expected to fail
     */
    function add_message_text(string $msg_text): void
    {
        if ($msg_text != '') {
            // do not repeat the same text more than once
            if (!in_array($msg_text, $this->msg_text)) {
                $this->msg_text[] = $msg_text;
            }
            // if a message text is added it is expected that the result was not ok, but other statuus are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * show the warning message to the user without interrupting the process
     *
     * @param string $msg_text the warning text to add
     * @return void is never expected to fail
     */
    function add_warning_text(string $msg_text): void
    {
        if ($msg_text != '') {
            // do not repeat the same text more than once
            if (!in_array($msg_text, $this->msg_text)) {
                $this->msg_text[] = $msg_text;
            }
            // set to warning only if everything has been fine until now
            if ($this->is_ok()) {
                $this->set_warning();
            }
        }
    }

    /**
     * show the info message to the user without interrupting the process
     *
     * @param string $info_text the warning text to add
     * @return void is never expected to fail
     */
    function add_info_text(string $info_text): void
    {
        if ($info_text != '') {
            // do not repeat the same text more than once
            if (!in_array($info_text, $this->info_text)) {
                $this->info_text[] = $info_text;
            }
        }
    }

    /**
     * combine the given message with this message
     *
     * @param user_message $msg_to_add a message of which all parameter should be added to this message
     * @return void is never expected to fail
     */
    function add(user_message $msg_to_add): void
    {
        foreach ($msg_to_add->get_all_info() as $msg_text) {
            $this->add_info_text($msg_text);
        }
        foreach ($msg_to_add->get_all_messages() as $msg_text) {
            $this->add_message_text($msg_text);
        }
        foreach ($msg_to_add->get_all_id_messages() as $msg_id) {
            $this->add_id($msg_id);
        }
        foreach ($msg_to_add->get_all_var_messages() as $msg_var) {
            $this->add_id_with_vars($msg_var[0], $msg_var[1], $msg_to_add->is_ok());
        }
        foreach ($msg_to_add->get_all_type_messages() as $key => $lst) {
            foreach ($lst as $entry) {
                $this->add_type_message($entry, $key);
            }
        }
        $this->combine_status($msg_to_add);
        if ($msg_to_add->checksum() != null) {
            if ($this->checksum == null) {
                $this->set_checksum($msg_to_add->checksum());
            } else {
                $this->set_checksum($this->checksum() + $msg_to_add->checksum());
            }
        }

        $lib = new library();
        $this->db_row_id_lst = $lib->array_merge_by_key($this->db_row_id_lst, $msg_to_add->db_row_id_lst);
        if ($msg_to_add->added_depending()) {
            $this->added_depending = true;
        }
    }

    /**
     * add the database id and the related name to the id list of the user message
     * @param user_message $msg_to_add the user message that contains the db id of an object just added to the db
     * @param string $name the name of the object that is related to the id
     * @return void
     */
    function add_list_name_id(user_message $msg_to_add, string $name = ''): void
    {
        $id = $msg_to_add->get_row_id();
        if ($id != 0 and $name != '') {
            $this->db_row_id_lst[$name] = $id;
        }
    }

    function set_added_depending(): void
    {
        $this->added_depending = true;
    }

    function unset_added_depending(): void
    {
        $this->added_depending = false;
    }


    /*
     * get
     */

    /**
     * @return bool true if user does not need to be informed
     */
    function is_ok(): bool
    {
        if ($this->msg_status == msg_id::OK) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * return the info only message text with all messages
     * @return string simple the message text
     */
    function all_info_text(): string
    {
        return implode(", ", $this->info_text);
    }

    /**
     * return the translated message text with all messages
     * @return string the message text in the ui language
     */
    function all_message_text(): string
    {
        global $mtr;
        $msg = implode(", ", $this->msg_text);
        $part = '';
        foreach ($this->typ_lst as $key => $sub_lst) {
            $part .= $key . ': ' . implode(", ", $sub_lst) . '; ';
        }
        if ($msg != '' and $part <> '') {
            $msg .= $msg . '; ' . $part;
        } else {
            $msg .= $part;
        }
        $part = '';
        foreach ($this->msg_id_lst as $msg_id) {
            if ($part != '') {
                $part .= ', ';
            }
            $part .= $mtr->txt($msg_id);
        }
        if ($msg != '' and $part <> '') {
            $msg .= $msg . '; ' . $part;
        } else {
            $msg .= $part;
        }

        // get the text for all messages with vars
        $part = $this->var_message_text();

        if ($msg != '' and $part <> '') {
            $msg .= $msg . '; ' . $part;
        } else {
            $msg .= $part;
        }
        return $msg;
    }

    /**
     * TODO Prio 3 review
     * @return string the translated text for all messages with vars
     */
    function var_message_text(): string
    {
        global $mtr;
        $lib = new library();
        return $lib->msg_var_text($this->msg_var_lst, $mtr);
    }

    /**
     * simple return the message text
     * @param int $pos used to get other message than the main message
     * @return string simple the message text
     */
    function get_message(int $pos = 1): string
    {
        // the first message should have the position 1 not 0 like in php array
        $pos = $pos - 1;
        if (count($this->msg_text) > $pos and $pos >= 0) {
            return $this->msg_text[$pos];
        } else {
            // TODO Prio 1 activate
            //$msg = 'user message position ' . $pos . ' not found';
            //log_warning($msg);
            //return $msg;
            return '';
        }
    }

    /**
     * simple return a translated message text with vars
     * TODO review
     * @param int $pos used to get other message than the main message
     * @return string simple the message text
     */
    function get_message_translated(int $pos = 1): string
    {
        // the first message should have the position 1 not 0 like in php array
        $pos = $pos - 1;
        if (count($this->msg_var_lst) > $pos and $pos >= 0) {
            return $this->var_message_text();
        } else {
            $msg = 'user message translation for position ' . $pos . ' not found';
            log_warning($msg);
            return $msg;
        }
    }

    /**
     * TODO should be deprecated once the msg_id is used for all messages
     * @return string with latest added message text
     */
    function get_last_message(): string
    {
        return $this->get_message(count($this->msg_text));
    }

    /**
     * TODO should pick the last either from msg_var_lst or msg_id_lst
     * @return string with latest added message translated to the user language
     */
    function get_last_message_translated(): string
    {
        return $this->get_message_translated(count($this->msg_var_lst));
    }

    /**
     * @return int|string the main database row to which this user message is related
     */
    function get_row_id(): int|string
    {
        return $this->db_row_id;
    }

    /**
     * @return bool true if the message is linked to a valid database row of just a database row has been created
     */
    function has_row(): bool
    {
        if ($this->db_row_id == 0 or $this->db_row_id == '') {
            return false;
        } else {
            return true;
        }
    }



    /*
     * internal
     */

    /**
     * @return array with all the information only text messages
     */
    protected function get_all_info(): array
    {
        return $this->info_text;
    }

    /**
     * @return array with all the text messages
     */
    protected function get_all_messages(): array
    {
        return $this->msg_text;
    }

    /**
     * @return array with all the translatable messages
     */
    protected function get_all_id_messages(): array
    {
        return $this->msg_id_lst;
    }

    /**
     * @return array with all the translatable messages with vars
     */
    protected function get_all_var_messages(): array
    {
        return $this->msg_var_lst;
    }

    /**
     * @return array with all the text messages
     */
    protected function get_all_type_messages(): array
    {
        return $this->typ_lst;
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