<?php

/*

    test/php/unit_workflow/login_url_tests.php - check the login from the static start page
    ------------------------------------------

    snapshots the html of each step of the login_start_page workflow: the user opens the login link
    of the static start page index.html, whose signup link keeps the start page as back target, and
    logs in, which returns to the static start page; the shared run state, the frontend setup and
    the snapshot helpers live in url_test_base (see docs/llm/testing.md)

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

include_once paths::SHARED . 'api.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class login_url_tests extends url_test_base
{

    // the back target of the login and signup links of the static start page index.html e.g. 9ix=1
    const array START_PAGE_BACK = [url_var::BACK . url_var::START_PAGE => 1];

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'login url->', 'url login ');

        $t->subheader($this->ts . 'workflow');

        // the snapshot unit test only renders the steps
        // for the write test the same workflow is used with do_it = true
        $this->login_start_page_workflow(workflows::WF_LOGIN_START_PAGE_NBR);
    }

    /**
     * run the login_start_page workflow and snapshot the html after every user action: open the
     * login link of the static start page, check that its signup link keeps the start page as back
     * target, follow that link and send the login form; only a write run really logs in, so only
     * there the redirect to the static start page is checked; the session is never touched (see
     * frontend::live_request). snapshots go into src/test/resources/web/html/workflow/login_start_page_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 41 for wf41
     * @param bool $do_it false to only render the steps, true to also log in the system test user
     */
    protected function login_start_page_workflow(int $wf_nbr, bool $do_it = false): void
    {
        global $ui_sys;

        $t = $this->t;
        // the login publishes the user as session user of the request cache (see
        // frontend::url_to_html), so the previous session user is restored at the end of the workflow
        $usr_keep = $ui_sys->usr ?? null;
        $this->ui->live_request = false;
        $this->wf_start($wf_nbr, workflows::WF_LOGIN_START_PAGE, $t->usr1, 0, $do_it);
        // the visitor of the static start page is not logged in yet
        $this->msg->usr = null;

        // edit: open the login form by the login link of the static start page
        $html = $this->assert_step(workflows::EDIT, self::START_PAGE_BACK, views::LOGIN_ID, true);
        $test_name = 'the signup link of the login keeps the static start page as back target';
        $t->assert_text_contains($test_name, $html, $this->signup_link_part());

        // show: follow the signup link, an excursion that leaves the spine of the login steps
        $login_path = $this->step_path;
        $this->assert_step(workflows::SHOW, self::START_PAGE_BACK, views::SIGNUP_ID, true);
        $this->step_path = $login_path;

        // fill: enter the name and the password and send the login form
        $login_url = test_users::login_url_array() + self::START_PAGE_BACK;
        $this->assert_step(workflows::FILL, $login_url, views::LOGIN_ID, true);
        if ($do_it) {
            $test_name = 'the login from the static start page returns to the static start page';
            $t->assert($test_name, frontend::redirect_url($this->next_url, $this->msg), api::START_PAGE);
        }
        $this->msg->reset();
        if ($usr_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_keep;
        }
    }

    /**
     * @return string the html escaped query of the signup link with the start page as back target e.g. m=13&amp;9ix=1
     */
    private function signup_link_part(): string
    {
        $back_part = http_build_query(self::START_PAGE_BACK);
        return url_var::MASK . url_var::EQ . views::SIGNUP_ID . htmlspecialchars(url_var::ADD) . $back_part;
    }

}
