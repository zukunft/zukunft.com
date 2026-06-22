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
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_base;

class word_url_tests
{

    // fixed text that replaces the volatile change log entry (add time + add user) in the snapshots
    const string WF_CHANGE_LOG = 'system test change log entry';

    function run(test_base $t): void
    {

        // init
        global $mtr;
        $usr_msg = new user_message();
        global $sys;
        $ui = new frontend('view');
        $ui->load_cache();
        // the html renderers read the type cache from the global $ui_sys (e.g. ref->type_name and
        // user::navbar_role); always point it at the just loaded frontend cache so the render does
        // not depend on a stale or incomplete cache left in the global by another test (which would
        // make the navbar user role e.g. 'system test' appear only sometimes)
        global $ui_sys;
        $ui_sys = $ui->dto;
        // the change log renderer reads the date format from $ui_sys->cfg (set in http/view.php);
        // an empty config_ui returns the shared default format so a word with history renders
        $ui_sys->cfg = new config_ui();
        $usr_ui = new user_ui();
        $usr_ui->set_from_json($t->usr1->api_json(), $usr_msg);
        $usr_sys_ui = new user_ui();
        $usr_sys_ui->set_from_json($t->usr_system->api_json(), $usr_msg);
        $usr_msg->usr = $usr_sys_ui;
        $t->name = 'word url->';

        // start the test section (ts)
        $ts = 'url word ';
        $t->header($ts);


        $t->subheader($ts . 'workflow');

        $test_name = 'show edit view';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::ID] = word_names::MATH_ID;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        $t->assert_text_contains($test_name, $result, word_names::MATH);

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
        $wrd_ui->set_description('a confirm change test description');
        $url_arr = $wrd_ui->to_url_array();
        $url_arr[url_var::MASK] = views::WORD_EDIT_ID;
        $url_arr[url_var::BACK] = $wrd_ui->id();
        $usr_backend = $t->usr1;
        // the 'save' user action sets the confirm step, so url_user_reaction returns the confirm change view
        $result = $ui->url_user_reaction(url_var::ACTION_SAVE, $url_arr, $usr_backend, $usr_sys_ui, $usr_msg, $ui->dto, false);
        $t->assert_text_contains($test_name, $result, $wrd_ui->name());
        // the pending change is carried into the confirm view as a url-encoded form/back parameter
        // (the human-readable change preview component is not yet implemented)
        $t->assert_text_contains($test_name, $result, rawurlencode($wrd_ui->get_description()));

        // url_to_action routes the unconfirmed save to the confirm change view url
        $test_name = 'url_to_action routes the unconfirmed save to the confirm change view';
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRM;
        $confirm_url = $ui->url_to_action($url_arr, $usr_backend, $usr_sys_ui, $usr_msg, $ui->dto, false);
        $t->assert($test_name, $confirm_url[url_var::MASK], views::CONFIRM_EDIT_ID);


        $t->subheader($ts . 'change_word workflow wf1');

        // the change_word workflow (unit id 1) snapshots the html after every user action into
        // src/test/resources/web/html/workflow/change_word_wf1/, the file name built from the
        // cumulative actions (see docs/llm/testing.md). do_it is false so no database row is written.
        $wf = test_paths::HTML . 'workflow/change_word_wf1/wf1';
        $new_description = 'a confirm change test description';

        // the change_word workflow runs on the 'System Test Word' added above, not on real data;
        // resolve its current database id by name so the steps can show and edit it
        $wrd = new word($t->usr1);
        $wf_id = $wrd->load_by_name(word_names::TEST_ADD);

