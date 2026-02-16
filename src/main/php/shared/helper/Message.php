<?php

/*

    shared/helper/Message.php - a base message object to collect exception messages
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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;

class Message
{

    public int $msg_status;

    // array of the messages id that should be shown to the user
    // in the language of the user frontend
    // to explain the result of a process
    public array $msg_id_lst;
    // array of an array of a message id
    // and a list of vars that should be added at the translated messages text
    // at the predefined and language specific place
    public array $msg_var_lst;

    /**
     * assumes that normally everything is fine
     */
    function __construct()
    {
        $this->msg_status = msg_id::OK;
        $this->msg_id_lst = [];
        $this->msg_var_lst = [];
    }

    /*
     * set
     */

    /**
     * set the status to not OK
     * @return void
     */
    function set_not_ok(): void
    {
        if ($this->msg_status < msg_id::NOK) {
            $this->msg_status = msg_id::NOK;
        }
    }

    /**
     * set the status to error
     * @return void
     */
    function set_error(): void
    {
        if ($this->msg_status < msg_id::ERROR) {
            $this->msg_status = msg_id::ERROR;
        }
    }

    /**
     * set the status to warning
     * @return void
     */
    function set_warning(): void
    {
        if ($this->msg_status < msg_id::WARNING) {
            $this->msg_status = msg_id::WARNING;
        }
    }


    /*
     * add
     */

    /**
     * add a message id and a list of related variables
     * to offer the user to see more details without a retry
     * more than one message id can be added to a user message result.
     * the message id is translated to the user interface language at the latest possible moment
     * the vars are expected to be in the target language already
     *
     * @param msg_id|null $msg_id the message text to add
     * @param array $var_lst the vars that should be added to the message text
     * @param bool $ok true if the result of the operation was OK
     * @return void is never expected to fail
     */
    function add(?msg_id $msg_id, array $var_lst, bool $ok = false): void
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
     * TODO Prio 3 rename to add_text because just a translatable text is added
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
    function add_err(?msg_id $msg_id, array $var_lst): void
    {
        $this->add($msg_id, $var_lst, true);
        $msg = $this->get_last_message_translated();
        log_err($msg);
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


    /*
     * modify
     */

    /**
     * combine the given message with this message
     *
     * @param Message $msg_to_add a message of which all parameters should be added to this message
     * @return void is never expected to fail
     */
    function merge(Message $msg_to_add): void
    {
        foreach ($msg_to_add->get_all_var_messages() as $msg_var) {
            $this->add($msg_var[0], $msg_var[1], $msg_to_add->is_ok());
        }
    }


    /*
     * info
     */

    /**
     * @return bool true if the user does not need to be informed
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
     * the most useful message for the user
     * translated to the frontend language
     *
     * @return string a text readable for humans, so no too long
     */
    function text(): string
    {
        // TODO Prio 1 fill up
        return $this->get_last_message_translated();
    }



    /*
     * internal
     */

    /**
     * @return array with all the translatable messages with vars
     */
    protected function get_all_var_messages(): array
    {
        return $this->msg_var_lst;
    }

    /**
     * TODO should pick the last either from msg_var_lst or msg_id_lst
     * @return string with the latest added message translated to the user language
     */
    function get_last_message_translated(): string
    {
        return $this->get_message_translated(count($this->msg_var_lst));
    }

    /**
     * simple return a translated message text with vars
     * TODO review
     * @param int $pos used to get another message than the main message
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
     * TODO Prio 3 review
     * @return string the translated text for all messages with vars
     */
    function var_message_text(): string
    {
        global $mtr;
        $lib = new library();
        return $lib->msg_var_text($this->msg_var_lst, $mtr);
    }

}
