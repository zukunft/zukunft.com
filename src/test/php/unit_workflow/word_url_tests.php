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
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_base;

class word_url_tests
{

    function run(test_base $t): void
    {

        // init
        global $mtr;
        $usr_msg = new user_message();
        global $sys;
        $ui = new frontend('view');
        $ui->load_cache();
        // the html renderers read the type cache from the global $ui_sys (e.g. ref->type_name);
        // point it at the just loaded frontend cache so the workflow render does not crash
        global $ui_sys;
        if ($ui_sys?->typ_lst_cache == null) {
            $ui_sys = $ui->dto;
        }
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
        $t->assert_text_contains($test_name, $result, word_names::MATH);

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

}
