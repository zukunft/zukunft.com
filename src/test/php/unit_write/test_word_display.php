<?php

/*

  test_word_display.php - TESTing of the WORD DISPLAY functions
  ---------------
  

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

// --------------------------------------
// start testing the system functionality 
// --------------------------------------

use api\phrase_api;
use api\triple_api;
use api\word_api;
use html\html_selector;
use model\library;
use model\verb;
use model\word;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_PAGE;

function run_word_display_test(test_cleanup $t): void
{

    global $usr;

    $lib = new library();

    $t->header('Test the word display class (classes/word_display.php)');

    // check the upward graph display
    // test uses the old function zum_word_list to compare, so it is a kind of double coding
    // correct test would be using a "fixed HTML text contains"
    $wrd_ZH = new word($usr);
    $wrd_ZH->load_by_name(word_api::TN_ZH, word::class);
    $direction = 'up';
    $target = word_api::TN_COMPANY;
    // get the link types related to the word
    $link_types = $wrd_ZH->link_types($direction);
    $result = $wrd_ZH->dsp_graph($direction, $link_types, 0);
    $t->dsp_contains('word_dsp->dsp_graph ' . $direction . ' for ' . $wrd_ZH->name(), $target, $result);

    // ... and the other side
    $wrd_ZH = new word($usr);
    $wrd_ZH->load_by_name(word_api::TN_ZH, word::class);
    $direction = 'down';
    $target = 'ZU';
    $link_types = $wrd_ZH->link_types($direction);
    $result = $wrd_ZH->dsp_graph($direction, $link_types, 0);
    $t->assert_text_contains('word_dsp->dsp_graph check if acronym ZU is found for Zurich', $target, $result);

    // ... and the graph display for 2019
    $wrd_2020 = new word($usr);
    $wrd_2020->load_by_name(word_api::TN_2020, word::class);
    $direction = 'down';
    $wrd_2021 = new word($usr);
    $wrd_2021->load_by_name(word_api::TN_2021, word::class);
    $lnk_20_to_21 = $t->load_triple(word_api::TN_2021, verb::FOLLOW, word_api::TN_2020);
    $target_part_is_followed = verb::FOLLOWED_BY;
    $link_types = $wrd_2020->link_types($direction);
    $result = $wrd_2020->dsp_graph($direction, $link_types, 0);
    $result = $lib->trim_html($result);
    $target = $lib->trim_html($target);
    $t->assert_text_contains($t->name . ' has follower', $result, $target_part_is_followed);
    // TODO use complete link instead of id and name
    $t->assert_text_contains($t->name . ' has 2020 id', $result, $wrd_2020->id());
    $t->assert_text_contains($t->name . ' has 2020 name', $result, word_api::TN_2020);
    $t->assert_text_contains($t->name . ' has 2021 id', $result, $wrd_2021->id());
    $t->assert_text_contains($t->name . ' has 2021 name', $result, word_api::TN_2021);
    $t->assert_text_contains($t->name . ' has 2020 to 2021 link', $result, $lnk_20_to_21->id());

    // ... and the other side
    $direction = 'up';
    $wrd_2019 = $t->load_word(word_api::TN_2019);
    $wrd_year = $t->load_word(word_api::TN_YEAR);
    $lnk_20_is_year = $t->load_triple(word_api::TN_2020, verb::IS, word_api::TN_YEAR);
    $lnk_19_to_20 = $t->load_triple(word_api::TN_2020, verb::FOLLOW, word_api::TN_2019);
    $link_types = $wrd_2020->link_types($direction);
    $result = $wrd_2020->dsp_graph($direction, $link_types, 0);
    $result = $lib->trim_html($result);
    $t->assert_text_contains($t->name . ' has year id', $result, $wrd_year->id());
    $t->assert_text_contains($t->name . ' has year name', $result, word_api::TN_YEAR);
    $t->assert_text_contains($t->name . ' has 2019 id', $result, $wrd_2019->id());
    $t->assert_text_contains($t->name . ' has 2019 name', $result, word_api::TN_2019);
    $t->assert_text_contains($t->name . ' has 2020 id', $result, $wrd_2020->id());
    $t->assert_text_contains($t->name . ' has 2020 name', $result, word_api::TN_2020);
    $t->assert_text_contains($t->name . ' has 2019 to 2020 link', $result, $lnk_19_to_20->id());

    // the value table for ABB
    $wrd_ZH = new word($usr);
    $wrd_ZH->load_by_name(word_api::TN_ZH, word::class);
    $wrd_year = new word($usr);
    $wrd_year->load_by_name(word_api::TN_YEAR, word::class);
    /*
    $target = zut_dsp_list_wrd_val($wrd_ZH->id(), $wrd_year->id(), $usr->id());
    $target = substr($target,0,208);
    */
    $target = word_api::TN_2020;
    $target = word_api::TN_ZH;
    // TODO add a sample
    //$result = $wrd_ZH->dsp_val_list($wrd_year, $wrd_year->is_mainly(), 0);
    //$t->display('word_dsp->dsp_val_list compare to old for '.$wrd_ZH->name, $target, $result, TIMEOUT_LIMIT_PAGE);
    //$t->dsp_contains(', word_dsp->dsp_val_list compare to old for ' . $wrd_ZH->name(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // the value table for Company
    /*
    $wrd_company = New word_dsp;
    $wrd_company->name = "TEST_WORD";
    $wrd_company->set_user($usr);
    $wrd_company->load();
    $wrd_ratios = New word_dsp;
    $wrd_ratios->name = "Company main ratio";
    $wrd_ratios->set_user($usr);
    $wrd_ratios->load();
    $target = zut_dsp_list_wrd_val($wrd_company->id, $wrd_ratios->id, $usr->id());
    $target = substr($target,0,200);
    $result = $wrd_company->dsp_val_list ($wrd_ratios, $back);
    $result = substr($result,0,200);
    $t->display('word_dsp->dsp_val_list compare to old for '.$wrd_company->name, $target, $result);
    */


    $t->header('Test the display selector class (web/html/selector.php)');

    // for testing the selector display a company selector and select ABB
    // TODO fix second run
    $phr_corp = $t->load_phrase(word_api::TN_COMPANY);
    $phr_ZH_INS = $t->load_phrase(triple_api::TN_ZH_COMPANY);
    $sel = new html_selector;
    $sel->form = 'test_form';
    $sel->name = 'select_company';
    $phr_lst = $phr_corp->phrases('down');
    $sel->lst = $phr_lst->lst_key();
    $sel->selected = $phr_ZH_INS->id();
    $sel->dummy_text = '... please select';
    $result .= $sel->display_old();
    $target = triple_api::TN_ZH_COMPANY;
    $t->dsp_contains(', display_selector->display of all ' . $phr_corp->name() . ' with ' . $phr_ZH_INS->dsp_name() . ' selected', $target, $result);

}