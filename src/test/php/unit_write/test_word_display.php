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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';

use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list as verb_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

function run_word_display_test(all_tests $t): void
{


    // init
    $msg = new user_message();
    $msg_ui = new user_message_ui();
    $lib = new library();
    $t_db = new test_db_load($t);

    // start the test section (ts)
    // TODO Prio 1 to be move to the ui tests?
    $ts = 'db write ui word ';
    $t->header($ts);

    // check the upward graph display
    // test uses the old function zum_word_list to compare, so it is a kind of double coding
    // correct test would be using a "fixed HTML text contains"
    $wrd_ZH = new word($t->usr1);
    $wrd_ZH->load_by_name(word_names::ZH, $msg);
    $direction = foaf_direction::UP;
    $target = word_names::COMPANY;
    // get the link types related to the word
    $link_types = $wrd_ZH->link_types($direction, $msg);
    $link_types_ui = new verb_list_ui();
    $link_types_ui->set_from_json($link_types->api_json(), $msg_ui);
    $wrd_ZH_ui = new word_ui($wrd_ZH->api_json());
    $result = $wrd_ZH_ui->dsp_graph($direction, $msg_ui, $link_types_ui, 0);
    // TODO Prio 1 activate
    //$t->dsp_contains('word_dsp->dsp_graph ' . $direction->value . ' for ' . $wrd_ZH->name(), $target, $result);

    // ... and the other side
    $wrd_ZH = new word($t->usr1);
    $wrd_ZH->load_by_name(word_names::ZH, $msg);
    $direction = foaf_direction::DOWN;
    $target = 'ZU';
    $link_types = $wrd_ZH->link_types($direction, $msg);
    $wrd_ZH_ui = new word_ui($wrd_ZH->api_json());
    $link_types_ui = new verb_list_ui();
    $link_types_ui->set_from_json($link_types->api_json(), $msg_ui);
    $result = $wrd_ZH_ui->dsp_graph($direction, $msg_ui, $link_types_ui, 0);
    // loading the link types and rendering the graph reads from the database, so a semi page timeout is used
    $t->assert_text_contains('word_dsp->dsp_graph check if acronym ZU is found for Zurich', $result, $target, $t::TIMEOUT_LIMIT_PAGE_SEMI);

    // ... and the graph display for 2019
    $wrd_2020 = new word($t->usr1);
    $wrd_2020->load_by_name(word_names::YEAR_2020, $msg);
    $direction = foaf_direction::DOWN;
    $wrd_2021 = new word($t->usr1);
    $wrd_2021->load_by_name(word_names::TEST_2021, $msg);
    $lnk_20_to_21 = $t_db->load_triple($msg, word_names::TEST_2021, verbs::FOLLOW, word_names::YEAR_2020);
    $target_part_is_followed = verbs::FOLLOWER_OF;
    $link_types = $wrd_2020->link_types($direction, $msg);
    $wrd_2020_ui = new word_ui($wrd_2020->api_json());
    $link_types_ui = new verb_list_ui();
    $link_types_ui->set_from_json($link_types->api_json(), $msg_ui);
    $result = $wrd_2020_ui->dsp_graph($direction, $msg_ui, $link_types_ui, 0);
    $result = $lib->trim_html($result);
    $target = $lib->trim_html($target);
    // TODO Prio 2 activate
    //$t->assert_text_contains($t->name . ' has follower', $result, $target_part_is_followed);
    // TODO use complete link instead of id and name
    // TODO Prio 2 activate
    //$t->assert_text_contains($t->name . ' has 2020 id', $result, $wrd_2020->id());
    //$t->assert_text_contains($t->name . ' has 2020 name', $result, words::TN_2020);
    //$t->assert_text_contains($t->name . ' has 2021 id', $result, $wrd_2021->id());
    //$t->assert_text_contains($t->name . ' has 2021 name', $result, words::TN_2021);
    //$t->assert_text_contains($t->name . ' has 2020 to 2021 link', $result, $lnk_20_to_21->id());

    // ... and the other side
    $direction = foaf_direction::UP;
    $wrd_2019 = $t_db->load_word($msg, word_names::YEAR_2019);
    $wrd_year = $t_db->load_word($msg, words::YEAR_CAP);
    $lnk_20_is_year = $t_db->load_triple($msg, word_names::YEAR_2020, verbs::IS, words::YEAR_CAP);
    $lnk_19_to_20 = $t_db->load_triple($msg, word_names::YEAR_2020, verbs::FOLLOW, word_names::YEAR_2019);
    $link_types = $wrd_2020->link_types($direction, $msg);
    $wrd_2020_ui = new word_ui($wrd_2020->api_json());
    $link_types_ui = new verb_list_ui();
    $link_types_ui->set_from_json($link_types->api_json(), $msg_ui);
    $result = $wrd_2020_ui->dsp_graph($direction, $msg_ui, $link_types_ui, 0);
    $result = $lib->trim_html($result);
    // TODO Prio 2 activate
    //$t->assert_text_contains($t->name . ' has year id', $result, $wrd_year->id());
    //$t->assert_text_contains($t->name . ' has year name', $result, words::TN_YEAR);
    //$t->assert_text_contains($t->name . ' has 2019 id', $result, $wrd_2019->id());
    //$t->assert_text_contains($t->name . ' has 2019 name', $result, words::TN_2019);
    //$t->assert_text_contains($t->name . ' has 2020 id', $result, $wrd_2020->id());
    //$t->assert_text_contains($t->name . ' has 2020 name', $result, words::TN_2020);
    //$t->assert_text_contains($t->name . ' has 2019 to 2020 link', $result, $lnk_19_to_20->id());

    // the value table for ABB
    $wrd_ZH = new word($t->usr1);
    $wrd_ZH->load_by_name(word_names::ZH, $msg, word::class);
    $wrd_year = new word($t->usr1);
    $wrd_year->load_by_name(words::YEAR_CAP, $msg, word::class);
    /*
    $target = zut_dsp_list_wrd_val($wrd_ZH->id(), $wrd_year->id(), $t->usr1->id());
    $target = substr($target,0,208);
    */
    $target = word_names::YEAR_2020;
    $target = word_names::ZH;
    // TODO add a sample
    //$result = $wrd_ZH->dsp_val_list($wrd_year, $wrd_year->is_mainly(), 0);
    //$t->assert('word_dsp->dsp_val_list compare to old for '.$wrd_ZH->name, $result, $target, $t::TIMEOUT_LIMIT_PAGE);
    //$t->dsp_contains(', word_dsp->dsp_val_list compare to old for ' . $wrd_ZH->name(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

    // the value table for company
    /*
    $wrd_company = New word_ui;
    $wrd_company->name = "TEST_WORD";
    $wrd_company->set_user($t->usr1);
    $wrd_company->load();
    $wrd_ratios = New word_ui;
    $wrd_ratios->name = "company main ratio";
    $wrd_ratios->set_user($t->usr1);
    $wrd_ratios->load();
    $target = zut_dsp_list_wrd_val($wrd_company->id, $wrd_ratios->id, $t->usr1->id());
    $target = substr($target,0,200);
    $result = $wrd_company->dsp_val_list ($wrd_ratios, $back);
    $result = substr($result,0,200);
    $t->assert('word_dsp->dsp_val_list compare to old for '.$wrd_company->name, $result, $target);
    */


    $t->subheader($ts . 'selector');

    // for testing the selector display a company selector and select ABB
    // TODO fix second run
    $phr_corp = $t_db->load_phrase(word_names::COMPANY, $msg);
    $phr_ZH_INS = $t_db->load_phrase(triple_names::COMPANY_ZURICH, $msg);
    /* TODO base it on the api
    $sel = new html_selector;
    $sel->form = 'test_form';
    $sel->name = 'select_company';
    $phr_lst = $phr_corp->phrases(foaf_direction::DOWN, $msg);
    $sel->lst = $phr_lst->lst_key();
    $sel->selected = $phr_ZH_INS->id();
    $sel->dummy_text = '... please select';
    $result .= $sel->display_old();
    $target = triples::TN_ZH_COMPANY;
    $t->dsp_contains(', display_selector->display of all ' . $phr_corp->name() . ' with ' . $phr_ZH_INS->dsp_name() . ' selected', $target, $result);
    */

}