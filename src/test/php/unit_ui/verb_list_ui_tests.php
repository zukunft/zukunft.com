<?php

/*

    test/unit/html/verb_list.php - testing of the verb list html frontend functions
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::VERB . 'verb_list.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list as verb_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_vrb = new test_verbs($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui html verb list ';
        $t->header($ts);

        // fill the frontend verb list based on the api message of the backend list
        $test_name = 'verbs of the api message';
        $db_lst = $t_vrb->list_short();
        $lst = new verb_list_ui();
        $lst->set_from_json($db_lst->api_json(), $msg);
        $t->assert($test_name, $lst->db_id_list(), $db_lst->db_id_list());
        $msg->reset();

        $test_name = 'a verb of a second api message is reported as double';
        $lst->set_from_json($db_lst->api_json(), $msg);
        $t->assert_msg_false($test_name, $msg, '"' . verbs::IS_NAME . '"');

        $test_name = 'a verb of a second api message is not added twice';
        $t->assert($test_name, $lst->db_id_list(), $db_lst->db_id_list());

        // a list can hold the same verb more than once e.g. to count the verb usages of a triple list
        $test_name = 'a verb of a second api message is added twice if duplicates are allowed';
        $lst_dbl = new verb_list_ui();
        $lst_dbl->set_from_json($db_lst->api_json(), $msg, true);
        $lst_dbl->set_from_json($db_lst->api_json(), $msg, true);
        $t->assert($test_name, count($lst_dbl->lst()), $db_lst->count() * 2);

        $test_name = 'a double verb is not reported if duplicates are allowed';
        $t->assert_empty($test_name, $msg->text());
        $msg->reset();

        // a type is picked by its id e.g. for each phrase of a table, so the list keeps the
        // position of each id instead of scanning all entries
        $test_name = 'a verb of the list is found by its id';
        $vrb = $db_lst->lst()[1];
        $t->assert($test_name, $lst->get($vrb->id())?->name(), $vrb->name());
        $test_name = '... and of a list with the verbs twice the first entry';
        $t->assert_true($test_name, $lst_dbl->get($vrb->id()) === $lst_dbl->lst()[1]);
        // negative: a type id that is not set selects no verb
        $test_name = 'a type id that is not set selects no verb';
        $t->assert_true($test_name, $lst->get(0) === null);

        $test_name = 'an empty api message adds no verb';
        $lst_empty = new verb_list_ui();
        $lst_empty->set_from_json($t_vrb->list_empty()->api_json(), $msg);
        $t->assert($test_name, $lst_empty->db_id_list(), []);
        $msg->reset();

        // every case of a type list: empty, one entry, the same entry twice and a code id of two
        // rows; the last one is an inconsistency of the backend that add_obj logs as an error, so
        // it is tested by the check alone, because a test must not write an error log entry
        $t->subheader($ts . 'list cases');
        $test_name = 'an empty list finds no verb by its code id';
        $t->assert($test_name, $lst_empty->id(verbs::IS), type_list::CODE_ID_NOT_FOUND);
        $test_name = '... and has no code id conflict';
        $t->assert_false($test_name, $lst_empty->code_id_conflict(verbs::IS, verbs::IS_ID));
        $lst_one = new verb_list_ui();
        $lst_one->set_from_json($t_vrb->list_one()->api_json(), $msg);
        $test_name = 'a list with one verb adds it without a message';
        $t->assert_empty($test_name, $msg->text());
        $test_name = '... finds it by its id';
        $t->assert($test_name, $lst_one->get(verbs::IS_ID)?->name(), verbs::IS_NAME);
        $test_name = '... and by its code id';
        $t->assert($test_name, $lst_one->id(verbs::IS), verbs::IS_ID);
        $test_name = '... and the same verb again is no code id conflict';
        $t->assert_false($test_name, $lst_one->code_id_conflict(verbs::IS, verbs::IS_ID));
        // negative: the code id of the verb for another row is the inconsistency
        $test_name = '... but its code id for another id is a conflict';
        $t->assert_true($test_name, $lst_one->code_id_conflict(verbs::IS, verbs::MEASURE_ID));
        $test_name = '... and an empty code id is never a conflict';
        $t->assert_false($test_name, $lst_one->code_id_conflict('', verbs::MEASURE_ID));
        $test_name = 'a list with every verb twice finds a verb by its code id';
        $t->assert($test_name, $lst_dbl->id(verbs::IS), verbs::IS_ID);
        $test_name = '... and has no code id conflict for the same verb';
        $t->assert_false($test_name, $lst_dbl->code_id_conflict(verbs::IS, verbs::IS_ID));

        $t->subheader($ts . 'debug id');

        // the type list writes an empty debug text for every list
        // TODO Prio 2 check the names once web type_list::dsp_id lists them
        //      (see docs/llm/pending_prio_2.md, "dsp id", web type_list)
        $test_name = 'is empty for a list with one verb';
        $dsp = $lst_one->dsp_id();
        $t->assert_empty($test_name, $dsp);

        // negative: the debug text lists no verb of the list
        $test_name = 'shows no verb name';
        $dsp = $lst_one->dsp_id();
        $t->assert_text_not_contains($test_name, $dsp, verbs::IS_NAME);

        // test the verb list display functions
        $form = 'verb_list_ui_test';
        $test_page = $html->text_h2('verb list display test');
        $test_page .= $lst->list(verb_ui::class, 'Verbs');
        $test_page .= 'link types: ' . '<br>' . $lst->dsp_list() . '<br>';

        // the three versions of a list, once with the description as tooltip and once with links
        // $lst has less entries than the short version shows, $lst_all more than the more version
        $lst_all = new verb_list_ui();
        $lst_all->set_from_json($t_vrb->list_all()->api_json(), $msg);
        $msg->reset();
        $test_page .= $html->text_h2('list with tooltips');
        $test_page .= $html->text_h3('short');
        $test_page .= $html->text_h4('below the limit');
        $test_page .= $lst->name_tip() . '<br>';
        $test_page .= $html->text_h4('above the limit');
        $test_page .= $lst_all->name_tip() . '<br>';
        $test_page .= $html->text_h3('more');
        $test_page .= $html->text_h4('below the limit');
        $test_page .= $lst->name_tip(shared_config::LIMIT_MORE_LIST) . '<br>';
        $test_page .= $html->text_h4('above the limit');
        $test_page .= $lst_all->name_tip(shared_config::LIMIT_MORE_LIST) . '<br>';
        $test_page .= $html->text_h3('all');
        $test_page .= $html->text_h4('below the limit');
        $test_page .= $lst->name_tip(type_list::LIMIT_ALL) . '<br>';
        $test_page .= $html->text_h4('above the limit');
        $test_page .= $lst_all->name_tip(type_list::LIMIT_ALL) . '<br>';
        $test_page .= $html->text_h2('list with links');
        $test_page .= $html->text_h3('short');
        $test_page .= $html->text_h4('below the limit');
        $test_page .= $lst->name_link(shared_config::LIMIT_SHORT_LIST, $base_url) . '<br>';
        $test_page .= $html->text_h4('above the limit');
        $test_page .= $lst_all->name_link(shared_config::LIMIT_SHORT_LIST, $base_url) . '<br>';
        $test_page .= $html->text_h3('more');
        $test_page .= $html->text_h4('below the limit');
        $test_page .= $lst->name_link(shared_config::LIMIT_MORE_LIST, $base_url) . '<br>';
        $test_page .= $html->text_h4('above the limit');
        $test_page .= $lst_all->name_link(shared_config::LIMIT_MORE_LIST, $base_url) . '<br>';
        $test_page .= $html->text_h3('all');
        $test_page .= $html->text_h4('below the limit');
        $test_page .= $lst->name_link(type_list::LIMIT_ALL, $base_url) . '<br>';
        $test_page .= $html->text_h4('above the limit');
        $test_page .= $lst_all->name_link(type_list::LIMIT_ALL, $base_url) . '<br>';

        $from_rows = 'selector: ' . '<br>';
        $from_rows .= $lst->type_selector($form, verbs::IS_ID, url_var::VERB, msg_id::FORM_SELECT_VERB) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        $t->html_page_test($test_page, 'verb_list', 'verb_list', $msg, $base_url, $lan);
    }

}
