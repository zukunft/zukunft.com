<?php

/*

    test/php/unit_workflow/url_test_base.php - shared base for the url based user workflow tests
    ----------------------------------------

    word_url_tests, triple_url_tests, value_url_tests, ... extend this class so the shared run
    state, the frontend setup and the snapshot helpers exist only once (see docs/llm/testing.md)

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

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::CONST . 'workflows.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';

use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\main\php\web\helper\user_request;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class url_test_base
{

    // the role that is always shown in the navbar user menu of a snapshot (see normalize_navbar_role)
    const string WF_FIXED_ROLE = 'system test';
    // the display names of the elevated profiles that user::navbar_role can render (user_profiles.csv),
    // longest first so that collapsing 'system test' never leaves a partial 'system' prefix behind
    const array NAVBAR_ROLE_NAMES = [
        'system test',
        'system link',
        'system log',
        'developer',
        'system',
        'admin',
    ];

    // the run state shared by all workflow steps so the step calls stay short (see docs/llm/testing.md)
    protected test_cleanup $t;       // the test environment
    protected string $ts;            // the test section prefix used in the headers
    protected frontend $ui;          // the frontend used to render the html
    protected user_ui $usr;          // the rendering (frontend) user
    protected user_message $usr_msg; // the message buffer carried through the steps
    protected user_request $req;     // the bundled request context for the workflow steps
    protected int $wf_id;            // the dynamic db id of the object the workflow runs on
    protected int $wf_fixed_id;      // the fixed snapshot id that replaces the dynamic id
    protected array $wf_norm_ids = []; // more volatile db ids replaced in the snapshots by their fixed test id (real id => fixed id) e.g. the from and to words of the test triple
    protected string $step_path;     // the snapshot file path grown by the cumulative spine steps
    protected string $http_method;   // the form method of the most recently rendered page, used as the method of the next save / confirm form submit
    protected string $url;           // the last call url of the workflow


    /**
     * load the frontend and the test users into the shared run state and print the section header
     *
     * @param test_cleanup $t the test environment
     * @param string $name the test name prefix e.g. 'word url->'
     * @param string $ts the test section prefix e.g. 'url word '
     */
    protected function init(test_cleanup $t, string $name, string $ts): void
    {
        $this->t = $t;
        $this->ts = $ts;
        $this->usr_msg = new user_message();
        $this->ui = new frontend('view');
        $this->ui->load_cache();
        // the html renderers read the type cache from the global $ui_sys; point it at the just loaded
        // frontend cache so the render does not depend on a stale cache left by another test
        global $ui_sys;
        $ui_sys = $this->ui->dto;
        // the change log renderer reads the date format from $ui_sys->cfg; an empty config_ui returns
        // the shared default format so an object with history renders
        $ui_sys->cfg = new config_ui();
        $this->usr = new user_ui();
        $this->usr->set_from_json($t->usr1->api_json(), $this->usr_msg);
        // the backend attributes an insert (the new row user and the change log author) to the
        // message user (see db_object_seq_id insert log), so use usr1 - the preferred test user -
        // to record all workflow changes for usr1; the test profile of usr1 is system privileged
        // (user::is_system), so adding the reserved test names is still allowed (check_preserved)
        $this->usr_msg->usr = $this->usr;
        $t->name = $name;
        $t->header($ts);
    }

    /**
     * start a workflow snapshot run: print the subheader, build the request context and the snapshot
     * path; the child sets $this->wf_id and $this->wf_fixed_id before calling this (docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 2 for wf2
     * @param string $name the workflow name used for the snapshot folder and the subheader e.g. 'change_word'
     * @param user $usr the requesting user that is performing the workflow actions
     * @param int $fix_id if the workflow adds an object the database id of the object may change. For the test files the id is fixed so that the test files are not volatile any more
     * @param bool $do_it false to only render the steps (snapshot unit test), true to also write the
     *                     confirmed change to the database (workflow write test)
     */
    protected function wf_start(
        int $wf_nbr,
        string $name,
        user $usr,
        int $fix_id = 0,
        bool $do_it = false
    ): void
    {
        // the snapshot file name prefix of this workflow e.g. 'wf2'
        $wf = workflows::WF_PREFIX . $wf_nbr;
        $this->t->subheader($this->ts . $name . ' workflow ' . $wf);
        $this->wf_fixed_id = $fix_id;
        // each workflow sets its own additional ids to normalize (e.g. the triple from/to words)
        $this->wf_norm_ids = [];
        // fix the user
        $usr_ui = new user_ui($usr->api_json());
        $this->usr = $usr_ui;
        // start each workflow with a fresh message buffer so a warning from a previous workflow
        // (e.g. the empty-name warning of a *_fail workflow) does not leak into this workflow's first snapshot;
        // the message user is usr1 so the workflow writes are recorded for the preferred test user (see init)
        $this->usr_msg = new user_message();
        $this->usr_msg->usr = $this->usr;
        // render in test mode so that the snapshot is reproducible without backend calls
        $this->req = new user_request($this->t->usr1, $this->usr_msg, $this->ui->dto, $do_it, true);
        // no page has been rendered yet; default the form method to get until the first render updates it
        $this->http_method = rest_ctrl::GET;
        // a write run (do_it true) persists the change and snapshots into the parallel workflow_write
        // folder so the read-only and write snapshots of the same workflow stay separate
        $base_path = $do_it ? test_paths::WORKFLOW_WRITE : test_paths::WORKFLOW;
        $this->step_path = $base_path
            . $name . workflows::NAME_SEP . $wf . DIRECTORY_SEPARATOR . $wf;
    }

    /**
     * run one workflow step and snapshot the resulting html:
     * build the step url from the step view, the object id and any extra url parameters (the pending
     * change for save and confirm), render the user reaction and compare the html against the
     * cumulative snapshot file (docs/llm/testing.md). the file name is the cumulative step path plus
     * this step, e.g. wf2_show_edit_save_confirm; the caller grows $this->step_path for the spine
     * steps (show, edit, save) and leaves it for the excursions (back, confirm, cancel)
     *
     * @param string $step the user reaction action const e.g. url_var::ACTION_SHOW
     * @param array $url_arr the extra url parameters of this step e.g. the fields of a pending change
     * @param int $msk_id the view shown by this step e.g. views::WORD_EDIT_ID
     * @return string the rendered html so the caller can check the button urls against the next step
     */
    protected function assert_step(string $step, array $url_arr, int $msk_id = 0): string
    {
        // remember the step for the next steps
        $this->step_path .= workflows::NAME_SEP . $step;
        // add the view to the url
        if ($step == workflows::BACK) {
            $url_arr = html_base::url_par_from_back_part($url_arr);
        } else {
            $url_arr[url_var::MASK] = $msk_id;
        }
        // add the step to the url
        $url_arr[url_var::STEP] = workflows::url_step($step);
        // compare the url with the fixed url test files
        $this->assert_url($this->step_path, $step, $url_arr, rest_ctrl::GET);
        // measure the action and the rendering separately, so a slow step shows which of the two
        // is slow; an interleaved db read or write still counts as db_read / db_write because its
        // own switch() restores this section
        global $sys;
        $sys->times->switch(system_time_type::URL_TO_ACTION);
        $next_url = $this->ui->url_to_action($url_arr,
            $this->req->usr_backend, $this->req->usr_msg,
            $this->req->dto, $this->req->do_it);
        $sys->times->switch(system_time_type::URL_TO_HTML);
        // render in test mode so that the snapshot is reproducible without backend calls
        $result = $this->ui->url_to_html($next_url, $this->req->usr_msg,
            $this->req->dto, $this->req->test_mode);
        // return to the default section for the following assertions
        $sys->times->switch(system_time_type::DEFAULT);
        $this->assert_html($this->step_path, $result, $next_url);
        return $result;
    }

    /**
     * run one workflow step and snapshot the resulting html:
     * build the step url from the step view, the object id and any extra url parameters (the pending
     * change for save and confirm), render the user reaction and compare the html against the
     * cumulative snapshot file (docs/llm/testing.md). the file name is the cumulative step path plus
     * this step, e.g. wf2_show_edit_save_confirm; the caller grows $this->step_path for the spine
     * steps (show, edit, save) and leaves it for the excursions (back, confirm, cancel)
     *
     * @param string $step the user reaction action const e.g. url_var::ACTION_SHOW
     * @param int $msk_id the view shown by this step e.g. views::WORD_EDIT_ID
     * @param array $url_par the extra url parameters of this step e.g. the fields of a pending change
     * @return string the rendered html so the caller can check the button urls against the next step
     */
    protected function assert_workflow_step(string $step, int $msk_id, array $url_par = []): string
    {
        // an add workflow has no object id yet (wf_id 0), so the id is only added for existing objects
        $url_arr = [url_var::MASK => $msk_id];
        if ($this->wf_id > 0) {
            $url_arr[url_var::ID] = $this->wf_id;
        }
        $url_arr = $url_arr + $url_par;
        $test_name = $this->step_path . workflows::NAME_SEP . $step;
        // a form submit (save / *_confirmed) uses the method of the form of the previous page; show,
        // edit, back and cancel are plain get navigation links
        $method = workflows::is_form_submit($step) ? $this->http_method : rest_ctrl::GET;
        // snapshot the url that this step's button press calls together with that http method
        $this->assert_wf_url($test_name, $step, $url_arr, $method);
        $result = $this->ui->execute_and_next($url_arr, $this->req);
        // remember the method of the form on the rendered page so the next save / confirm uses it
        $this->http_method = $this->form_method($result);
        $this->assert_wf_html($test_name, $result, $url_arr);
        return $result;
    }

    /**
     * // TODO Prio 2 review
     * the http method the form of a rendered page uses: post if the page contains a post form (the
     * edit / confirm form once form_start posts), otherwise get (a get form or a plain navigation link)
     *
     * @param string $html the rendered html of a workflow step
     * @return string the http method const e.g. rest_ctrl::GET
     */
    protected function form_method(string $html): string
    {
        $result = rest_ctrl::GET;
        if (str_contains($html, html_base::METHOD . '="' . html_base::METHOD_POST . '"')) {
            $result = rest_ctrl::POST;
        }
        return $result;
    }

    /**
     * // TODO Prio 2 review
     * snapshot the url that the button of a workflow step calls into a parallel '<step>_url.txt' file
     * next to the html snapshot, so a reviewer sees the request the step sends. the file lines are:
     * the http method, the directly callable url (with the pod host), the standard url (numeric view id
     * and url var keys), the same url in the human readable format (code id and long url var keys) and
     * the human url as json. the volatile object id is normalized to the fixed id (docs/llm/testing.md)
     *
     * @param string $test_name the description and snapshot file path of the step
     * @param string $step the user reaction action const e.g. url_var::ACTION_SAVE
     * @param array $url_arr the url parameters of the step before the process step is added
     * @param string $method the http method the request is sent with (from the previous page's form)
     */
    protected function assert_url(string $test_name, string $step, array $url_arr, string $method): void
    {
        // the user reaction adds the process step (e.g. save -> to confirm) to the request url
        $script = api::HOST_SAME . api::MAIN_SCRIPT_EXT . url_var::PAR;
        $url_map = new url_mapper();
        // a form submit (save / *_confirmed) carries the named submit button marker so that view.php
        // routes the directly called url through url_to_action (which writes); without it the url just
        // re-renders the form. the marker is a control key, so it is only in the callable / standard url
        $request_arr = $url_arr;
        if (workflows::is_form_submit($step)) {
            $request_arr[url_var::POST_SUBMIT] = '';
        }
        $query = http_build_query($request_arr);
        // the call url has the full pod host so a developer can paste it into a browser and call it
        // directly; the standard url is the relative request the form sends
        $call_url = THIS_URL . api::MAIN_SCRIPT_EXT . url_var::PAR . $query;
        $std_url = $script . $query;
        $human_url = $script . $url_map->standard_url_to_human($url_arr, $this->usr_msg);
        // the human url as a json object with the 8- / 9-prefixed vars grouped into subarrays
        $human_json = $url_map->human_url_to_json($url_arr, $this->usr_msg);
        $content = $method . "\n" . $call_url . "\n" . $std_url . "\n" . $human_url . "\n" . $human_json;
        $content = $this->normalize_ids($content, $url_arr[url_var::ID] ?? 0);
        // building the human url and json mapping reads from the database and compares against a file,
        // so a file timeout is used to avoid a false timeout
        $this->t->assert_file($test_name . '_url', $content,
            test_paths::RESOURCE . $test_name . '_url' . test_files::TXT, test_files::TXT, '', $this->t::TIMEOUT_LIMIT_FILE);
    }

    /**
     * replace the volatile db ids with the fixed test ids so the snapshot file is stable: the prime
     * object id of the workflow and the additional ids of the related objects (e.g. the from and to
     * words of the test triple), each both in the url form ('=999') and in the json form ('"999"')
     *
     * @param string $content the url lines or the html of a workflow step
     * @param int $id the current db id of the prime object of the workflow (0 if not known)
     * @return string the content with the volatile ids replaced by the fixed test ids
     */
    private function normalize_ids(string $content, int $id): string
    {
        $norm_ids = [$id => $this->wf_fixed_id] + $this->wf_norm_ids;
        foreach ($norm_ids as $db_id => $fixed_id) {
            if ($db_id > 0 and $db_id != $fixed_id) {
                $content = str_replace(
                    [url_var::EQ . $db_id, '"' . $db_id . '"'],
                    [url_var::EQ . $fixed_id, '"' . $fixed_id . '"'],
                    $content);
            }
        }
        return $content;
    }

    /**
     * // TODO Prio 2 review
     * snapshot the url that the button of a workflow step calls into a parallel '<step>_url.txt' file
     * next to the html snapshot, so a reviewer sees the request the step sends. the file lines are:
     * the http method, the directly callable url (with the pod host), the standard url (numeric view id
     * and url var keys), the same url in the human readable format (code id and long url var keys) and
     * the human url as json. the volatile object id is normalized to the fixed id (docs/llm/testing.md)
     *
     * @param string $test_name the description and snapshot file path of the step
     * @param string $step the user reaction action const e.g. url_var::ACTION_SAVE
     * @param array $url_arr the url parameters of the step before the process step is added
     * @param string $method the http method the request is sent with (from the previous page's form)
     */
    protected function assert_wf_url(string $test_name, string $step, array $url_arr, string $method): void
    {
        // the user reaction adds the process step (e.g. save -> to confirm) to the request url
        $url_arr[url_var::STEP] = url_var::action_step($step);
        $script = api::HOST_SAME . api::MAIN_SCRIPT_EXT . url_var::PAR;
        $url_map = new url_mapper();
        // a form submit (save / *_confirmed) carries the named submit button marker so that view.php
        // routes the directly called url through url_to_action (which writes); without it the url just
        // re-renders the form. the marker is a control key, so it is only in the callable / standard url
        $request_arr = $url_arr;
        if (workflows::is_form_submit($step)) {
            $request_arr[url_var::POST_SUBMIT] = '';
        }
        $query = http_build_query($request_arr);
        // the call url has the full pod host so a developer can paste it into a browser and call it
        // directly; the standard url is the relative request the form sends
        $call_url = THIS_URL . api::MAIN_SCRIPT_EXT . url_var::PAR . $query;
        $std_url = $script . $query;
        $human_url = $script . $url_map->standard_url_to_human($url_arr, $this->usr_msg);
        // the human url as a json object with the 8- / 9-prefixed vars grouped into subarrays
        $human_json = $url_map->human_url_to_json($url_arr, $this->usr_msg);
        $content = $method . "\n" . $call_url . "\n" . $std_url . "\n" . $human_url . "\n" . $human_json;
        $content = $this->normalize_ids($content, $this->wf_id);
        // building the human url and json mapping reads from the database and compares against a file,
        // so a file timeout is used to avoid a false timeout
        $this->t->assert_file($test_name . '_url', $content,
            test_paths::RESOURCE . $test_name . '_url' . test_files::TXT, test_files::TXT, '', $this->t::TIMEOUT_LIMIT_FILE);
    }

    /**
     * check that the cancel button shown in the rendered html points to the view and object id that
     * the workflow navigates to next, so the simulated back/cancel step really follows the url that
     * the rendered button would call instead of a hand-built url that may drift from it
     *
     * @param string $html the rendered html that shows the cancel button
     * @param int $msk_id the view the cancel button is expected to return to e.g. views::WORD_ID
     * @param string $test_name the description of the step
     */
    protected function assert_button_url(string $html, int $msk_id, string $test_name): void
    {
        $hit = [];
        $mask_esc = preg_quote(url_var::MASK, '/');
        $id_esc = preg_quote(url_var::ID, '/');
        $btn_esc = preg_quote(html_base::BS_BTN_CANCEL, '/');
        $pattern = '/href="[^"]*[?&]' . $mask_esc . '=(\d+)&amp;' . $id_esc . '=(\d+)"[^>]*' . $btn_esc . '/';
        preg_match($pattern, $html, $hit);
        $btn_target = ($hit[1] ?? '') . workflows::NAME_SEP . ($hit[2] ?? '');
        $exp_target = $msk_id . workflows::NAME_SEP . $this->wf_id;
        $this->t->assert($test_name, $btn_target, $exp_target);
    }

    /**
     * snapshot a workflow step after replacing the values that are volatile between test runs
     * (the dynamically assigned object id, the change log add time / add user and the navbar user
     * role) with fixed text, so the result does not change every run
     *
     * @param string $test_name the description and snapshot file path of the step
     * @param string $html the rendered html of the step
     */
    protected function assert_wf_html(string $test_name, string $html): void
    {
        // replace the volatile object / back id (assigned dynamically on insert) with a fixed test id;
        // an add workflow has no id yet (wf_id 0), so there is nothing to normalize
        $html = $this->normalize_ids($html, $this->wf_id);
        // the change history of the test object shows the real change time and change user, both of
        // which vary per run; replace each change log line (date time + user + action) with a fixed
        // text - this covers the default view (in a container div) and the edit view (a bare line)
        $html = preg_replace(
            '#\d{2}-\d{2}-\d{4} \d{2}:\d{2}[^<\n]*#',
            workflows::WF_CHANGE_LOG,
            $html);
        $html = $this->normalize_navbar_role($html);
        $this->t->assert_html_page($test_name, $html, $test_name);
    }

    /**
     * snapshot a workflow step after replacing the values that are volatile between test runs
     * (the dynamically assigned object id, the change log add time / add user and the navbar user
     * role) with fixed text, so the result does not change every run
     *
     * @param string $test_name the description and snapshot file path of the step
     * @param string $html the rendered html of the step
     * @param array $url_arr array of the url parameter
     */
    protected function assert_html(string $test_name, string $html, array $url_arr): void
    {
        // replace the volatile object / back id (assigned dynamically on insert) with a fixed test id;
        // an add workflow has no id yet (wf_id 0), so there is nothing to normalize
        $html = $this->normalize_ids($html, $url_arr[url_var::ID] ?? 0);
        // the change history of the test object shows the real change time and change user, both of
        // which vary per run; replace each change log line (date time + user + action) with a fixed
        // text - this covers the default view (in a container div) and the edit view (a bare line)
        $html = preg_replace(
            '#\d{2}-\d{2}-\d{4} \d{2}:\d{2}[^<\n]*#',
            workflows::WF_CHANGE_LOG,
            $html);
        $html = $this->normalize_navbar_role($html);
        $this->t->assert_html_page($test_name, $html, $test_name);
    }

    /**
     * user::navbar_role() resolves the elevated role label only when the user profile cache is
     * loaded; that is not guaranteed across test runners (a missing profile gives an empty role
     * and a differing cache another role name), so any rendered role prefix is collapsed first and
     * the system test role is always shown in the navbar user menu to keep the snapshot deterministic
     *
     * @param string $html the rendered html of the step
     * @return string the html with the fixed navbar user role
     */
    private function normalize_navbar_role(string $html): string
    {
        $result = $html;
        $name = $this->req->usr_msg->usr?->name();
        if ($name != null and $name != '') {
            // scope the role prefix to the navbar user menu (the <details class="user-menu"> block)
            // only, so it is never added to other occurrences of the user name on the page e.g. in
            // the change log 'who' column (the navbar_role label is a navbar-only display of the
            // requesting user, see user::navbar_role)
            $open = '<' . html_base::DETAILS . ' ' . html_base::CLASS_HTML . '="user-menu">';
            $close = '</' . html_base::DETAILS . '>';
            $start = strpos($html, $open);
            if ($start !== false) {
                $end = strpos($html, $close, $start);
                if ($end !== false) {
                    $end += strlen($close);
                    $navbar = substr($html, $start, $end - $start);
                    // collapse any elevated role prefix (see user::navbar_role) that the live render
                    // has added, so re-adding the fixed role below never stacks e.g. to 'system
                    // system test'; the display names of the elevated profiles have no const (csv)
                    foreach (self::NAVBAR_ROLE_NAMES as $role) {
                        $navbar = str_replace($role . ' ' . $name, $name, $navbar);
                    }
                    // then always show the fixed system test role in the navbar user menu
                    $navbar = str_replace($name, self::WF_FIXED_ROLE . ' ' . $name, $navbar);
                    $result = substr($html, 0, $start) . $navbar . substr($html, $end);
                }
            }
        }
        return $result;
    }

}