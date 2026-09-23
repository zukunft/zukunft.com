<?php

/*

    test/unit/html/result.php - testing of the html frontend functions for result
    ------------------------
  

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

include_once paths::SHARED_TYPES . 'api_types.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_base;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class result_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $t_res = new test_results($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html result ';
        $t->header($ts);

        $api_json = $t_res->result_simple()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $res = new result($api_json);
        $test_page = $html->text_h2('result display test');
        $test_page .= 'with tooltip: ' . $res->display() . '<br>';
        $test_page .= 'with link: ' . $res->display_linked() . '<br>';
        $test_page .= $t->dsp_title_named_edit($res, $msg);
        $t->html_page_test($test_page, 'result', 'result', $msg);

        // the result default page names the result by its phrases and the calculated number, the
        // same way the value default page does (see system_views.json result_default)
        $t->subheader($ts . 'page title');

        $res_page = $t_res->result_page_related_ui();
        $link_start = '<' . html_base::A . ' ' . html_base::HREF;

        $test_name = 'the name link of a result shows the phrases as links and the number';
        $t->assert_text_order($test_name, $res_page->name_link($msg),
            $link_start, $res_page->val_formatted($msg));
        // negative: a result without phrases has no link to show, but still no php warning
        $test_name = 'the name link of an empty result has no phrase link';
        $t->assert_text_not_contains($test_name, new result()->name_link($msg), $link_start);

        $test_name = 'the result page title shows the phrases and the number';
        $ttl_html = new system_form()->title_value($res_page, $msg);
        $t->assert_text_contains($test_name, $ttl_html, $res_page->val_formatted($msg));
        $test_name = '... with the link to change the result';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::RESULT_EDIT_ID);

        // below the title the page shows in three columns what the number is based on
        $t->subheader($ts . 'used for the calculation');

        $lst_ui = new ui_list();

        $test_name = 'the values used for the calculation are listed with their phrases';
        $val_html = $lst_ui->values_used($res_page, $msg);
        $t->assert_text_contains($test_name, $val_html, triple_names::E);

        $test_name = 'the formulas used for the calculation are listed';
        $frm_html = $lst_ui->formulas_used($res_page, $msg);
        $t->assert_text_contains($test_name, $frm_html, formula_names::INCREASE);
        // the formula of the result itself is named in the title, so it is not repeated here
        $test_name = '... without the formula that has calculated the result';
        $t->assert_text_not_contains($test_name, $frm_html, formula_names::SCALE_TO_SEC);

        // the table of the used results shows the phrase and the number of each used result,
        // like the results column of the value page (see result_list::table)
        $test_name = 'the results used for the calculation are listed with their phrase and number';
        $res_html = $lst_ui->results_used($res_page, $msg);
        $t->assert_text_order($test_name, $res_html, word_names::MATH, (string)results::TV_INT);

        // negative: an empty list says so instead of showing nothing or an empty table
        $res_none = $t_res->result_page_related_ui();
        $res_none->values_used = new value_list();
        $res_none->formulas_used = new formula_list();
        $res_none->results_used = new result_list();
        $test_name = 'a calculation without a used value says so';
        $t->assert($test_name, $lst_ui->values_used($res_none, $msg), $mtr->txt(msg_id::INFO_NO_VALUES_USED));
        $test_name = 'a calculation without another used formula says so';
        $t->assert($test_name, $lst_ui->formulas_used($res_none, $msg), $mtr->txt(msg_id::INFO_NO_FORMULAS_USED));
        $test_name = 'a calculation without another used result says so';
        $t->assert($test_name, $lst_ui->results_used($res_none, $msg), $mtr->txt(msg_id::INFO_NO_RESULTS_USED));

        // beside the columns the result page shows the change log and the user overwrites in the
        // tab box, which the api sends like for a value (see result::api_json_array)
        $t->subheader($ts . 'view tab box');

        $t_log = new test_log($t);
        $res_related = $t_res->result_main_max();
        $res_related->changes_related = $t_log->log_list_result();
        // test mode so the backend emits the given change list without loading it from the database
        $res_json = json_decode($res_related->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED, api_types::INCL_PHRASES]), true);

        $test_name = 'the changes of a result are sent to the frontend';
        $t->assert_true($test_name, ($res_json[json_fields::CHANGES] ?? []) != []);

        // the overwrites are read from the user sandbox table, which the test mode skips, so the
        // 'my' row is added here like on the value page
        $res_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => change_fields::FLD_NUMERIC_VALUE,
                json_fields::USR_VALUE => (string)results::TV_PCT,
                json_fields::STD_VALUE => (string)results::TV_INT,
            ],
        ];
        $res_tab = new result(json_encode($res_json));

        $test_name = 'the changes of a result reach the frontend result object';
        $t->assert_true($test_name, $res_tab->chg_log != null and !$res_tab->chg_log->is_empty());
        $test_name = 'the result page shows the changes tab';
        // the my tab is only shown to a user with an id, so the session user comes from the factory
        $usr_keep = $ui_sys->usr ?? null;
        $ui_sys->usr = new user_ui(new test_users()->user_sys_normal()->api_json());
        $tab_html = $lst_ui->view_tab_box($res_tab, $msg, true);
        $t->assert_text_contains($test_name, $tab_html,
            'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"');
        $test_name = '... and the tab with the overwrites of the session user';
        $t->assert_text_contains($test_name, $tab_html,
            'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"');
        // negative: a result that is not yet stored cannot have a change log, so it has no changes
        // tab; a stored result keeps the tab even if the log is empty here, because an empty log
        // can also mean that it has not been loaded (see ui_log::change_log_table_pure)
        $test_name = 'a result that does not exist yet shows no changes tab';
        $t->assert_text_not_contains($test_name, $lst_ui->view_tab_box(new result(), $msg, true),
            'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"');

        // restore the session user for the following tests
        if ($usr_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_keep;
        }

        $t->subheader($ts . 'format');

        $test_name = 'big numbers use the user config thousand separator';
        $t->assert($test_name, $res->val_formatted($msg), "123'456");

        $test_name = 'percent values use the user config percent decimals';
        $api_json = $t_res->result_pct()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $res = new result($api_json);
        $t->assert($test_name, $res->val_formatted($msg), '1.23%');

        $test_name = 'a missing number returns an empty text';
        $res = new result();
        $t->assert($test_name, $res->val_formatted($msg), '');
        $test_name = '... also as the numeric value component of a view, which would else stop the page';
        $t->assert($test_name, new ui_base()->num_value($msg, $res), '');
        $test_name = 'the number of the url is mapped even after an unrelated earlier error of the request';
        $err_msg = new user_message();
        $err_msg->add_message(msg_id::RESET_MAIL_SENT->value);
        $res_map = new result();
        $res_map->url_mapper([url_var::ID => 1, url_var::NUMERIC_VALUE => '5'], $err_msg);
        $t->assert($test_name, $res_map->number(), 5.0);
        $test_name = '... but not if the mapping of the result itself fails e.g. because the id is missing';
        $res_no_id = new result();
        $res_no_id->url_mapper([url_var::NUMERIC_VALUE => '5'], new user_message());
        $t->assert_true($test_name, $res_no_id->number() === null);
        $test_name = '... which shows a given number';
        $api_json = $t_res->result_simple()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $t->assert_true($test_name, new ui_base()->num_value($msg, new result($api_json)) != '');

        // the overwrite form writes the changed number of the result without the confirm view
        $t->subheader($ts . 'value overwrite');
        $test_name = 'the result overwrite form shows the number field';
        $form = new system_form();
        $api_json = $t_res->result_simple()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $res = new result($api_json);
        $res_id = (string)$res->id();
        $res_html = $form->form_value_overwrite($res);
        $t->assert_text_contains($test_name, $res_html, 'name="' . url_var::NUMERIC_VALUE . '"');
        $test_name = '... writes via the result edit mask';
        $t->assert_text_contains($test_name, $res_html, $html->form_hidden(url_var::MASK, (string)views::RESULT_EDIT_ID));
        $test_name = '... for the shown result';
        $t->assert_text_contains($test_name, $res_html, $html->form_hidden(url_var::ID, $res_id));
        $test_name = '... without the confirm view';
        $t->assert_text_contains($test_name, $res_html, $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED));
        $test_name = '... shows the result with its default view';
        $res_back = $html->form_hidden(url_var::BACK . url_var::MASK, (string)views::RESULT_ID);
        $t->assert_text_contains($test_name, $res_html, $res_back);
    }

}