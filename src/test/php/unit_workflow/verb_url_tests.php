<?php

/*

    test/php/unit_workflow/verb_url_tests.php - check the url based verb user workflows
    -----------------------------------------

    snapshots the html of each step of the change_verb workflow; the shared run state, the frontend
    setup and the snapshot helpers live in url_test_base (see docs/llm/testing.md)

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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_verbs.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'verb url->', 'url verb ');

        $t->subheader($this->ts . 'workflow');

        // the snapshot unit test only renders the steps
        // for the write tests the same workflow is used with do_it = true
        $this->change_verb_workflow(workflows::WF_CHANGE_VERB_NBR);
    }

    /**
     * run the change_verb workflow and snapshot the html after every user action: the edit view sends
     * the stored values as the '8' opening values, the name in formulas is changed, saved and confirmed;
     * the negative: a save of the edit view without a change shows no field change. it runs on the
     * 'System Test Verb' (added by verb_write_url_tests of a write run), never on a seed verb.
     * snapshots go into src/test/resources/web/html/workflow/change_verb_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 48 for wf48
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_verb_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        $html = new html_base();
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_VERB, $this->t->usr1, verbs::TEST_ADD_ID, $do_it);

        // the current database id of the test verb; in a read-only run the test verb has not been
        // written, so the fixed id is used directly
        $vrb = new verb();
        $vrb->set_user($this->t->usr1);
        $this->wf_id = $vrb->load_by_name(verbs::TEST_ADD_NAME, $msg);
        if ($this->wf_id == 0) {
            $this->wf_id = verbs::TEST_ADD_ID;
        }
        $this->wf_fixed_id = verbs::TEST_ADD_ID;

        // the url of the unchanged edit form with the '8' opening values and the verb page as back target
        $url_arr = test_verbs::edit_url();
        $url_arr[url_var::ID] = $this->wf_id;
        $url_arr = $url_arr + html_base::pre_url_array($url_arr);
        $url_arr[url_var::BACK . url_var::MASK] = views::VERB_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // edit: open the verb edit view
        $page = $this->assert_step(workflows::EDIT, $url_arr, views::VERB_EDIT_ID);
        $test_name = 'the edit view sends the stored name in formulas as the opening value';
        $pre_frm_name = $html->form_hidden(url_var::PRE . url_var::NAME_IN_FORMULA, '');
        $this->t->assert_text_contains($test_name, $page, $pre_frm_name);
        $wf_path = $this->step_path;

        // user is typing the name in formulas
        $url_arr[url_var::NAME_IN_FORMULA] = verbs::TEST_ADD_FORMULA_NAME;

        // save: press save on the edit form which shows the confirm change view
        $page = $this->assert_step(workflows::SAVE, $url_arr, views::VERB_EDIT_ID);
        $test_name = 'the confirm view shows the changed name in formulas';
        $frm_name_row = $html->td(url_var::NAME_IN_FORMULA_HUMAN);
        $this->t->assert_text_contains($test_name, $page, $frm_name_row);

        // confirmed: confirm the change so it is actually written to the database (with do_it true)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);
        if ($do_it) {
            $test_name = 'change_verb workflow has changed the name in formulas';
            $this->assert_verb_formula_name_in_db($test_name, verbs::TEST_ADD_FORMULA_NAME);
        }

        // negative: the edit view after the change sends the new name in formulas as its opening value,
        // so a save without a change of any field shows no change table
        $url_arr[url_var::PRE . url_var::NAME_IN_FORMULA] = verbs::TEST_ADD_FORMULA_NAME;
        $this->step_path = $wf_path . workflows::NAME_SEP . workflows::STEP_UNCHANGED;
        $page = $this->assert_step(workflows::SAVE, $url_arr, views::VERB_EDIT_ID);
        $test_name = 'the confirm view of a save without a change shows no field change';
        $this->t->assert_text_not_contains($test_name, $page, styles::CHANGE_PREVIEW);
    }

    /**
     * check that the test verb has the expected name in formulas in the database, used by the change
     * write workflow to verify that the confirmed step was actually persisted
     *
     * @param string $test_name the description of the assertion
     * @param string $frm_name the expected name in formulas of the test verb in the database
     */
    private function assert_verb_formula_name_in_db(string $test_name, string $frm_name): void
    {
        $msg = new user_message();
        $vrb = new verb();
        $vrb->set_user($this->t->usr1);
        $vrb->load_by_name(verbs::TEST_ADD_NAME, $msg);
        $this->t->assert($test_name, $vrb->frm_name, $frm_name);
    }

}
