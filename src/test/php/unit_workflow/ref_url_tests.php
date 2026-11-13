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

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_IMPORT . 'import_wikidata_preview.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'ref_link_wikipedia.php';
include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_CONST_FIELDS . 'ref_fields.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SYSTEM . 'wikidata_import.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_refs.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata_preview;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_link_wikipedia;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\fields\ref_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\wikidata_import;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_url_tests extends url_test_base
{

    // a part of the lead text of the live wikipedia page refs::GENEVA_WIKIPEDIA_KEY that stays stable
    const string WIKIPEDIA_DESCRIPTION_PART = 'Geneva';
    // the start of a pronunciation e.g. the IPA "[ʒənɛv]" that must not be part of the description
    const string WIKIPEDIA_PRONUNCIATION_PART = '[';
    // the description of the import json that the live wikipedia page of a refresh workflow gives
    const string WIKIPEDIA_IMPORT_COM = 'the description and the wikidata reference that the refresh takes from the wikipedia page ';
    // stands for the time of the live wikipedia page in the expected message, which is unknown upfront
    const string WIKIPEDIA_TIME_MARK = 'System Test Wikipedia Time';
    // the row of the reference that a typed confirm url tries to delete
    const int TYPED_ROW = 1;
    // the description and the reference rows that the ref_rows workflow confirms on Zurich (canton)
    const string REF_ROWS_DESCRIPTION = 'System Test Description of the Reference Rows';
    const int ROW_CHANGE = 1;
    const int ROW_DELETE = 2;
    const int ROW_ADD = 3;
    // the cases of the ref_row_confirm workflow, each with and without a change of the description
    const array ROW_CONFIRM_CASES = [
        'add' => url_var::CRUD_CREATE,
        'change' => url_var::CRUD_UPDATE,
        'del' => url_var::CRUD_DELETE,
    ];
    const string CASE_DESCRIPTION = 'description';
    const int CASE_ROW = 1;
    // the snapshot set of the ref_rows_form save without a change
    const string CASE_UNCHANGED = 'unchanged';

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
        $this->ref_row_confirm_workflow(workflows::WF_REF_ROW_CONFIRM_NBR);
        $this->ref_rows_form_workflow(workflows::WF_REF_ROWS_FORM_NBR);
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
     * of Bern (city), which has no wikipedia reference, shows its page again, then the user presses
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

        $test_name = 'a triple without wikipedia reference shows its page again';
        $url = $this->ui->url_with_wikipedia_refresh($no_ref_url, $ui_msg, false);
        $mask = $url[url_var::MASK];
        $this->t->assert($test_name, $mask, views::TRIPLE_ID);

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

        $test_name = '... and without a reference change, because the triple has the wikidata id of the page';
        $this->t->assert($test_name, url_var::ref_rows($cnf_url), []);

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

        // refresh again: the triple is now in line with the wikipedia page, so nothing is to confirm
        $test_name = 'a second refresh shows the page of the triple again';
        $in_line_url = $this->ui->url_with_wikipedia_refresh($edit_url, $ui_msg, false);
        $mask = $in_line_url[url_var::MASK];
        $this->t->assert($test_name, $mask, views::TRIPLE_ID);

        // the time of the live page is unknown upfront, so the message is checked by the text before it
        $test_name = '... with the message that no update is needed since the time of the page';
        $msg_txt = $ui_msg->text();
        $in_line_start = $this->in_line_message_start(triple_names::CITY_GE);
        $this->t->assert_text_contains($test_name, $msg_txt, $in_line_start);
        $ui_msg->reset();

        // negative: a typed confirm url cannot delete the reference of another phrase
        $test_name = 'a reference row of another phrase is refused with a message';
        $other_row = [url_var::ACTION => url_var::CRUD_DELETE, url_var::REF => refs::MATH_ID];
        $other_url = array_merge($cnf_url, url_var::ref_row_url_array([self::TYPED_ROW => $other_row]));
        $this->ui->url_to_action($other_url, $this->req->usr_backend, $ui_msg, $this->req->dto, true);
        $this->t->assert_text_contains($test_name, $ui_msg->var_message_text(), triple_names::CITY_GE);
        $ui_msg->reset();

        $test_name = '... and the reference of the other phrase is kept';
        $ref = new ref($this->t->usr1);
        $ref->load_by_id(refs::MATH_ID, $msg);
        $this->t->assert_false($test_name, $ref->is_excluded());
        $this->t->assert_msg($test_name, $msg);
        $msg->reset();
    }

    /**
     * @param string $name the name of the phrase that is in line with its wikipedia page
     * @return string the message of a refresh without update until the time of the page, which the live page sets
     */
    private function in_line_message_start(string $name): string
    {
        global $mtr;
        $vars = [msg_id::VAR_NAME => $name, msg_id::VAR_TIME => self::WIKIPEDIA_TIME_MARK];
        $in_line_txt = new library()->msg_var_text([[msg_id::WIKIPEDIA_IN_LINE, $vars]], $mtr);
        // the complete text if the message names no time, which then fails the assert
        return explode(self::WIKIPEDIA_TIME_MARK, $in_line_txt)[0];
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
            // else the change log tab of the snapshot shows the overlay of every earlier run
            $this->t->cleanup_change_log_of_user(triple::class, triple_names::CITY_GE_ID, $this->t->usr1);
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
     * read the live wikipedia page of each wikipedia refresh workflow and compare the description and
     * the wikidata id that the refresh takes from it with the import json of the last run, so that a
     * change of the live page is reported as such and not only as a failed assert of a workflow
     */
    protected function wikipedia_page_tests(): void
    {
        $this->t->subheader($this->ts . 'wikipedia pages of the refresh workflows');
        $ge_trp = [
            json_fields::NAME => triple_names::CITY_GE,
            json_fields::EX_FROM => word_names::GE,
            json_fields::EX_VERB => verbs::IS_NAME,
            json_fields::EX_TO => word_names::CITY,
        ];
        $this->assert_wikipedia_page(json_fields::TRIPLES, $ge_trp, refs::GENEVA_WIKIPEDIA_KEY,
            test_files::IMPORT_WIKIPEDIA_REFRESH_GENEVA);
        $health = [json_fields::NAME => word_names::HEALTH];
        $this->assert_wikipedia_page(json_fields::WORDS, $health, refs::HEALTH_WIKIPEDIA_KEY,
            test_files::IMPORT_WIKIPEDIA_REFRESH_HEALTH);
    }

    /**
     * @param string $list_key the import list of the phrase e.g. json_fields::WORDS
     * @param array $phr_json the import json of the phrase without the values taken from wikipedia
     * @param string $page the name of the wikipedia page e.g. "Health"
     * @param string $file the import json of the last run e.g. test_files::IMPORT_WIKIPEDIA_REFRESH_HEALTH
     */
    private function assert_wikipedia_page(string $list_key, array $phr_json, string $page, string $file): void
    {
        $msg = new user_message($this->t->usr1);
        $wik = new ref_link_wikipedia();
        $summary = $wik->summary($page, $msg);
        $phr_json[json_fields::DESCRIPTION] = $wik->description($summary);
        $phr_json[json_fields::REFS] = [
            [json_fields::NAME => $page, json_fields::TYPE_NAME => ref_types::WIKIPEDIA],
            [json_fields::NAME => $wik->wikidata_id($summary), json_fields::TYPE_NAME => ref_types::WIKIDATA],
        ];
        $import = [
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::DATA_VERSION => def::DATA_VERSION_INIT,
            json_fields::DESCRIPTION => self::WIKIPEDIA_IMPORT_COM . $page,
            $list_key => [$phr_json],
        ];
        $json = json_encode($import, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $test_name = 'the live wikipedia page ' . $page . ' gives the description and the wikidata id of the last run';
        $this->t->assert_file($test_name, $json, $file, test_files::JSON);
        $this->t->assert_msg($test_name, $msg);
    }

    /**
     * run the wikidata_refresh workflow on the word health of the base data, which has a wikipedia
     * but no wikidata reference: the refresh icon shows the confirm page with the description of the
     * wikipedia page and the add of the wikidata reference that the page names, and the confirm writes
     * both; the system user owns the word, so the description is a user overlay of usr1 and the added
     * reference a new row of usr1, which cleanup_wikidata_refresh removes again
     *
     * @param int $wf_nbr the id of the current workflow version used as the file name prefix
     */
    protected function wikidata_refresh_workflow(int $wf_nbr): void
    {
        $msg = new user_message($this->t->usr1);
        $wrd_id = word_names::HEALTH_ID;
        $this->wf_start($wf_nbr, workflows::WF_WIKIDATA_REFRESH, $this->t->usr1, $wrd_id, true);
        // the frontend reads the session user from the request cache, e.g. to block a user without login
        $this->req->dto->usr = $this->usr;
        $ui_msg = new user_message_ui($this->usr);
        $this->wf_id = $wrd_id;
        $this->wf_fixed_id = $wrd_id;
        $refresh_url = [
            url_var::MASK => views::WORD_EDIT_ID,
            url_var::ID => $wrd_id,
            url_var::REFRESH => url_var::REFRESH_WIKIPEDIA,
        ];

        // show: the word page lists the wikipedia reference with its refresh icon
        $html = $this->assert_step(workflows::SHOW, [url_var::ID => $this->wf_id], views::WORD_ID);

        $test_name = 'the wikipedia reference on the word page links to the refresh';
        $this->t->assert_text_contains($test_name, $html, url_var::REFRESH . '=' . url_var::REFRESH_WIKIPEDIA);

        // refresh: the user presses the refresh icon, which shows the confirm page
        $test_name = 'the refresh of a word without wikidata reference shows the confirm page';
        $cnf_url = $this->ui->url_with_wikipedia_refresh($refresh_url, $ui_msg, false);
        $this->t->assert($test_name, $cnf_url[url_var::MASK], views::CONFIRM_EDIT_ID);

        $test_name = '... with the add of the wikidata reference that wikipedia names';
        $wd_row = url_var::ref_rows($cnf_url)[phrase_ui::REFRESH_WIKIDATA_ROW] ?? [];
        $this->t->assert($test_name, $wd_row[url_var::EXTERNAL_KEY] ?? '', refs::HEALTH_WIKIDATA_KEY);
        $test_name = '... as a new reference';
        $this->t->assert($test_name, $wd_row[url_var::ACTION] ?? '', url_var::CRUD_CREATE);

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();

        // confirmed: the user confirms the description and the wikidata reference
        $test_name = 'the confirmed refresh adds the wikidata reference';
        $cnf_url[url_var::POST_SUBMIT] = '';
        $this->ui->url_to_action($cnf_url, $this->req->usr_backend, $ui_msg, $this->req->dto, true);
        $ref_lst = new ref_list($this->t->usr1);
        $ref_lst->load_by_phr_id($wrd_id, $msg);
        $db_key = $ref_lst->get_by_type_code_id(ref_types::WIKIDATA)?->get_external_key();
        $this->t->assert($test_name, $db_key, refs::HEALTH_WIKIDATA_KEY);
        $this->t->assert_msg($test_name, $msg);
        $msg->reset();

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();
    }

    /**
     * undo the wikidata_refresh workflow on the word health: remove the description of usr1 and the
     * added wikidata reference, so that the standard rows of the base data have never been changed
     */
    protected function cleanup_wikidata_refresh(): void
    {
        $msg = new user_message($this->t->usr1);
        $wrd = new word($this->t->usr1);
        $wrd->load_by_id(word_names::HEALTH_ID, $msg);
        if ($wrd->has_usr_cfg()) {
            $wrd->del_usr_cfg($msg);
            // else the change log tab of the snapshot shows the overlay of every earlier run
            $this->t->cleanup_change_log_of_user(word::class, word_names::HEALTH_ID, $this->t->usr1);
        }
        $this->t->cleanup_ref_rows_of($wrd->phrase(), refs::HEALTH_WIKIDATA_KEY, $msg);
        $test_name = 'the cleanup of the wikidata refresh has removed the changes of usr1 on health';
        $this->t->assert_msg($test_name, $msg);
    }

    /**
     * run the wikidata_import workflow on the triple Zurich (city) of the base data, which has the wikidata
     * reference Q72: the refresh icon of the reference shows the import preview of the values of wikidata, and
     * the confirm writes them as usr1 with the source wikidata; the backend reads the live wikidata entity, so
     * this workflow runs only in the write tests and only the triple page before the refresh is a snapshot
     * (see workflows::WF_WIKIDATA_IMPORT), and cleanup_wikidata_import removes the import again
     *
     * @param int $wf_nbr the id of the current workflow version used as the file name prefix
     */
    protected function wikidata_import_workflow(int $wf_nbr): void
    {
        global $mtr;
        $msg = new user_message($this->t->usr1);
        $trp_id = triple_names::CITY_ZH_ID;
        $this->wf_start($wf_nbr, workflows::WF_WIKIDATA_IMPORT, $this->t->usr1, $trp_id, true);
        // the frontend reads the session user from the request cache, e.g. to block a user without login
        $this->req->dto->usr = $this->usr;
        $ui_msg = new user_message_ui($this->usr);
        $this->wf_id = $trp_id;
        $this->wf_fixed_id = $trp_id;
        $refresh_url = [
            url_var::MASK => views::TRIPLE_EDIT_ID,
            url_var::ID => $trp_id,
            url_var::REFRESH => url_var::REFRESH_WIKIDATA,
        ];

        // show: the triple page lists the wikidata reference with its refresh icon
        $html = $this->assert_step(workflows::SHOW, [url_var::ID => $this->wf_id], views::TRIPLE_ID);

        $test_name = 'the wikidata reference on the triple page links to the import preview';
        $this->t->assert_text_contains($test_name, $html, url_var::REFRESH . '=' . url_var::REFRESH_WIKIDATA);

        // refresh: the user presses the refresh icon, which shows the import preview
        $test_name = 'the refresh shows the import preview';
        $pv_url = $this->ui->url_with_wikidata_refresh($refresh_url, $ui_msg, false);
        $mask = $pv_url[url_var::MASK];
        $this->t->assert($test_name, $mask, views::IMPORT_PREVIEW_ID);

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();

        $test_name = 'the import preview reads the values of the triple from wikidata';
        $imp_ui = new wikidata_import();
        $imp_ui->load($pv_url, $ui_msg);
        $pv_name = $imp_ui->preview[json_fields::NAME] ?? '';
        $this->t->assert($test_name, $pv_name, triple_names::CITY_ZH);

        // the converter adds an unknown item as a word, so that the cleanup has no triple to remove
        $test_name = '... without a new triple';
        $new_triples = $imp_ui->preview[json_fields::PREVIEW_TRIPLES_NEW] ?? [];
        $this->t->assert($test_name, $new_triples, []);

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();

        // confirmed: the user confirms the preview, whose checksum the confirm form carries
        $test_name = 'the confirmed import shows the page of the triple again';
        $import = $imp_ui->preview[json_fields::PREVIEW_IMPORT] ?? [];
        $cnf_url = $pv_url;
        $cnf_url[url_var::ACTION] = url_var::ACTION_IMPORT;
        $cnf_url[url_var::STEP] = url_var::STEP_CONFIRMED;
        $cnf_url[url_var::IMPORT_CHECKSUM] = $imp_ui->checksum();
        $cnf_url[url_var::POST_SUBMIT] = '';
        $next_url = $this->ui->url_to_action($cnf_url, $this->req->usr_backend, $ui_msg, $this->req->dto, true);
        $back_id = $next_url[url_var::ID] ?? 0;
        $this->t->assert($test_name, $back_id, $trp_id);

        $test_name = '... with the message that the values are imported';
        $this->t->assert_text_contains($test_name, $ui_msg->text(), $mtr->txt(msg_id::WIKIDATA_IMPORTED));
        $ui_msg->reset();

        $test_name = 'every imported value is in the database with the source wikidata';
        $sources = [];
        $expected = [];
        foreach ($import[json_fields::VALUES] ?? [] as $val_json) {
            $names = $val_json[json_fields::WORDS];
            $key = implode(wikidata_import::WORD_SEPARATOR, $names);
            $val = $this->value_of_names($names, $msg);
            $sources[$key] = $val?->source_name($msg);
            $expected[$key] = sources::WIKIDATA;
        }
        $this->t->assert($test_name, $sources, $expected);
        $this->t->assert_msg($test_name, $msg);
        $msg->reset();

        // the seed has no population of Zurich (city) of most years, so a preview without value is a failure
        $test_name = '... and at least one value is imported';
        $this->t->assert_greater_zero($test_name, count($sources));

        // refresh again: the values are now in line with wikidata, so nothing is to confirm
        $test_name = 'a second preview has no value to import';
        $imp_ui->load($pv_url, $ui_msg);
        $again = $imp_ui->preview[json_fields::PREVIEW_IMPORT] ?? [];
        $again_values = $again[json_fields::VALUES] ?? [];
        $this->t->assert($test_name, $again_values, []);

        $test_name = '... without a message';
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();
    }

    /**
     * undo the wikidata_import workflow on Zurich (city): the values and the words that usr1 has imported are
     * deleted and the overlays of usr1 on a value or a word of the base data are removed, found by the live convert of the
     * entity, which gives the same values as the import before, so that the base data has never been changed
     */
    protected function cleanup_wikidata_import(): void
    {
        $msg = new user_message($this->t->usr1);
        $claim_msg = new user_message($this->t->usr1); // not reported, because wikidata_write_tests compares the live claims
        $entity_json = new import_wikidata()->get_entity_json(refs::ZURICH_CITY_WIKIDATA_KEY);
        $preview = new import_wikidata_preview($this->t->usr1);
        $import = $preview->import_of_entity($entity_json, triple_names::CITY_ZH, $msg, $claim_msg);
        $deleted = [];
        foreach ($import[json_fields::VALUES] ?? [] as $val_json) {
            $names = $val_json[json_fields::WORDS];
            // a value that is kept apart from an own value of the user by the word "wikidata" (see import_wikidata_preview::conflict)
            $wd_names = array_merge($names, [import_wikidata_preview::CONFLICT_WORD]);
            $deleted = array_merge($deleted, $this->cleanup_imported_value($names, $msg));
            $deleted = array_merge($deleted, $this->cleanup_imported_value($wd_names, $msg));
        }
        // after the values, because a word that a value uses cannot be deleted
        $wrd_names = array_column($import[json_fields::WORDS] ?? [], json_fields::NAME);
        $wrd_names[] = import_wikidata_preview::CONFLICT_WORD;
        foreach ($wrd_names as $name) {
            $wrd = new word($this->t->usr1);
            $wrd->load_by_name((string)$name, $msg);
            // a word of the base data is owned by the system user
            if ($wrd->id() != 0 and $wrd->owner_id() == $this->t->usr1->id()) {
                $this->t->cleanup_leftover_refs($wrd->phrase(), $msg);
                $wrd->del($msg);
                $deleted[] = $wrd;
            } elseif ($wrd->has_usr_cfg()) {
                // e.g. the type time that an earlier import has given the year and postal code "1950" of the base data
                $wrd->del_usr_cfg($msg);
                $this->t->cleanup_change_log_of_user(word::class, $wrd->id(), $this->t->usr1);
            }
        }
        $this->t->cleanup_change_log_of_deleted($deleted);
        $test_name = 'the cleanup of the wikidata import has removed the values and the words of usr1';
        $this->t->assert_msg($test_name, $msg);
    }

    /**
     * @param array $names the phrase names of a value of the wikidata import e.g. ["population", "Zurich (city)", "2021"]
     * @param user_message $msg to collect the problems of the delete
     * @return array the value if usr1 has added it and it is deleted now, or none for a value of the base data,
     *               whose overlay of usr1 is removed
     */
    private function cleanup_imported_value(array $names, user_message $msg): array
    {
        $result = [];
        $val = $this->value_of_names($names, $msg);
        if ($val?->owner_id() == $this->t->usr1->id()) {
            $this->t->del_value($val, $msg);
            $result[] = $val;
        } elseif ($val?->has_usr_cfg()) {
            $val->del_usr_cfg($msg);
            // else the change log of the value of the base data shows the overlay of every earlier run
            $this->t->cleanup_change_log_of_user(value::class, $val->id(), $this->t->usr1);
        }
        return $result;
    }

    /**
     * @param array $names the phrase names of a value e.g. ["population", "Zurich (city)", "2021"]
     * @param user_message $msg to collect the load warnings
     * @return value|null the value of usr1 with exactly these phrases or null if a phrase or the value does not exist
     */
    private function value_of_names(array $names, user_message $msg): ?value
    {
        $result = null;
        $phr_lst = new phrase_list($this->t->usr1);
        $phr_lst->load_by_names($names, $msg);
        // without a missing phrase, because the group of the other phrases names another value
        if ($phr_lst->count() == count($names)) {
            $val = new value($this->t->usr1);
            if ($val->load_by_grp($phr_lst->get_grp_id(), $msg)) {
                $result = $val;
            }
        }
        return $result;
    }

    /**
     * run the ref_rows workflow on the triple Zurich (canton) of the base data: the user confirms the
     * description together with three reference rows, the change of the wikidata id, the delete of
     * the wikipedia reference and the add of a conceptnet reference; the system user owns the triple
     * and its references, so the description, the change and the delete are overlays of usr1 and only
     * the added reference is a new row, which cleanup_ref_rows removes again
     *
     * @param int $wf_nbr the id of the current workflow version used as the file name prefix
     */
    protected function ref_rows_workflow(int $wf_nbr): void
    {
        global $sys;
        $msg = new user_message($this->t->usr1);
        $trp_id = triple_names::CANTON_ZURICH_ID;
        $this->wf_start($wf_nbr, workflows::WF_REF_ROWS, $this->t->usr1, $trp_id, true);
        // the frontend reads the session user from the request cache, e.g. to block a user without login
        $this->req->dto->usr = $this->usr;
        $ui_msg = new user_message_ui($this->usr);

        // the stored triple as its edit view shows it, with its wikipedia and its wikidata reference
        $old = new triple_ui();
        $old->load_by_id_with_related($trp_id, $ui_msg, $this->usr->id());
        $new = clone $old;
        $new->description = self::REF_ROWS_DESCRIPTION;
        $edit_url = [url_var::MASK => views::TRIPLE_EDIT_ID, url_var::ID => $trp_id];
        $desc_url = $old->phrase()->confirm_url_of_refresh($edit_url, $old, $new, $ui_msg);
        $desc_url[url_var::POST_SUBMIT] = '';
        $wd_ref = $old->ref_lst?->get_by_type_code_id(ref_types::WIKIDATA);
        $wp_ref = $old->ref_lst?->get_by_type_code_id(ref_types::WIKIPEDIA);
        $rows = [
            self::ROW_CHANGE => [
                url_var::ACTION => url_var::CRUD_UPDATE,
                url_var::REF => $wd_ref?->id() ?? 0,
                url_var::EXTERNAL_KEY => refs::CHANGE_NEW_KEY,
            ],
            self::ROW_DELETE => [
                url_var::ACTION => url_var::CRUD_DELETE,
                url_var::REF => $wp_ref?->id() ?? 0,
            ],
            self::ROW_ADD => [
                url_var::ACTION => url_var::CRUD_CREATE,
                url_var::REF_TYPE => $sys->typ_lst->ref_typ->id(ref_types::CONCEPTNET),
                url_var::EXTERNAL_KEY => refs::SYSTEM_TEST_ROW_ADD,
            ],
        ];
        $pre_rows = [
            self::ROW_CHANGE => [url_var::EXTERNAL_KEY => $wd_ref?->external_key() ?? ''],
            self::ROW_DELETE => [url_var::EXTERNAL_KEY => $wp_ref?->external_key() ?? ''],
        ];
        $cnf_url = array_merge($desc_url, url_var::ref_row_url_array($rows, $pre_rows));

        // confirmed: the user confirms the description and the three reference rows at once
        $test_name = 'the confirm of a description with three reference rows is written without a message';
        $this->ui->url_to_action($cnf_url, $this->req->usr_backend, $ui_msg, $this->req->dto, true);
        $this->t->assert_msg($test_name, $ui_msg);
        $ui_msg->reset();

        $test_name = '... the description is changed';
        $db_trp = new triple($this->t->usr1);
        $db_trp->load_by_id($trp_id, $msg);
        $this->t->assert($test_name, $db_trp->get_description(), self::REF_ROWS_DESCRIPTION);

        $test_name = '... the wikidata id is changed';
        $ref_lst = new ref_list($this->t->usr1);
        $ref_lst->load_by_phr_id($db_trp->phrase()->id(), $msg);
        $db_wd_ref = $ref_lst->get_by_type_code_id(ref_types::WIKIDATA);
        $this->t->assert($test_name, $db_wd_ref?->get_external_key(), refs::CHANGE_NEW_KEY);

        $test_name = '... as an overlay of the user on the wikidata reference';
        $usr_ref = new ref($this->t->usr1);
        $usr_ref->load_by_id($wd_ref?->id() ?? 0, $msg);
        $this->t->assert_true($test_name, $usr_ref->has_usr_cfg());
        $test_name = '... and the overlay has the new wikidata id';
        $this->t->assert($test_name, $usr_ref->get_external_key(), refs::CHANGE_NEW_KEY);

        $test_name = '... only for the user, because the system user owns the reference';
        $std_lst = new ref_list($this->t->usr_system);
        $std_lst->load_by_phr_id($db_trp->phrase()->id(), $msg);
        $std_key = $std_lst->get_by_type_code_id(ref_types::WIKIDATA)?->get_external_key();
        $this->t->assert($test_name, $std_key, refs::ZURICH_CANTON_WIKIDATA_KEY);

        $test_name = '... and the change of the wikidata id is logged for the user';
        $log_txt = '';
        if ($db_wd_ref != null) {
            $log_txt = $this->t->log_last_by_field($db_wd_ref, $msg, ref_fields::FLD_EX_KEY, $db_wd_ref->id(), true, true);
        }
        $this->t->assert_text_contains($test_name, $log_txt, refs::CHANGE_NEW_KEY);

        $test_name = '... the wikipedia reference is deleted for the user';
        $db_wp_ref = $ref_lst->get_by_type_code_id(ref_types::WIKIPEDIA);
        $this->t->assert_null($test_name, $db_wp_ref?->get_external_key());

        $test_name = '... and the conceptnet reference is added';
        $db_cn_ref = $ref_lst->get_by_type_code_id(ref_types::CONCEPTNET);
        $this->t->assert($test_name, $db_cn_ref?->get_external_key(), refs::SYSTEM_TEST_ROW_ADD);
        $this->t->assert_msg($test_name, $msg);
        $msg->reset();

        // negative: the confirm never adds a second reference of a type that the phrase has already,
        // the same key as the stored one, so that a missing check changes nothing for the cleanup
        $test_name = 'a create row of a reference type that the phrase has already is refused with a message';
        $dup_row = [
            url_var::ACTION => url_var::CRUD_CREATE,
            url_var::REF_TYPE => $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA),
            url_var::EXTERNAL_KEY => refs::CHANGE_NEW_KEY,
        ];
        $dup_url = array_merge($desc_url, url_var::ref_row_url_array([self::ROW_ADD => $dup_row]));
        $this->ui->url_to_action($dup_url, $this->req->usr_backend, $ui_msg, $this->req->dto, true);
        $this->t->assert_text_contains($test_name, $ui_msg->var_message_text(), triple_names::CANTON_ZURICH);
        $ui_msg->reset();
    }

    /**
     * learn the id of the reference that a confirmed reference row has just added, because each run
     * gives it a new id, so that already the snapshot of this step shows the fixed test id
     *
     * @param string $step the user reaction action const of the step e.g. workflows::CONFIRMED
     */
    protected function after_action(string $step): void
    {
        if ($this->req->do_it and $step == workflows::CONFIRMED) {
            // the backend read needs a backend message, because $this->msg is the message of the frontend
            $msg = new user_message($this->t->usr1);
            $added_ref = new ref($this->t->usr1);
            $added_id = $added_ref->load_by_ex_key(refs::SYSTEM_TEST_ROW_ADD, $msg);
            if ($added_id > 0) {
                $this->wf_norm_ids[$added_id] = refs::SYSTEM_TEST_ROW_ADD_ID;
            }
            $test_name = 'the reference that a confirmed row may have added can be read';
            $this->t->assert_msg($test_name, $msg);
        }
    }

    /**
     * run the ref_row_confirm workflow on the triple Zurich (canton): for each case the edit view is
     * saved with one reference row, the add of a conceptnet reference, the change of the wikidata id or
     * the delete of the wikipedia reference, once with the stored and once with a new description, and
     * the confirm page is confirmed; the html of both steps is a snapshot per case, and a write run
     * checks the database after each case and undoes it, so that the cases do not depend on each other
     *
     * @param int $wf_nbr the id of the current workflow version used as the file name prefix
     * @param bool $do_it false to only render the steps, true to also write the reference rows
     */
    protected function ref_row_confirm_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $trp_id = triple_names::CANTON_ZURICH_ID;
        $this->wf_start($wf_nbr, workflows::WF_REF_ROW_CONFIRM, $this->t->usr1, $trp_id, $do_it);
        $this->wf_id = $trp_id;
        $this->wf_fixed_id = $trp_id;
        // the triple page after the confirm links the from and the to word by their name
        $trp = new test_triples($this->t)->zh_canton();
        $phr_lst = new phrase_list($this->t->usr1);
        $phr_lst->add($trp->get_from());
        $phr_lst->add($trp->get_to());
        $this->add_phrases_to_cache($phr_lst);
        $wf_path = $this->step_path;
        foreach (self::ROW_CONFIRM_CASES as $case => $action) {
            $this->ref_row_confirm_case($wf_path, $case, $action, false, $do_it);
            $this->ref_row_confirm_case($wf_path, $case, $action, true, $do_it);
        }
    }

    /**
     * @param string $wf_path the snapshot path of the workflow without a step e.g. ".../wf44"
     * @param string $case the name of the case e.g. "add"
     * @param string $action the crud action of the reference row of the case
     * @param bool $with_description true to change the description with the same confirm
     * @param bool $do_it true to write the case and check the database
     */
    private function ref_row_confirm_case(
        string $wf_path,
        string $case,
        string $action,
        bool   $with_description,
        bool   $do_it
    ): void
    {
        $case_name = $case;
        if ($with_description) {
            $case_name .= workflows::NAME_SEP . self::CASE_DESCRIPTION;
        }
        // one snapshot set per case e.g. wf44_add_description_save.html
        $this->step_path = $wf_path . workflows::NAME_SEP . $case_name;
        if ($do_it) {
            $this->cleanup_ref_rows();
        }
        $url_arr = $this->ref_row_confirm_url($action, $with_description);

        // save: the saved edit view shows the confirm page with the reference row
        $this->assert_step(workflows::SAVE, $url_arr, views::TRIPLE_EDIT_ID);

        // confirmed: the confirm writes the description and the reference row
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::CONFIRM_EDIT_ID);

        if ($do_it) {
            $this->assert_ref_row_written($case_name, $action, $with_description);
            $this->cleanup_ref_rows();
        }
    }

    /**
     * @param string $action the crud action of the reference row
     * @param bool $with_description true to change the description with the same confirm
     * @return array the url of the saved edit view of Zurich (canton) with the stored values as '8'
     *               values, the description and the reference row of the case
     */
    private function ref_row_confirm_url(string $action, bool $with_description): array
    {
        $url_arr = $this->zh_canton_save_url($with_description);
        [$fields, $pre_fields] = $this->ref_row_of_case($action);
        $row = self::CASE_ROW;
        $row_url = url_var::ref_row_url_array([$row => $fields], [$row => $pre_fields]);
        return array_merge($url_arr, $row_url);
    }

    /**
     * @param bool $with_description true to change the description with the same save
     * @return array the url of the saved edit view of Zurich (canton) without reference rows, with the
     *               stored values as '8' values and the triple page as the back target
     */
    private function zh_canton_save_url(bool $with_description): array
    {
        // the fields of the base data, because the write must not change e.g. the given name
        $trp = new test_triples($this->t)->zh_canton();
        $url_arr = [
            url_var::ID => triple_names::CANTON_ZURICH_ID,
            url_var::NAME => triple_names::CANTON_ZURICH,
            url_var::DESCRIPTION => triple_names::CANTON_ZURICH_COM,
            url_var::PHRASE_FROM => $trp->get_from()->id(),
            url_var::VERB => $trp->get_verb_id(),
            url_var::PHRASE_TO => $trp->get_to()->id(),
        ];
        $url_arr = array_merge($url_arr, html_base::pre_url_array($url_arr));
        if ($with_description) {
            $url_arr[url_var::DESCRIPTION] = self::REF_ROWS_DESCRIPTION;
        }
        $url_arr[url_var::BACK . url_var::MASK] = views::TRIPLE_ID;
        $url_arr[url_var::BACK . url_var::ID] = triple_names::CANTON_ZURICH_ID;
        return $url_arr;
    }

    /**
     * @param string $action the crud action of the reference row
     * @return array the fields of the reference row and the stored values of the reference
     */
    private function ref_row_of_case(string $action): array
    {
        global $sys;
        $wp_type = $sys->typ_lst->ref_typ->id(ref_types::WIKIPEDIA);
        $wd_type = $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA);
        return match ($action) {
            url_var::CRUD_CREATE => [[
                url_var::ACTION => url_var::CRUD_CREATE,
                url_var::REF_TYPE => $sys->typ_lst->ref_typ->id(ref_types::CONCEPTNET),
                url_var::EXTERNAL_KEY => refs::SYSTEM_TEST_ROW_ADD,
            ], []],
            url_var::CRUD_UPDATE => [[
                url_var::ACTION => url_var::CRUD_UPDATE,
                url_var::REF => refs::ZURICH_CANTON_WIKIDATA_ID,
                url_var::REF_TYPE => $wd_type,
                url_var::EXTERNAL_KEY => refs::CHANGE_NEW_KEY,
            ], [
                url_var::REF_TYPE => $wd_type,
                url_var::EXTERNAL_KEY => refs::ZURICH_CANTON_WIKIDATA_KEY,
            ]],
            default => [[
                url_var::ACTION => url_var::CRUD_DELETE,
                url_var::REF => refs::ZURICH_CANTON_WIKIPEDIA_ID,
            ], [
                url_var::REF_TYPE => $wp_type,
                url_var::EXTERNAL_KEY => refs::ZURICH_CANTON_WIKIPEDIA_KEY,
            ]],
        };
    }

    /**
     * check that a write run of a ref_row_confirm case has written the description and the reference row
     *
     * @param string $case_name the name of the case e.g. "del_description"
     * @param string $action the crud action of the reference row
     * @param bool $with_description true if the confirm has changed the description too
     */
    private function assert_ref_row_written(string $case_name, string $action, bool $with_description): void
    {
        $msg = new user_message($this->t->usr1);
        $test_name = $case_name . ' is confirmed without a message';
        $this->t->assert_msg($test_name, $this->msg);
        $this->msg->reset();

        $test_name = '... with the description';
        $db_trp = new triple($this->t->usr1);
        $db_trp->load_by_id(triple_names::CANTON_ZURICH_ID, $msg);
        $description = triple_names::CANTON_ZURICH_COM;
        if ($with_description) {
            $description = self::REF_ROWS_DESCRIPTION;
        }
        $this->t->assert($test_name, $db_trp->get_description(), $description);

        $test_name = '... and the reference row';
        $ref_lst = new ref_list($this->t->usr1);
        $ref_lst->load_by_phr_id($db_trp->phrase()->id(), $msg);
        [$type, $key] = match ($action) {
            url_var::CRUD_CREATE => [ref_types::CONCEPTNET, refs::SYSTEM_TEST_ROW_ADD],
            url_var::CRUD_UPDATE => [ref_types::WIKIDATA, refs::CHANGE_NEW_KEY],
            // a deleted reference is no longer listed for the user
            default => [ref_types::WIKIPEDIA, null],
        };
        $db_key = $ref_lst->get_by_type_code_id($type)?->get_external_key();
        if ($key == null) {
            $this->t->assert_null($test_name, $db_key);
        } else {
            $this->t->assert($test_name, $db_key, $key);
        }
        $this->t->assert_msg($test_name, $msg);
        $msg->reset();
        if ($action == url_var::CRUD_UPDATE) {
            $this->assert_ref_overlay(refs::ZURICH_CANTON_WIKIDATA_ID, refs::CHANGE_NEW_KEY,
                refs::ZURICH_CANTON_WIKIDATA_KEY);
        }
    }

    /**
     * check that the change of a reference of the system user by usr1 is a user overlay with the new
     * value and that the standard row keeps the stored value, so that a failed change tells which of
     * the two is wrong
     *
     * @param int $id the database id of the changed reference
     * @param string $usr_key the external key that usr1 has confirmed
     * @param string $std_key the external key of the standard row
     */
    private function assert_ref_overlay(int $id, string $usr_key, string $std_key): void
    {
        $msg = new user_message($this->t->usr1);
        $test_name = '... the reference ' . $id . ' has an overlay of usr1';
        $usr_ref = new ref($this->t->usr1);
        $usr_ref->load_by_id($id, $msg);
        $this->t->assert_true($test_name, $usr_ref->has_usr_cfg());
        $test_name = '... that usr1 reads by the id with the confirmed key';
        $this->t->assert($test_name, $usr_ref->get_external_key(), $usr_key);
        $test_name = '... and the standard row keeps its key';
        $std_ref = new ref($this->t->usr_system);
        $std_ref->load_standard($id, $msg);
        $this->t->assert($test_name, $std_ref->get_external_key(), $std_key);
        $this->t->assert_msg($test_name, $msg);
    }

    /**
     * run the ref_rows_form workflow on the triple Zurich (canton): the edit view shows the references as
     * rows of its form; the save with the changed key of the wikidata reference, the checked delete box of
     * the wikipedia reference and the filled add row shows the three rows on the confirm page, and a save of
     * the unchanged rows shows none; it only renders, because a write would change the change log of the
     * seed triple (see docs/llm/pending_prio_2.md)
     *
     * @param int $wf_nbr the id of the current workflow version used as the file name prefix
     */
    protected function ref_rows_form_workflow(int $wf_nbr): void
    {
        global $mtr;
        $trp_id = triple_names::CANTON_ZURICH_ID;
        $this->wf_start($wf_nbr, workflows::WF_REF_ROWS_FORM, $this->t->usr1, $trp_id);
        $this->wf_id = $trp_id;
        $this->wf_fixed_id = $trp_id;
        $trp = new test_triples($this->t)->zh_canton();
        $phr_lst = new phrase_list($this->t->usr1);
        $phr_lst->add($trp->get_from());
        $phr_lst->add($trp->get_to());
        $this->add_phrases_to_cache($phr_lst);
        // a read run renders without backend calls, so the references of the rows come from the cache;
        // the cache is shared by all workflows, so the stored list is set back at the end
        $ui_msg = new user_message_ui($this->usr);
        $cache_refs = $this->req->dto->ref_lst;
        $refs = $this->req->dto->ref_list_cloned();
        foreach (new test_refs($this->t)->ref_list_zh_canton_ui()->lst() as $ref) {
            $refs->add($ref, $ui_msg);
        }
        $this->req->dto->set_ref_list($refs);
        $wf_path = $this->step_path;
        $refs_title = $mtr->txt(msg_id::INFO_CONFIRM_REFS);

        // edit: the edit view shows a row with the delete box per reference and the add row
        $edit_url = [
            url_var::ID => $trp_id,
            url_var::BACK . url_var::MASK => views::TRIPLE_ID,
            url_var::BACK . url_var::ID => $trp_id,
        ];
        $html = $this->assert_step(workflows::EDIT, $edit_url, views::TRIPLE_EDIT_ID);
        $test_name = 'the edit view of Zurich (canton) shows the wikidata reference as a row of its form';
        $this->t->assert_text_contains($test_name, $html, refs::ZURICH_CANTON_WIKIDATA_KEY);
        $test_name = '... with the delete box';
        $this->t->assert_text_contains($test_name, $html, $mtr->txt(msg_id::FORM_FIELD_REF_DELETE));

        // save: the confirm page shows the three changing rows
        $save_url = array_merge($this->zh_canton_save_url(false), $this->ref_rows_form_rows(true));
        $html = $this->assert_step(workflows::SAVE, $save_url, views::TRIPLE_EDIT_ID);
        $test_name = 'the save of the reference rows shows them on the confirm page';
        $this->t->assert_text_contains($test_name, $html, $refs_title);
        $test_name = '... with the new key of the wikidata reference';
        $this->t->assert_text_contains($test_name, $html, refs::CHANGE_NEW_KEY);
        $test_name = '... and the key of the added reference';
        $this->t->assert_text_contains($test_name, $html, refs::SYSTEM_TEST_ROW_ADD);

        // confirmed: the confirm would write the rows (do_it false here, so nothing is written)
        $this->assert_step(workflows::CONFIRMED, $save_url, views::CONFIRM_EDIT_ID);

        // negative: the save of the unchanged rows and the empty add row shows no reference change
        $this->step_path = $wf_path . workflows::NAME_SEP . self::CASE_UNCHANGED;
        $save_url = array_merge($this->zh_canton_save_url(false), $this->ref_rows_form_rows(false));
        $html = $this->assert_step(workflows::SAVE, $save_url, views::TRIPLE_EDIT_ID);
        $test_name = 'the save of the unchanged reference rows shows no reference change';
        $this->t->assert_text_not_contains($test_name, $html, $refs_title);

        $this->req->dto->set_ref_list($cache_refs);
    }

    /**
     * @param bool $with_change true for the rows of the change, false for the rows as the edit view shows them
     * @return array the url vars of the reference rows of Zurich (canton) as the edit view posts them: the
     *               wikidata reference, the wikipedia reference and the add row, each with its '86' values
     */
    private function ref_rows_form_rows(bool $with_change): array
    {
        global $sys;
        $wd_key = $with_change ? refs::CHANGE_NEW_KEY : refs::ZURICH_CANTON_WIKIDATA_KEY;
        // the checked delete box overwrites the update action of the row
        $wp_action = $with_change ? url_var::CRUD_DELETE : url_var::CRUD_UPDATE;
        $add_key = $with_change ? refs::SYSTEM_TEST_ROW_ADD : '';
        $rows = [
            self::ROW_CHANGE => [
                url_var::ACTION => url_var::CRUD_UPDATE,
                url_var::REF => refs::ZURICH_CANTON_WIKIDATA_ID,
                url_var::EXTERNAL_KEY => $wd_key,
            ],
            self::ROW_DELETE => [
                url_var::ACTION => $wp_action,
                url_var::REF => refs::ZURICH_CANTON_WIKIPEDIA_ID,
                url_var::EXTERNAL_KEY => refs::ZURICH_CANTON_WIKIPEDIA_KEY,
            ],
            self::ROW_ADD => [
                url_var::ACTION => url_var::CRUD_CREATE,
                url_var::REF_TYPE => $sys->typ_lst->ref_typ->id(ref_types::CONCEPTNET),
                url_var::EXTERNAL_KEY => $add_key,
            ],
        ];
        $pre_rows = [
            self::ROW_CHANGE => [url_var::EXTERNAL_KEY => refs::ZURICH_CANTON_WIKIDATA_KEY],
            self::ROW_DELETE => [url_var::EXTERNAL_KEY => refs::ZURICH_CANTON_WIKIPEDIA_KEY],
        ];
        return url_var::ref_row_url_array($rows, $pre_rows);
    }

    /**
     * undo the ref_rows workflow on Zurich (canton): remove the description, the changed wikidata id
     * and the exclusion of the wikipedia reference, which are overlays of usr1, and delete the added
     * conceptnet reference, so that the standard rows of the base data have never been changed
     */
    protected function cleanup_ref_rows(): void
    {
        $msg = new user_message($this->t->usr1);
        $trp = new triple($this->t->usr1);
        $trp->load_by_id(triple_names::CANTON_ZURICH_ID, $msg);
        if ($trp->has_usr_cfg()) {
            $trp->del_usr_cfg($msg);
            // else the change log tab of the snapshot shows the overlay of every earlier run
            $this->t->cleanup_change_log_of_user(triple::class, triple_names::CANTON_ZURICH_ID, $this->t->usr1);
        }
        $this->t->cleanup_ref_rows_of($trp->phrase(), refs::SYSTEM_TEST_ROW_ADD, $msg);
        $test_name = 'the cleanup of the reference rows has removed the changes of usr1 on Zurich (canton)';
        $this->t->assert_msg($test_name, $msg);
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
