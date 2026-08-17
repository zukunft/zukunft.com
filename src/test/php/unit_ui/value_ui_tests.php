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
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class value_ui_tests
{
    function run(test_cleanup $t): void
    {
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

        // call the add value page and check if at least some basic keywords are returned
        $back = 0;
        $result = file_get_contents('https://zukunft.com/http/value_add.php?back=' . $back . $phr_lst_added->id_url_long());
        $target = words::TN_RENAMED;
        $t->dsp_contains(', frontend value_add.php ' . $result . ' contains at least ' . words::TN_RENAMED, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        $result = file_get_contents('https://zukunft.com/http/value_add.php?back=' . $back . $phr_lst_ch->id_url_long());
        $target = words::TN_CH;
        $t->dsp_contains(', frontend value_add.php ' . $result . ' contains at least ' . words::TN_CH, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test the edit value frontend
        $result = file_get_contents('https://zukunft.com/http/value_edit.php?id=' . $val_added->id() . '&back=' . $back);
        $target = words::TN_RENAMED;
        $t->dsp_contains(', frontend value_edit.php ' . $result . ' contains at least ' . words::TN_RENAMED, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        $result = file_get_contents('https://zukunft.com/http/value_edit.php?id=' . $val_ch->id() . '&back=' . $back);
        $target = words::TN_CH;
        $t->dsp_contains(', frontend value_edit.php ' . $result . ' contains at least ' . words::TN_CH, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test the del value frontend
        $result = file_get_contents('https://zukunft.com/http/value_del.php?id=' . $val_added->id() . '&back=' . $back);
        $target = words::TN_RENAMED;
        $t->dsp_contains(', frontend value_del.php ' . $result . ' contains at least ' . words::TN_RENAMED, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        $result = file_get_contents('https://zukunft.com/http/value_del.php?id=' . $val_ch->id() . '&back=' . $back);
        $target = words::TN_CH;
        $t->dsp_contains(', frontend value_del.php ' . $result . ' contains at least ' . words::TN_CH, $target, $result, $t::TIMEOUT_LIMIT_PAGE);


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
        $result = $val_lst->dsp_table($wrd_col, $wrd->id(), $msg_ui);
        $target = values::TV_NESN_SALES_2016_FORMATTED;
        $t->dsp_contains(', value_list_dsp->dsp_table for "' . $wrd->name() . '" (' . $result . ') contains ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
        //$result = $val_lst->dsp_table($wrd_col, $wrd->id);
        //$target = zuv_table ($wrd->id, $wrd_col->id, $t->usr1->id());
        //$t->assert('value_list_dsp->dsp_table for "'.$wrd->name.'"', $result, $target, $t::TIMEOUT_LIMIT_DB);
        */

    }

}