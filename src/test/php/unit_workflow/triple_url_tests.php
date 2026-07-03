<?php

/*

    test/php/unit_workflow/triple_url_tests.php - check the url based change_triple user workflow
    -------------------------------------------

    snapshots the html of each step of the change_triple workflow; the shared run state, the
    frontend setup and the snapshot helpers live in url_test_base (see docs/llm/testing.md)

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

include_once paths::MODEL_WORD . 'triple.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'triple url->', 'url triple ');

        $this->add_triple_fail_workflow(workflows::WF_ADD_TRIPLE_FAIL_NBR);
        $this->add_triple_workflow(workflows::WF_ADD_TRIPLE_NBR);
        $this->change_triple_workflow(workflows::WF_CHANGE_TRIPLE_NBR);
        //$this->change_triple_by_name_workflow(workflows::WF_CHANGE_TRIPLE_BY_NAME_NBR);
        $this->del_triple_workflow(workflows::WF_DEL_TRIPLE_NBR);
    }

    /**
     * run the add_triple_fail workflow and snapshot the html after every user action
     *
     * the negative twin of add_triple_workflow: pressing save on the add form without a from and a to
     * phrase does not show the confirm add view but re-renders the add form with the missing-phrases
     * warning and writes nothing, so this workflow only runs read-only and has no write twin. snapshots
     * go into src/test/resources/web/html/workflow/add_triple_fail_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 11 for wf11
     * @param bool $do_it false to only render the steps (a blocked add never writes)
     */
    protected function add_triple_fail_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_triple workflow creates a new triple, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_TRIPLE_FAIL, triple_names::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty triple
        $url_arr = test_triples::triple_new_url();
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add triple form
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_ADD_ID);

        // mark the failing input variant (the from and to phrases are missing) in the snapshot file name
        $this->step_path .= workflows::NAME_SEP . workflows::STEP_NO_PHRASES;

        // save: press save without a from and a to phrase; the confirm add view is not shown, the add
        // form is rendered again with the warning (no write)
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_ADD_ID);

        // the missing from and to phrases are reported as a warning instead of confirming the new triple
        $test_name = $this->step_path . workflows::NAME_SEP . 'warns_no_phrases';
        $this->t->assert_true($test_name, $this->usr_msg->has_msg_id(msg_id::TRIPLE_PHRASES_MISSING));
    }

    /**
     * run the add_triple edit workflow and snapshot the html after every user action, mirroring
     * add_word_workflow: the back and cancel excursions abort the add without writing, then the add is
     * redone and the final confirm would add the triple (do_it false here, so nothing is written).
     * snapshots go into src/test/resources/web/html/workflow/add_triple_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 4 for wf4
     */
    protected function add_triple_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_triple workflow creates a new triple, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_TRIPLE, triple_names::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty triple
        $url_arr = test_triples::triple_new_url();

        $this->wf_id = 0;
        $this->wf_fixed_id = triple_names::SYSTEM_TEST_ADD_ID;

        // the new triple fields posted by the add form on save and shown again in the confirm add view
        $t_trp = new test_triples($this->t);
        $add = $t_trp->add_url_array();

        // edit: open the empty add triple form
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new triple
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_ADD_ID);

        // user is typing the new word description
        $url_arr[url_var::NAME] = triple_names::SYSTEM_TEST_ADD;

        // save: press save on the add form which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_ADD_ID);

        // cancel: discard the new triple in the confirm view and return to the start view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::START_ID);

        // edit: re-open the add form to redo the new triple
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_ADD_ID);

        // user is typing the new word description
        $url_arr[url_var::NAME] = triple_names::SYSTEM_TEST_ADD;

        // save: press save again which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_ADD_ID);

        // add_confirmed: confirm the new triple so it would be added (do_it false here, so nothing is
        // written); the confirm form carries the '9'-prefixed back target = the triple view (no id yet)
        // TODO Prio 0 activate
        //$this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the triple, so check it is now in the database; the reserved
        // 'System Test Triple' is added as the system base, so read it as the system user (a stale usr1
        // sandbox overlay from a previous run must not mask the freshly written base value)
        if ($do_it) {
            // TODO Prio 0 activate
            //$this->assert_triple_in_db('add_triple workflow has written the triple', triple_names::SYSTEM_TEST_ADD, $this->t->usr_system, triple_names::SYSTEM_TEST_ADD_COM);
        }
    }

    /**
     * run a change_triple workflow where the from and to phrases are posted as the phrase names the
     * datalist edit fields submit (e.g. 'Zurich'), checking that the save resolves the names to phrases
     * and renders the confirm view instead of crashing; this guards the regression where the submitted
     * names were passed to the int-typed set_from_by_id (see web/word/triple.php::phrase_from_name) and
     * the empty weight was assigned to the typed ?float property. snapshots go into
     * src/test/resources/web/html/workflow/change_triple_by_name_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 8 for wf8
     * @param bool $do_it false to only render the steps (the regression guard does not need a write)
     */
    protected function change_triple_by_name_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the workflow runs on the seeded 'mathematical constant' triple; resolve its db id by name and
        // set the fixed snapshot id so the snapshot does not depend on the assigned id
        $trp = new triple($this->t->usr1);
        $this->wf_id = $trp->load_by_name(triple_names::MATH_CONST);
        $this->wf_fixed_id = triple_names::MATH_CONST_ID;
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_TRIPLE_BY_NAME, $do_it);

        // the pending change posts the from and to phrases as names (as the datalist fields do) and an
        // empty weight, so the save must resolve the names and keep the weight null instead of crashing
        $t_trp = new test_triples($this->t);
        $change = $t_trp->change_description_url_array($this->wf_id);

        // show: display the test triple in its default triple view
        $this->assert_step(workflows::SHOW, $url_arr, views::TRIPLE_ID);

        // edit: open the triple edit view
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_EDIT_ID);

        // save: press save with the from/to posted by name which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_EDIT_ID, $change);

        // confirmed: confirm the pending change so it would be written (do_it false here, so nothing is
        // written); the from/to names must still resolve so the confirm form carries the resolved change
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);
    }

    /**
     * run the change_triple edit workflow and snapshot the html after every user action
     *
     * the workflow runs on the seeded 'mathematical constant' triple with do_it false, so no db row
     * is written; it snapshots into src/test/resources/web/html/workflow/change_triple_wf<nbr>/, the
     * file name built from the cumulative actions (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 5 for wf5
     */
    protected function change_triple_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_TRIPLE, triple_names::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $trp = new triple($this->t->usr1);
        $this->wf_id = $trp->load_by_name(triple_names::SYSTEM_TEST_ADD);
        // in a read-only run the add workflow has not written the triple, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = triple_names::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = triple_names::SYSTEM_TEST_ADD_ID;

        // initial url with the added triple; the url carries the current db id of the triple so the
        // rendered buttons and the confirmed write target the real row (the snapshot files normalize
        // the id back to the fixed test id)
        $url_arr = test_triples::triple_add_url();
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::TRIPLE_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test triple in its default triple view
        $this->assert_step(workflows::SHOW, $url_arr, views::TRIPLE_ID);

        // edit: open the triple edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the triple view
        $this->assert_button_url($html, views::TRIPLE_ID, $this->step_path);

        // back: leave the edit view without a change and return to the triple view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::TRIPLE_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_EDIT_ID);

        // user is typing the new word description
        $url_arr[url_var::DESCRIPTION] = triple_names::SYSTEM_TEST_ADD_COM;

        // save: press save on the edit form which shows the confirm change view
        $html = $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_EDIT_ID);

        // the next cancel step presses this confirm view's cancel button, so it must point to the triple view
        $this->assert_button_url($html, views::TRIPLE_ID, $this->step_path);

        // TODO Prio 1 undo of the user typing should be done by the process not the test
        $url_arr[url_var::DESCRIPTION] = '';

        // cancel: discard the pending change in the confirm view and return to the triple view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::TRIPLE_ID);

        // edit: re-open the edit view to redo the change
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_EDIT_ID);

        // user is typing the new word description
        $url_arr[url_var::DESCRIPTION] = triple_names::SYSTEM_TEST_ADD_COM;

        // save: press save again which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_EDIT_ID);

        // confirmed: confirm the pending change so it would be written to the database (do_it false
        // here, so nothing is actually written); the confirm form carries the '9'-prefixed back target
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new description in the database;
        // the change is a usr1 user sandbox overlay on top of the system base, so read it as usr1
        if ($do_it) {
            // TODO Prio 0 activate
            //$this->assert_triple_in_db('change_triple workflow has changed the triple', triple_names::SYSTEM_TEST_ADD, $this->t->usr1, triple_names::SYSTEM_TEST_ADD_COM);
        }

        // the second round fills every still-missing field of the triple (the weight and phrase type)
        $t_trp = new test_triples($this->t);
        $fill = $t_trp->fill_url_array($this->wf_id);
        $url_arr = $url_arr + $fill;

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_EDIT_ID);

        // fill: press save on the edit form with every field filled which shows the confirm change view
        $this->assert_step(workflows::FILL, $url_arr, views::TRIPLE_EDIT_ID);

        // confirmed: confirm the filled change (do_it false here, so nothing is actually written)
        // TODO Prio 0 activate
        //$this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the filled fields, so check a previously empty field (the weight) is
        // now set in the database; the change is a usr1 user sandbox overlay, so read it as usr1
        if ($do_it) {
            // TODO Prio 0 activate
            //$this->assert_triple_filled_in_db('change_triple workflow has filled the triple', triple_names::SYSTEM_TEST_ADD, $this->t->usr1);
        }
    }

    /**
     * run the del_triple edit workflow and snapshot the html after every user action, mirroring
     * del_word_workflow: the back and cancel excursions abort the deletion without writing, then the
     * deletion is redone and the final confirm would remove the triple (do_it false here, so nothing is
     * written). snapshots go into src/test/resources/web/html/workflow/del_triple_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 6 for wf6
     */
    protected function del_triple_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the del_triple workflow runs on the reserved 'System Test Triple'; resolve its current db id
        // by name and set the fixed snapshot id so the snapshot does not depend on the assigned id
        $this->wf_start($wf_nbr, workflows::WF_DEL_TRIPLE, triple_names::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with the added triple
        $url_arr = test_triples::triple_add_url();
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        /*
        $msg = new user_message();
        $trp = new triple($this->t->usr1);
        $this->wf_id = $trp->load_by_name(triple_names::SYSTEM_TEST_ADD);
        $this->wf_fixed_id = triple_names::SYSTEM_TEST_ADD_ID;
        $trp->reload_objects($msg);
        $trp_ui = new triple_ui($trp->api_json());
        $url_arr = $trp_ui->to_url_array();
        */

        // show: display the test triple in its default triple view
        $this->assert_step(workflows::SHOW, $url_arr, views::TRIPLE_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_DEL_ID);

        // back: leave the delete form without deleting and return to the triple view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::TRIPLE_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the triple view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::TRIPLE_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::TRIPLE_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_DEL_ID);

        // del_confirmed: confirm the deletion so the triple is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed back
        // target = the triple view + id (as the real confirm form does), otherwise dbo_for_url falls back
        // to the default word object and the delete would target a word instead of this triple
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the triple; a non-owner delete is a soft delete, so check the
        // triple is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_triple_removed('del_triple workflow has removed the triple');
        }
    }

    /**
     * check that the workflow test triple exists in the database with the expected description, used by
     * the add and change write workflows to verify the confirmed step was actually persisted
     *
     * 'System Test Triple' is a reserved name, so the add workflow writes the system (base) version owned
     * by the system user, while a later change by the (non-owner) frontend user usr1 only writes a usr1
     * user sandbox overlay on top of that base. the verifying read must therefore use the same user the
     * workflow wrote with: the system user for the base value added, usr1 for the sandbox value changed.
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test triple in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test triple in the database
     */
    private function assert_triple_in_db(string $test_name, string $name, user $usr, string $description): void
    {
        $trp = new triple($usr);
        $trp->load_by_name($name);
        $this->t->assert($test_name, $trp->name(), $name);
        $this->t->assert($test_name, $trp->description, $description);
    }

    /**
     * check that the second change_triple round actually filled the previously empty fields of the test
     * triple, used by the change write workflow to verify the filled confirm step was persisted. the fill
     * is a usr1 user sandbox overlay on top of the system base, so the weight is read as usr1.
     *
     * @param string $test_name the description of the assertion
     * @param string $name the expected name of the test triple in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     */
    private function assert_triple_filled_in_db(string $test_name, string $name, user $usr): void
    {
        $trp = new triple($usr);
        $trp->load_by_name($name);
        $this->t->assert($test_name, $trp->name(), $name);
        $this->t->assert_true($test_name, $trp->weight == 1);
    }

    /**
     * check that the workflow test triple has been removed for usr1, used by the delete write workflow to
     * verify the confirmed deletion was persisted. deleting the system owned 'System Test Triple' lands in
     * one of two valid states depending on the triple's accumulated user sandbox usage: if no other user
     * uses it the row is hard deleted and gone (the expected case), if another user still uses it the
     * delete is a soft delete that keeps the row but flags it excluded for usr1. both mean the triple is no
     * longer an active triple for usr1, so accept either a missing row or the excluded flag - otherwise the
     * test is fragile against the shared test database state left by previous runs.
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_triple_removed(string $test_name): void
    {
        $trp = new triple($this->t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD);
        $this->t->assert_true($test_name, $trp->id() == 0 || $trp->is_excluded());
    }

}