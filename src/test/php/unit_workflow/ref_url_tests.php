<?php

/*

    test/php/unit_workflow/ref_url_tests.php - check the url based reference user workflows
    ----------------------------------------

    snapshots the html of each step of the add_ref workflow; the shared run state, the frontend
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

include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::USER . 'user_message.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_refs.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_url_tests extends url_test_base
{

    // a part of the lead text of the live wikipedia page refs::GENEVA_WIKIPEDIA_KEY that stays stable
    const string WIKIPEDIA_DESCRIPTION_PART = 'Geneva';
    // the start of a pronunciation e.g. the IPA "[ʒənɛv]" that must not be part of the description
    const string WIKIPEDIA_PRONUNCIATION_PART = '[';

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'ref url->', 'url ref ');

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
        $this->add_ref_workflow(workflows::WF_ADD_REF_NBR);
        $this->change_ref_workflow(workflows::WF_CHANGE_REF_NBR);
        $this->del_ref_workflow(workflows::WF_DEL_REF_NBR);
    }

    /**
     * run the add_ref workflow and snapshot the html after every user action, mirroring
     * add_source_workflow: the back excursion aborts the add without writing, then the reference
     * is entered again and the final confirm adds it (do_it false here, so nothing is written).
     * the word workflows already cover the cancel excursion of an add, so this workflow keeps
     * only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/add_ref_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 28 for wf28
     * @param bool $do_it false to only render the steps, true to also write the new reference
     */
    protected function add_ref_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the add_ref workflow creates a new reference, so there is no object id to load yet
        $this->wf_start($wf_nbr, workflows::WF_ADD_REF, $this->t->usr1, refs::SYSTEM_TEST_ADD_ID, $do_it);

        // initial url with an empty reference
        $url_arr = test_refs::ref_new_url();

        $this->wf_id = 0;
        $this->wf_fixed_id = refs::SYSTEM_TEST_ADD_ID;

        // the new reference fields posted by the add form on save and shown again in the confirm add view
        $t_ref = new test_refs($this->t);
        $add = $t_ref->add_url_array();

        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add reference form
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the add form to enter the new reference
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_ADD_ID);

        // user is entering the new reference: the phrase, the external key, the type, the url and the description
        $url_arr = $add + $url_arr;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::REF_ADD_ID);

        // confirmed: confirm the new reference so it is actually added (with do_it true); the confirm
        // form posts the confirm add mask and the back mask carries the object type (like the url that
        // url_to_action builds on save)
        $url_arr[url_var::BACK . url_var::MASK] = views::REF_ID;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the reference, so check it is now in the database
        if ($do_it) {
            $this->assert_ref_in_db('add_ref workflow has written the reference',
                refs::SYSTEM_TEST_ADD, $this->t->usr1, refs::SYSTEM_TEST_ADD_COM);
        }
    }

    /**
     * run the change_ref edit workflow and snapshot the html after every user action, mirroring
     * change_source_workflow: the back excursion aborts the change without writing, then the url is
     * changed and the confirm writes it; a second round then also changes the description and
     * confirms again. the word workflows already cover the cancel excursion of a change, so this
     * workflow keeps only the back step (see docs/llm/pending.md). snapshots go into
     * src/test/resources/web/html/workflow/change_ref_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 29 for wf29
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_ref_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the change_ref workflow runs on the 'System Test Reference' (added by the add_ref workflow
        // of a write run), never on real data; resolve its current database id by its external key
        // and set the fixed snapshot id of the test reference
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_REF, $this->t->usr1, refs::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $ref = new ref($this->t->usr1);
        $this->wf_id = $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD, $msg);
        // in a read-only run the add workflow has not written the reference, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = refs::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = refs::SYSTEM_TEST_ADD_ID;

        // initial url with the added reference; the url carries the current db id of the reference
        // so the rendered buttons and the confirmed write target the real row (the snapshot files
        // normalize the id back to the fixed test id)
        $t_ref = new test_refs($this->t);
        $url_arr = test_refs::ref_add_url();
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::REF_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test reference in its default reference view
        $this->assert_step(workflows::SHOW, $url_arr, views::REF_ID);

        // edit: open the reference edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::REF_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the reference view
        $this->assert_button_url($html, views::REF_ID, $this->step_path);

        // back: leave the edit view without a change and return to the reference view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::REF_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_EDIT_ID);

        // user is typing the new reference url
        $url_arr[url_var::URL] = refs::TEST_URL_CHANGED;

        // save: press save on the edit form which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::REF_EDIT_ID);

        // confirmed: confirm the pending change so it is actually written to the database (with do_it
        // true); the confirm form posts the confirm update mask, because url_to_action only routes a
        // confirmed change of an edit mask to the database write (see views::EDIT_MASKS_IDS)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new url in the database;
        // usr1 owns the reference added by the add_ref workflow (see url_test_base::init),
        // so the change is written to the usr1 standard row and is read back as usr1
        if ($do_it) {
            $this->assert_ref_url_in_db('change_ref workflow has changed the reference',
                refs::SYSTEM_TEST_ADD, $this->t->usr1, refs::TEST_URL_CHANGED);
        }

        // the second round also changes the description; the fill url carries the refreshed '8'
        // opening values (the changed url is now the saved state), so its keys win the union and
        // the confirm shows only the new description
        $fill = $t_ref->fill_url_array($this->wf_id);
        $url_arr = $fill + $url_arr;

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_EDIT_ID);

        // fill: press save on the edit form with every field filled which shows the confirm change view
        $this->assert_step(workflows::FILL, $url_arr, views::REF_EDIT_ID);

        // confirmed: confirm the filled change so it is also written to the database (with do_it true)
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the filled fields, so check the new description in the database
        if ($do_it) {
            $this->assert_ref_in_db('change_ref workflow has filled the reference',
                refs::SYSTEM_TEST_ADD, $this->t->usr1, refs::TEST_DESCRIPTION_CHANGED);
        }
    }

    /**
     * run the del_ref workflow and snapshot the html after every user action, mirroring
     * del_source_workflow: the back excursion leaves the delete form and the cancel excursion
     * discards the deletion in the confirm view, both without writing, and only the final
     * confirmed step removes the reference. snapshots go into
     * src/test/resources/web/html/workflow/del_ref_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 30 for wf30
     * @param bool $do_it false to only render the steps, true to also delete the reference
     */
    protected function del_ref_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the del_ref workflow runs on the 'System Test Reference' (added by the add_ref workflow of
        // a write run); resolve its current database id by its external key and set the fixed
        // snapshot id
        $this->wf_start($wf_nbr, workflows::WF_DEL_REF, $this->t->usr1, refs::SYSTEM_TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $ref = new ref($this->t->usr1);
        $this->wf_id = $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD, $msg);
        // in a read-only run the add workflow has not written the reference, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = refs::SYSTEM_TEST_ADD_ID;
        }
        $this->wf_fixed_id = refs::SYSTEM_TEST_ADD_ID;

        // initial url with the added reference; the url carries the current db id of the reference
        // so the confirmed delete targets the real row (the snapshot files normalize the id back to
        // the fixed test id)
        $url_arr = test_refs::ref_add_url();
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // show: display the test reference in its default reference view
        $this->assert_step(workflows::SHOW, $url_arr, views::REF_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_DEL_ID);

        // back: leave the delete form without deleting; the back step follows the '9' back target of
        // the url, so it returns to the start view the user came from (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::START_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::REF_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the reference view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::REF_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::REF_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::REF_DEL_ID);

        // confirmed: confirm the deletion so the reference is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed
        // back target = the reference view + id (as the real confirm form does), otherwise dbo_for_url
        // falls back to the default word object and the delete would target a word
        $url_arr[url_var::BACK . url_var::MASK] = views::REF_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the reference; a non-owner delete is a soft delete, so
        // check the reference is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_ref_removed('del_ref workflow has removed the reference');
        }
    }

    /**
     * run the wikipedia_refresh workflow on the triple Geneva (city) of the base data: the refresh
     * of Bern (city), which has no wikipedia reference, keeps the edit view, then the user presses
     * the refresh icon of the wikipedia reference of Geneva (city) and confirms the description of
     * the wikipedia page; the backend reads the live wikipedia page, so this workflow runs only in
     * the write tests and only the triple page before the refresh is a snapshot, because the live
     * text can change (see workflows::WF_WIKIPEDIA_REFRESH); the system user owns the triple, so the
     * confirmed description is only a user overlay of usr1, which cleanup_wikipedia_refresh removes
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 42 for wf42
     */
    protected function wikipedia_refresh_workflow(int $wf_nbr): void
    {
        $msg = new user_message($this->t->usr1);
        $this->wf_start($wf_nbr, workflows::WF_WIKIPEDIA_REFRESH, $this->t->usr1,
            triple_names::CITY_GE_ID, true);
        // the frontend reads the session user from the request cache, e.g. to block a user without login
        $this->req->dto->usr = $this->usr;
        $ui_msg = new user_message_ui($this->usr);
        $this->wf_id = triple_names::CITY_GE_ID;
        $this->wf_fixed_id = triple_names::CITY_GE_ID;
        $no_ref_url = [
            url_var::MASK => views::TRIPLE_EDIT_ID,
            url_var::ID => triple_names::CITY_BE_ID,
            url_var::REFRESH => url_var::REFRESH_WIKIPEDIA,
        ];
        $edit_url = [
            url_var::MASK => views::TRIPLE_EDIT_ID,
            url_var::ID => $this->wf_id,
            url_var::REFRESH => url_var::REFRESH_WIKIPEDIA,
        ];

        $test_name = 'a triple without wikipedia reference stays in the edit view';
        $url = $this->ui->url_with_wikipedia_refresh($no_ref_url, $ui_msg, false);
        $mask = $url[url_var::MASK];
        $this->t->assert($test_name, $mask, views::TRIPLE_EDIT_ID);

        $test_name = '... and the user is told that the triple has no wikipedia reference';
        $msg_txt = $ui_msg->text();
        $this->t->assert_text_contains($test_name, $msg_txt, triple_names::CITY_BE);
        $ui_msg->reset();

        // show: the triple page lists the wikipedia reference with its refresh icon
        $html = $this->assert_step(workflows::SHOW, [url_var::ID => $this->wf_id], views::TRIPLE_ID);

        $test_name = 'the wikipedia reference on the triple page links to the refresh';
        $this->t->assert_text_contains($test_name, $html, url_var::REFRESH . '=' . url_var::REFRESH_WIKIPEDIA);

        // refresh: the user presses the refresh icon, which shows the confirm page
        $test_name = 'the refresh shows the confirm page of the edit';
        $cnf_url = $this->ui->url_with_wikipedia_refresh($edit_url, $ui_msg, false);
        $mask = $cnf_url[url_var::MASK];
        $this->t->assert($test_name, $mask, views::CONFIRM_EDIT_ID);

        $test_name = '... with the lead text of the wikipedia page as description';
        $description = $cnf_url[url_var::DESCRIPTION] ?? '';
        $this->t->assert_text_contains($test_name, $description, self::WIKIPEDIA_DESCRIPTION_PART);

        $test_name = '... but without the pronunciation';
        $this->t->assert_text_not_contains($test_name, $description, self::WIKIPEDIA_PRONUNCIATION_PART);

        $test_name = '... with the to phrase of the triple, so that the confirm can write it';
        $to_id = $cnf_url[url_var::PHRASE_TO] ?? 0;
        $this->t->assert($test_name, $to_id, word_names::CITY_ID);

        $test_name = '... and without a wikidata change, because the triple has the wikidata id of the page';
        $this->t->assert_key_not_exists($test_name, url_var::LINK . url_var::EXTERNAL_KEY, $cnf_url);

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();

        // confirmed: the user confirms the description
        $test_name = 'the confirmed refresh writes the description';
        $cnf_url[url_var::POST_SUBMIT] = '';
        $this->ui->url_to_action($cnf_url, $this->req->usr_backend, $ui_msg, $this->req->dto, true);
        $db_trp = new triple($this->t->usr1);
        $db_trp->load_by_id(triple_names::CITY_GE_ID, $msg);
        $db_description = $db_trp->get_description();
        $this->t->assert($test_name, $db_description, $description);

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();

        $test_name = '... and the wikidata reference is kept';
        $ref_lst = new ref_list($this->t->usr1);
        $ref_lst->load_by_phr_id($db_trp->phrase()->id(), $msg);
        $db_wd_ref = $ref_lst->get_by_type_code_id(ref_types::WIKIDATA);
        $db_key = $db_wd_ref?->get_external_key();
        $this->t->assert($test_name, $db_key, refs::GENEVA_KEY);
        $msg->reset();
    }

    /**
     * remove the description of Geneva (city) that the wikipedia refresh workflow has confirmed, which
     * is a user overlay of usr1, so that the standard row of the base data has never been changed
     */
    protected function cleanup_wikipedia_refresh(): void
    {
        $msg = new user_message($this->t->usr1);
        $trp = new triple($this->t->usr1);
        $trp->load_by_id(triple_names::CITY_GE_ID, $msg);
        if ($trp->has_usr_cfg()) {
            $trp->del_usr_cfg($msg);
        }
        $test_name = 'the cleanup of the wikipedia refresh has removed the description of usr1';
        $this->t->assert_msg($test_name, $msg);

        // Bern (city) has no references in the base data, so that its refresh must tell the user
        $be_msg = new user_message($this->t->usr_system);
        $be = new triple($this->t->usr_system);
        $be->load_by_id(triple_names::CITY_BE_ID, $be_msg);
        $this->t->cleanup_leftover_refs($be->phrase(), $be_msg);
        $test_name = '... and the references left on Bern (city)';
        $this->t->assert_msg($test_name, $be_msg);
    }

    /**
     * check that the workflow test reference has been removed from the database, used by the del
     * write workflow to verify the confirmed step was actually persisted (mirrors
     * source_url_tests::assert_source_removed); a non-owner delete only excludes the reference in
     * the user sandbox, so both states count as removed
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_ref_removed(string $test_name): void
    {
        $msg = new user_message();
        $ref = new ref($this->t->usr1);
        $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD, $msg);
        $this->t->assert_true($test_name, ($ref->id() == 0 or $ref->is_excluded()));
    }

    /**
     * check that the first change_ref round actually changed the url of the test reference, used
     * by the change write workflow to verify the confirmed step was persisted (mirrors
     * source_url_tests::assert_source_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param string $key the external key of the test reference in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $url the expected url of the test reference in the database
     */
    private function assert_ref_url_in_db(string $test_name, string $key, user $usr, string $url): void
    {
        $msg = new user_message();
        $ref = new ref($usr);
        $ref->load_by_ex_key($key, $msg);
        $this->t->assert($test_name, $ref->get_external_key(), $key);
        $this->t->assert($test_name, $ref->get_url(), $url);
    }

    /**
     * check that the workflow test reference exists in the database with the expected description,
     * used by the add write workflow to verify the confirmed step was actually persisted (mirrors
     * source_url_tests::assert_source_in_db); a reference has no name, so it is selected by its
     * external key
     *
     * @param string $test_name the description of the assertion
     * @param string $key the expected external key of the test reference in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test reference in the database
     */
    private function assert_ref_in_db(string $test_name, string $key, user $usr, string $description): void
    {
        $msg = new user_message();
        $ref = new ref($usr);
        $ref->load_by_ex_key($key, $msg);
        $this->t->assert($test_name, $ref->get_external_key(), $key);
        $this->t->assert($test_name, $ref->description, $description);
    }

}
