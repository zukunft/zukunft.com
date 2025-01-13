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

namespace unit_ui;

use api\word\word as word_api;
use api\value\value as value_api;
use cfg\word\word;
use cfg\word\word_list;
use cfg\value\value_base;
use cfg\value\value_list;
use html\value\value_list as value_list_dsp;
use cfg\phrase\phrase_list;
use html\html_base;
use html\value\value as value_dsp;
use shared\types\api_type;
use test\test_cleanup;

class value_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('value tests');

        $val = new value_dsp($t->value()->api_json([api_type::INCL_PHRASES]));
        $test_page = $html->text_h2('value display test');
        $test_page .= 'with tooltip: ' . $val->display() . '<br>';
        $test_page .= 'with link: ' . $val->display_linked() . '<br>';
        $t->html_test($test_page, 'value', 'value', $t);


        // TODO review

        global $usr;

        $t->header('Test the value frontend scripts (e.g. /value_add.php)');

        /*
        // prepare the frontend testing
        $phr_lst_added = new phrase_list($usr);
        $phr_lst_added->add_name(word_api::TN_INHABITANTS);
        $phr_lst_added->add_name(word_api::TN_MIO);
        $phr_lst_added->add_name(word_api::TN_2020);
        $phr_lst_ch = clone $phr_lst_added;
        $phr_lst_ch->add_name(word_api::TN_CH);
        $phr_lst_added->add_name(word_api::TN_RENAMED);
        $val_added = new value($usr);
        $val_added->load_by_grp($phr_lst_added->get_grp_id());
        $val_ch = new value($usr);
        $val_ch->load_by_grp($phr_lst_ch->get_grp_id());

        // call the add value page and check if at least some basic keywords are returned
        $back = 0;
        $result = file_get_contents('https://zukunft.com/http/value_add.php?back=' . $back . $phr_lst_added->id_url_long());
        $target = word_api::TN_RENAMED;
        $t->dsp_contains(', frontend value_add.php ' . $result . ' contains at least ' . word_api::TN_RENAMED, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        $result = file_get_contents('https://zukunft.com/http/value_add.php?back=' . $back . $phr_lst_ch->id_url_long());
        $target = word_api::TN_CH;
        $t->dsp_contains(', frontend value_add.php ' . $result . ' contains at least ' . word_api::TN_CH, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test the edit value frontend
        $result = file_get_contents('https://zukunft.com/http/value_edit.php?id=' . $val_added->id() . '&back=' . $back);
        $target = word_api::TN_RENAMED;
        $t->dsp_contains(', frontend value_edit.php ' . $result . ' contains at least ' . word_api::TN_RENAMED, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        $result = file_get_contents('https://zukunft.com/http/value_edit.php?id=' . $val_ch->id() . '&back=' . $back);
        $target = word_api::TN_CH;
        $t->dsp_contains(', frontend value_edit.php ' . $result . ' contains at least ' . word_api::TN_CH, $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test the del value frontend
        $result = file_get_contents('https://zukunft.com/http/value_del.php?id=' . $val_added->id() . '&back=' . $back);
        $target = word_api::TN_RENAMED;
        $t->dsp_contains(', frontend value_del.php ' . $result . ' contains at least ' . word_api::TN_RENAMED, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        $result = file_get_contents('https://zukunft.com/http/value_del.php?id=' . $val_ch->id() . '&back=' . $back);
        $target = word_api::TN_CH;
        $t->dsp_contains(', frontend value_del.php ' . $result . ' contains at least ' . word_api::TN_CH, $target, $result, $t::TIMEOUT_LIMIT_PAGE);


        $t->header('Test the value list class (classes/value_list.php)');

        // check the database consistency for all values
        $val_lst = new value_list($usr);
        $result = $val_lst->check_all();
        $target = '';
        $t->display('value_list->check_all', $target, $result, $t::TIMEOUT_LIMIT_DB);

        // test get a single value from a value list by group and time
        // get all value for Switzerland
        $wrd = new word($usr);
        $wrd->load_by_name(word_api::TN_CH);
        $val_lst = $wrd->val_lst();
        // build the phrase list to select the value Sales for 2014
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
        $wrd_time = $wrd_lst->assume_time();
        $grp = $wrd_lst->get_grp();
        $result = $grp->id();
        $target = '2116';
        $t->display('word_list->get_grp for ' . $wrd_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_DB);
        $val = $val_lst->get_by_grp($grp, $wrd_time);
        if ($val != null) {
            $result = $val->number();
        }
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->display('value_list->get_by_grp for ' . $wrd_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_DB);

        // ... get all times of the Switzerland values
        $time_lst = $val_lst->time_list();
        $wrd_2014 = new word($usr);
        $wrd_2014->load_by_name(word_api::TN_2014);
        if ($time_lst->does_contain($wrd_2014)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->display('value_list->time_lst is ' . $time_lst->dsp_name() . ', which includes ' . $wrd_2014->name(), true, $result, $t::TIMEOUT_LIMIT_DB);

        // ... and filter by times
        $time_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_2019, word_api::TN_2021));
        $used_value_lst = $val_lst->filter_by_time($time_lst);
        $used_time_lst = $used_value_lst->time_list();
        if ($time_lst->does_contain($wrd_2014)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->display('value_list->time_lst is ' . $used_time_lst->dsp_name() . ', which does not include ' . $wrd_2014->name(), true, $result);

        // ... but not 2020
        $wrd_2020 = new word($usr);
        $wrd_2020->load_by_name(word_api::TN_2020);
        if ($time_lst->does_contain($wrd_2020)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->display('value_list->filter_by_phrase_lst is ' . $used_time_lst->dsp_name() . ', but includes ' . $wrd_2020->name(), true, $result);

        // ... and filter by phrases
        $sector_lst = new word_list($usr);
        $sector_lst->load_by_names(array('Low Voltage Products', 'Power Products'));
        $phr_lst = $sector_lst->phrase_lst();
        $used_value_lst = $val_lst->filter_by_phrase_lst($phr_lst);
        $used_phr_lst = $used_value_lst->phr_lst();
        $wrd_auto = new word($usr);
        $wrd_auto->load_by_name('Discrete Automation and Motion');
        if ($used_phr_lst->does_contain($wrd_auto)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->display('value_list->filter_by_phrase_lst is ' . $used_phr_lst->dsp_name() . ', which does not include ' . $wrd_auto->name(), true, $result);

        // ... but not 2016
        $wrd_power = new word($usr);
        $wrd_power->load_by_name('Power Products');
        if ($used_phr_lst->does_contain($wrd_power)) {
            $result = true;
        } else {
            $result = false;
        }
        $t->display('value_list->filter_by_phrase_lst is ' . $used_phr_lst->dsp_name() . ', but includes ' . $wrd_power->name(), true, $result);


        $t->header('Test the value list display class (classes/value_list_display.php)');

        // test the value table
        $wrd = new word($usr);
        $wrd->load_by_name('Nestlé');
        $wrd_col = new word($usr);
        $wrd_col->load_by_name(word_api::TN_CASH_FLOW);
        $val_lst = new value_list_dsp();
        // TODO review
        //$val_lst->set_phr($wrd->phrase());
        $result = $val_lst->dsp_table($wrd_col, $wrd->id());
        $target = value_api::TV_NESN_SALES_2016_FORMATTED;
        $t->dsp_contains(', value_list_dsp->dsp_table for "' . $wrd->name() . '" (' . $result . ') contains ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
        //$result = $val_lst->dsp_table($wrd_col, $wrd->id);
        //$target = zuv_table ($wrd->id, $wrd_col->id, $usr->id());
        //$t->display('value_list_dsp->dsp_table for "'.$wrd->name.'"', $target, $result, $t::TIMEOUT_LIMIT_DB);
        */

    }

}