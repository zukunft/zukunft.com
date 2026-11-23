<?php

/*

    shared/helper/Message.php - a base message object to collect exception messages
    -------------------------

    $msg is the suggested var name

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
    // array of an array of a message id,
    // a list of vars that should be added at the translated messages text
    // at the predefined and language specific place
    // and the severity of the entry e.g. msg_id::ERROR or msg_id::INFO
    public array $msg_var_lst;
    // true only in a test that expects an internal error (test_base::assert_log_err): log_err_msg keeps the text
    // of the error here and neither writes nor counts it, because it is the expected result and no error of the run
    public bool $log_test_only = false;
    // the text of the last internal error that log_err_msg has kept while log_test_only is true
    public string $log_test_txt = '';

    /**
     * assumes that normally everything is fine
     */
    function __construct()
    {
        $this->msg_status = msg_id::OK;
        $this->msg_id_lst = [];
        $this->msg_var_lst = [];
    }

    /**
     * clear the collected messages and the status
     *
     * only a test may call this on the message it owns, to start the next test from a clean
     * message (testing.md "created once and reset after each checked test"); a function that
     * has received a message never resets it (state-and-messages.md)
     *
     * @return void
     */
    function reset(): void
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
        // a lower status is worse (e.g. msg_id::ERROR < msg_id::NOK < msg_id::OK),
        // so only a better status is replaced and an already worse one is kept
        if ($this->msg_status > msg_id::NOK) {
            $this->msg_status = msg_id::NOK;
        }
    }

    /**
     * set the status to error
     * @return void
     */
    function set_error(): void
    {
        if ($this->msg_status > msg_id::ERROR) {
            $this->msg_status = msg_id::ERROR;
        }
    }

    /**
     * set the status to warning
     * @return void
     */
    function set_warning(): void
    {
        if ($this->msg_status > msg_id::WARNING) {
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
        $severity = msg_id::NOK;
        if ($ok) {
            $severity = msg_id::INFO;
        }
        $this->add_with_severity($msg_id, $var_lst, $severity);
    }

    /**
     * add a message id with its vars and its severity
     * and lower the status of this message for a not ok or an error entry, but never for a warning or an info
     *
     * @param msg_id|null $msg_id the message text to add
     * @param array $var_lst the vars that should be added to the message text
     * @param int $severity the status const of the entry e.g. msg_id::ERROR, msg_id::NOK, msg_id::WARNING or msg_id::INFO
     * @return void is never expected to fail
     */
    function add_with_severity(?msg_id $msg_id, array $var_lst, int $severity): void
    {
        if ($msg_id != null) {
            // check the var list
            foreach ($var_lst as $var) {
                if (is_array($var)) {
                    log_warning('var ' . implode(",", $var) . 'is an array');
                }
            }

            // do not repeat the same text more than once, but keep the worse severity of a repeated text
            $key = $msg_id->name . ':' . implode(",", $var_lst);
            $pos = $this->var_message_pos($key);
            if ($pos === null) {
                $this->msg_var_lst[] = [$msg_id, $var_lst, $severity];
            } else {
                $this->msg_var_lst[$pos][2] = min($this->severity($this->msg_var_lst[$pos]), $severity);
            }

            // a lower status is worse, so set_error and set_not_ok keep an already worse status
            if ($severity <= msg_id::ERROR) {
                $this->set_error();
            } elseif ($severity == msg_id::NOK) {
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
            // if a message text is added it is expected that the result was not ok, but other statuum are not changed
            if ($this->is_ok()) {
                $this->set_not_ok();
            }
        }
    }

    /**
     * add a error message with variables, set the error status
     * and add the translated message to the log so that the admin can also see it
     * TODO Prio 3 check if the causing user is added to the log
     *
     * @param msg_id|null $msg_id the message text to add
     * @param array $var_lst the vars that should be added to the message text
     * @return void is never expected to fail
     */
    function add_err(?msg_id $msg_id, array $var_lst): void
    {
        $this->add_with_severity($msg_id, $var_lst, msg_id::ERROR);
        $msg_txt = $this->get_last_message_translated();
        // on the message, so that a test that expects the error keeps it instead of counting it (test_base::assert_log_err)
        log_err_on_msg($msg_txt, $this);
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
     * combine the status of two messages and assume the worst: an added message that is not ok makes this
     * message at least not ok, an error of either message is kept (a lower status is worse)
     * @param Message $msg_to_add the message whose status is combined with the status of this message
     * @return void
     */
    function combine_status(Message $msg_to_add): void
    {
        if (!$msg_to_add->is_ok()) {
            $this->msg_status = min($this->msg_status, $msg_to_add->msg_status, msg_id::NOK);
        }
    }

    /**
     * combine the given message with this message
     *
     * @param Message $msg_to_add a message of which all parameters should be added to this message
     * @return void is never expected to fail
     */
    function merge(Message $msg_to_add): void
    {
        foreach ($msg_to_add->get_all_var_messages() as $msg_var) {
            $this->add_with_severity($msg_var[0], $msg_var[1], $this->severity($msg_var));
        }
        // the status too, because e.g. a set_error without an entry is not ok
        $this->combine_status($msg_to_add);
        // the expected internal error of a test that a buffer has kept
        if ($msg_to_add->log_test_txt != '') {
            $this->log_test_txt = $msg_to_add->log_test_txt;
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
     * a not ok message can be a problem of the user input (e.g. an expression that names no term),
     * which the user can fix and which the answer to the user already names, whereas an error is
     * a problem that only the admin can fix, so only an error belongs into the system log
     *
     * @return bool true if this message reports a problem that the user cannot solve
     */
    function is_error(): bool
    {
        return $this->msg_status <= msg_id::ERROR;
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
     * @param array $msg_var an entry of msg_var_lst
     * @return int the severity of the entry; an entry without severity e.g. of an api message of an older pod is not ok
     */
    protected function severity(array $msg_var): int
    {
        $result = msg_id::NOK;
        if (array_key_exists(2, $msg_var)) {
            $result = $msg_var[2];
        }
        return $result;
    }

    /**
     * @param string $key the message id name and the vars of a message as created by add_with_severity
     * @return int|null the position of the entry with the same text in msg_var_lst or null if the text is new
     */
    private function var_message_pos(string $key): ?int
    {
        $result = null;
        foreach ($this->msg_var_lst as $pos => $msg_row) {
            if ($result === null and $msg_row[0]->name . ':' . implode(",", $msg_row[1]) == $key) {
                $result = $pos;
            }
        }
        return $result;
    }

    /**
     * @return array with all the translatable messages without vars
     */
    protected function get_all_id_messages(): array
    {
        return $this->msg_id_lst;
    }

    /**
     * @return string with the latest added message translated to the user language
     *         or an empty string if this message has nothing to say
     */
    function get_last_message_translated(): string
    {
        global $mtr;

        $result = '';
        if ($this->msg_var_lst != []) {
            $result = $this->get_message_translated(count($this->msg_var_lst));
        } elseif ($this->msg_id_lst != []) {
            // a message added with add_id has no vars, so the message id is translated directly
            $last_id = $this->msg_id_lst[array_key_last($this->msg_id_lst)];
            if (!$last_id instanceof msg_id) {
                // the api mapper fills the list with the already translated texts
                $result = $last_id;
            } elseif ($mtr == null) {
                // without a translator e.g. in a setup script the enum value is the english text
                $result = $last_id->value;
            } else {
                $result = $mtr->txt($last_id);
            }
        }
        return $result;
    }

    /**
     * simple return a translated message text with vars
     * TODO Prio 2 review
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

    /**
     * the errors with vars e.g. of a failed import without its infos and warnings, limited to keep the text readable
     * @param int $limit the number of errors that are named e.g. the max errors of the import in config.yaml
     * @return string the translated text of the first errors and the number of the errors that are not named
     */
    function var_error_text(int $limit): string
    {
        global $mtr;
        $lib = new library();
        $err_lst = $this->var_error_list();
        $result = $lib->msg_var_text(array_slice($err_lst, 0, $limit), $mtr);
        $more = count($err_lst) - $limit;
        if ($more > 0) {
            $result .= ' ' . $this->more_text($more);
        }
        return $result;
    }

    /**
     * @return array the entries of msg_var_lst that are not ok or an error, but no warning and no info
     */
    private function var_error_list(): array
    {
        $result = [];
        foreach ($this->msg_var_lst as $msg_var) {
            if ($this->severity($msg_var) < msg_id::WARNING) {
                $result[] = $msg_var;
            }
        }
        return $result;
    }

    /**
     * @param int $more the number of errors that are not named
     * @return string e.g. "... and 3 more" in the language of the user
     */
    private function more_text(int $more): string
    {
        $dots = $this->translated(msg_id::THREE_POINTS);
        $and = $this->translated(msg_id::AND_MORE_BEFORE);
        return $dots . ' ' . $and . ' ' . $more . ' ' . $this->translated(msg_id::MORE);
    }

    /**
     * @param msg_id $msg_id a message without vars
     * @return string the text in the language of the user or the english text e.g. without a translator in a setup script
     */
    private function translated(msg_id $msg_id): string
    {
        global $mtr;
        $result = $msg_id->value;
        if ($mtr != null) {
            $result = $mtr->txt($msg_id);
        }
        return $result;
    }

}
