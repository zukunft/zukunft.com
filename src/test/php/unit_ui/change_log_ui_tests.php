<?php

/*

    test/unit/html/change_log.php - testing of the change log display functions
    -----------------------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;


use Zukunft\ZukunftCom\main\php\cfg\log\change_log_link_list as change_log_link_list_cfg;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_link_list as change_log_link_list_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class change_log_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_log = new test_log($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui change log ';
        $t->header($ts);

        $t->subheader($ts . 'display');

        //$wrd_pi = new word_dsp(2, words::TN_CONST);
        $test_page = $html->text_h2('Change log display test');

        // prepare test data
        $url_arr = [];
        $api_typ_lst = new api_type_list([api_types::TEST_MODE]);

        $test_page .= '<br>changes as a text<br>';
        $chg = $t_log->log_word_add();
        $chg_ui = new change_log_named($chg->api_json());
        $test_page .= $chg_ui->dsp() .  '<br>';

        // a change in the user sandbox is prefixed with a translatable 'user' before the action
        // also in the changes tab text (see change_log_named::entry / action_txt)
        $chg_usr_ui = new change_log_named($t_log->log_word_add_view()->api_json());
        $chg_usr_txt = $chg_usr_ui->dsp(true);
        $test_page .= $chg_usr_txt . '<br>';
        $test_name = 'the changes tab prefixes a user sandbox change with user';
        $t->assert_text_contains($test_name, $chg_usr_txt, 'added user "' . views::WORD_NAME . '"');

        // adding an empty value in the user sandbox removes the user's overwrite for that field, so the
        // changes tab shows 'remove user overwrite for view' instead of '... added user ""'
        $chg_rem_ui = new change_log_named($t_log->log_word_remove_view()->api_json());
        $chg_rem_txt = $chg_rem_ui->dsp(true);
        $test_page .= $chg_rem_txt . '<br>';
        $test_name = 'the changes tab shows the remove user overwrite text for an empty sandbox change';
        $t->assert_text_contains($test_name, $chg_rem_txt, 'remove user overwrite for view');


        $test_page .= '<br>simple list of changes of a word<br>';
        $log_lst = $t_log->log_list_named();
        $log_ui = new change_log_list($log_lst->api_json($api_typ_lst));
        $test_page .= $log_ui->tbl($url_arr);

        $test_page .= '<br>condensed list of changes of a word<br>';
        $log_lst = $t_log->log_list_named();
        $log_ui = new change_log_list($log_lst->api_json($api_typ_lst));
        $test_page .= $log_ui->tbl($url_arr, true, true);

        // the tr changes table (tbl) also shows 'remove user overwrite for view' in the field column
        // when a user sandbox change adds an empty value (see change_log_named::tr)
        $test_page .= '<br>changes table with a removed user overwrite<br>';
        $log_rem_ui = new change_log_list($t_log->log_list_word_changes()->api_json($api_typ_lst));
        $rem_tbl = $log_rem_ui->tbl($url_arr);
        $test_page .= $rem_tbl;
        $test_name = 'the tr changes table shows the remove user overwrite text';
        $t->assert_text_contains($test_name, $rem_tbl, 'remove user overwrite for view');

        // the condensed changes table also shows 'remove user overwrite for view' (without a trailing
        // ': ' because there is no value to show, see change_log_named::tr)
        $test_page .= '<br>condensed changes table with a removed user overwrite<br>';
        $rem_tbl_cond = $log_rem_ui->tbl($url_arr, true, true);
        $test_page .= $rem_tbl_cond;
        $test_name = 'the condensed changes table shows the remove user overwrite text';
        $t->assert_text_contains($test_name, $rem_tbl_cond, 'remove user overwrite for view');

        $t->html_page_test($test_page, 'change_log', 'change_log', $msg, $base_url, $lan);

        $t->subheader($ts . 'filter and limit');

        // regression: the api change entries carry no own id (all id 0), so the default id-dedup of
        // add() must not collapse filter()/head() to a single change row (see change_log_list)
        $log_ui = new change_log_list($t_log->log_list_named()->api_json($api_typ_lst));

        $test_name = 'change_log_list->head keeps more than one change despite id 0';
        $t->assert_true($test_name, $log_ui->head(10)->count() > 1);

        $test_name = 'change_log_list->head limits to the requested number of changes';
        $t->assert($test_name, $log_ui->head(2)->count(), 2);

        // filter the changes of the test word: all word changes share the same row_id and table,
        // so several rows must remain (one per change), not be merged into one
        $wrd = new word();
        $wrd->set_id(word_names::MATH_ID);
        $test_name = 'change_log_list->filter keeps all changes of a word despite id 0';
        $t->assert_true($test_name, $log_ui->filter($wrd)->count() > 1);

        $t->subheader($ts . 'sort');

        // two same-second changes whose alphabetical what order equals the write order,
        // so only the change id can restore the newest first order after the sort
        $test_name = 'test setup: the alphabetical what order equals the write order';
        $log_ui = new change_log_list($t_log->log_list_same_second()->api_json($api_typ_lst));
        $chg_lst = $log_ui->lst();
        $first = $chg_lst[0];
        $last = end($chg_lst);
        $first_what = $first->what_text();
        $last_what = $last->what_text();
        $t->assert_true($test_name, strcmp($first_what, $last_what) < 0);
        $test_name = 'the change id reaches the frontend via the api json';
        $t->assert_true($test_name, $last->id() > $first->id() and $first->id() > 0);
        $test_name = 'same-second changes show the last written change first via the change id';
        $log_ui->sort_by_time_and_what();
        $t->assert($test_name, $log_ui->lst()[0]->what_text(), $last_what);

        // the newer change comes first even if it has the lower change id
        $test_name = 'the change time stays the first sort key before the change id';
        $log_ui = new change_log_list($t_log->log_list_second_apart()->api_json($api_typ_lst));
        $newer_what = $log_ui->lst()[0]->what_text();
        $log_ui->sort_by_time_and_what();
        $t->assert($test_name, $log_ui->lst()[0]->what_text(), $newer_what);

        // an api message from before the change id was added falls back to the what text order
        $test_name = 'same-second changes without a change id fall back to the what text order';
        $log_no_id_ui = $t_log->log_list_same_second_no_id_ui();
        $log_no_id_ui->sort_by_time_and_what();
        $t->assert($test_name, $log_no_id_ui->lst()[0]->what_text(), $first_what);

        // link change history rendering (e.g. the triples added to or removed from a word)
        // the link list classes are loaded here, not at the top of the file, because the frontend
        // change_log_link extends change_log_named which sits at the root of the bootstrap include chain
        include_once paths::MODEL_LOG . 'change_log_link_list.php';
        include_once html_paths::LOG . 'change_log_link_list.php';

        $t->subheader($ts . 'link changes');
        // round-trip a backend link change through the api to the frontend and render it
        $cl_lst = new change_log_link_list_cfg();
        $cl = $t_log->log_link();
        $cl->new_text_to = word_names::MATH;
        $cl_lst->add($cl);
        $log_link_ui = new change_log_link_list_ui($cl_lst->api_json($api_typ_lst));
        $test_name = 'a link change is shown as a link to the new target';
        $t->assert_text_contains($test_name, $log_link_ui->tbl($url_arr), word_names::MATH);
        $test_name = 'an empty link change list renders no table row';
        $t->assert_text_not_contains($test_name, new change_log_link_list_ui()->tbl($url_arr), '<tr');
    }

}