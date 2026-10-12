<?php

/*

    web/system/job.php - the extension of the batch task API objects to create job base html code
    ------------------

    $job is the suggested var name

    This file is part of the frontend of zukunft.com - calc with words

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

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::API_OBJECT . 'controller.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::HTML . 'rest_call.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use DateTime;
use DateTimeInterface;
use Exception;

class job extends db_object
{

    // the start of the html form name of a job change button
    const string ACTION_FORM_PREFIX = 'job_';

    /*
     * object vars
     */

    private DateTime $request_time;
    private ?DateTime $start_time;
    private ?DateTime $end_time;
    private int $user_id;
    public string $user_name = '';
    public string $type;
    private string $status;
    private int $priority;


    /*
     * set and get
     */

    /**
     * set the vars of this batch job html object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        // TODO use empty date instead?
        $request_timestamp = new DateTime();
        if (array_key_exists(json_fields::TIME_REQUEST, $json_array)) {
            try {
                $request_timestamp = new DateTime($json_array[json_fields::TIME_REQUEST]);
            } catch (Exception $e) {
                $msg->add_error_text('Error converting system log timestamp ' . $json_array[json_fields::TIME_REQUEST]
                    . ' because ' . $e->getMessage());
            }
        } else {
            log_err('Mandatory time missing in API JSON ' . json_encode($json_array));
        }
        $this->set_request_time($request_timestamp);
        $start_time = null;
        if (array_key_exists(json_fields::TIME_START, $json_array)) {
            try {
                $start_time = new DateTime($json_array[json_fields::TIME_START]);
            } catch (Exception $e) {
                $msg->add_error_text('Error converting system log timestamp ' . $json_array[json_fields::TIME_START]
                    . ' because ' . $e->getMessage());
            }
        }
        $this->set_start_time($start_time);
        $end_time = null;
        if (array_key_exists(json_fields::TIME_END, $json_array)) {
            try {
                $end_time = new DateTime($json_array[json_fields::TIME_END]);
            } catch (Exception $e) {
                $msg->add_error_text('Error converting system log timestamp ' . $json_array[json_fields::TIME_END]
                    . ' because ' . $e->getMessage());
            }
        }
        $this->set_end_time($end_time);
        if (array_key_exists(json_fields::USER_ID, $json_array)) {
            $this->set_user_id($json_array[json_fields::USER_ID]);
        } else {
            $this->set_user_id(0);
        }
        if (array_key_exists(json_fields::TYPE, $json_array)) {
            $this->set_type($json_array[json_fields::TYPE]);
        } else {
            $this->set_type(0);
        }
        if (array_key_exists(json_fields::STATUS, $json_array)) {
            $this->set_status($json_array[json_fields::STATUS]);
        } else {
            $this->set_status('');
        }
        if (array_key_exists(json_fields::PRIORITY, $json_array)) {
            $this->set_priority($json_array[json_fields::PRIORITY]);
        } else {
            $this->set_priority(0);
        }
        if (array_key_exists(json_fields::USER_NAME, $json_array)) {
            $this->user_name = $json_array[json_fields::USER_NAME];
        }
        return $msg->is_ok();
    }

    function set_request_time(DateTime $iso_time_str): void
    {
        $this->request_time = $iso_time_str;
    }

    function request_time(): DateTime
    {
        return $this->request_time;
    }

    function set_start_time(?DateTime $iso_time_str): void
    {
        $this->start_time = $iso_time_str;
    }

    function start_time(): ?DateTime
    {
        return $this->start_time;
    }

    function set_end_time(?DateTime $iso_time_str): void
    {
        $this->end_time = $iso_time_str;
    }

    function end_time(): ?DateTime
    {
        return $this->end_time;
    }

    function set_user_id(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    function user_id(): int
    {
        return $this->user_id;
    }

    function set_type(int $type): void
    {
        $this->type = $type;
    }

    function type(): int
    {
        return $this->type;
    }

    function set_status(string $status): void
    {
        $this->status = $status;
    }

    function status(): string
    {
        return $this->status;
    }

    function set_priority(int $priority): void
    {
        $this->priority = $priority;
    }

    function priority(): int
    {
        return $this->priority;
    }

    function user_name(): string
    {
        return $this->user_name;
    }

    /**
     * @return bool true if the job is not yet completed, so its priority can be changed and it can be cancelled;
     *              a completed, failed or cancelled job always has an end time (see job_runner::run_job)
     */
    function is_open(): bool
    {
        return $this->end_time() === null;
    }


    /*
     * modify
     */

    /**
     * change the priority of this job or cancel it via the api, whose backend checks that the requesting user may
     * do the change: the user who has requested the job can downgrade or cancel it, an admin can also upgrade it
     *
     * @param string $action the job action e.g. url_var::ACTION_JOB_CANCEL
     * @param user_message $msg the frontend message with the requesting user to report why the change failed
     * @return bool true if the job has been changed
     */
    function change(string $action, user_message $msg): bool
    {
        // a database change without a requesting user on the message is never written
        // (docs/llm/state-and-messages.md)
        if ($msg->usr == null) {
            $msg->add(msg_id::USER_MISSING, [msg_id::VAR_NAME => $this->dsp_id()]);
        } else {
            $rest = new rest_call();
            $data = [
                json_fields::ID => $this->id(),
                json_fields::ACTION => $action,
                json_fields::USER_ID => $msg->usr->id()
            ];
            $json_body = $rest->api_put(self::class, $data);
            if (array_key_exists(json_fields::MSG, $json_body)) {
                $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
            }
        }
        return $msg->is_ok();
    }


    /*
     * base elements
     */

    /**
     * @returns string the html code to show one batch job for non admin users
     */
    function display(): string
    {
        $html = new html_base();
        $result = '';
        // TODO replace with the user date format setting,
        //      which can also be the local system setting
        //      or the pod setting
        $result .= $html->td($this->request_time()->format(DateTimeInterface::ATOM));
        if ($this->start_time() != null) {
            $result .= $html->td($this->start_time()->format(DateTimeInterface::ATOM));
        } else {
            $result .= $html->td('');
        }
        if ($this->end_time() != null) {
            $result .= $html->td($this->end_time()->format(DateTimeInterface::ATOM));
        } else {
            $result .= $html->td('');
        }
        $result .= $html->td($this->user_name());
        $result .= $html->td($this->type());
        $result .= $html->td($this->status());
        $result .= $html->td($this->priority());
        return $result;
    }

    /**
     * @param bool $is_admin true if the requesting user is an admin, who can also upgrade the job
     * @param int $msk_id the id of the shown view, so that the button returns to this view
     * @returns string the html code of the job as table cells followed by the buttons to change an open job
     */
    function display_with_actions(bool $is_admin, int $msk_id): string
    {
        $html = new html_base();
        $buttons = '';
        if ($this->is_open()) {
            if ($is_admin) {
                $buttons .= $this->action_form(url_var::ACTION_JOB_UPGRADE, msg_id::SYSTEM_BUTTON_JOB_UPGRADE, $msk_id);
            }
            $buttons .= $this->action_form(url_var::ACTION_JOB_DOWNGRADE, msg_id::SYSTEM_BUTTON_JOB_DOWNGRADE, $msk_id);
            $buttons .= $this->action_form(url_var::ACTION_JOB_CANCEL, msg_id::SYSTEM_BUTTON_JOB_CANCEL, $msk_id);
        }
        return $this->display() . $html->td($buttons);
    }

    /**
     * @returns string the html code to show the table header for system log entries and non admin users
     */
    function header(bool $with_actions = false): string
    {
        $html = new html_base();
        // TODO replace with language specific headers
        $result = $html->th('request time');
        $result .= $html->th('start time');
        $result .= $html->th('end time');
        $result .= $html->th('user');
        $result .= $html->th('type');
        $result .= $html->th('status');
        $result .= $html->th('priority');
        if ($with_actions) {
            $result .= $html->th('');
        }
        return $html->tr($result);
    }

    /**
     * a button that changes this job directly without a confirm view: the named submit with the confirmed step
     * lets url_to_action call the job change and then show the calling view again
     *
     * @param string $action the job action e.g. url_var::ACTION_JOB_CANCEL
     * @param msg_id $label the message id of the button text
     * @param int $msk_id the id of the shown view, so that the button returns to this view
     * @return string the html code of the form with the button
     */
    private function action_form(string $action, msg_id $label, int $msk_id): string
    {
        global $mtr;
        $html = new html_base();
        $result = $html->form_start(self::ACTION_FORM_PREFIX . $action . '_' . $this->id());
        $result .= $html->form_hidden(url_var::MASK, (string)$msk_id);
        $result .= $html->form_hidden(url_var::JOB, (string)$this->id());
        $result .= $html->form_hidden(url_var::ACTION, $action);
        $result .= $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED);
        $result .= $html->button_bs($mtr->txt($label), html_base::BS_BTN_CANCEL, '', url_var::POST_SUBMIT);
        $result .= $html->form_end();
        return $result;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = parent::api_array($typ_lst, $msg);
        $vars[json_fields::TIME_REQUEST] = $this->request_time()->format(DateTimeInterface::ATOM);
        $vars[json_fields::TIME_START] = $this->start_time()->format(DateTimeInterface::ATOM);
        $vars[json_fields::TIME_END] = $this->end_time()->format(DateTimeInterface::ATOM);
        $vars[json_fields::USER_ID] = $this->user_id();
        $vars[json_fields::TYPE] = $this->type();
        $vars[json_fields::STATUS] = $this->status();
        $vars[json_fields::PRIORITY] = $this->priority();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

    /*
     * to review
     */

    /**
     * display a job with a link to the main page for the job
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(array $url_arr = [], string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url_old(rest_ctrl::VIEW, $this->id(), $url_arr, url_var::WORDS);
        return $html->ref($url, $this->name(), $this->get_description(), $style);
    }

    /**
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string $style the CSS style that should be used
     * @returns string the job as a table cell
     */
    function td(array $url_arr = [], string $style = '', int $intent = 0): string
    {
        $cell_text = $this->display_linked($url_arr, $style);
        return (new html_base)->td($cell_text, '', $intent);
    }

    /**
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string $style the CSS style that should be used
     * @returns string the batch_job as a table cell
     */
    function th(array $url_arr = [], string $style = ''): string
    {
        return (new html_base)->th($this->display_linked($url_arr, $style));
    }

    /**
     * @return string the html code for a table row with the batch_job
     */
    function tr(): string
    {
        return new html_base()->tr($this->td());
    }


}
