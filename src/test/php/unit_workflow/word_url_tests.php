<?php

/*

    test/php/unit_workflow/word_url_tests.php - render the url based word user workflows read-only
    -----------------------------------------

    renders and snapshots the word workflows without changing the database (do_it false), split
    into the read tests (url_to_html only), the write path routing tests (url_to_action with
    do_it false) and the combined workflow snapshots; the same workflows run again with the
    confirmed steps written to the database in the write twin
    test/php/unit_write_workflow/word_write_url_tests.php


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\helper\user_request;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'word url->', 'url word ');

        // read: pure page renders via url_to_html, which never changes anything
        $this->url_to_html_tests($t);

        // write path routing: url_to_action with do_it false, so nothing is written;
        // the url_to_action tests that write are in the write twin word_write_url_tests
        $this->url_to_action_tests($t);

        // combined: the workflow snapshots that chain url_to_action and url_to_html per user action
        $this->workflow_tests($t);

    }

    /**
     * the read tests: render complete pages via url_to_html and check the html,
     * without any url_to_action call, so nothing can be changed
     *
     * @param test_cleanup $t the test environment
     */
    private function url_to_html_tests(test_cleanup $t): void
    {
        $ui = $this->ui;
        $usr_ui = $this->usr;
        $msg = $this->msg;
        $msg_ui = new user_message_ui();
        $ts = $this->ts;
        // every url array below starts from this factory via to_url_array($msg), so the factory
        // shows centrally which test objects this test uses (docs/llm/testing.md)
        $t_wrd = new test_words($t);

        $t->subheader($ts . 'url_to_html');

        $test_name = 'show edit view';
        $url_arr = $t_wrd->word_dsp()->to_url_array($msg);
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::USER] = users::SYSTEM_ID;
        $result = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        $t->assert_text_contains($test_name, $result, word_names::MATH, $t::TIMEOUT_LIMIT_PAGE_LONG);

        $test_name = '... view with execution time measurement';
        $url_arr[url_var::DEBUG] = url_var::DEBUG_EXE_TIME_REPORT;
        $result = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        $t->assert_text_contains($test_name, $result, word_names::MATH, $t::TIMEOUT_LIMIT_PAGE_LONG);

        $test_name = 'add request via url without name should return a missing error message';
        $url_arr = test_words::word_new_url($msg_ui);
        $url_arr[url_var::MASK] = views::WORD_ADD_ID;
        $url_arr[url_var::ACTION] = url_var::CRUD_CREATE;
        $url_arr[url_var::NAME] = '';
        $result = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        // TODO Prio 1 activate
        //$t->assert_text_contains($test_name, $result, msg_id::WORD_NAME_MISSING->text());

        $test_name = '... with name ask the user to confirm adding the word';
        global $mtr;
        $url_arr[url_var::NAME] = word_names::TEST_ADD;
        // the user presses the save button of the add form, which adds the named submit marker;
        // without the marker the same url just renders the add form with the given values
        $url_arr[url_var::POST_SUBMIT] = '';
        $result = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        // the assert follows a complete view render via url, so a long page timeout is used
        $t->assert_text_contains($test_name, $result, $mtr->txt(msg_id::FORM_TITLE_CONFIRM_ADD), $t::TIMEOUT_LIMIT_PAGE_LONG);

        // the 'Change word' edit form must post the url vars the url mapper understands
        // (e.g. name="k" for the name) and never the translated label (name="Name"),
        // because a label key cannot be mapped and triggers "url mapper ... is missing"
        $test_name = 'change word edit form posts url vars not labels';
        $url_arr = $t_wrd->word_dsp()->to_url_array($msg);
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $form = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        // the first assert follows a complete edit form render via url, so a long page timeout is used
        $t->assert_text_contains($test_name, $form, 'name="' . url_var::NAME . '"', $t::TIMEOUT_LIMIT_PAGE_LONG);
        $t->assert_text_contains($test_name, $form, 'name="' . url_var::DESCRIPTION . '"');
        $t->assert_text_contains($test_name, $form, 'name="' . url_var::PLURAL . '"');
        $t->assert_text_contains($test_name, $form, 'name="' . url_var::MASK . '"');

        $test_name = '... and not the translated form labels as field names';
        $t->assert_text_not_contains($test_name, $form, 'name="Name"');
        $t->assert_text_not_contains($test_name, $form, 'name="Description"');
        $t->assert_text_not_contains($test_name, $form, 'name="Plural"');
        $t->assert_text_not_contains($test_name, $form, 'name="mask"');
        $t->assert_text_not_contains($test_name, $form, 'name="confirm"');

        // simulate pressing the save button on the 'Change word' form:
        // the corrected url vars must map cleanly without any "url ... is missing" error
        // (the failing url was ?mask=3&id=259&back=259&confirm=1&Name=USD&py=3&...)
        $test_name = 'change word save url maps without missing url mapper error';
        $save_msg = new user_message_ui();
        $save_msg->usr = $usr_ui;
        // the pending change is the factory word with only the description changed;
        // to_url_array drops the empty fields - the every-field save url is covered
        // by the fill step of the change_word workflow (fill_url_array)
        $wrd_chg = $t_wrd->word_dsp();
        $wrd_chg->set_description(word_names::TEST_CHANGE_COM);
        $url_arr = $wrd_chg->to_url_array($msg);
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::BACK] = $wrd_chg->id();
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRM;
        $result = $ui->url_to_html($url_arr, $save_msg, $ui->dto, true);
        $t->assert_false($test_name, $save_msg->has_msg_id(msg_id::URL_MAP_MISSING));
        $t->assert_false($test_name, $save_msg->has_msg_id(msg_id::URL_KEY_MISSING));
        // the render time of the save url above is charged to this assert, so a long page timeout is used
        $t->assert_text_contains($test_name, $result, word_names::MATH, $t::TIMEOUT_LIMIT_PAGE_LONG);

        // negative: a pod url that is missing the mandatory mask_id key
        // must still report the missing url key (the error path stays intact)
        $test_name = 'pod url without mask_id still reports the missing url key';
        $err_msg = new user_message_ui();
        $err_msg->usr = $usr_ui;
        $url_arr = $t_wrd->word_dsp()->to_url_array($msg);
        $url_arr[url_var::MASK_POD] = views::WORD_EDIT;
        $ui->url_to_html($url_arr, $err_msg, $ui->dto, true);
        $t->assert_true($test_name, $err_msg->has_msg_id(msg_id::URL_KEY_MISSING));


        $t->subheader($ts . 'search');

        // simulates http://localhost/http/view.php?m=67&pattern=def
        // the find url carries only a search pattern and no test object,
        // so it is the one url of this test not built via a factory to_url_array
        $test_name = 'search words by pattern via url';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_FIND_ID;
        $url_arr[url_var::PATTERN_HUMAN] = 'def';
        $result = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        $t->assert_text_contains($test_name, $result, 'def', $t::TIMEOUT_LIMIT_PAGE_LONG);
    }

    /**
     * the write path routing tests: url_to_action with do_it false, so the routing of a save
     * to the confirm view is checked without writing anything; the url_to_action tests that
     * execute a confirmed create or delete are in the write twin word_write_url_tests,
     * because they change the database (see docs/llm/testing.md)
     *
     * @param test_cleanup $t the test environment
     */
    private function url_to_action_tests(test_cleanup $t): void
    {
        $ui = $this->ui;
        $usr_ui = $this->usr;
        $msg = $this->msg;
        $ts = $this->ts;
        $t_wrd = new test_words($t);

        $t->subheader($ts . 'confirm change');

        // simulate the user pressing save on the 'Change word' edit form:
        // url_user_reaction routes the still unconfirmed change (step = STEP_CONFIRM) to the
        // confirm change view (views::CONFIRM_EDIT) built by url_to_action, which shows the
        // pending change before it is written to the database (docs/llm/state-and-messages.md)
        // never use read test objects e.g. like math in this section
        $test_name = 'pressing save shows the confirm change view with the pending change';
        $msg->usr = $usr_ui;
        // build the edit form url array from a test word instead of hard-coding the field keys;
        // change the description so the confirm view shows it as the pending change.
        // the test word is admin protected, so render it as the system (admin) user
        $wrd_ui = $t_wrd->word_dsp();
        $wrd_ui->set_description(word_names::TEST_CHANGE_COM);
        $url_arr = $wrd_ui->to_url_array($msg);
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::BACK] = $wrd_ui->id();
        $usr_backend = $t->usr1;
        $req = new user_request($usr_backend, $msg, $ui->dto, false, true);
        // the 'save' user action sets the confirm step, so url_user_reaction returns the confirm change view
        $url_arr[url_var::STEP] = url_var::ACTION_SAVE;
        $result = $ui->execute_and_next($url_arr, $req);
        // the assert follows the confirm change view render via url, so a long page timeout is used
        $t->assert_text_contains($test_name, $result, $wrd_ui->name(), $t::TIMEOUT_LIMIT_PAGE_LONG);
        // the pending change is carried into the confirm view as a url-encoded form/back parameter
        // (the human-readable change preview component is not yet implemented)
        $t->assert_text_contains($test_name, $result, rawurlencode($wrd_ui->get_description()));

        // url_to_action routes the unconfirmed save to the confirm change view url
        $test_name = 'url_to_action routes the unconfirmed save to the confirm change view';
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRM;
        $confirm_url = $ui->url_to_action($url_arr, $usr_backend, $msg, $ui->dto, false);
        $t->assert($test_name, $confirm_url[url_var::MASK], views::CONFIRM_EDIT_ID);
    }

    /*
     * The general process for the workflow test steps are
     * 1. object - create the initial test object using a test/create function e.g. $t_wrd->test_add()
     * 2. url - create the url based on the test object using a to_url() function
     * 3. start - add the view and the back path to the url to be able simulate different starting points
     * 4. view - create the html code using the url_to_html function and check if the code matches the result fixed before using assert_html_by_url that uses the url as parameter
     * 5. user - simulate a user action by changing the url, which cam be either
     *    a) edit - change the url values of a field to simulate the user typing or selecting
     *    b) press - change the url to simulate if the user has pressed a button
     * 6. action - based on the url call the url_to_action function to execute the request (or just simulate the execution)
     * 7. repeat - take the url returned by url_to_action and repeat step 4 (view)
     * the process ends if there is no user action
     */

    /**
     * the combined workflow snapshot tests: every step chains url_to_action (routing, with
     * do_it false so nothing is written) and url_to_html (render) like a real user request
     *
     * @param test_cleanup $t the test environment
     */
    private function workflow_tests(test_cleanup $t): void
    {
        $ui = $this->ui;
        $msg = $this->msg;
        $ts = $this->ts;
        $t->subheader($this->ts . 'workflow');

        // the snapshot unit test only renders the steps
        // for the write tests the same workflows are used the do_it = true
        // the fail step are before the real step to leave the database in a correct state
        $this->add_word_fail_workflow(workflows::WF_ADD_WORD_FAIL_NBR, false);
        $this->add_word_workflow(workflows::WF_ADD_WORD_NBR, false);
        $this->change_word_fail_workflow(workflows::WF_CHANGE_WORD_FAIL_NBR, false);
        $this->change_word_workflow(workflows::WF_CHANGE_WORD_NBR, false);
        $this->change_word_all_sandbox_fields_workflow(workflows::WF_CHANGE_WORD_ALL_SANDBOX_FIELDS_NBR, false);
        $this->del_word_fail_workflow(workflows::WF_DEL_WORD_FAIL_NBR, false);
        $this->del_word_workflow(workflows::WF_DEL_WORD_NBR, false);


        $t->subheader($ts . 'search');

        // simulates http://localhost/http/view.php?m=67&pattern=def
        // the find url carries only a search pattern and no test object,
        // so it is the one url of this test not built via a factory to_url_array
        $test_name = 'search words by pattern via url';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_FIND_ID;
        $url_arr[url_var::PATTERN_HUMAN] = 'def';
        $result = $ui->url_to_html($url_arr, $msg, $ui->dto, true);
        $t->assert_text_contains($test_name, $result, 'def', $t::TIMEOUT_LIMIT_PAGE_LONG);

    }

    /**
     * run the add_word_fail workflow and snapshot the html after every user action
     *
     * the negative twin of add_word_workflow: pressing save on the add form with an invalid entry (here
     * an empty name) does not show the confirm add view but re-renders the add form with the empty-name
     * warning and writes nothing, so this workflow only runs read-only and has no write twin. snapshots
     * go into src/test/resources/web/html/workflow/add_word_fail_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 9 for wf9
     * @param bool $do_it false to only render the steps (a failed save never writes)
     */
    protected function add_word_fail_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::WF_ADD_WORD_FAIL, $this->t->usr1, word_names::TEST_ADD_ID, $do_it);

        // initial url with an empty word
        $url_arr = test_words::word_new_url($this->msg);
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add word form
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_ADD_ID);

        // mark the failing input variant (the empty name) in the snapshot file name
        $this->step_path .= workflows::NAME_SEP . workflows::STEP_NO_NAME;

        // save: press save with the empty name
        // the confirm add view is not shown, the add form is rendered again with the warning (no write)
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_ADD_ID);

        // the empty name is reported as a warning instead of confirming the new word
        $test_name = $this->step_path . workflows::NAME_SEP . 'warns_empty_name';
        $this->t->assert_true($test_name, $this->msg->has_msg_id(msg_id::NAME_EMPTY));
    }

    /**
     * run the add_word workflow and snapshot the html after every user action
     *
     * the same step sequence serves the snapshot unit test ($do_it false, no write) and the workflow
     * write test ($do_it true): the back and cancel excursions abort the add without writing, then the
     * add is redone and only the final confirm writes the new word. snapshots go into
     * src/test/resources/web/html/workflow/add_word_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 1 for wf1
     * @param bool $do_it false to only render the steps, true to also write the new word
     */
    protected function add_word_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::ADD_WORD, $this->t->usr1, word_names::TEST_ADD_ID, $do_it);

        // initial url with an empty word
        $url_arr = test_words::word_new_url($this->msg);
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // edit: open the empty add word form
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_ADD_ID);

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_step(workflows::BACK, $url_arr);

        // edit: re-open the add form to enter the new word
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_ADD_ID);

        // user is typing the new word name
        $url_arr[url_var::NAME] = word_names::TEST_ADD;

        // save: press save on the add form which shows the confirm add view;
        // the submitted form carries the add mask so url_to_action can map it to the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_ADD_ID);

        // cancel: discard the new word in the confirm view and return to the start view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr);

        // edit: re-open the add form to redo the new word
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_ADD_ID);

        // user is typing again the new word name
        $url_arr[url_var::NAME] = word_names::TEST_ADD;

        // save: press save which shows the confirm add view
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_ADD_ID);

        // save: press confirm which shows the previous view
        // TODO Prio 2 with the green message that zu word has been added
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_ADD_ID);

        // a write run must actually create the word, so check it is now in the database
        if ($do_it) {
            $this->assert_word_in_db('add_word workflow has written the word',
                word_names::TEST_ADD, $this->t->usr1);
        }

    }

    /**
     * run the change_word edit workflow and snapshot the html after every user action
     *
     * the same step sequence serves the snapshot unit test ($do_it false, no write) and the workflow
     * write test ($do_it true): the back and cancel excursions abort the change without writing, then
     * the change is redone and the first confirm writes the changed description to the database. a second
     * round then re-opens the edit view, fills every still-missing field from the filled test word and
     * confirms again so the filled fields are also written. snapshots go into
     * src/test/resources/web/html/workflow/change_word_wf<nbr>/, the file name built from the cumulative
     * actions (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 2 for wf2
     * @param bool $do_it false to only render the steps, true to also write the confirmed change
     */
    protected function change_word_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the change_word workflow runs on the 'System Test Word' (added by the add_word workflow
        // of a write run), never on real data; resolve its current database id by name and set the
        // fixed snapshot id of the test word
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_WORD, $this->t->usr1, word_names::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD, $msg);
        // in a read-only run the add workflow has not written the word, so use the fixed id directly
        if ($this->wf_id == 0) {
            $this->wf_id = word_names::TEST_ADD_ID;
        }
        $this->wf_fixed_id = word_names::TEST_ADD_ID;

        // initial url with the added word; the url carries the current db id of the word so the
        // rendered buttons and the confirmed write target the real row (the snapshot files normalize
        // the id back to the fixed test id)
        $url_arr = test_words::word_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::WORD_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the test word in its default word view
        $this->assert_step(workflows::SHOW, $url_arr, views::WORD_ID);

        // edit: open the word edit view
        $html = $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // the next back step presses this edit view's cancel button, so it must point to the word view
        $this->assert_button_url($html, views::WORD_ID, $this->step_path);

        // back: leave the edit view without a change and return to the word view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::WORD_ID);

        // edit: re-open the edit view to make the change
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // user is typing the new word description
        $url_arr[url_var::DESCRIPTION] = word_names::TEST_CHANGE_COM;

        // save: press save on the edit form which shows the confirm change view
        $html = $this->assert_step(workflows::SAVE, $url_arr, views::WORD_EDIT_ID);

        // the next cancel step presses this confirm view's cancel button, so it must point to the word view
        $this->assert_button_url($html, views::WORD_ID, $this->step_path);

        // TODO Prio 1 undo of the user typing should be done by the process not the test
        $url_arr[url_var::DESCRIPTION] = '';

        // cancel: discard the pending change in the confirm view and return to the word view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::WORD_ID);

        // edit: re-open the edit view to redo the change
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // user is typing the new word description
        $url_arr[url_var::DESCRIPTION] = word_names::TEST_CHANGE_COM;

        // save: press save again which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_EDIT_ID);

        // update_confirmed: confirm the pending change so it is actually written to the database;
        // the confirm form posts the confirm update mask, because url_to_action only routes a
        // confirmed change of an edit mask to the database write (see views::EDIT_MASKS_IDS)
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the change, so check the new description in the database;
        // usr1 owns the base word (it is added with the usr1 message user, see url_test_base::init),
        // so the change is written to the usr1 standard row and is read back as usr1
        if ($do_it) {
            $this->assert_word_in_db('change_word workflow has changed the word',
                word_names::TEST_ADD, $this->t->usr1, word_names::TEST_CHANGE_COM);
        }

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // the second round fills every still-missing field of the now-saved word from the filled test word
        $t_wrd = new test_words($this->t);
        $fill = $t_wrd->fill_url_array($this->msg);
        $url_arr = $url_arr + $fill;

        // fill: press save on the edit form with every field filled which shows the confirm change view;
        // unlike the single-field save above the confirm view now shows every changed field
        $this->assert_step(workflows::FILL, $url_arr, views::WORD_EDIT_ID);

        // confirmed: confirm the filled change so it is also written to the database (with $do_it true)
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the filled fields, so check a previously empty field (the plural) is
        // now set in the database; the change is a usr1 user sandbox overlay, so read it as usr1;
        // the expected plural comes from the same fill url so the check follows the filled test word
        if ($do_it) {
            $this->assert_word_filled_in_db('change_word workflow has filled the word',
                word_names::TEST_ADD, $this->t->usr1, $fill[url_var::PLURAL]);
        }
    }

    /**
     * run the change_word_all_sandbox_fields workflow and snapshot the html after every user action
     *
     * the same step sequence serves the snapshot unit test ($do_it false, no write) and the workflow
     * write test ($do_it true): the changing user usr2 does not own the base word (usr1 does), fills
     * almost all sandbox fields in one edit round via the filled test word factory and confirms, then
     * changes the description twice in two more edit rounds, so the confirmed changes land in a usr2
     * user sandbox overlay while the owner keeps the unchanged base word; each snapshot shows the
     * page that results from the step's action, so each confirm step snapshot is the word page with
     * the change log grown by the confirmed change. snapshots go into
     * src/test/resources/web/html/workflow/change_word_all_sandbox_fields_wf<nbr>/
     * resp. workflow_write/ for a write run (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 17 for wf17
     * @param bool $do_it false to only render the steps, true to also write the usr2 sandbox overlay
     */
    protected function change_word_all_sandbox_fields_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the changing user is usr2 who does not own the word; a write run loads the changer fresh
        // so its in-memory profile matches the stored one - the shared $t->usr2 object can carry a
        // profile that an earlier test left different from the database, which would make the
        // uses_sandbox flag flip look like a profile escalation and block the whole word save
        // (see user::enforce_profile_privilege)
        $changer = $this->t->usr2;
        if ($do_it) {
            $changer = new user();
            $changer->load_by_id($this->t->usr2->id(), $msg);
        }
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_WORD_ALL_SANDBOX_FIELDS,
            $changer, word_names::TEST_ADD_ID, $do_it);

        // the workflow runs on the 'System Test Word' created by the write twin for usr1; resolve
        // its current database id by name and fall back to the fixed id in a read-only run
        $t_wrd = new test_words($this->t);
        $this->wf_id = $t_wrd->word_id_or_fixed(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $this->wf_fixed_id = word_names::TEST_ADD_ID;

        // initial url with the base word; the url carries the current db id of the word so the
        // rendered buttons and the confirmed write target the real row (the snapshot files
        // normalize the id back to the fixed test id)
        $url_arr = test_words::word_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::WORD_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the base word in its default word view as seen by the changing user
        $this->assert_step(workflows::SHOW, $url_arr, views::WORD_ID);

        // edit: open the word edit view
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // user 2 fills almost all sandbox fields with the values of the filled test word; the fill
        // values must win over the factory url values, so the union starts with them (the array
        // union operator keeps the keys of the first array)
        $fill = $t_wrd->fill_url_array($this->msg);
        $url_arr = $fill + $url_arr;

        // fill: press save with every sandbox field changed which shows the confirm change view
        $this->assert_step(workflows::FILL, $url_arr, views::WORD_EDIT_ID);

        // confirm: confirm the change; with $do_it true it is written as a usr2 user sandbox
        // overlay, because usr2 does not own the base word; the resulting page is the word view
        // with the changed fields and the change log entries of the confirmed change
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_EDIT_ID);

        // edit: re-open the edit view to change the description a first time (the fill round kept
        // the base description, see test_words::word_filled_add)
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // user 2 is typing the first new description; the '8'-prefixed opening value is the
        // description the word still has after the fill round, so the confirm view shows the diff
        $url_arr[url_var::DESCRIPTION] = word_names::TEST_CHANGE_COM;
        $url_arr[url_var::PRE . url_var::DESCRIPTION] = word_names::TEST_ADD_COM;

        // save: press save which shows the confirm change view with the description change
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_EDIT_ID);

        // confirm: confirm the first description change (written with $do_it true)
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_EDIT_ID);

        // edit: re-open the edit view to change the description a second time
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // user 2 is typing the second new description on top of the first one
        $url_arr[url_var::DESCRIPTION] = word_names::TEST_CHANGE_TWO_COM;
        $url_arr[url_var::PRE . url_var::DESCRIPTION] = word_names::TEST_CHANGE_COM;

        // save: press save which shows the confirm change view
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_EDIT_ID);

        // confirm: confirm the second description change; the change log on the resulting word
        // page now shows both description changes on top of the filled fields
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must persist the changes as a per-user overlay: the changing user sees the
        // filled fields and the latest description, the owner keeps the unchanged base word
        // without an overlay of their own
        if ($do_it) {
            $test_name = 'change_word_all_sandbox_fields has created the user overlay';
            $wrd_changer = new word($changer);
            $wrd_changer->load_by_name(word_names::TEST_ADD, $msg);
            $this->t->assert_true($test_name, $wrd_changer->has_usr_cfg());
            $test_name = '... and the changing user sees the filled plural';
            $this->t->assert($test_name, $wrd_changer->plural ?? '', $fill[url_var::PLURAL]);
            $test_name = '... and the changing user sees the second changed description';
            $this->t->assert($test_name, $wrd_changer->get_description() ?? '', word_names::TEST_CHANGE_TWO_COM);
            $test_name = '... while the owner still sees the unfilled word';
            $wrd_owner = new word($this->t->usr1);
            $wrd_owner->load_by_name(word_names::TEST_ADD, $msg);
            $this->t->assert($test_name, $wrd_owner->plural ?? '', '');
            $test_name = '... and the owner still sees the original description';
            $this->t->assert($test_name, $wrd_owner->get_description() ?? '', word_names::TEST_ADD_COM);
            $test_name = '... and the owner has no user sandbox overlay';
            $this->t->assert_false($test_name, $wrd_owner->has_usr_cfg());
        }
    }

    /**
     * run the change_word_fail workflow and snapshot the html after every user action
     *
     * checks that pressing save with an invalid change (here an empty name) does not show the confirm
     * view but re-renders the edit view with the warning, and that the '8'-prefixed opening db values
     * (here the phrase type) are kept so the original db snapshot is still used for the next change
     * compare (see docs/llm/state-and-messages.md). a failed save writes nothing, so this workflow only
     * runs read-only and has no write twin. snapshots go into
     * src/test/resources/web/html/workflow/change_word_fail_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 7 for wf7
     * @param bool $do_it false to only render the steps (a failed save never writes)
     */
    protected function change_word_fail_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the workflow runs on the 'System Test Word'; resolve its current database id by name
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_WORD_FAIL, $this->t->usr1, word_names::TEST_ADD_ID, $do_it);

        // initial url with the added word
        $url_arr = test_words::word_add_url($this->msg);
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::WORD_ID;
        $url_arr[url_var::BACK . url_var::ID] = word_names::TEST_ADD_ID;

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD, $msg);
        $this->wf_fixed_id = word_names::TEST_ADD_ID;

        // the invalid change: clear the name (which blocks the save) but change the phrase type and send
        // its '8'-prefixed opening value, so the kept baseline can be checked after the failed save
        $phr_typ = $this->ui->dto->typ_lst_cache->phr_typ;
        $type_old = (string)$phr_typ->default_id();
        $type_new = (string)$phr_typ->id(phrase_types::TIME);
        $invalid = [
            url_var::NAME => '',
            url_var::PHRASE_TYPE => $type_new,
            url_var::PRE . url_var::PHRASE_TYPE => $type_old,
            url_var::PRE . url_var::NAME => word_names::TEST_ADD,
        ];

        // show: display the test word in its default word view
        $this->assert_step(workflows::SHOW, $url_arr, views::WORD_ID);

        // edit: open the word edit view
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_EDIT_ID);

        // user is doing invalid changes; the invalid values must win over the factory url values,
        // so the union starts with them (the array union operator keeps the keys of the first array)
        $url_arr = $invalid + $url_arr;

        // save: press save with the empty name; the confirm view is not shown, the edit view is rendered
        // again with the warning and the phrase type '8' baseline kept at the original db value
        $html = $this->assert_step(workflows::SAVE, $url_arr, views::WORD_EDIT_ID);

        // the empty name is reported as a warning instead of confirming the change
        $test_name = $this->step_path . workflows::NAME_SEP . 'keeps_pre';
        $this->t->assert_true($test_name, $this->msg->has_msg_id(msg_id::NAME_EMPTY));
        // the original phrase type '8' baseline is preserved for the next compare, not reset to the change
        $this->t->assert_text_contains($test_name, $html, 'name="' . url_var::PRE . url_var::PHRASE_TYPE . '" value="' . $type_old . '"');
    }

    /**
     * run the del_word_fail workflow and snapshot the html after every user action
     *
     * the negative twin of del_word_workflow: confirming the deletion of a word that is still in use
     * (the frontend reads the usage posted with the url) does not show the confirm delete view but
     * re-renders the delete form with the in-use warning and writes nothing, so this workflow only runs
     * read-only and has no write twin. it runs on the reserved test word with a forced usage, so seeded
     * data is never touched. snapshots go into
     * src/test/resources/web/html/workflow/del_word_fail_wf<nbr>/ (docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 10 for wf10
     * @param bool $do_it false to only render the steps (a blocked delete never writes)
     */
    protected function del_word_fail_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        $msg_ui = new user_message_ui();
        // the workflow runs on the reserved 'System Test Word' (created earlier in this test run) so a
        // blocked delete can never touch seeded data; resolve its database id by name and set the fixed
        // snapshot id
        $this->wf_start($wf_nbr, workflows::WF_DEL_WORD_FAIL, $this->t->usr1, word_names::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id and load the related objects so the usage shows
        // that the word is still in use
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD, $msg);
        $this->wf_fixed_id = word_names::TEST_ADD_ID;
        if ($this->wf_id > 0) {
            // TODO Prio 1 use load_related ? (without by_id?)
            $wrd->load_by_id_with_related($wrd->id(), $msg);
            $wrd_ui = new word_ui($wrd->api_json());
        } else {
            // in a read-only run the add workflow has not written the word, so render the factory
            // word with the fixed test id instead of the empty db row (a zero id url would be
            // rejected by url_to_html with 'id of word is empty')
            $this->wf_id = word_names::TEST_ADD_ID;
            $wrd_ui = test_words::word_add_ui();
        }
        // the api json does not yet carry the usage, and the test word is not really linked, so force
        // the usage that blocks the deletion (the frontend check reads the usage posted with the url)
        // TODO Prio 0 remove workaround until the backend maintains the usage field
        $wrd_ui->usage = $wrd->usage ?? 0;
        if ($wrd_ui->usage == 0) {
            $wrd_ui->usage = 1;
        }

        // initial url with the in-use word
        $url_arr = $wrd_ui->to_url_array($msg_ui);
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::WORD_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the in-use word in its default word view
        $this->assert_step(workflows::SHOW, $url_arr, views::WORD_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_DEL_ID);

        // mark the failing variant (the word is still in use) in the snapshot file name
        $this->step_path .= workflows::NAME_SEP . workflows::STEP_IN_USE;

        // save: press delete; the confirm delete view is not shown, the delete form is rendered again
        // with the in-use warning (no write)
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_DEL_ID);

        // the still-in-use word is reported as a warning instead of confirming the deletion
        $test_name = $this->step_path . workflows::NAME_SEP . 'warns_in_use';
        $this->t->assert_true($test_name, $this->msg->has_msg_id(msg_id::DELETE_IN_USE));
    }

    /**
     * run the del_word workflow and snapshot the html after every user action
     *
     * the same step sequence serves the snapshot unit test ($do_it false, no write) and the workflow
     * write test ($do_it true): the back and cancel excursions abort the deletion without writing,
     * then the deletion is redone and only the final confirm removes the word. snapshots go into
     * src/test/resources/web/html/workflow/del_word_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 3 for wf3
     * @param bool $do_it false to only render the steps, true to also delete the word
     */
    protected function del_word_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $msg = new user_message();
        // the del_word workflow runs on the 'System Test Word' (added by the add_word workflow of
        // a write run); resolve its current database id by name and set the fixed snapshot id
        $this->wf_start($wf_nbr, workflows::WF_DEL_WORD, $this->t->usr1, word_names::TEST_ADD_ID, $do_it);

        // set the real and the fixed object id TODO Prio 2 at least to be replace with an url var
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD, $msg);
        // in a read-only run without the earlier test the word may be missing, so use the fixed id
        if ($this->wf_id == 0) {
            $this->wf_id = word_names::TEST_ADD_ID;
        }
        $this->wf_fixed_id = word_names::TEST_ADD_ID;

        // initial url with the added word; the url carries the current db id of the word so the
        // confirmed delete targets the real row (the snapshot files normalize the id back to the
        // fixed test id)
        $url_arr = test_words::word_add_url($this->msg);
        $url_arr[url_var::ID] = $this->wf_id;
        // fix the values before the changes in the url TODO Prio 2 should be done by the process automatic
        $url_pre = html_base::pre_url_array($url_arr);
        $url_arr = $url_arr + $url_pre;
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;

        // show: display the test word in its default word view
        $this->assert_step(workflows::SHOW, $url_arr, views::WORD_ID);

        // edit: open the delete confirmation form
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_DEL_ID);

        // back: leave the delete form without deleting and return to the word view (no write)
        $this->assert_step(workflows::BACK, $url_arr, views::WORD_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_DEL_ID);

        // save: press delete on the form which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_DEL_ID);

        // cancel: discard the deletion in the confirm view and return to the word view (no write)
        $this->assert_step(workflows::CANCEL, $url_arr, views::WORD_ID);

        // edit: re-open the delete form
        $this->assert_step(workflows::EDIT, $url_arr, views::WORD_DEL_ID);

        // save: press delete again which shows the confirm delete view
        $this->assert_step(workflows::SAVE, $url_arr, views::WORD_DEL_ID);

        // del_confirmed: confirm the deletion so the word is actually removed from the database (with
        // $do_it true); the confirm mask does not encode the object type, so carry the '9'-prefixed back
        // target = the word view + id (as the real confirm form does) so dbo_for_url resolves the word
        // instead of falling back to the default word object
        $url_arr[url_var::BACK . url_var::MASK] = views::WORD_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_DEL_ID);

        // a write run must actually delete the word; a non-owner delete is a soft delete, so check the
        // word is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_word_removed('del_word workflow has removed the word');
        }
    }

    /**
     * check that the workflow test word exists in the database with the expected description, used by
     * the add and change write workflows to verify the confirmed step was actually persisted
     *
     * 'System Test Word' is a reserved name, so the add workflow writes the system (base) version owned
     * by the system user, while a later change by the (non-owner) frontend user usr1 only writes a usr1
     * user sandbox overlay on top of that base. the verifying read must therefore use the same user the
     * workflow wrote with: the system user for the base value added, usr1 for the sandbox value changed.
     *
     * @param string $test_name the description of the assertion
     * @param string $name the name of the test word in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $des the description of the test word in the database
     */
    private function assert_word_in_db(string $test_name, string $name, user $usr, string $des = ''): void
    {
        $msg = new user_message();
        $wrd = new word($usr);
        $wrd->load_by_name($name, $msg);
        $this->t->assert($test_name, $wrd->name(), $name);
        // a word added without a description has null in the database, which the caller expects as ''
        $this->t->assert($test_name, $wrd->get_description() ?? '', $des);
    }

    /**
     * TODO Prio 2 create a more general form
     * check that the second change_word round actually filled the previously empty fields of the test
     * word, used by the change write workflow to verify the filled confirm step was persisted. usr1
     * owns the base word (added with the usr1 message user), so the plural is written to and read as usr1.
     *
     * @param string $test_name the description of the assertion
     * @param string $name the name of the test word in the database
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $plural a field of the test word in the database
     */
    private function assert_word_filled_in_db(string $test_name, string $name, user $usr, string $plural = ''): void
    {
        $msg = new user_message();
        $wrd = new word($usr);
        $wrd->load_by_name($name, $msg);
        $this->t->assert($test_name, $wrd->name(), $name);
        $this->t->assert($test_name, $wrd->plural ?? '', $plural);
    }

    /**
     * check that the workflow test word has been removed for usr1, used by the delete write workflow to
     * verify the confirmed deletion was persisted. deleting the system owned 'System Test Word' lands in
     * one of two valid states depending on the word's accumulated user sandbox usage: if no other user
     * uses it the row is hard deleted and gone (the expected case), if another user still uses it the
     * delete is a soft delete that keeps the row but flags it excluded for usr1. both mean the word is no
     * longer an active word for usr1, so accept either a missing row or the excluded flag - otherwise the
     * test is fragile against the shared test database state left by previous runs.
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_word_removed(string $test_name): void
    {
        $msg = new user_message();
        $wrd = new word($this->t->usr1);
        $wrd->load_by_name(word_names::TEST_ADD, $msg);
        $this->t->assert_true($test_name, $wrd->id() == 0 || $wrd->is_excluded());
    }

}
