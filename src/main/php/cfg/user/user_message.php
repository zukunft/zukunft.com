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

namespace cfg;

class user_message
{
    // the message types that defines what needs to be done next
    const OK = 1;
    const NOK = 2;
    const WARNING = 3;
    //const YES_NO = 4;
    //const CONFIRM_CANCEL = 5;

    private int $msg_status;

    // array of the messages that should be shown to the user to explain the result of a process
    private array $msg_text;
    // the prime database row that has caused the user message
    private int|string $db_row_id;
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
        $this->msg_text = [];
        if ($msg_text == '') {
            $this->msg_status = self::OK;
        } else {
            $this->msg_text[] = $msg_text;
            $this->msg_status = self::NOK;
        }
        $this->db_row_id = 0;
    }


    /*
     * set
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


    /*
     * add
     */

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
     * @param string $msg_text the warning text to add
     * @return void is never expected to fail
     */
    function add_info(string $msg_text): void
    {
        if ($msg_text != '') {
            // do not repeat the same text more than once
            if (!in_array($msg_text, $this->msg_text)) {
                $this->msg_text[] = $msg_text;
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
        foreach ($msg_to_add->get_all_messages() as $msg_text) {
            $this->add_message($msg_text);
        }
        $this->combine_status($msg_to_add);
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
     * return the message text with all messages
     * @return string simple the message text
     */
    function all_message_text(): string
    {
        return implode(", ", $this->msg_text);
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
    function has_row():bool
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
     * @return array with all the text messages
     */
    protected function get_all_messages(): array
    {
        return $this->msg_text;
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