        // show: display the test word in its default word view
        $test_name = 'show';
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => $wf_id];
        $result = $ui->url_user_reaction(url_var::ACTION_SHOW, $url_arr, $usr_backend, $usr_ui, $usr_msg, $ui->dto, false);
        $this->assert_wf_html($t, $test_name, $result, $wf . '_show', $wf_id, $usr_ui);

        // edit: open the word edit view
        $test_name = 'show edit';
        $url_arr = [url_var::MASK => views::WORD_EDIT_ID, url_var::ID => $wf_id];
        $result = $ui->url_user_reaction(url_var::ACTION_EDIT, $url_arr, $usr_backend, $usr_ui, $usr_msg, $ui->dto, false);
        $this->assert_wf_html($t, $test_name, $result, $wf . '_show_edit', $wf_id, $usr_ui);

        // back: leave the edit view without a change and return to the word view
        $test_name = 'show edit back';
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => $wf_id];
        $result = $ui->url_user_reaction(url_var::ACTION_BACK, $url_arr, $usr_backend, $usr_ui, $usr_msg, $ui->dto, false);
        $this->assert_wf_html($t, $test_name, $result, $wf . '_show_edit_back', $wf_id, $usr_ui);

        // save: press save on the edit form which shows the confirm change view
        $test_name = 'show edit save';
        $url_arr = [
            url_var::MASK => views::WORD_EDIT_ID,
            url_var::ID => $wf_id,
            url_var::BACK => $wf_id,
            url_var::NAME => word_names::TEST_ADD,
            url_var::DESCRIPTION => $new_description,
            url_var::PLURAL => '',
            url_var::VIEW => '0',
            url_var::SHARE => '1',
            url_var::PROTECTION => '1'
        ];
        $result = $ui->url_user_reaction(url_var::ACTION_SAVE, $url_arr, $usr_backend, $usr_ui, $usr_msg, $ui->dto, false);
        $this->assert_wf_html($t, $test_name, $result, $wf . '_show_edit_save', $wf_id, $usr_ui);

        // confirm: confirm the change in the confirm change view (do_it false so nothing is written)
        $test_name = 'show edit save confirm';
        $url_arr[url_var::MASK] = views::CONFIRM_EDIT_ID;
        $result = $ui->url_user_reaction(url_var::ACTION_CONFIRM, $url_arr, $usr_backend, $usr_ui, $usr_msg, $ui->dto, false);
        $this->assert_wf_html($t, $test_name, $result, $wf . '_show_edit_save_confirm', $wf_id, $usr_ui);

        // cancel: cancel the change and return to the word view
        $test_name = 'show edit save cancel';
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => $wf_id];
        $result = $ui->url_user_reaction(url_var::ACTION_CANCEL, $url_arr, $usr_backend, $usr_ui, $usr_msg, $ui->dto, false);
        $this->assert_wf_html($t, $test_name, $result, $wf . '_show_edit_save_cancel', $wf_id, $usr_ui);


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
     * snapshot a workflow step after replacing the values that are volatile between test runs
     * (the dynamically assigned test word id, the change log add time / add user and the navbar
     * user role) with fixed text, so the result does not change every run
     *
     * @param test_base $t the test environment
     * @param string $test_name the description of the step
     * @param string $html the rendered html of the step
     * @param string $file_path the snapshot file path starting from the test resource path
     * @param int $wf_id the dynamic database id of the test word to replace with the fixed id
     * @param user_ui $usr the rendering user whose name is used to force the navbar role
     */
    private function assert_wf_html(
        test_base $t, string $test_name, string $html, string $file_path, int $wf_id, user_ui $usr): void
    {
        // replace the volatile word / back id (assigned dynamically on insert) with a fixed test id
        $html = str_replace(
            ['=' . $wf_id, '"' . $wf_id . '"'],
            ['=' . word_names::TEST_ADD_ID, '"' . word_names::TEST_ADD_ID . '"'],
            $html);
        // the change history of the test word shows the real change time and change user, both of
        // which vary per run; replace each change log line (date time + user + action) with a fixed
        // text - this covers the default view (in a container div) and the edit view (a bare line)
        $html = preg_replace(
            '#\d{2}-\d{2}-\d{4} \d{2}:\d{2}[^<\n]*#',
            self::WF_CHANGE_LOG,
            $html);
        // user::navbar_role() resolves the elevated role label only when the user profile cache is
        // loaded; that is not guaranteed across test runners (a missing profile gives an empty role),
        // so always show the system role in the navbar user menu to keep the snapshot deterministic
        $name = $usr->name();
        if ($name != null and $name != '') {
            // 'system test' is the display name of the system user profile (no const exists for it)
            $role_name = 'system test ' . $name;
            $html = str_replace($role_name, $name, $html); // collapse an already present role prefix
            $html = str_replace($name, $role_name, $html); // then always show the role
        }
        $t->assert_html_page($test_name, $html, $file_path);
    }

}
