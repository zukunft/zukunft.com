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

namespace cfg\user;

include_once SHARED_ENUM_PATH . 'messages.php';

//include_once SHARED_PATH . 'library.php';

use shared\enum\messages as msg_id;
use shared\library;

class user_message
{
    // the message types that defines what needs to be done next
    const OK = 1;
    const NOK = 2;
    const WARNING = 3;
    //const YES_NO = 4;
    //const CONFIRM_CANCEL = 5;

    private int $msg_status;
    private int|null $checksum = null;

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
    // to trace to progress
    private string $url;

    // a list of solutions suggested by the program
    //private user_actions $actions;

    /**
     * assumes that normally everything is fine
     * @param string $msg_text an initial message text
     *                         if this text is not empty it is assumed that something went wrong
     */
    function __construct(string $msg_text = '')
    {
        $this->info_text = [];
        $this->msg_text = [];
        if ($msg_text == '') {
            $this->msg_status = self::OK;
        } else {
            $this->msg_text[] = $msg_text;
            $this->msg_status = self::NOK;
        }
        $this->db_row_id = 0;
        $this->db_row_id_lst = [];
        $this->msg_id_lst = [];
        $this->msg_var_lst = [];
        $this->typ_lst = [];
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
        $this->msg_status = self::NOK;

    }

    /**
     * set the status to ok
     * @return void
     */
    function set_ok(): void
    {
        $this->msg_status = self::OK;

    }

    /**
     * set the status to warning
     * @return void
     */
    function set_warning(): void
    {
        $this->msg_status = self::WARNING;

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

    function checksum(): int|null
    {
        return $this->checksum;
    }

    function db_row_id_lst(): array
    {
        return $this->db_row_id_lst;
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
            // if a message text is added it is expected that the result was not ok, but other stati are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * add a message id and a list of related variables
     * to offer the user to see more details without retry
     * more than one message id can be added to a user message result
     * the message id is translated to the user interface language at the latest possible moment
     * the vars are expected to be in the target language already
     *
     * @param msg_id|null $msg_id the message text to add
     * @return void is never expected to fail
     */
    function add_id_with_vars(?msg_id $msg_id, array $var_lst): void
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
            // if a message text is added it is expected that the result was not ok, but other stati are not changed
            if ($this->is_ok()) {
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
            // if a message text is added it is expected that the result was not ok, but other stati are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * to offer the user to see more details without retry
     * more than one message text can be added to a user message result
     *
     * @param string $msg_text the message text to add
     * @return void is never expected to fail
     */
    function add_message(string $msg_text): void
    {
        if ($msg_text != '') {
            // do not repeat the same text more than once
            if (!in_array($msg_text, $this->msg_text)) {
                $this->msg_text[] = $msg_text;
            }
            // if a message text is added it is expected that the result was not ok, but other stati are not changed
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
    function add_warning(string $msg_text): void
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
    function add_info(string $info_text): void
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
            $this->add_info($msg_text);
        }
        foreach ($msg_to_add->get_all_messages() as $msg_text) {
            $this->add_message($msg_text);
        }
        foreach ($msg_to_add->get_all_id_messages() as $msg_id) {
            $this->add_id($msg_id);
        }
        foreach ($msg_to_add->get_all_var_messages() as $msg_var) {
            $this->add_id_with_vars($msg_var[0], $msg_var[1]);
        }
        foreach ($msg_to_add->get_all_type_messages() as $key => $lst) {
            foreach ($lst as $entry) {
                $this->add_type_message($entry, $key);
            }
        }
        $this->combine_status($msg_to_add);

        $lib = new library();
        $this->db_row_id_lst = $lib->array_merge_by_key($this->db_row_id_lst, $msg_to_add->db_row_id_lst);
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


    /*
     * get
     */

    /**
     * @return bool true if user does not need to be informed
     */
    function is_ok(): bool
    {
        if ($this->msg_status == self::OK) {
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
        $part = '';
        foreach ($this->msg_var_lst as $msg_var) {
            if ($part != '') {
                $part .= ', ';
            }
            $msg_txt = $mtr->txt($msg_var[0]);
            foreach ($msg_var[1] as $key => $var) {
                // avoid using escaped var makers (probably not 100% correct)
                $msg_txt .= str_replace(
                    msg_id::VAR_ESC_START . $key . msg_id::VAR_ESC_END,
                    msg_id::VAR_TEMP_START . msg_id::VAR_TEMP_VAR . $key . msg_id::VAR_TEMP_END, $msg_txt);
                // replace the var
                $msg_txt .= str_replace(
                    msg_id::VAR_START . $key . msg_id::VAR_END,
                    $var, $msg_txt);
                // undo escaped vars
                $msg_txt .= str_replace(
                    msg_id::VAR_TEMP_START . msg_id::VAR_TEMP_VAR . $key . msg_id::VAR_TEMP_END,
                    msg_id::VAR_ESC_START . $key . msg_id::VAR_ESC_END, $msg_txt);
            }
            // replace the escaped var makers
            $msg_txt .= str_replace(msg_id::VAR_ESC_START, msg_id::VAR_START, $msg_txt);
            $msg_txt .= str_replace(msg_id::VAR_ESC_END, msg_id::VAR_END, $msg_txt);
            $part .= $msg_txt;
        }
        if ($msg != '' and $part <> '') {
            $msg .= $msg . '; ' . $part;
        } else {
            $msg .= $part;
        }
        return $msg;
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
            return '';
        }
    }

    /**
     * @return string with latest added message
     */
    function get_last_message(): string
    {
        return $this->get_message(count($this->msg_text));
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
            $this->msg_status = self::NOK;
        }
    }

}