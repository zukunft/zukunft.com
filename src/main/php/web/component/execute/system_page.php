<?php

/*

    web/component/execute/system_page.php - create the html code for fixed system pages
    -------------------------------------

    to create the HTML code to display a fixed system page

    The main sections of this object are
    - object vars:       the variables of this word object


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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SYSTEM . 'job.php';
include_once html_paths::SYSTEM . 'job_list.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::USER . 'user.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_HELPER . 'Translator.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\job;
use Zukunft\ZukunftCom\main\php\web\system\job_list;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class system_page extends component
{

    /**
     * HTML for a page title
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @return string the html code to start a new form and display the tile
     */
    function system_tile(?msg_id $ui_msg_code_id = null): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h2($mtr->txt($ui_msg_code_id));
        }
        return $result;
    }

    /**
     * HTML for a subtitle
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @return string the html code to start a new form and display the subtitle
     */
    function system_sub_tile(
        ?msg_id $ui_msg_code_id = null
    ): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
        }
        return $result;
    }

    /**
     * HTML for a subtitle
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @return string the html code to start a new form and display the subtitle
     */
    function system_sub_tile_var(
        ?msg_id $ui_msg_code_id = null,
        ?int    $value_numeric = null,
        ?msg_id $ui_msg_code_id_vars = null,
        ?int    $value_exception = null,
        ?msg_id $ui_msg_code_id_exception = null
    ): string
    {
        global $mtr;
        $lib = new library();

        $html = new html_base();
        $result = '';
        if ($value_exception != null and $value_numeric == $value_exception) {
            if ($ui_msg_code_id_exception != null) {
                $result .= $html->text_h3($mtr->txt($ui_msg_code_id_exception));
            }
        } else {
            if ($ui_msg_code_id != null) {
                $result .= $html->text_h3($mtr->txt($ui_msg_code_id));
            }
            if ($ui_msg_code_id_vars != null) {
                $msg_id = $mtr->txt($ui_msg_code_id_vars);
                $msg_txt = '';

                // TODO Prio 1 move to a general function
                if ($msg_id == msg_id::FORM_SUB_TITLE_VAR_USAGE->value) {
                    $msg_txt = msg_id::SYS_MSG_USAGE;
                    $msg_txt = $lib->msg_var_replace(
                        $msg_txt->value,
                        msg_id::VAR_USAGE,
                        $value_numeric);
                }

                $result .= ' ' . $html->text_h3($msg_txt);
            }
        }
        return $result;
    }

    // TODO Prio 0 fill with real code
    /**
     * show a view zoomed to 1/3 of its original size as a preview so that the user can see a
     * preview of the original page based on a different view mask
     */
    function preview(): string
    {
        return 'preview placeholder';
    }

    // TODO Prio 0 fill with real code
    function about_body(): string
    {
        $html = new html_base();
        return $html->about_body();
    }

    // TODO Prio 0 fill with real code
    /**
     * request from the user the values relevant for the initial setup
     * so the main question ist that the user confirms the admin username and password from the .env for
     * or fill the admin user of the .env file entry is empty
     *
     * additional all values from the .env.example file should be show and be changeable
     *
     */
    function setup_body(): string
    {
        return 'setup_body placeholder';
    }

    /**
     * build the signup form HTML
     *
     * @param array $url_array the POST parameters from the signup form submission; used to pre-fill fields after a validation error
     * @return string the complete signup page body HTML (without notification bar; caller renders that separately)
     */
    function signup_body(array $url_array): string
    {
        global $mtr;

        $html = new html_base();
        $usr_name = $url_array[url_var::USERNAME] ?? '';
        $email = $url_array[url_var::EMAIL] ?? '';

        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::SIGNUP_ID);
        foreach (url_var::back_par($url_array) as $key => $val) {
            $extra_hidden .= $html->form_hidden($key, $val);
        }

        $web_usr = new user_dsp();
        $form_str = $web_usr->form_signup($extra_hidden, $usr_name, $email);

        $result = $html->p($mtr->txt(msg_id::SIGNUP_ALPHA_NOTICE));
        $result .= $html->p($html->dsp_err($mtr->txt(msg_id::SIGNUP_DATA_WARNING)));
        $result .= $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    function login_body(array $url_array = []): string
    {

        $html = new html_base();
        $_SESSION[url_var::SESSION_LOGGED] = false;

        // embed the login mask and any BACK-prefixed params as hidden fields so they survive the POST
        // view.php dispatches on url_var::MASK in url_to_action; without LOGIN_ID the POST has no routing key and action_login is never called
        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::LOGIN_ID);
        foreach (url_var::back_par($url_array) as $key => $val) {
            $extra_hidden .= $html->form_hidden($key, $val);
        }

        $web_usr = new user_dsp();
        $back_url = html_base::url_from_back($url_array);
        $form_str = $web_usr->form_login($extra_hidden, $back_url);

        return $html->logo_flex() . $html->br2() . $html->div($form_str, html_base::CLASS_INPUT_SECTION);
    }

    /**
     * build the account activation (password change) form HTML
     *
     * @param array $url_array the URL params; expects id and optionally key from the activation link
     * @return string the complete activation page body HTML
     */
    function activate_body(array $url_array): string
    {
        $html = new html_base();
        $usr_id = (int)($url_array[url_var::ID] ?? 0);
        $key = $url_array[url_var::POST_KEY] ?? '';

        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::LOGIN_ACTIVATE_ID);
        foreach (url_var::back_par($url_array) as $key_par => $val) {
            $extra_hidden .= $html->form_hidden($key_par, $val);
        }

        $web_usr = new user_dsp();
        $form_str = $web_usr->form_activate($extra_hidden, $usr_id, $key);

        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    /**
     * build the password reset request form HTML
     *
     * @param array $url_array the URL params; back params are forwarded as hidden fields
     * @return string the complete reset page body HTML
     */
    function reset_body(array $url_array): string
    {
        global $mtr;

        $html = new html_base();

        $extra_hidden = $html->form_hidden(url_var::MASK, (string)views::LOGIN_RESET_ID);
        foreach (url_var::back_par($url_array) as $key => $val) {
            $extra_hidden .= $html->form_hidden($key, $val);
        }

        $web_usr = new user_dsp();
        $back_url = html_base::url_from_back($url_array);
        $form_str = $mtr->txt(msg_id::RESET_PROMPT) . $html->br2() . $web_usr->form_reset($extra_hidden, $back_url);

        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    /**
     * HTML shown on the logout confirmation page
     * @return string the logout page body HTML
     */
    function logout_body(): string
    {
        global $mtr;

        $html = new html_base();
        $result = $html->logo_flex();
        $result .= $html->br2();
        $result .= $html->div($html->p($mtr->txt(msg_id::LOGOUT_NOTICE)), html_base::CLASS_INPUT_SECTION);
        return $result;
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code to search for words, verbs, triple, formulas
     * based on the context (foaf terms) and "fixed" selections like the type or the share or protection
     * limit the number of search and selection fields so that it matches a small screen
     */
    function body_search(): string
    {
        return 'body_search placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * like body_search but with all possible fields
     * @return string
     */
    function body_search_full(): string
    {
        return 'body_search_full placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code to show all relations of a value
     * e.g. where it is used and are it causes an impact
     */
    function value_details(): string
    {
        return 'value_details placeholder';
    }

    // TODO Prio 0 fill with real code
    function result_explain(): string
    {
        return 'result_explain placeholder';
    }

    // TODO Prio 0 fill with real code
    function formula_test(): string
    {
        return 'formula_test placeholder';
    }

    // TODO Prio 0 fill with real code
    function sandbox(): string
    {
        return 'sandbox placeholder';
    }

    // TODO Prio 0 fill with real code
    function undo(): string
    {
        return 'undo placeholder';
    }

    // TODO Prio 0 fill with real code
    function user_setting(): string
    {
        return 'user_setting placeholder';
    }

    // TODO Prio 0 fill with real code
    function process(): string
    {
        return 'process placeholder';
    }

    // TODO Prio 0 fill with real code
    function error_log(): string
    {
        return 'error_log placeholder';
    }

    /**
     * render the admin error-update page body: a table of unresolved program issues an admin can
     * review and re-status; mirrors the display path of /http/error_update.php — the actual sys_log
     * status change is dispatched by the URL action handler so this body only renders the data
     *
     * @param sys_log_list|null $errors pre-loaded list of unresolved program issues; when null or
     *                                  empty the "no open errors" notice is shown
     * @param user_dsp|null $usr the session user; when null or not an admin a permission notice is
     *                           rendered instead of the issue list — default-deny matches the
     *                           legacy /http/error_update.php behaviour where anyone but an admin
     *                           saw the permission notice; the same user is forwarded to the
     *                           per-row renderer so each status-change link carries the right context
     * @param string $back back-link forwarded to each row's status-change link so navigation is preserved
     * @return string the HTML body for the error_update page
     */
    function error_update(
        ?sys_log_list $errors = null,
        ?user_dsp     $usr = null,
        string        $back = ''
    ): string
    {
        global $mtr;

        $html = new html_base();

        // default-deny: only an explicit admin sees the issue list; everyone else gets the permission notice
        if ($usr === null or !$usr->is_admin()) {
            $result = $html->text_h3($mtr->txt(msg_id::ERROR_UPDATE_PERMISSION_DENIED));
        } elseif ($errors !== null and !$errors->is_empty()) {
            $result = $html->text_h3($mtr->txt(msg_id::ERROR_UPDATE_PROGRAM_ISSUES))
                . $errors->get_html($usr, $back);
        } else {
            $result = $html->text_h3($mtr->txt(msg_id::ERROR_UPDATE_NO_OPEN));
        }
        return $result;
    }

    // TODO Prio 0 fill with real code
    function process_progress(): string
    {
        return 'process_progress placeholder';
    }

    // TODO Prio 0 fill with real code
    function process_list(): string
    {
        return 'process_list placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code that contains the most relevant user response delay within a time period defined in the system configuration
     */
    function admin_url_delay(): string
    {
        return 'admin_url_delay placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code that contains the last failed user logins
     */
    function admin_login_fails(): string
    {
        return 'admin_login_fails placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code that all internal system errors that are not yet assigned to a developer
     */
    function admin_errors_unassigned(): string
    {
        return 'admin_errors_unassigned placeholder';
    }

    // TODO Prio 0 fill with real code

    /**
     * @return string with the HTML code that all internal system errors that have not been updated since a some time (as defined in the system config)
     */
    function admin_errors_delayed_fix(): string
    {
        return 'admin_errors_delayed_fix placeholder';
    }

    /**
     * render an HTML table of all not-yet-closed system jobs ordered by the longest delay first;
     * "delay" is the time elapsed between request_time and now, and a job is "not yet closed" when end_time is null;
     * sorting by request_time ascending puts the longest-waiting job at the top of the table
     *
     * @param job_list|null $jobs the open-jobs list to render; when null or empty an empty-state row is shown
     *                            so the column headers stay visible to the admin
     * @return string the HTML code of the delayed-jobs table
     */
    function admin_jobs_delayed(?job_list $jobs = null): string
    {
        global $mtr;

        $html = new html_base();

        // keep only jobs that have not yet ended and sort by request_time ascending so the longest-waiting job is first
        $open = [];
        if ($jobs !== null and !$jobs->is_empty()) {
            foreach ($jobs->lst() as $job_obj) {
                if ($job_obj->end_time() === null) {
                    $open[] = $job_obj;
                }
            }
            usort($open, fn(job $a, job $b) => $a->request_time() <=> $b->request_time());
        }

        // build the body: one row per open job, or a single empty-state cell when no open jobs are available
        $body = '';
        foreach ($open as $job_obj) {
            $body .= $html->tr($job_obj->display());
        }
        if ($body === '') {
            $body = $html->tr($html->td($mtr->txt(msg_id::ADMIN_NO_OPEN_JOBS)));
        }

        // TODO Prio 1 wire the data source: load via web/system/job_list with the cut_off_time from the system config
        // job::header() already returns a full <tr><th>…</th></tr> row, so a fresh job instance is used to render the header
        $result = $html->tbl(new job()->header() . $body);
        return $result;
    }

}
