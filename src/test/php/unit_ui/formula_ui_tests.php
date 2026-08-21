<?php

/*

    test/unit/html/formula.php - testing of the html frontend functions for formulas
    --------------------------
  

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

use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::LOG . 'change_log_list.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link as formula_link_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_frm = new test_formulas($t);
        $msg = new user_message();
        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::FORMULA_ID, url_var::ID => formula_names::INCREASE_ID];

        // start the test section (ts)
        $ts = 'unit ui html formula ';
        $t->header($ts);

        $frm = new formula($t_frm->formula()->api_json());
        $test_page = $html->text_h2('formula display test');
        $test_page .= 'with tooltip: ' . $frm->name_tip() . '<br>';
        $test_page .= 'with link: ' . $frm->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $frm->btn_add($url_arr, $base_url) . '<br>';
        $test_page .= 'edit button: ' . $frm->btn_edit($url_arr, $base_url) . '<br>';
        $test_page .= 'del button: ' . $frm->btn_del($url_arr, $base_url) . '<br>';
        $test_page .= $t->dsp_title_named_edit($frm, $msg);

        // the formula page title shows the formula name with its assigned phrases as subtitle,
        // e.g. "increase" with the assigned "year" phrase
        $frm_increase = $t_frm->formula_increase_ui();
        $test_page .= $t->dsp_title_formula($frm_increase, $msg);

        // the expression in latex format with a tooltip and a link for each term, e.g. the
        // "definition of joule" formula joule = ( kg * metre * metre ) / ( second * second )
        $frm_joule = $t_frm->formula_joule_ui();
        $test_page .= $html->text_h2('expression in latex format with term links');
        $test_page .= 'latex with links: ' . $frm_joule->expression_latex_link() . '<br>';

        // the increase formula expression in latex format with a tooltip and a link for each
        // term (percent, this and prior)
        $frm_increase_linked = $t_frm->formula_increase_ui(true);
        $test_page .= $html->text_h2('increase expression in latex format with term links');
        $test_page .= 'latex with links: ' . $frm_increase_linked->expression_latex_link() . '<br>';

        // expression_latex shows the same expression in latex format without the term links,
        // e.g. the increase formula "percent = ( this - prior ) / prior"
        $test_page .= $html->text_h2('increase expression in latex format without term links');
        $test_page .= 'latex without links: ' . $frm_increase->expression_latex() . '<br>';

        // the changes of the increase formula as a table, e.g. the name and expression added
        $t_log = new test_log($t);
        $back = new back_trace();
        $api_typ_lst = new api_type_list([api_types::TEST_MODE]);
        $log_lst = new change_log_list($t_log->log_list_formula_increase()->api_json($api_typ_lst));
        $test_page .= $html->text_h2('changes of the formula increase');
        $test_page .= $log_lst->tbl($back);

        // the results of the increase formula as a table of the result phrases and the value
        $t_res = new test_results($t);
        $list = new ui_list();
        $res_cfg = new data_object();
        $res_cfg->set_result_list(new result_list(
            $t_res->result_list()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES])));
        $test_page .= $html->text_h2('results of the formula increase');
        $test_page .= $list->results_related($frm_increase, $res_cfg);

        // the assigned-phrases component shows only the phrases the formula is assigned to (the
        // "year" carried by the increase formula), never the full phrase list; test_mode true so
        // the assigned list carried by the formula is used without an api reload
        $assigned = $list->phrases_of_formula($frm_increase, $msg, null, true);
        // building the assigned phrases list reads and writes to the database, so a db timeout is used
        $test_name = 'assigned phrases of the increase formula show the assigned "year"';
        $t->assert_text_contains($test_name, $assigned, words::YEAR_CAP, $t::TIMEOUT_LIMIT_DB);
        $test_name = 'assigned phrases of the increase formula exclude a not assigned phrase';
        $t->assert_text_not_contains($test_name, $assigned, words::PERCENT);
        $test_page .= $html->text_h2('assigned phrases of the formula increase');
        $test_page .= $assigned;

        // the values of the phrases used by the increase formula shown as a table,
        // e.g. the inhabitants of the regions that the increase is calculated for
        $t_val = new test_values($t);
        $test_page .= $html->text_h2('values of the phrases used for the formula increase');
        $test_page .= $t_val->value_list_zh_ui()->table($msg);

        $t->html_page_test($test_page, 'formula', 'formula', $msg, $base_url, $lan);

        $t->subheader($ts . 'view tab box');

        // the formula default view shows the same tab box as the word default view (see
        // base_views.json formula_default): a views tab with the views that can show the formula,
        // a changes tab with its change log and a my tab with the user overwrites of the session
        // user; the backend fills the three lists under the INCL_RELATED flag, so this block first
        // checks the api round trip and then the rendering of the tabs
        global $ui_sys;
        global $mtr;
        $t_msk = new test_views($t);
        $t_usr = new test_users();
        $frm_related = $t_frm->formula_increase();
        $frm_related->views_related = $t_msk->view_list_word();
        $frm_related->changes_related = $t_log->log_list_formula_increase();
        // test mode so the backend emits the two given lists without loading them from the database
        $frm_json = json_decode($frm_related->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]), true);

        $test_name = 'the views of a formula are sent to the frontend';
        $t->assert_true($test_name, ($frm_json[json_fields::VIEWS] ?? []) != []);
        $test_name = 'the changes of a formula are sent to the frontend';
        $t->assert_true($test_name, ($frm_json[json_fields::CHANGES] ?? []) != []);

        // the overwrites are read from the user sandbox table, which the test mode skips, so the
        // 'my' and 'others' rows are added here like on the word and the triple page
        $frm_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => fields::FLD_DESCRIPTION,
                json_fields::USR_VALUE => 'my formula description',
                json_fields::STD_VALUE => 'the standard formula description',
            ],
        ];
        $frm_tab = new formula(json_encode($frm_json));

        $test_name = 'the views of a formula reach the frontend formula object';
        $t->assert_true($test_name, $frm_tab->view_lst != null and !$frm_tab->view_lst->is_empty());
        $test_name = 'the changes of a formula reach the frontend formula object';
        $t->assert_true($test_name, $frm_tab->chg_log != null and !$frm_tab->chg_log->is_empty());

        $views_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS)) . '"';
        $log_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"';
        $my_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"';
        $usr_tab_keep = $ui_sys->usr ?? null;
        // the user comes from the factory, because the my tab is only shown to a user with an id
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $tab_html = $list->view_tab_box($frm_tab, $msg, true);

        $test_name = 'the formula page shows the views tab';
        $t->assert_text_contains($test_name, $tab_html, $views_tab_ref);
        $test_name = '... with the name of a view that can show the formula';
        $t->assert_text_contains($test_name, $tab_html, views::SCIENCE);
        // the switch button must open the edit view of the shown object, so on a formula page the
        // formula edit view and never the word edit view (see view::switch_link)
        $test_name = '... and a switch button that opens the formula edit view';
        $t->assert_text_contains($test_name, $tab_html,
            url_var::MASK . '=' . views::FORMULA_EDIT_ID
            . '&amp;' . url_var::ID . '=' . formula_names::INCREASE_ID);

        $test_name = 'the formula page shows the changes tab';
        $t->assert_text_contains($test_name, $tab_html, $log_tab_ref);
        $test_name = '... with the change that added the formula';
        $t->assert_text_contains($test_name, $tab_html, formula_names::INCREASE);

        $test_name = 'the user with formula overwrites sees the my tab';
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);
        $test_name = '... with the user value and the standard value of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, 'my formula description');
        $t->assert_text_contains($test_name, $tab_html, 'the standard formula description');

        // a formula loaded without the related data has neither a views nor a my tab
        $frm_plain = new formula($t_frm->formula_increase()->api_json());
        $plain_html = $list->view_tab_box($frm_plain, $msg, true);
        $test_name = 'a formula without views shows no views tab';
        $t->assert_text_not_contains($test_name, $plain_html, $views_tab_ref);
        $test_name = 'a formula without overwrites shows no my tab';
        $t->assert_text_not_contains($test_name, $plain_html, $my_tab_ref);

        $test_name = 'without a logged in user the formula page shows no my tab';
        unset($ui_sys->usr);
        $t->assert_text_not_contains($test_name, $list->view_tab_box($frm_tab, $msg, true), $my_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_tab_keep;
        }

        $t->subheader($ts . 'link title');

        // the formula link default page shows the generated link name as the page title with the
        // linked formula and phrase as links in the subtitle (see base_views.json
        // formula_link_default); a page request (INCL_RELATED) carries the names of the linked
        // objects, so that the subtitle links have a text and not only a target
        $lnk = new formula_link_ui($t_frm->formula_link_filled_included()->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]));
        $sfm = new system_form();
        $ttl_html = $sfm->title_link($lnk, $msg);
        $test_name = 'the formula link title names the linked formula';
        $t->assert_text_contains($test_name, $ttl_html, formula_names::SCALE_TO_SEC);
        $test_name = '... and the linked phrase in the subtitle';
        $t->assert_text_contains($test_name, $ttl_html, word_names::MINUTE);
        $test_name = 'the formula link title links to the formula link edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::FORMULA_LINK_EDIT_ID);
        $test_name = 'the formula link title has a subtitle for the share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);

        // the page url carries only the ids of the linked objects, so the names of the subtitle
        // links come from the request cache
        $test_name = 'the formula link title of a page url names the linked objects';
        $lnk_url = new formula_link_ui();
        $lnk_url->url_mapper([
            url_var::FORMULA => (string)formula_names::SCALE_TO_SEC_ID,
            url_var::PHRASE => (string)word_names::MINUTE_ID
        ], $msg, $ui_sys);
        $url_html = $sfm->title_link($lnk_url, $msg);
        $t->assert_text_contains($test_name, $url_html, formula_names::SCALE_TO_SEC);
        $t->assert_text_contains($test_name . ' and the phrase', $url_html, word_names::MINUTE);

        // a fresh formula link of an add form has no share and protection and no linked objects
        // with names, so no empty subtitle brackets are shown
        $test_name = 'a fresh formula link shows no subtitle';
        $lnk_new = new formula_link_ui();
        $t->assert_text_not_contains($test_name, $sfm->title_link($lnk_new, $msg), styles::SUBTITLE);
        // ... and an empty name, never a 'objects not set' placeholder as the page title
        $test_name = 'a fresh formula link has an empty name';
        $t->assert($test_name, $lnk_new->name(), '');

        // TODO review

        /*
        $ts = 'unit ui html formula user ';
        $t->header($ts);

        // load the main test word
        $wrd_company = $t->test_word(words::TN_COMPANY);

        // call the add formula page and check if at least some keywords are returned
        $frm = $t->load_formula(formulas::TN_INCREASE);
        $result = file_get_contents('https://zukunft.com/http/formula_add.php?word=' . $wrd_company->id() . '&back=' . $wrd_company->id() . '');
        $target = 'Add new formula for';
        $t->dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the headline', $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
        $target = words::TN_COMPANY;
        $t->dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the linked word ' . words::TN_COMPANY, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test the edit formula frontend
        $result = file_get_contents('https://zukunft.com/http/formula_edit.php?id=' . $frm->id() . '&back=' . $wrd_company->id());
        $target = formulas::TN_INCREASE;
        $t->dsp_contains(', frontend formula_edit.php ' . $result . ' contains at least ' . $frm->name(), $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test the del formula frontend
        $result = file_get_contents('https://zukunft.com/http/formula_del.php?id=' . $frm->id() . '&back=' . $wrd_company->id());
        $target = formulas::TN_INCREASE;
        $t->dsp_contains(', frontend formula_del.php ' . $result . ' contains at least ' . $frm->name(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);
        */

    }

}