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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::CONST . 'workflows.php';

use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\main\php\web\helper\user_request;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class url_test_base
{

    // the run state shared by all workflow steps so the step calls stay short (see docs/llm/testing.md)
    protected test_cleanup $t;       // the test environment
    protected string $ts;            // the test section prefix used in the headers
    protected frontend $ui;          // the frontend used to render the html
    protected user_ui $usr;          // the rendering (frontend) user
    protected user_ui $usr_sys;      // the system (admin) user used for admin protected objects
    protected user_message $usr_msg; // the message buffer carried through the steps
    protected user_request $req;     // the bundled request context for the workflow steps
    protected int $wf_id;            // the dynamic db id of the object the workflow runs on
    protected int $wf_fixed_id;      // the fixed snapshot id that replaces the dynamic id
    protected string $step_path;     // the snapshot file path grown by the cumulative spine steps

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
        $this->usr_sys = new user_ui();
        $this->usr_sys->set_from_json($t->usr_system->api_json(), $this->usr_msg);
        $this->usr_msg->usr = $this->usr_sys;
        $t->name = $name;
        $t->header($ts);
    }

    /**
     * start a workflow snapshot run: print the subheader, build the request context and the snapshot
     * path; the child sets $this->wf_id and $this->wf_fixed_id before calling this (docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 2 for wf2
     * @param string $name the workflow name used for the snapshot folder and the subheader e.g. 'change_word'
     * @param bool $do_it false to only render the steps (snapshot unit test), true to also write the
     *                     confirmed change to the database (workflow write test)
     */
    protected function wf_start(int $wf_nbr, string $name, bool $do_it = false): void
    {
        // the snapshot file name prefix of this workflow e.g. 'wf2'
        $wf = workflows::WF_PREFIX . $wf_nbr;
        $this->t->subheader($this->ts . $name . ' workflow ' . $wf);
        $this->req = new user_request($this->t->usr1, $this->usr, $this->usr_msg, $this->ui->dto, $do_it);
        $this->step_path = test_paths::WORKFLOW
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
     * @param int $msk_id the view shown by this step e.g. views::WORD_EDIT_ID
     * @param array $url_par the extra url parameters of this step e.g. the fields of a pending change
     */
    protected function assert_workflow_step(string $step, int $msk_id, array $url_par = []): void
    {
        // an add workflow has no object id yet (wf_id 0), so the id is only added for existing objects
        $url_arr = [url_var::MASK => $msk_id];
        if ($this->wf_id > 0) {
            $url_arr[url_var::ID] = $this->wf_id;
        }
        $url_arr = $url_arr + $url_par;
        $test_name = $this->step_path . workflows::NAME_SEP . $step;
        $result = $this->ui->url_user_reaction($step, $url_arr, $this->req);
        $this->assert_wf_html($test_name, $result);
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
        if ($this->wf_id > 0) {
            $html = str_replace(
                ['=' . $this->wf_id, '"' . $this->wf_id . '"'],
                ['=' . $this->wf_fixed_id, '"' . $this->wf_fixed_id . '"'],
                $html);
        }
        // the change history of the test object shows the real change time and change user, both of
        // which vary per run; replace each change log line (date time + user + action) with a fixed
        // text - this covers the default view (in a container div) and the edit view (a bare line)
        $html = preg_replace(
            '#\d{2}-\d{2}-\d{4} \d{2}:\d{2}[^<\n]*#',
            workflows::WF_CHANGE_LOG,
            $html);
        // user::navbar_role() resolves the elevated role label only when the user profile cache is
        // loaded; that is not guaranteed across test runners (a missing profile gives an empty role),
        // so always show the system role in the navbar user menu to keep the snapshot deterministic
        $name = $this->req->usr->name();
        if ($name != null and $name != '') {
            // 'system test' is the display name of the system user profile (no const exists for it)
            $role_name = 'system test ' . $name;
            $html = str_replace($role_name, $name, $html); // collapse an already present role prefix
            $html = str_replace($name, $role_name, $html); // then always show the role
        }
        $this->t->assert_html_page($test_name, $html, $test_name);
    }

}