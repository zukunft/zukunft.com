<?php

/*

    test/php/unit_workflow/formula_url_tests.php - check the url based formula user workflows
    --------------------------------------------

    snapshots the html of each step of the add_formula and change_formula workflows; the shared run
    state, the frontend setup and the snapshot helpers live in url_test_base (see docs/llm/testing.md)

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
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_FORMULA . 'formula.php';
include_once test_paths::CREATE . 'test_formulas.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'formula url->', 'url formula ');

        $this->add_formula_workflow(workflows::WF_ADD_FORMULA_NBR);
        $this->change_formula_workflow(workflows::WF_CHANGE_FORMULA_NBR);
        $this->del_formula_workflow(workflows::WF_DEL_FORMULA_NBR);
    }

    /**
     * run the add_formula edit workflow and snapshot the html after every user action, mirroring
     * add_triple_workflow: the back and cancel excursions abort the add without writing, then the add is
     * redone and the final confirm would add the formula (do_it false here, so nothing is written).
     * snapshots go into src/test/resources/web/html/workflow/add_formula_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 14 for wf14
     * @param bool $do_it false to only render the steps, true to also write the new formula
     */
    protected function add_formula_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_formula workflow creates a new formula, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_FORMULA, $this->t->usr1, formula_names::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty formula
        $url_arr = test_formulas::formula_new_url();

        $this->wf_id = 0;
        $this->wf_fixed_id = formula_names::SYSTEM_TEST_ADD_ID;

        // the new formula fields posted by the add form on save and shown again in the confirm add view
        $t_frm = new test_formulas($this->t);
        $add = $t_frm->add_url_array();

        // edit: open the empty add formula form
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new formula
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_ADD_ID);

        // user is entering the new formula: the name, the expression and the description
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_ADD_ID);

        // cancel: discard the new formula in the confirm view and return to the start view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::START_ID);

        // edit: re-open the add form to redo the new formula
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_ADD_ID);

        // user is entering the new formula again
        $url_arr = $add + $url_arr;

        // save: press save again which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_ADD_ID);

        // confirmed: confirm the new formula so it is actually added (with do_it true); the confirm form
        // posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::FORMULA_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the formula, so check it is now in the database; the reserved
        // 'System Test Formula' is added as the system base, so read it as the system user (a stale usr1
        // sandbox overlay from a previous run must not mask the freshly written base value)
        if ($do_it) {
            $this->assert_formula_in_db('add_formula workflow has written the formula',
                formula_names::SYSTEM_TEST_ADD, $this->t->usr1, formula_names::SYSTEM_TEST_ADD_COM);
        }
    }

    /**
     * run the change_formula edit workflow and snapshot the html after every user action, mirroring
     * change_triple_workflow: the back and cancel excursions abort the change without writing, then the
     * change is redone and the confirm would write the changed description (do_it false here, so nothing
     * is written); a second round then fills the still-missing need-all-values flag and confirms again.
     * snapshots go into src/test/resources/web/html/workflow/change_formula_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 15 for wf15
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_formula_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the workflow runs on the reserved 'System Test Formula' added above, not on seeded data;
        // resolve its current database id by name and set the fixed snapshot id of the test formula
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_FORMULA, $this->t->usr1, formula_names::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $frm = new formula($this->t->usr1);
        $this->wf_id = $frm->load_by_name(formula_names::SYSTEM_TEST_ADD);
        // in a read-only run the add workflow has not written the formula, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = formula_names::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = formula_names::SYSTEM_TEST_ADD_ID;

        // initial url with the added formula; the url carries the current db id of the formula so the
        // rendered buttons and the confirmed write target the real row (the snapshot files normalize
        // the id back to the fixed test id)
        $t_frm = new test_formulas($this->t);
        $url_arr = $t_frm->formula_add_url();
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::FORMULA_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test formula in its default formula view
        $this->assert_step(workflows::SHOW, $url_arr, views::FORMULA_ID);

        // edit: open the formula edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the formula view
        $this->assert_button_url($html, views::FORMULA_ID, $this->step_path);

        // back: leave the edit view without a change and return to the formula view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::FORMULA_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_EDIT_ID);

        // user is typing the new formula description
        $url_arr[url_var::DESCRIPTION] = formula_names::SYSTEM_TEST_ADD_COM;

        // save: press save on the edit form which shows the confirm change view
        $html = $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_EDIT_ID);

        // the next cancel step presses this confirm view's cancel button, so it must point to the formula view
        $this->assert_button_url($html, views::FORMULA_ID, $this->step_path);

        // TODO Prio 1 undo of the user typing should be done by the process not the test
        $url_arr[url_var::DESCRIPTION] = '';

        // cancel: discard the pending change in the confirm view and return to the formula view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::FORMULA_ID);

        // edit: re-open the edit view to redo the change
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_EDIT_ID);

        // user is typing the new formula description
        $url_arr[url_var::DESCRIPTION] = formula_names::SYSTEM_TEST_ADD_COM;

        // save: press save again which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_EDIT_ID);

        // confirmed: confirm the pending change so it would be written to the database (do_it false
        // here, so nothing is actually written); the confirm form carries the '9'-prefixed back target
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new description in the database;
        // the change is a usr1 user sandbox overlay on top of the system base, so read it as usr1
        if ($do_it) {
            $this->assert_formula_in_db('change_formula workflow has changed the formula',
                formula_names::SYSTEM_TEST_ADD, $this->t->usr1, formula_names::SYSTEM_TEST_ADD_COM);
        }

        // the second round fills the still-missing need-all-values flag; the fill url carries the
        // refreshed '8' opening values (the first change is now the saved state), so its keys win
        // the union and the confirm view shows only the new flag as changed
        $fill = $t_frm->fill_url_array($this->wf_id);
        $url_arr = $fill + $url_arr;

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_EDIT_ID);

        // fill: press save on the edit form with every field filled which shows the confirm change view
        $this->assert_step(workflows::FILL, $url_arr, views::FORMULA_EDIT_ID);

        // confirmed: confirm the filled change so it is also written to the database (with do_it true)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);
    }

    /**
     * check the html pages and urls of the del_formula delete workflow
     * and check the database results if $do_it is true
     *
     * @param int $wf_nbr the test internal workflow id
     * @param bool $do_it true if the database actions should be executed and tested
     */
    protected function del_formula_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the del_formula workflow runs on the reserved 'System Test Formula'
        // resolve its current db id by name and set the fixed snapshot id so the snapshot does not depend on the assigned id
        $this->wf_start($wf_nbr, workflows::WF_DEL_FORMULA, $this->t->usr1, formula_names::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $frm = new formula($this->t->usr1);
        $this->wf_id = $frm->load_by_name(formula_names::SYSTEM_TEST_ADD);
        // in a read-only run the add workflow has not written the formula, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = formula_names::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = formula_names::SYSTEM_TEST_ADD_ID;

        // initial url with the added formula; the url carries the current db ids of the formula and of
        // its from and to words so the confirmed delete targets the real rows (the snapshot files
        // normalize the ids back to the fixed test ids)
        $t_frm = new test_formulas($this->t);
        $url_arr = $t_frm->formula_add_url();
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // show: display the test formula in its default formula view
        $this->assert_step(workflows::SHOW, $url_arr, views::FORMULA_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_DEL_ID);

        // back: leave the delete form without deleting and return to the formula view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::FORMULA_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the formula view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::FORMULA_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::FORMULA_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::FORMULA_DEL_ID);

        // del_confirmed: confirm the deletion so the formula is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed back
        // target = the formula view + id (as the real confirm form does), otherwise dbo_for_url falls back
        // to the default word object and the delete would target a word instead of this formula
        $url_arr[url_var::BACK . url_var::MASK] = views::FORMULA_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the formula; a non-owner delete is a soft delete, so check the
        // formula is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_formula_removed('del_formula workflow has removed the formula');
        }
    }

    /**
     * check that the workflow test formula exists in the database with the expected description, used by
     * the add write workflow to verify the confirmed step was actually persisted (mirrors
     * triple_url_tests::assert_triple_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test formula in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test formula in the database
     */
    private function assert_formula_in_db(string $test_name, string $name, user $usr, string $description): void
    {
        $frm = new formula($usr);
        $frm->load_by_name($name);
        $this->t->assert($test_name, $frm->name(), $name);
        $this->t->assert($test_name, $frm->description, $description);
    }

    /**
     * check that the workflow test formula has been removed for usr1
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_formula_removed(string $test_name): void
    {
        $trp = new formula($this->t->usr1);
        $trp->load_by_name(formula_names::SYSTEM_TEST_ADD);
        $this->t->assert_true($test_name, $trp->id() == 0 || $trp->is_excluded());
    }

}