<?php

/*

    test/php/unit_workflow/view_url_tests.php - check the url based view user workflows
    -----------------------------------------

    snapshots the html of each step of the add_view workflow; the shared run state, the frontend
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

include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::HTML . 'html_base.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_views.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'view url->', 'url view ');

        // this test has no pure read (url_to_html) and no routing-only (url_to_action) tests yet;
        // when one is added, use the url_to_html_tests / url_to_action_tests split of word_url_tests
        $this->workflow_tests($t);
    }

    /**
     * the combined workflow snapshot tests: every step chains url_to_action (routing, with
     * do_it false so nothing is written) and url_to_html (render) like a real user request
     *
     * @param test_cleanup $t the test environment
     */
    private function workflow_tests(test_cleanup $t): void
    {
        $t->subheader($this->ts . 'workflow');

        // the snapshot unit test only renders the steps
        // for the write tests the same workflows are used with do_it = true
        $this->add_view_workflow(workflows::WF_ADD_VIEW_NBR);
        $this->change_view_workflow(workflows::WF_CHANGE_VIEW_NBR);
        $this->del_view_workflow(workflows::WF_DEL_VIEW_NBR);
    }

    /**
     * run the add_view workflow and snapshot the html after every user action, mirroring
     * add_source_workflow: the back excursion aborts the add without writing, then the view is
     * entered again and the final confirm adds it (do_it false here, so nothing is written).
     * the word workflows already cover the cancel excursion of an add, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/add_view_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 22 for wf22
     * @param bool $do_it false to only render the steps, true to also write the new view
     */
    protected function add_view_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_view workflow creates a new view, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_VIEW, $this->t->usr1, views::TEST_ADD_ID, $do_it);

        // initial url with an empty view
        $url_arr = test_views::view_new_url($this->msg);

        $this->wf_id = 0;
        $this->wf_fixed_id = views::TEST_ADD_ID;

        // the new view fields posted by the add form on save and shown again in the confirm add view
        $t_msk = new test_views($this->t);
        $add = $t_msk->add_url_array();

        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add view form
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new view
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_ADD_ID);

        // user is entering the new view: the name, the description, the type and the style
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::VIEW_ADD_ID);

        // confirmed: confirm the new view so it is actually added (with do_it true); the confirm form
        // posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::VIEW_DEFAULT_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the view, so check it is now in the database
        if ($do_it) {
            $this->assert_view_in_db('add_view workflow has written the view',
                views::TEST_ADD_NAME, $this->t->usr1, view_styles::COL_SM_4_ID);
        }
    }

    /**
     * run the change_view edit workflow and snapshot the html after every user action, mirroring
     * change_source_workflow: the back excursion aborts the change without writing, then the style is
     * changed and the confirm writes it; a second round then also changes the description and confirms
     * again. the word workflows already cover the cancel excursion of a change, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/change_view_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 23 for wf23
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_view_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the change_view workflow runs on the 'System Test View' (added by the add_view workflow of
        // a write run), never on real data; resolve its current database id by name and set the fixed
        // snapshot id of the test view
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_VIEW, $this->t->usr1, views::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $msk = new view($this->t->usr1);
        $this->wf_id = $msk->load_by_name(views::TEST_ADD_NAME, $msg);
        // in a read-only run the add workflow has not written the view, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = views::TEST_ADD_ID;
        }
        $this->wf_fixed_id = views::TEST_ADD_ID;

        // initial url with the added view; the url carries the current db id of the view so the
        // rendered buttons and the confirmed write target the real row (the snapshot files normalize
        // the id back to the fixed test id)
        $t_msk = new test_views($this->t);
        $url_arr = $t_msk->view_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::VIEW_DEFAULT_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test view in its default view page
        $this->assert_step(workflows::SHOW, $url_arr, views::VIEW_DEFAULT_ID);

        // edit: open the view edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the view page
        $this->assert_button_url($html, views::VIEW_DEFAULT_ID, $this->step_path);

        // back: leave the edit view without a change and return to the view page (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::VIEW_DEFAULT_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_EDIT_ID);

        // user is selecting the new view style
        $url_arr[url_var::STYLE] = view_styles::COL_SM_8_ID;

        // save: press save on the edit form which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::VIEW_EDIT_ID);

        // confirmed: confirm the pending change so it is actually written to the database (with do_it
        // true); the confirm form posts the confirm update mask, because url_to_action only routes a
        // confirmed change of an edit mask to the database write (see views::EDIT_MASKS_IDS)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new style in the database;
        // usr1 owns the view added by the add_view workflow (see url_test_base::init),
        // so the change is written to the usr1 standard row and is read back as usr1
        if ($do_it) {
            $this->assert_view_in_db('change_view workflow has changed the view',
                views::TEST_ADD_NAME, $this->t->usr1, view_styles::COL_SM_8_ID);
        }

        // the second round also changes the description; the fill url carries the refreshed '8'
        // opening values (the changed style is now the saved state), so its keys win the union and
        // the confirm shows only the new description
        $fill = $t_msk->fill_url_array($this->wf_id);
        $url_arr = $fill + $url_arr;

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_EDIT_ID);

        // fill: press save on the edit form with every field filled which shows the confirm change view
        $this->assert_step(workflows::FILL, $url_arr, views::VIEW_EDIT_ID);

        // confirmed: confirm the filled change so it is also written to the database (with do_it true)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the filled fields, so check the new description in the database
        if ($do_it) {
            $this->assert_view_filled_in_db('change_view workflow has filled the view',
                views::TEST_ADD_NAME, $this->t->usr1, views::TEST_DESCRIPTION_CHANGED);
        }
    }

    /**
     * run the del_view workflow and snapshot the html after every user action, mirroring
     * del_source_workflow: the back excursion leaves the delete form and the cancel excursion
     * discards the deletion in the confirm view, both without writing, and only the final
     * confirmed step removes the view. snapshots go into
     * src/test/resources/web/html/workflow/del_view_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 24 for wf24
     * @param bool $do_it false to only render the steps, true to also delete the view
     */
    protected function del_view_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the del_view workflow runs on the 'System Test View' (added by the add_view workflow of a
        // write run); resolve its current database id by name and set the fixed snapshot id
        $this->wf_start($wf_nbr, workflows::WF_DEL_VIEW, $this->t->usr1, views::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $msk = new view($this->t->usr1);
        $this->wf_id = $msk->load_by_name(views::TEST_ADD_NAME, $msg);
        // in a read-only run the add workflow has not written the view, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = views::TEST_ADD_ID;
        }
        $this->wf_fixed_id = views::TEST_ADD_ID;

        // initial url with the added view; the url carries the current db id of the view so the
        // confirmed delete targets the real row (the snapshot files normalize the id back to the
        // fixed test id)
        $t_msk = new test_views($this->t);
        $url_arr = $t_msk->view_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // show: display the test view in its default view page
        $this->assert_step(workflows::SHOW, $url_arr, views::VIEW_DEFAULT_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_DEL_ID);

        // back: leave the delete form without deleting; the back step follows the '9' back target of
        // the url, so it returns to the start view the user came from (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::VIEW_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the view page (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::VIEW_DEFAULT_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::VIEW_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::VIEW_DEL_ID);

        // confirmed: confirm the deletion so the view is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed
        // back target = the view page + id (as the real confirm form does), otherwise dbo_for_url
        // falls back to the default word object and the delete would target a word
        $url_arr[url_var::BACK . url_var::MASK] = views::VIEW_DEFAULT_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the view; a non-owner delete is a soft delete, so check
        // the view is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_view_removed('del_view workflow has removed the view');
        }
    }

    /**
     * check that the workflow test view exists in the database with the expected style, used by the
     * add write workflow to verify the confirmed step was actually persisted (mirrors
     * source_url_tests::assert_source_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test view in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param int $style_id the expected style of the test view in the database
     */
    private function assert_view_in_db(string $test_name, string $name, user $usr, int $style_id): void
    {
        $msg = new user_message();
        $msk = new view($usr);
        $msk->load_by_name($name, $msg);
        $this->t->assert($test_name, $msk->name(), $name);
        $this->t->assert($test_name, $msk->get_style_id() ?? 0, $style_id);
    }

    /**
     * check that the second change_view round actually changed the description of the test view,
     * used by the change write workflow to verify the filled confirm step was persisted (mirrors
     * source_url_tests::assert_source_filled_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the name of the test view in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test view in the database
     */
    private function assert_view_filled_in_db(string $test_name, string $name, user $usr, string $description): void
    {
        $msg = new user_message();
        $msk = new view($usr);
        $msk->load_by_name($name, $msg);
        $this->t->assert($test_name, $msk->name(), $name);
        $this->t->assert($test_name, $msk->description, $description);
    }

    /**
     * check that the workflow test view has been removed from the database, used by the del write
     * workflow to verify the confirmed step was actually persisted (mirrors
     * source_url_tests::assert_source_removed); a non-owner delete only excludes the view in the
     * user sandbox, so both states count as removed
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_view_removed(string $test_name): void
    {
        $msg = new user_message();
        $msk = new view($this->t->usr1);
        $msk->load_by_name(views::TEST_ADD_NAME, $msg);
        $this->t->assert_true($test_name, ($msk->id() == 0 or $msk->is_excluded()));
    }

}
