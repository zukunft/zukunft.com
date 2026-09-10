<?php

/*

    test/php/unit_workflow/component_url_tests.php - check the url based component user workflows
    ----------------------------------------------

    snapshots the html of each step of the add_component workflow; the shared run state, the frontend
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

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::HTML . 'html_base.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_components.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'component url->', 'url component ');

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
        $this->add_component_workflow(workflows::WF_ADD_COMPONENT_NBR);
        $this->change_component_workflow(workflows::WF_CHANGE_COMPONENT_NBR);
        $this->del_component_workflow(workflows::WF_DEL_COMPONENT_NBR);
    }

    /**
     * run the add_component workflow and snapshot the html after every user action, mirroring
     * add_source_workflow: the back excursion aborts the add without writing, then the component is
     * entered again and the final confirm adds it (do_it false here, so nothing is written).
     * the word workflows already cover the cancel excursion of an add, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/add_component_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 25 for wf25
     * @param bool $do_it false to only render the steps, true to also write the new component
     */
    protected function add_component_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_component workflow creates a new component, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_COMPONENT, $this->t->usr1, components::TEST_ADD_ID, $do_it);

        // initial url with an empty component
        $url_arr = test_components::component_new_url($this->msg);

        $this->wf_id = 0;
        $this->wf_fixed_id = components::TEST_ADD_ID;

        // the new component fields posted by the add form on save and shown again in the confirm add view
        $t_cmp = new test_components($this->t);
        $add = $t_cmp->add_url_array();

        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add component form
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new component
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_ADD_ID);

        // user is entering the new component: the name, the description, the type and the style
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::COMPONENT_ADD_ID);

        // confirmed: confirm the new component so it is actually added (with do_it true); the confirm
        // form posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::COMPONENT_DEFAULT_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the component, so check it is now in the database
        if ($do_it) {
            $this->assert_component_in_db('add_component workflow has written the component',
                components::TEST_ADD_NAME, $this->t->usr1, component_types::TEXT_ID);
        }
    }

    /**
     * run the change_component edit workflow and snapshot the html after every user action, mirroring
     * change_view_workflow: the back excursion aborts the change without writing, then the style is
     * changed and the confirm writes it; a second round then also changes the description and confirms
     * again. the word workflows already cover the cancel excursion of a change, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/change_component_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 26 for wf26
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_component_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the change_component workflow runs on the 'System Test View Component' (added by the
        // add_component workflow of a write run), never on real data; resolve its current database id
        // by name and set the fixed snapshot id of the test component
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_COMPONENT, $this->t->usr1, components::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $cmp = new component($this->t->usr1);
        $this->wf_id = $cmp->load_by_name(components::TEST_ADD_NAME, $msg);
        // in a read-only run the add workflow has not written the component, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = components::TEST_ADD_ID;
        }
        $this->wf_fixed_id = components::TEST_ADD_ID;

        // initial url with the added component; the url carries the current db id of the component so
        // the rendered buttons and the confirmed write target the real row (the snapshot files
        // normalize the id back to the fixed test id)
        $t_cmp = new test_components($this->t);
        $url_arr = $t_cmp->component_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::COMPONENT_DEFAULT_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test component in its default component page
        $this->assert_step(workflows::SHOW, $url_arr, views::COMPONENT_DEFAULT_ID);

        // edit: open the component edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the component page
        $this->assert_button_url($html, views::COMPONENT_DEFAULT_ID, $this->step_path);

        // back: leave the edit view without a change and return to the component page (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::COMPONENT_DEFAULT_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_EDIT_ID);

        // user is selecting the new component style
        $url_arr[url_var::STYLE] = view_styles::COL_SM_8_ID;

        // save: press save on the edit form which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::COMPONENT_EDIT_ID);

        // confirmed: confirm the pending change so it is actually written to the database (with do_it
        // true); the confirm form posts the confirm update mask, because url_to_action only routes a
        // confirmed change of an edit mask to the database write (see views::EDIT_MASKS_IDS)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new style in the database;
        // usr1 owns the component added by the add_component workflow (see url_test_base::init),
        // so the change is written to the usr1 standard row and is read back as usr1
        if ($do_it) {
            $this->assert_component_style_in_db('change_component workflow has changed the component',
                components::TEST_ADD_NAME, $this->t->usr1, view_styles::COL_SM_8_ID);
        }

        // the second round also changes the description; the fill url carries the refreshed '8'
        // opening values (the changed style is now the saved state), so its keys win the union and
        // the confirm shows only the new description
        $fill = $t_cmp->fill_url_array($this->wf_id);
        $url_arr = $fill + $url_arr;

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_EDIT_ID);

        // fill: press save on the edit form with every field filled which shows the confirm change view
        $this->assert_step(workflows::FILL, $url_arr, views::COMPONENT_EDIT_ID);

        // confirmed: confirm the filled change so it is also written to the database (with do_it true)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the filled fields, so check the new description in the database
        if ($do_it) {
            $this->assert_component_filled_in_db('change_component workflow has filled the component',
                components::TEST_ADD_NAME, $this->t->usr1, components::TEST_DESCRIPTION_CHANGED);
        }
    }

    /**
     * run the del_component workflow and snapshot the html after every user action, mirroring
     * del_view_workflow: the back excursion leaves the delete form and the cancel excursion
     * discards the deletion in the confirm view, both without writing, and only the final
     * confirmed step removes the component. snapshots go into
     * src/test/resources/web/html/workflow/del_component_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 27 for wf27
     * @param bool $do_it false to only render the steps, true to also delete the component
     */
    protected function del_component_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the del_component workflow runs on the 'System Test View Component' (added by the
        // add_component workflow of a write run); resolve its current database id by name and set
        // the fixed snapshot id
        $this->wf_start($wf_nbr, workflows::WF_DEL_COMPONENT, $this->t->usr1, components::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $cmp = new component($this->t->usr1);
        $this->wf_id = $cmp->load_by_name(components::TEST_ADD_NAME, $msg);
        // in a read-only run the add workflow has not written the component, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = components::TEST_ADD_ID;
        }
        $this->wf_fixed_id = components::TEST_ADD_ID;

        // initial url with the added component; the url carries the current db id of the component so
        // the confirmed delete targets the real row (the snapshot files normalize the id back to the
        // fixed test id)
        $t_cmp = new test_components($this->t);
        $url_arr = $t_cmp->component_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // show: display the test component in its default component page
        $this->assert_step(workflows::SHOW, $url_arr, views::COMPONENT_DEFAULT_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_DEL_ID);

        // back: leave the delete form without deleting; the back step follows the '9' back target of
        // the url, so it returns to the start view the user came from (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::COMPONENT_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the component page (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::COMPONENT_DEFAULT_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::COMPONENT_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::COMPONENT_DEL_ID);

        // confirmed: confirm the deletion so the component is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed
        // back target = the component page + id (as the real confirm form does), otherwise dbo_for_url
        // falls back to the default word object and the delete would target a word
        $url_arr[url_var::BACK . url_var::MASK] = views::COMPONENT_DEFAULT_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the component; a non-owner delete is a soft delete, so
        // check the component is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_component_removed('del_component workflow has removed the component');
        }
    }

    /**
     * check that the workflow test component exists in the database with the expected type, used by
     * the add write workflow to verify the confirmed step was actually persisted (mirrors
     * source_url_tests::assert_source_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test component in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param int $type_id the expected component type of the test component in the database
     */
    private function assert_component_in_db(string $test_name, string $name, user $usr, int $type_id): void
    {
        $msg = new user_message();
        $cmp = new component($usr);
        $cmp->load_by_name($name, $msg);
        $this->t->assert($test_name, $cmp->name(), $name);
        $this->t->assert($test_name, $cmp->type_id($msg) ?? 0, $type_id);
    }

    /**
     * check that the first change_component round actually changed the style of the test component,
     * used by the change write workflow to verify the confirmed step was persisted (mirrors
     * view_url_tests::assert_view_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the name of the test component in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param int $style_id the expected style of the test component in the database
     */
    private function assert_component_style_in_db(string $test_name, string $name, user $usr, int $style_id): void
    {
        $msg = new user_message();
        $cmp = new component($usr);
        $cmp->load_by_name($name, $msg);
        $this->t->assert($test_name, $cmp->name(), $name);
        $this->t->assert($test_name, $cmp->get_style_id() ?? 0, $style_id);
    }

    /**
     * check that the second change_component round actually changed the description of the test
     * component, used by the change write workflow to verify the filled confirm step was persisted
     * (mirrors view_url_tests::assert_view_filled_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the name of the test component in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test component in the database
     */
    private function assert_component_filled_in_db(string $test_name, string $name, user $usr, string $description): void
    {
        $msg = new user_message();
        $cmp = new component($usr);
        $cmp->load_by_name($name, $msg);
        $this->t->assert($test_name, $cmp->name(), $name);
        $this->t->assert($test_name, $cmp->description, $description);
    }

    /**
     * check that the workflow test component has been removed from the database, used by the del
     * write workflow to verify the confirmed step was actually persisted (mirrors
     * view_url_tests::assert_view_removed); a non-owner delete only excludes the component in the
     * user sandbox, so both states count as removed
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_component_removed(string $test_name): void
    {
        $msg = new user_message();
        $cmp = new component($this->t->usr1);
        $cmp->load_by_name(components::TEST_ADD_NAME, $msg);
        $this->t->assert_true($test_name, ($cmp->id() == 0 or $cmp->is_excluded()));
    }

}
