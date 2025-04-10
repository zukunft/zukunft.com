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

namespace html\user;

include_once SHARED_ENUM_PATH . 'messages.php';

use shared\enum\messages as msg_id;

class user_message
{
    // the message types that defines what needs to be done next
    const OK = 1;
    //const INFO = 2;
    //const YES_NO = 3;
    //const CONFIRM_CANCEL = 4;
    const NOK = 5;
    const WARNING = 6;
    const ERROR = 7;

    private int $msg_status;

    // array of the messages that should be shown to the user to explain the result of a process
    private array $txt;
    // the prime database row that has caused the user message
    private int|string $db_row_id;

    // array of the messages id that should be shown to the user
    // in the language of the user frontend
    // to explain the result of a process
    private array $msg_id_lst;

    // a list of solutions suggested by the program
    //private user_actions $actions;

    /**
     * assumes that normally everything is fine
     * @param string $txt an initial message text
     *                         if this text is not empty it is assumed that something went wrong
     */
    function __construct(string $txt = '')
    {
        $this->txt = [];
        if ($txt == '') {
            $this->msg_status = self::OK;
        } else {
            $this->txt[] = $txt;
            $this->msg_status = self::NOK;
        }
        $this->db_row_id = 0;
        $this->msg_id_lst = [];
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
        if ($this->msg_status < self::NOK) {
            $this->msg_status = self::NOK;
        }
    }

    /**
     * set the status to warning
     * @return void
     */
    function set_error(): void
    {
        if ($this->msg_status < self::ERROR) {
            $this->msg_status = self::ERROR;
        }
    }

    /**
     * set the status to warning
     * @return void
     */
    function set_warning(): void
    {
        if ($this->msg_status < self::WARNING) {
            $this->msg_status = self::WARNING;
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
            // if a message text is added it is expected that the result was not ok, but other stati are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * add a message that is classified as an error
     * @param string $txt the explanation that should be shown to the user
     * @return void
     */
    function add_err(string $txt): void
    {
        if ($txt != '') {
            $this->add_message_text($txt);
            $this->set_error();
        }
    }

    /**
     * add a message that is classified as an warning
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
     * to offer the user to see more details without retry
     * more than one message text can be added to a user message result
     *
     * @param string $txt the message text to add
     * @return void is never expected to fail
     */
    function add_message(string $txt): void
    {
        if ($txt != '') {
            $this->add_message_text($txt);
            // if a message text is added it is expected that the result was not ok, but other stati are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    private function add_message_text(string $txt): void
    {
        // do not repeat the same text more than once
        if (!in_array($txt, $this->txt)) {
            $this->txt[] = $txt;
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
     * simple return the message text
     * @param int $pos used to get other message than the main message
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
     * @return string with latest added message
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
     * @return array with all the text messages
     */
    protected function get_all_messages(): array
    {
        return $this->txt;
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