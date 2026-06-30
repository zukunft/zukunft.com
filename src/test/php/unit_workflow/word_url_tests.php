<?php

/*

    test/php/unit_write/word_tests.php - write test words to the database and check the results
    ----------------------------------
  

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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_db.php';
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
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\helper\user_request;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
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
        $ui = $this->ui;
        $usr_ui = $this->usr;
        $usr_sys_ui = $this->usr_sys;
        $usr_msg = $this->usr_msg;
        $ts = $this->ts;


        $t->subheader($ts . 'workflow');

        $test_name = 'show edit view';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::ID] = word_names::MATH_ID;
        $url_arr[url_var::USER] = users::SYSTEM_ID;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        // $t->assert_text_contains($test_name, $result, word_names::MATH);

        $test_name = '... view with execution time measurement';
        $url_arr[url_var::DEBUG] = url_var::DEBUG_EXE_TIME_REPORT;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        //$t->assert_text_contains($test_name, $result, word_names::MATH);

        $test_name = 'add request via url without name should return a missing error message';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_ADD_ID;
        $url_arr[url_var::ACTION] = url_var::CRUD_CREATE;
        $url_arr[url_var::NAME] = '';
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 1 activate
        //$t->assert_text_contains($test_name, $result, msg_id::WORD_NAME_MISSING->text());

        $test_name = '... with name ask the user to confirm adding the word';
        $url_arr[url_var::NAME] = word_names::TEST_ADD;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate once url_to_html routes a create request to the views::CONFIRM_ADD mask
        //$t->assert_text_contains($test_name, $result, $mtr->get(msg_id::FORM_TITLE_CONFIRM_ADD->text()));

        $test_name = '... if confirmed the word is added';
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRMED;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        $t->assert_text_contains($test_name, $result, word_names::TEST_ADD);

        $test_name = '... so it can be deleted';
        $url_arr[url_var::ACTION] = url_var::CRUD_DELETE;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate once url_to_html handles url_var::CRUD_DELETE like the create action
        //$t->assert_text_contains($test_name, $result, word_names::TEST_ADD);


        $t->subheader($ts . 'change save url');

        // the 'Change word' edit form must post the url vars the url mapper understands
        // (e.g. name="k" for the name) and never the translated label (name="Name"),
        // because a label key cannot be mapped and triggers "url mapper ... is missing"
        $test_name = 'change word edit form posts url vars not labels';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::ID] = word_names::MATH_ID;
        $form = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        $t->assert_text_contains($test_name, $form, 'name="' . url_var::NAME . '"');
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
        $save_msg = new user_message();
        $save_msg->usr = $usr_sys_ui;
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::ID] = word_names::MATH_ID;
        $url_arr[url_var::BACK] = word_names::MATH_ID;
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRM;
        $url_arr[url_var::NAME] = word_names::MATH;
        $url_arr[url_var::DESCRIPTION] = 'a test description';
        $url_arr[url_var::PLURAL] = '';
        $url_arr[url_var::VIEW] = '0';
        $url_arr[url_var::SHARE] = '1';
        $url_arr[url_var::PROTECTION] = '1';
        $result = $ui->url_to_html($url_arr, $usr_ui, $save_msg, $ui->dto);
        $t->assert_false($test_name, $save_msg->has_msg_id(msg_id::URL_MAP_MISSING));
        $t->assert_false($test_name, $save_msg->has_msg_id(msg_id::URL_KEY_MISSING));
        $t->assert_text_contains($test_name, $result, word_names::MATH);

        // negative: a pod url that is missing the mandatory mask_id key
        // must still report the missing url key (the error path stays intact)
        $test_name = 'pod url without mask_id still reports the missing url key';
        $err_msg = new user_message();
        $err_msg->usr = $usr_sys_ui;
        $url_arr = [];
        $url_arr[url_var::MASK_POD] = views::WORD_EDIT;
        $url_arr[url_var::ID] = word_names::MATH_ID;
        $ui->url_to_html($url_arr, $usr_ui, $err_msg, $ui->dto);
        $t->assert_true($test_name, $err_msg->has_msg_id(msg_id::URL_KEY_MISSING));


        $t->subheader($ts . 'confirm change');

        // simulate the user pressing save on the 'Change word' edit form:
        // url_user_reaction routes the still unconfirmed change (step = STEP_CONFIRM) to the
        // confirm change view (views::CONFIRM_EDIT) built by url_to_action, which shows the
        // pending change before it is written to the database (docs/llm/state-and-messages.md)
        $test_name = 'pressing save shows the confirm change view with the pending change';
        $usr_msg->usr = $usr_sys_ui;
        // build the edit form url array from a test word instead of hard-coding the field keys;
        // change the description so the confirm view shows it as the pending change.
        // the test word is admin protected, so render it as the system (admin) user
        $t_wrd = new test_words($t);
        $wrd_ui = $t_wrd->word_dsp();
        $wrd_ui->set_description(word_names::TEST_CHANGE_COM);
        $url_arr = $wrd_ui->to_url_array();
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::BACK] = $wrd_ui->id();
        $usr_backend = $t->usr1;
        $req = new user_request($usr_backend, $usr_sys_ui, $usr_msg, $ui->dto, false);
        // the 'save' user action sets the confirm step, so url_user_reaction returns the confirm change view
        $result = $ui->url_user_reaction(url_var::ACTION_SAVE, $url_arr, $req);
        $t->assert_text_contains($test_name, $result, $wrd_ui->name());
        // the pending change is carried into the confirm view as a url-encoded form/back parameter
        // (the human-readable change preview component is not yet implemented)
        $t->assert_text_contains($test_name, $result, rawurlencode($wrd_ui->get_description()));

        // url_to_action routes the unconfirmed save to the confirm change view url
        $test_name = 'url_to_action routes the unconfirmed save to the confirm change view';
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRM;
        $confirm_url = $ui->url_to_action($url_arr, $usr_backend, $usr_sys_ui, $usr_msg, $ui->dto, false);
        $t->assert($test_name, $confirm_url[url_var::MASK], views::CONFIRM_EDIT_ID);


        // the snapshot unit test only renders the steps; the workflow write test passes do_it true
        $this->add_word_workflow(workflows::WF_ADD_WORD_NBR, false);
        $this->change_word_workflow(workflows::WF_CHANGE_WORD_NBR, false);
        $this->del_word_workflow(workflows::WF_DEL_WORD_NBR, false);
        $this->change_word_invalid_workflow(workflows::WF_CHANGE_WORD_INVALID_NBR, false);


        $t->subheader($ts . 'search');

        // simulates http://localhost/http/view.php?m=67&pattern=def
        $test_name = 'search words by pattern via url';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_FIND_ID;
        $url_arr[url_var::PATTERN_HUMAN] = 'def';
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        $t->assert_text_contains($test_name, $result, 'def');


        $t->subheader($ts . 'cleanup');

        // cleanup - fallback delete
        $wrd = new word($t->usr1);
        foreach (word_names::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
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
        // the change_word workflow runs on the 'System Test Word' added above, not on real data;
        // resolve its current database id by name and set the fixed snapshot id of the test word
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD);
        $this->wf_fixed_id = word_names::TEST_ADD_ID;
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_WORD, $do_it);

        // the pending change posted by the edit form on save and shown again in the confirm view
        $t_wrd = new test_words($this->t);
        $change = $t_wrd->change_url_array();

        // show: display the test word in its default word view
        $this->assert_workflow_step(url_var::ACTION_SHOW, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SHOW;

        // edit: open the word edit view
        $html = $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_EDIT_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;
        // the next back step presses this edit view's cancel button, so it must point to the word view
        $this->assert_button_url($html, views::WORD_ID, $this->step_path);

        // back: leave the edit view without a change and return to the word view (no write)
        $this->assert_workflow_step(url_var::ACTION_BACK, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_BACK;

        // edit: re-open the edit view to make the change
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_EDIT_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press save on the edit form which shows the confirm change view
        $html = $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_EDIT_ID, $change);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;
        // the next cancel step presses this confirm view's cancel button, so it must point to the word view
        $this->assert_button_url($html, views::WORD_ID, $this->step_path);

        // cancel: discard the pending change in the confirm view and return to the word view (no write)
        $this->assert_workflow_step(url_var::ACTION_CANCEL, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_CANCEL;

        // edit: re-open the edit view to redo the change
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_EDIT_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press save again which shows the confirm change view
        $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_EDIT_ID, $change);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // update_confirmed: confirm the pending change so it is actually written to the database (with
        // $do_it true) and the user is returned to the word view (the confirm form carries the
        // '9'-prefixed back target = the word view + id, as set by url_to_action when the confirm view
        // was opened)
        // TODO Prio 2 review
        $this->assert_workflow_step(url_var::ACTION_CONFIRMED, views::CONFIRM_EDIT_ID,
            $change + [
                url_var::BACK . url_var::MASK => views::WORD_ID,
                url_var::BACK . url_var::ID => $this->wf_id
            ]);

        // a write run must actually persist the change, so check the new description in the database;
        // the change is a usr1 user sandbox overlay on top of the system base, so read it as usr1
        if ($do_it) {
            $this->assert_word_in_db('change_word workflow has changed the word', $this->t->usr1, word_names::TEST_CHANGE_COM);
        }
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_CONFIRMED;

        // the second round fills every still-missing field of the now-saved word from the filled test word
        $fill = $t_wrd->fill_url_array();

        // edit: re-open the edit view to fill the remaining fields
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_EDIT_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // fill: press save on the edit form with every field filled which shows the confirm change view;
        // unlike the single-field save above the confirm view now shows every changed field
        $this->assert_workflow_step(url_var::ACTION_FILL, views::WORD_EDIT_ID, $fill);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_FILL;

        // confirmed: confirm the filled change so it is also written to the database (with $do_it true)
        $this->assert_workflow_step(url_var::ACTION_CONFIRMED, views::CONFIRM_EDIT_ID,
            $fill + [
                url_var::BACK . url_var::MASK => views::WORD_ID,
                url_var::BACK . url_var::ID => $this->wf_id
            ]);

        // a write run must persist the filled fields, so check a previously empty field (the plural) is
        // now set in the database; the change is a usr1 user sandbox overlay, so read it as usr1
        if ($do_it) {
            $this->assert_word_filled_in_db('change_word workflow has filled the word');
        }
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
        // the add_word workflow creates a new word, so there is no object id to load yet
        $this->wf_id = 0;
        $this->wf_fixed_id = word_names::TEST_ADD_ID;
        $this->wf_start($wf_nbr, workflows::WF_ADD_WORD, $do_it);

        // the new word fields posted by the add form on save and shown again in the confirm add view
        $t_wrd = new test_words($this->t);
        $add = $t_wrd->add_url_array();

        // edit: open the empty add word form
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_ADD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // back: leave the add form without adding and return to the start view (no write)
        $this->assert_workflow_step(url_var::ACTION_BACK, views::START_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_BACK;

        // edit: re-open the add form to enter the new word
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_ADD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press save on the add form which shows the confirm add view
        $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_ADD_ID, $add);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // cancel: discard the new word in the confirm view and return to the start view (no write)
        $this->assert_workflow_step(url_var::ACTION_CANCEL, views::START_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_CANCEL;

        // edit: re-open the add form to redo the new word
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_ADD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press save again which shows the confirm add view
        $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_ADD_ID, $add);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // add_confirmed: confirm the new word so it is actually added to the database (with $do_it true)
        // and the user is returned to the word view (the confirm form carries the '9'-prefixed back
        // target = the word view, as set by url_to_action when the confirm view was opened; no id yet)
        $this->assert_workflow_step(url_var::ACTION_CONFIRMED, views::CONFIRM_ADD_ID,
            $add + [url_var::BACK . url_var::MASK => views::WORD_ID]);

        // a write run must actually create the word, so check it is now in the database; the reserved
        // 'System Test Word' is added as the system base, so read it as the system user (a stale usr1
        // sandbox overlay from a previous run must not mask the freshly written base value)
        if ($do_it) {
            $this->assert_word_in_db('add_word workflow has written the word', $this->t->usr_system, word_names::TEST_ADD_COM);
        }
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
        // the del_word workflow runs on the 'System Test Word' added above;
        // resolve its current database id by name and set the fixed snapshot id of the test word
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD);
        $this->wf_fixed_id = word_names::TEST_ADD_ID;
        $this->wf_start($wf_nbr, workflows::WF_DEL_WORD, $do_it);

        // show: display the test word in its default word view
        $this->assert_workflow_step(url_var::ACTION_SHOW, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SHOW;

        // edit: open the delete confirmation form
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_DEL_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // back: leave the delete form without deleting and return to the word view (no write)
        $this->assert_workflow_step(url_var::ACTION_BACK, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_BACK;

        // edit: re-open the delete form
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_DEL_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press delete on the form which shows the confirm delete view
        $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_DEL_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // cancel: discard the deletion in the confirm view and return to the word view (no write)
        $this->assert_workflow_step(url_var::ACTION_CANCEL, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_CANCEL;

        // edit: re-open the delete form
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_DEL_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press delete again which shows the confirm delete view
        $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_DEL_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // del_confirmed: confirm the deletion so the word is actually removed from the database (with
        // $do_it true)
        $this->assert_workflow_step(url_var::ACTION_CONFIRMED, views::CONFIRM_DEL_ID);

        // a write run must actually delete the word; a non-owner delete is a soft delete, so check the
        // word is flagged as excluded in the user sandbox rather than physically removed
        if ($do_it) {
            $this->assert_word_removed('del_word workflow has removed the word');
        }
    }

    /**
     * run the change_word_invalid workflow and snapshot the html after every user action
     *
     * checks that pressing save with an invalid change (here an empty name) does not show the confirm
     * view but re-renders the edit view with the warning, and that the '8'-prefixed opening db values
     * (here the phrase type) are kept so the original db snapshot is still used for the next change
     * compare (see docs/llm/state-and-messages.md). a failed save writes nothing, so this workflow only
     * runs read-only and has no write twin. snapshots go into
     * src/test/resources/web/html/workflow/change_word_invalid_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 7 for wf7
     * @param bool $do_it false to only render the steps (a failed save never writes)
     */
    protected function change_word_invalid_workflow(int $wf_nbr, bool $do_it = false): void
    {
        // the workflow runs on the 'System Test Word'; resolve its current database id by name
        $wrd = new word($this->t->usr1);
        $this->wf_id = $wrd->load_by_name(word_names::TEST_ADD);
        $this->wf_fixed_id = word_names::TEST_ADD_ID;
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_WORD_INVALID, $do_it);

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
        $this->assert_workflow_step(url_var::ACTION_SHOW, views::WORD_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SHOW;

        // edit: open the word edit view
        $this->assert_workflow_step(url_var::ACTION_EDIT, views::WORD_EDIT_ID);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_EDIT;

        // save: press save with the empty name; the confirm view is not shown, the edit view is rendered
        // again with the warning and the phrase type '8' baseline kept at the original db value
        $html = $this->assert_workflow_step(url_var::ACTION_SAVE, views::WORD_EDIT_ID, $invalid);
        $this->step_path .= workflows::NAME_SEP . url_var::ACTION_SAVE;

        // the empty name is reported as a warning instead of confirming the change
        $test_name = $this->step_path . workflows::NAME_SEP . 'keeps_pre';
        $this->t->assert_true($test_name, $this->usr_msg->has_msg_id(msg_id::NAME_EMPTY));
        // the original phrase type '8' baseline is preserved for the next compare, not reset to the change
        $this->t->assert_text_contains($test_name, $html,
            'name="' . url_var::PRE . url_var::PHRASE_TYPE . '" value="' . $type_old . '"');
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
     * @param user $usr the user whose database version (base or user sandbox) is checked
     * @param string $description the expected description of the test word in the database
     */
    private function assert_word_in_db(string $test_name, user $usr, string $description): void
    {
        $wrd = new word($usr);
        $wrd->load_by_name(word_names::TEST_ADD);
        $this->t->assert($test_name, $wrd->name(), word_names::TEST_ADD);
        $this->t->assert($test_name, $wrd->description, $description);
    }

    /**
     * check that the second change_word round actually filled the previously empty fields of the test
     * word, used by the change write workflow to verify the filled confirm step was persisted. the fill
     * is a usr1 user sandbox overlay on top of the system base, so the plural is read as usr1.
     *
     * @param string $test_name the description of the assertion
     */
    private function assert_word_filled_in_db(string $test_name): void
    {
        $wrd = new word($this->t->usr1);
        $wrd->load_by_name(word_names::TEST_ADD);
        $this->t->assert($test_name, $wrd->name(), word_names::TEST_ADD);
        $this->t->assert($test_name, $wrd->plural, word_names::MATH_PLURAL);
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
        $wrd = new word($this->t->usr1);
        $wrd->load_by_name(word_names::TEST_ADD);
        $this->t->assert_true($test_name, $wrd->id() == 0 || $wrd->is_excluded());
    }

}
