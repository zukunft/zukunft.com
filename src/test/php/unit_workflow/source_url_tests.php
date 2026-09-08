<?php

/*

    test/php/unit_workflow/source_url_tests.php - check the url based source user workflows
    -------------------------------------------

    snapshots the html of each step of the add_source workflow; the shared run state, the frontend
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

include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::HTML . 'html_base.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_sources.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class source_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'source url->', 'url source ');

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
        $this->add_source_workflow(workflows::WF_ADD_SOURCE_NBR);
        $this->change_source_workflow(workflows::WF_CHANGE_SOURCE_NBR);
        $this->del_source_workflow(workflows::WF_DEL_SOURCE_NBR);
    }

    /**
     * run the add_source workflow and snapshot the html after every user action, mirroring
     * add_word_workflow: the back excursion aborts the add without writing, then the source is
     * entered again and the final confirm adds it (do_it false here, so nothing is written).
     * the word workflows already cover the cancel excursion of an add, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/add_source_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 19 for wf19
     * @param bool $do_it false to only render the steps, true to also write the new source
     */
    protected function add_source_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_source workflow creates a new source, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_SOURCE, $this->t->usr1, sources::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty source
        $url_arr = test_sources::source_new_url($this->msg);

        $this->wf_id = 0;
        $this->wf_fixed_id = sources::SYSTEM_TEST_ADD_ID;

        // the new source fields posted by the add form on save and shown again in the confirm add view
        $t_src = new test_sources($this->t);
        $add = $t_src->add_url_array();

        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add source form
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new source
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_ADD_ID);

        // user is entering the new source: the name, the type, the url, the doi and the description
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::SOURCE_ADD_ID);

        // confirmed: confirm the new source so it is actually added (with do_it true); the confirm form
        // posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::SOURCE_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the source, so check it is now in the database
        if ($do_it) {
            $this->assert_source_in_db('add_source workflow has written the source',
                sources::SYSTEM_TEST_ADD, $this->t->usr1, sources::SYSTEM_TEST_ADD_URL);
        }
    }

    /**
     * run the change_source edit workflow and snapshot the html after every user action, mirroring
     * change_word_workflow: the back excursion aborts the change without writing, then the url is
     * changed and the confirm writes it; a second round then changes the description and fills the
     * default view that the add form leaves unset, and confirms again. the word workflows already
     * cover the cancel excursion of a change, so this workflow keeps only the back step (see
     * docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/change_source_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 20 for wf20
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_source_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the change_source workflow runs on the 'System Test Source' (added by the add_source
        // workflow of a write run), never on real data; resolve its current database id by name
        // and set the fixed snapshot id of the test source
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_SOURCE, $this->t->usr1, sources::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $src = new source($this->t->usr1);
        $this->wf_id = $src->load_by_name(sources::SYSTEM_TEST_ADD, $msg);
        // in a read-only run the add workflow has not written the source, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = sources::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = sources::SYSTEM_TEST_ADD_ID;

        // initial url with the added source; the url carries the current db id of the source so the
        // rendered buttons and the confirmed write target the real row (the snapshot files normalize
        // the id back to the fixed test id)
        $t_src = new test_sources($this->t);
        $url_arr = $t_src->source_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::SOURCE_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test source in its default source view
        $this->assert_step(workflows::SHOW, $url_arr, views::SOURCE_ID);

        // edit: open the source edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the source view
        $this->assert_button_url($html, views::SOURCE_ID, $this->step_path);

        // back: leave the edit view without a change and return to the source view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::SOURCE_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_EDIT_ID);

        // user is typing the new source url
        $url_arr[url_var::URL] = sources::TEST_URL_CHANGED;

        // save: press save on the edit form which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::SOURCE_EDIT_ID);

        // confirmed: confirm the pending change so it is actually written to the database (with do_it
        // true); the confirm form posts the confirm update mask, because url_to_action only routes a
        // confirmed change of an edit mask to the database write (see views::EDIT_MASKS_IDS)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new url in the database;
        // usr1 owns the source added by the add_source workflow (see url_test_base::init),
        // so the change is written to the usr1 standard row and is read back as usr1
        if ($do_it) {
            $this->assert_source_in_db('change_source workflow has changed the source',
                sources::SYSTEM_TEST_ADD, $this->t->usr1, sources::TEST_URL_CHANGED);
        }

        // the second round changes the description and fills the default view that the add form
        // leaves unset; the fill url carries the refreshed '8' opening values (the changed url is now
        // the saved state), so its keys win the union and the confirm shows only the new fields
        $fill = $t_src->fill_url_array($this->wf_id);
        $url_arr = $fill + $url_arr;

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_EDIT_ID);

        // fill: press save on the edit form with every field filled which shows the confirm change view;
        // unlike the single-field save above the confirm view now shows every changed field
        $this->assert_step(workflows::FILL, $url_arr, views::SOURCE_EDIT_ID);

        // confirmed: confirm the filled change so it is also written to the database (with do_it true)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the filled fields, so check the previously unset default view is
        // now set in the database; the source page view is the one the fill url selects
        if ($do_it) {
            $this->assert_source_filled_in_db('change_source workflow has filled the source',
                sources::SYSTEM_TEST_ADD, $this->t->usr1, views::SOURCE_ID);
        }
    }

    /**
     * run the del_source workflow and snapshot the html after every user action, mirroring
     * del_word_workflow: the back excursion leaves the delete form and the cancel excursion
     * discards the deletion in the confirm view, both without writing, and only the final
     * confirmed step removes the source. snapshots go into
     * src/test/resources/web/html/workflow/del_source_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 21 for wf21
     * @param bool $do_it false to only render the steps, true to also delete the source
     */
    protected function del_source_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the del_source workflow runs on the 'System Test Source' (added by the add_source workflow
        // of a write run); resolve its current database id by name and set the fixed snapshot id
        $this->wf_start($wf_nbr, workflows::WF_DEL_SOURCE, $this->t->usr1, sources::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $src = new source($this->t->usr1);
        $this->wf_id = $src->load_by_name(sources::SYSTEM_TEST_ADD, $msg);
        // in a read-only run the add workflow has not written the source, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = sources::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = sources::SYSTEM_TEST_ADD_ID;

        // initial url with the added source; the url carries the current db id of the source so the
        // confirmed delete targets the real row (the snapshot files normalize the id back to the
        // fixed test id)
        $t_src = new test_sources($this->t);
        $url_arr = $t_src->source_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // show: display the test source in its default source view
        $this->assert_step(workflows::SHOW, $url_arr, views::SOURCE_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_DEL_ID);

        // back: leave the delete form without deleting and return to the source view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::SOURCE_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::SOURCE_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the source view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::SOURCE_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::SOURCE_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::SOURCE_DEL_ID);

        // confirmed: confirm the deletion so the source is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed
        // back target = the source view + id (as the real confirm form does), otherwise dbo_for_url
        // falls back to the default word object and the delete would target a word
        $url_arr[url_var::BACK . url_var::MASK] = views::SOURCE_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the source; a non-owner delete is a soft delete, so check
        // the source is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_source_removed('del_source workflow has removed the source');
        }
    }

    /**
     * check that the workflow test source exists in the database with the expected url, used by the
     * add write workflow to verify the confirmed step was actually persisted (mirrors
     * formula_url_tests::assert_formula_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test source in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $url the expected url of the test source in the database
     */
    private function assert_source_in_db(string $test_name, string $name, user $usr, string $url): void
    {
        $msg = new user_message();
        $src = new source($usr);
        $src->load_by_name($name, $msg);
        $this->t->assert($test_name, $src->name(), $name);
        $this->t->assert($test_name, $src->url, $url);
    }

    /**
     * check that the second change_source round actually filled the default view of the test source,
     * used by the change write workflow to verify the filled confirm step was persisted (mirrors
     * word_url_tests::assert_word_filled_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the name of the test source in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param int $view_id the expected default view id of the test source in the database
     */
    private function assert_source_filled_in_db(string $test_name, string $name, user $usr, int $view_id): void
    {
        $msg = new user_message();
        $src = new source($usr);
        $src->load_by_name($name, $msg);
        $this->t->assert($test_name, $src->name(), $name);
        $this->t->assert($test_name, $src->view?->id() ?? 0, $view_id);
    }

    /**
     * check that the workflow test source has been removed from the database, used by the del write
     * workflow to verify the confirmed step was actually persisted (mirrors
     * word_url_tests::assert_word_removed); a non-owner delete only excludes the source in the user
     * sandbox, so both states count as removed
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_source_removed(string $test_name): void
    {
        $msg = new user_message();
        $src = new source($this->t->usr1);
        $src->load_by_name(sources::SYSTEM_TEST_ADD, $msg);
        $this->t->assert_true($test_name, $src->id() == 0 || $src->is_excluded());
    }

}
