<?php

/*

    test/unit/html/value.php - testing of the html frontend functions for value
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

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::CREATE . 'test_phrases.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\fields\value_fields;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class value_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $t_val = new test_values($t);
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $tl = new test_lib();
        $msg = new user_message();
        $msg_ui = new user_message_ui();

        // set once at the start for all pages of this test: the pod url for the styles
        // and the language of the page; the called functions only pass them through
        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui value ';
        $t->header($ts);

        $t->subheader($ts . 'html');

        $val = new value($t_val->value($msg)->api_json([api_types::INCL_PHRASES]));
        $test_page = $html->text_h2('value display test');
        $test_page .= 'with name and tooltip: ' . $val->name_tip($msg_ui) . '<br>';
        $test_page .= 'with name and link: ' . $val->name_link($msg_ui) . '<br>';
        $test_page .= 'with tooltip: ' . $val->value($msg_ui) . '<br>';
        $test_page .= 'with detail link: ' . $val->value_link($msg_ui) . '<br>';
        $test_page .= 'with edit link: ' . $val->value_edit($msg_ui) . '<br>';
        // the calling page (e.g. the default word view) is passed as the return path and the
        // page phrases are excluded from the name, like in a table where the page phrase is
        // the table context and the measure is shown behind the number
        $val_zh = $t_val->people_zh_canton_mio_ui();
        $test_page .= 'with linked names and separate measure: ' . $val_zh->links_and_measure($msg_ui, $url_arr) . '<br>';
        $wrd_canton = $t_wrd->word_canton();
        $phr_lst_ex = new phrase_list_ui();
        $phr_lst_ex->add($wrd_canton->phrase(), $msg_ui);
        $test_page .= 'with linked names and separate measure (ex ' . $wrd_canton->name() . '): ' . $val_zh->links_and_measure($msg_ui, $url_arr, $phr_lst_ex) . '<br>';
        // the data object supplies the tooltips: the symbol "mio" has no description of its
        // own, so the description of the related word "million" is shown instead
        $dto = new data_object();
        $dto->phr_lst = $t_phr->list_canton_mio_cache_ui();
        $val_sym = $t_val->people_zh_canton_mio_symbol_ui();
        $test_page .= 'with the tooltips from the cache: ' . $val_sym->links_and_measure($msg_ui, $url_arr, null, $dto) . '<br>';
        $test_page .= 'with measure type: ' . $tl->ui_value($t_val->light_speed())->with_unit_and_info($msg_ui) . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $val->btn_add($url_arr, $base_url) . '<br>';
        $test_page .= 'edit button: ' . $val->btn_edit($url_arr, $base_url) . '<br>';
        $test_page .= 'del button: ' . $val->btn_del($url_arr, $base_url) . '<br>';
        $val_protected = new value($t_val->value_protected($msg)->api_json([api_types::INCL_PHRASES]));
        $test_page .= $t->dsp_title_value($val_protected, $msg_ui);
        $t->html_page_test($test_page, 'value html components', 'value', $msg_ui, $base_url, $lan);

        $t->subheader($ts . 'links and measure');
        // the speed of light value: "speed of light" names the value, "m/s" is the measure
        // shown behind the number and "1983 (year of definition)" explains it as the tooltip
        $val_ls = $tl->ui_value($t_val->light_speed());
        $lam_html = $val_ls->links_and_measure($msg_ui, $url_arr);
        $test_name = 'the name phrase is shown before the number and the measure behind it';
        $t->assert_text_order($test_name, $lam_html,
            triple_names::SPEED_OF_LIGHT, values::SPEED_OF_LIGHT_TXT, triple_names::M_PER_S);
        $test_name = 'the information only phrase explains the number as the tooltip';
        $t->assert_text_contains($test_name, $lam_html, '"' . triple_names::DEFINITION_YEAR_1983 . '"');
        $test_name = 'the calling page is kept as the return path of the number link';
        $t->assert_text_contains($test_name, $lam_html,
            url_var::BACK . url_var::MASK . '=' . views::WORD_ID);
        // negative: an excluded phrase (e.g. the column header of a table) is not repeated
        $test_name = 'an excluded phrase is not part of the name';
        $ex_lst = $val_ls->grp->phr_lst();
        $lam_ex_html = $val_ls->links_and_measure($msg_ui, $url_arr, $ex_lst);
        $t->assert_text_not_contains($test_name, $lam_ex_html, triple_names::SPEED_OF_LIGHT);
        $test_name = '... but the number is still shown';
        $t->assert_text_contains($test_name, $lam_ex_html, values::SPEED_OF_LIGHT_TXT);
        // the three parts are one row, so that the css can keep them side by side
        $test_name = 'the name, the number and the measure are wrapped in one row';
        $t->assert_text_contains($test_name, $lam_html, styles::VALUE_ROW);
        $test_name = '... with the name, the number and the measure as separate parts';
        $t->assert_text_order($test_name, $lam_html,
            styles::VALUE_NAME, styles::VALUE_NUM, styles::VALUE_UNIT);
        $test_name = 'a value without a name part shows no empty name';
        $t->assert_text_not_contains($test_name, $lam_ex_html, styles::VALUE_NAME);
        // the phrase links and their separator stay on one line, so that the html snapshot
        // does not add a line break - and with it a space - in front of the comma;
        // the canton value is used, because it has more than one phrase in the name part
        $test_name = 'the separator of the phrase links follows the link without a space';
        $t->assert_text_contains($test_name,
            $val_zh->links_and_measure($msg_ui, $url_arr), '</a>, <a ');

        $t->subheader($ts . 'tooltips from the data object');
        // the scaling symbol "mio" has no description, so without the cache it has no tooltip
        $test_name = 'without the data object the symbol has no tooltip';
        $lam_no_dto = $val_sym->links_and_measure($msg_ui, $url_arr);
        $t->assert_text_not_contains($test_name, $lam_no_dto, word_names::MIO_COM);
        // with the cache the description of the related word "million" is the symbol tooltip
        $test_name = 'the description of the related word is the tooltip of the symbol';
        $lam_dto = $val_sym->links_and_measure($msg_ui, $url_arr, null, $dto);
        $t->assert_text_contains($test_name, $lam_dto,
            html_base::TITLE_HTML . '="' . word_names::MIO_COM . '"');
        $test_name = '... and the symbol itself is still shown and linked';
        $t->assert_text_contains($test_name, $lam_dto, '>' . word_names::MIO_SHORT . '</a>');


        $t->subheader($ts . 'source selector');

        // the source field of the value add and edit form offers to add a new source and, if a
        // source is selected, to change it, so the user can create a missing source without
        // leaving the value form (see value::source_crud_links and system_views.json value_edit)
        // the form value carries the source that the frontend source list offers, so the selector
        // can preselect it; the page value uses the reserved source, which is not in that list
        $t_src = new test_sources($t);
        $val_src = $t_val->value_form_ui($msg);
        $sel_html = $val_src->source_selector(views::VALUE_EDIT, '', $t_src->source_list_ui());

        $test_name = 'the source selector links to the add source view';
        $t->assert_text_contains($test_name, $sel_html, url_var::MASK . '=' . views::SOURCE_ADD_ID);
        $test_name = '... with the add icon';
        $t->assert_text_contains($test_name, $sel_html, icons::ADD);

        // the change icon targets the selected source, not the value itself
        $test_name = 'the source selector links to the edit view of the selected source';
        $t->assert_text_contains($test_name, $sel_html,
            url_var::MASK . '=' . views::SOURCE_EDIT_ID . '&amp;id=' . sources::BFS_ID);
        $test_name = '... and preselects the source of the value';
        $t->assert_text_contains($test_name, $sel_html,
            '<option value="' . sources::BFS_ID . '"  selected >');

        // a value without a source has nothing to change, so only the add icon is shown
        $val_no_src = new value($t_val->value($msg)->api_json([api_types::INCL_PHRASES]));
        $sel_no_src = $val_no_src->source_selector(views::VALUE_EDIT, '', $t_src->source_list_ui());
        $test_name = 'a value without a source shows no edit source link';
        $t->assert_text_not_contains($test_name, $sel_no_src,
            url_var::MASK . '=' . views::SOURCE_EDIT_ID);
        $test_name = '... but still offers to add a source';
        $t->assert_text_contains($test_name, $sel_no_src, url_var::MASK . '=' . views::SOURCE_ADD_ID);


        $t->subheader($ts . 'show source');

        // a value built from a url carries only the source id, so the value page names the source
        // from the frontend cache; without this the page would show no source at all
        // (see system_form::show_source and base_views.json value_default)
        $sfm = new system_form();
        $val_src_id = $t_val->value_source_by_id_ui($msg);
        $test_name = 'the source known by id only is named from the frontend cache';
        $t->assert_text_contains($test_name, $sfm->show_source($val_src_id, $t_src->source_list_ui()),
            sources::BFS);

        // without the cache the id cannot be resolved, so no half filled source line is shown
        $test_name = 'without the cache the source known by id only is not shown';
        $t->assert($test_name, $sfm->show_source($val_src_id), '');

        // the api sends the source with its name for a page request, so no cache is needed
        $test_name = 'the source sent by the api is shown without the cache';
        $t->assert_text_contains($test_name, $sfm->show_source($t_val->value_form_ui($msg)),
            sources::BFS);

        // a value without any source shows no source line
        $test_name = 'a value without a source shows no source line';
        $t->assert($test_name, $sfm->show_source($val_no_src), '');


        $t->subheader($ts . 'similar values and results');

        // the value default page shows the values that share a phrase with the shown value in one
        // column and the results that use it in the next (see base_views.json value_default)
        $val_rel = $t_val->value_page_related_ui($msg);
        $lst_ui = new ui_list();
        $sim_html = $lst_ui->values_similar($val_rel, $msg_ui);

        $test_name = 'the similar values of pi list the other mathematical constants';
        $t->assert_text_contains($test_name, $sim_html, triple_names::E);

        // a value is never listed among its own similar values (see value_list::remove)
        $test_name = 'pi is not listed among its own similar values';
        $t->assert_text_not_contains($test_name, $sim_html, triple_names::PI_SYMBOL_NAME);

        // the results column lists the results that use the value, each with its phrase and number
        $res_html = $lst_ui->results_by_value($val_rel, $msg_ui);
        $test_name = 'the results of a value are shown with their phrase and their number';
        $t->assert_text_order($test_name, $res_html, word_names::MATH, (string)results::TV_INT);

        // a value that is not used for results says so instead of showing an empty table
        $val_no_res = $t_val->value_page_ui($msg);
        $val_no_res->results_related = new result_list();
        $test_name = 'a value without results shows the not used message';
        $t->assert($test_name, $lst_ui->results_by_value($val_no_res, $msg_ui),
            $mtr->txt(msg_id::INFO_NOT_USED_FOR_RESULTS));

        // a value built from an url carries no similar values (only a page request loads them),
        // so the values of the page cache that share a phrase with it are shown instead; the
        // zurich values share the phrases zurich, inhabitants and 2019, so they are similar
        $val_url = $tl->ui_value($t_val->people_zh());
        $dto_sim = new data_object();
        // INCL_PHRASES so each cached value carries its group phrases, which the filter compares
        $dto_sim->val_lst = $tl->list_to_ui($t_val->value_list_zh(), [api_types::INCL_PHRASES]);
        $sim_cache_html = $lst_ui->values_similar($val_url, $msg_ui, $dto_sim);

        // the phrases of the shown value are the context of the column and left out of the lines,
        // so the canton value is named by the phrases that the shown value does not have
        $test_name = 'without the loaded list the similar values come from the page cache';
        $t->assert_text_contains($test_name, $sim_cache_html, word_names::CANTON);

        // the shown value is never similar to itself, so the cache keeps every value but that one;
        // the rendered lines cannot show this, because the phrases of the shown value are the
        // context of the column and are left out of every line
        $sim_lst = $dto_sim->val_lst->filter($msg_ui, $val_url);
        $test_name = '... and the shown value itself is not among them';
        $t->assert($test_name, $sim_lst->count(), $dto_sim->val_lst->count() - 1);

        // a value built from an url carries no results either, so the results of the page cache
        // that are based on all phrases of the value are shown instead; the cache holds the
        // result of the math phrase and the result of the percent phrase
        $t_res = new test_results($t);
        $dto_res = new data_object();
        // INCL_PHRASES so each cached result carries its group phrases, which the filter compares
        $dto_res->res_lst = new result_list(
            $t_res->result_list()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]));

        // the math phrase of the shown value is the context of the column and left out of the
        // lines, so the result is recognised by its number
        $val_math = $tl->ui_value($t_val->value_for_phrases([$t_wrd->word()->phrase()]));
        $test_name = 'without the loaded list the results come from the page cache';
        $t->assert_text_contains($test_name,
            $lst_ui->results_by_value($val_math, $msg_ui, $dto_res), (string)results::TV_INT);

        // the cache holds the results of the whole page, so a value that no cached result is
        // based on shows the not used message and never another value's results
        $test_name = '... and only the results that are based on the shown value';
        $t->assert($test_name,
            $lst_ui->results_by_value($tl->ui_value($t_val->value_pi()), $msg_ui, $dto_res),
            $mtr->txt(msg_id::INFO_NOT_USED_FOR_RESULTS));


        $t->subheader($ts . 'view tab box');

        // the value default page shows the views that can show a value, the change log and the
        // user overwrites in the tab box (see the 'value tab box' of base_views.json)
        $t_msk = new test_views($t);
        $t_log = new test_log($t);
        $t_usr = new test_users();
        $list = new ui_list();
        $val_related = $t_val->value($msg);
        $val_related->views_related = $t_msk->view_list_word();
        $val_related->changes_related = $t_log->log_list_value();
        // test mode so the backend emits the two given lists without loading them from the database
        $val_json = json_decode($val_related->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED], $msg), true);

        $test_name = 'the views of a value are sent to the frontend';
        $t->assert_true($test_name, ($val_json[json_fields::VIEWS] ?? []) != []);
        $test_name = 'the changes of a value are sent to the frontend';
        $t->assert_true($test_name, ($val_json[json_fields::CHANGES] ?? []) != []);

        // the overwrites are read from the user sandbox table, which the test mode skips, so the
        // 'my' rows are added here like on the word and the formula page
        $val_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => value_fields::FLD_VALUE,
                json_fields::USR_VALUE => (string)values::SAMPLE_INT,
                json_fields::STD_VALUE => (string)values::PI_SHORT,
            ],
        ];
        $val_tab = new value(json_encode($val_json));

        $test_name = 'the views of a value reach the frontend value object';
        $t->assert_true($test_name, $val_tab->view_lst != null and !$val_tab->view_lst->is_empty());
        $test_name = 'the changes of a value reach the frontend value object';
        $t->assert_true($test_name, $val_tab->chg_log != null and !$val_tab->chg_log->is_empty());

        $views_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS)) . '"';
        $log_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"';
        $my_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"';
        $usr_tab_keep = $ui_sys->usr ?? null;
        // the user comes from the factory, because the my tab is only shown to a user with an id
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $tab_html = $list->view_tab_box($val_tab, $msg_ui, true);

        $test_name = 'the value page shows the views tab';
        $t->assert_text_contains($test_name, $tab_html, $views_tab_ref);
        $test_name = '... with the name of a view that can show the value';
        $t->assert_text_contains($test_name, $tab_html, views::SCIENCE);
        // the switch button must open the edit view of the shown object, so on a value page the
        // value edit view and never the word edit view (see view::switch_link)
        $test_name = '... and a switch button that opens the value edit view';
        $t->assert_text_contains($test_name, $tab_html, url_var::MASK . '=' . views::VALUE_EDIT_ID);

        $test_name = 'the value page shows the changes tab';
        $t->assert_text_contains($test_name, $tab_html, $log_tab_ref);
        $test_name = '... with the change that added the value';
        $t->assert_text_contains($test_name, $tab_html, (string)values::PI_SHORT);

        $test_name = 'the user with value overwrites sees the my tab';
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);
        $test_name = '... with the your and instead columns';
        $t->assert_text_contains($test_name, $tab_html, $mtr->txt(msg_id::MY_TBL_YOUR));
        $t->assert_text_contains($test_name, $tab_html, $mtr->txt(msg_id::MY_TBL_INSTEAD));
        $test_name = '... and the translated name of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, $mtr->text_db_field(value_fields::FLD_VALUE));

        // like on the word page the undo icon links to the confirm page of the value edit view
        // that sets the field back to the standard value (see value::db_fld_to_url)
        $test_name = '... and an undo link to the confirm page for the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, icons::UNDO);
        $t->assert_text_contains($test_name, $tab_html,
            url_var::NUMERIC_VALUE . '=' . values::PI_SHORT);
        $t->assert_text_contains($test_name, $tab_html,
            url_var::PRE . url_var::NUMERIC_VALUE . '=' . values::SAMPLE_INT);
        $t->assert_text_contains($test_name, $tab_html, url_var::STEP . '=' . url_var::STEP_CONFIRM);

        // a value loaded without the related data has neither a views nor a my tab
        $val_plain = new value($t_val->value($msg)->api_json());
        $plain_html = $list->view_tab_box($val_plain, $msg_ui, true);
        $test_name = 'a value without views shows no views tab';
        $t->assert_text_not_contains($test_name, $plain_html, $views_tab_ref);
        $test_name = 'a value without overwrites shows no my tab';
        $t->assert_text_not_contains($test_name, $plain_html, $my_tab_ref);

        $test_name = 'without a logged in user the value page shows no my tab';
        unset($ui_sys->usr);
        $t->assert_text_not_contains($test_name, $list->view_tab_box($val_tab, $msg_ui, true), $my_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_tab_keep;
        }


        // TODO review


        // start the test section (ts)
        $ts = 'unit ui html value ';
        $t->header($ts);

        /*
        // prepare the frontend testing
        $phr_lst_added = new phrase_list($t->usr1);
        $phr_lst_added->add_name(words::TN_INHABITANTS);
        $phr_lst_added->add_name(words::TN_MIO);
        $phr_lst_added->add_name(words::TN_2020);
        $phr_lst_ch = clone $phr_lst_added;
        $phr_lst_ch->add_name(words::TN_CH);
        $phr_lst_added->add_name(words::TN_RENAMED);
        $val_added = new value($t->usr1);
        $val_added->load_by_grp($phr_lst_added->get_grp_id());
        $val_ch = new value($t->usr1);
        $val_ch->load_by_grp($phr_lst_ch->get_grp_id());


        $t->subheader($ts . 'Test the value list class (classes/value_list.php)');

        // check the database consistency for all values
        $val_lst = new value_list($t->usr1);
        $result = $val_lst->check_all();
        $target = '';
        $t->assert('value_list->check_all', $result, $target, $t::TIMEOUT_LIMIT_DB);

        // test get a single value from a value list by group and time
        // get all value for Switzerland
        $wrd = new word($t->usr1);
        $wrd->load_by_name(words::TN_CH);
        $val_lst = $wrd->val_lst();
        // build the phrase list to select the value sales for 2014
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_by_names(array(words::TN_CH, words::TN_INHABITANTS, words::TN_MIO, words::TN_2020));
        $wrd_time = $wrd_lst->assume_time();
        $grp = $wrd_lst->get_grp();
        $result = $grp->id();
        $target = '2116';
        $t->assert('word_list->get_grp for ' . $wrd_lst->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_DB);
        $val = $val_lst->get_by_grp($grp, $wrd_time);
        if ($val != null) {
            $result = $val->number();
        }
        $target = values::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->assert('value_list->get_by_grp for ' . $wrd_lst->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_DB);

        // ... get all times of the Switzerland values
        $time_lst = $val_lst->time_list();
        $wrd_2014 = new word($t->usr1);
        $wrd_2014->load_by_name(words::TN_2014);
        if ($time_lst->does_contain($wrd_2014)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->assert('value_list->time_lst is ' . $time_lst->dsp_name() . ', which includes ' . $wrd_2014->name(), $result, true, $t::TIMEOUT_LIMIT_DB);

        // ... and filter by times
        $time_lst = new word_list($t->usr1);
        $wrd_lst->load_by_names(array(words::TN_2019, words::TN_2021));
        $used_value_lst = $val_lst->filter_by_time($time_lst);
        $used_time_lst = $used_value_lst->time_list();
        if ($time_lst->does_contain($wrd_2014)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->assert('value_list->time_lst is ' . $used_time_lst->dsp_name() . ', which does not include ' . $wrd_2014->name(), $result, true);

        // ... but not 2020
        $wrd_2020 = new word($t->usr1);
        $wrd_2020->load_by_name(words::TN_2020);
        if ($time_lst->does_contain($wrd_2020)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->assert('value_list->filter_by_phrase_lst is ' . $used_time_lst->dsp_name() . ', but includes ' . $wrd_2020->name(), $result, true);

        // ... and filter by phrases
        $sector_lst = new word_list($t->usr1);
        $sector_lst->load_by_names(array('Low Voltage Products', 'Power Products'));
        $phr_lst = $sector_lst->phrase_lst();
        $used_value_lst = $val_lst->filter_by_phrase_lst($phr_lst);
        $used_phr_lst = $used_value_lst->phr_lst();
        $wrd_auto = new word($t->usr1);
        $wrd_auto->load_by_name('Discrete Automation and Motion');
        if ($used_phr_lst->does_contain($wrd_auto)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->assert('value_list->filter_by_phrase_lst is ' . $used_phr_lst->dsp_name() . ', which does not include ' . $wrd_auto->name(), $result, true);

        // ... but not 2016
        $wrd_power = new word($t->usr1);
        $wrd_power->load_by_name('Power Products');
        if ($used_phr_lst->does_contain($wrd_power)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->assert('value_list->filter_by_phrase_lst is ' . $used_phr_lst->dsp_name() . ', but includes ' . $wrd_power->name(), $result, true);


        $t->subheader($ts . 'Test the value list display class (classes/value_list_display.php)');

        // test the value table
        $wrd = new word($t->usr1);
        $wrd->load_by_name('Nestlé');
        $wrd_col = new word($t->usr1);
        $wrd_col->load_by_name(words::TN_CASH_FLOW);
        $val_lst = new value_list_dsp();
        // TODO review
        //$val_lst->set_phr($wrd->phrase());
        $result = $val_lst->dsp_table($wrd_col, [url_var::MASK => views::PHRASE_ID, url_var::ID => $wrd->id()], $msg_ui);
        $target = values::TV_NESN_SALES_2016_FORMATTED;
        $t->dsp_contains(', value_list_dsp->dsp_table for "' . $wrd->name() . '" (' . $result . ') contains ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
        //$result = $val_lst->dsp_table($wrd_col, $wrd->id);
        //$target = zuv_table ($wrd->id, $wrd_col->id, $t->usr1->id());
        //$t->assert('value_list_dsp->dsp_table for "'.$wrd->name.'"', $result, $target, $t::TIMEOUT_LIMIT_DB);
        */

    }

}