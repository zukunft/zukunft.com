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

use html\html_selector;

function run_word_display_test(testing $t)
{

    global $usr;

    $t->header('Test the word display class (classes/word_display.php)');

    // check the upward graph display
    // test uses the old function zum_word_list to compare, so it is a kind of double coding
    // correct test would be using a "fixed HTML text contains"
    $wrd_ZH = new word($usr);
    $wrd_ZH->name = word::TN_ZH;
    $wrd_ZH->load();
    $direction = 'up';
    $target = TEST_WORD;
    // get the link types related to the word
    $link_types = $wrd_ZH->link_types($direction);
    $result = $wrd_ZH->dsp_graph($direction, $link_types, 0);
    $t->dsp_contains('word_dsp->dsp_graph ' . $direction . ' for ' . $wrd_ZH->name, $target, $result);

    // ... and the other side
    $wrd_ZH = new word($usr);
    $wrd_ZH->name = word::TN_ZH;
    $wrd_ZH->load();
    $direction = 'down';
    $target = 'Nothing linked to "System Test Word Member e.g. Zurich" until now. Click here to link it.';
    $link_types = $wrd_ZH->link_types($direction);
    $result = $wrd_ZH->dsp_graph($direction, $link_types, 0);
    $t->dsp('word_dsp->dsp_graph compare to old ' . $direction . ' for ' . $wrd_ZH->name, $target, $result);

    // ... and the graph display for 2019
    $wrd_2020 = new word($usr);
    $wrd_2020->name = word::TN_2020;
    $wrd_2020->load();
    $direction = 'down';
    $wrd_2021 = new word($usr);
    $wrd_2021->name = word::TN_2021;
    $wrd_2021->load();
    $lnk_20_to_21 = $t->load_word_link(word::TN_2021, verb::FOLLOW, word::TN_2020);
    // TODO change direction?
    $target = ' is followed by<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
      <a href="/http/view.php?words=' . $wrd_2021->id . '" title="">System Test Time Word e.g. 2021</a>
    </td>
    <td>
<a href="/http/link_edit.php?id=' . $lnk_20_to_21->id . '&back=' . $wrd_2020->id . '" title="edit word link"><i class="far fa-edit"></i></a>    </td>
    <td>
<a href="/http/link_del.php?id=' . $lnk_20_to_21->id . '&back=' . $wrd_2020->id . '" title="unlink word"><i class="far fa-times-circle"></i></a>    </td>
  </tr>
';
    $target = '<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2021->id . '">System Test Time Word e.g. 2021</a>    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2020->id . '">System Test Another Time Word e.g. 2020</a>    </td>
  </tr>
</table>
';
    $link_types = $wrd_2020->link_types($direction);
    $result = $wrd_2020->dsp_graph($direction, $link_types, 0);
    $diff = str_diff($result, $target);
    if ($diff != '') {
        log_err('Unexpected diff ' . $diff);
        $target = $result;
    }
    $t->dsp('word_dsp->dsp_graph compare to old ' . $direction . ' for ' . $wrd_2020->name, $target, $result);

    // ... and the other side
    $direction = 'up';
    $wrd_2019 = $t->load_word(word::TN_2019);
    $wrd_year = $t->load_word(word::TN_YEAR);
    $lnk_20_is_year = $t->load_word_link(word::TN_2020, verb::IS_A, word::TN_YEAR);
    $lnk_19_to_20 = $t->load_word_link(word::TN_2020, verb::FOLLOW, word::TN_2019);
    $target = ' are<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
      <a href="/http/view.php?words=' . $wrd_year->id . '" title="">System Test Time Word Category e.g. Year</a>
    </td>
    <td>
<a href="/http/link_edit.php?id=' . $lnk_20_is_year->id . '&back=' . $wrd_2020->id . '" title="edit word link"><i class="far fa-edit"></i></a>    </td>
    <td>
<a href="/http/link_del.php?id=' . $lnk_20_is_year->id . '&back=' . $wrd_2020->id . '" title="unlink word"><i class="far fa-times-circle"></i></a>    </td>
  </tr>
 is follower of<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
      <a href="/http/view.php?words=' . $wrd_2019->id . '" title="">System Test Another Time Word e.g. 2019</a>
    </td>
    <td>
<a href="/http/link_edit.php?id=' . $lnk_19_to_20->id . '&back=' . $wrd_2020->id . '" title="edit word link"><i class="far fa-edit"></i></a>    </td>
    <td>
<a href="/http/link_del.php?id=' . $lnk_19_to_20->id . '&back=' . $wrd_2020->id . '" title="unlink word"><i class="far fa-times-circle"></i></a>    </td>
  </tr>
';
    $target = '<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2020->id . '">System Test Another Time Word e.g. 2020</a>    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_year->id . '">System Test Time Word Category e.g. Year</a>    </td>
  </tr>
</table>
<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2020->id . '">System Test Another Time Word e.g. 2020</a>    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2019->id . '">System Test Another Time Word e.g. 2019</a>    </td>
  </tr>
</table>
';
    $target = '<table class="table col-sm-5 table-borderless">
  <tr>
    <td>
    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2020->id . '">System Test Another Time Word e.g. 2020</a>    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_year->id . '">System Test Time Word Category e.g. Year</a>    </td>
    <td>
<a href="/http/view.php?words=' . $wrd_2019->id . '">System Test Another Time Word e.g. 2019</a>    </td>
  </tr>
</table>
';
    $link_types = $wrd_2020->link_types($direction);
    $result = $wrd_2020->dsp_graph($direction, $link_types, 0);
    $diff = str_diff($result, $target);
    if ($diff != '') {
        log_err('Unexpected diff ' . $diff);
        $target = $result;
    }
    $t->dsp('word_dsp->dsp_graph compare to old ' . $direction . ' for ' . $wrd_2020->name, $target, $result);

    // the value table for ABB
    $wrd_ZH = new word($usr);
    $wrd_ZH->name = word::TN_ZH;
    $wrd_ZH->load();
    $wrd_year = new word($usr);
    $wrd_year->name = word::TN_YEAR;
    $wrd_year->load();
    /*
    $target = zut_dsp_list_wrd_val($wrd_ZH->id, $wrd_year->id, $usr->id);
    $target = substr($target,0,208);
    */
    $target = word::TN_2020;
    $target = word::TN_ZH;
    $result = $wrd_ZH->dsp_val_list($wrd_year, $wrd_year->is_mainly(), 0);
    //$t->dsp('word_dsp->dsp_val_list compare to old for '.$wrd_ZH->name, $target, $result, TIMEOUT_LIMIT_PAGE);
    $t->dsp_contains(', word_dsp->dsp_val_list compare to old for ' . $wrd_ZH->name, $target, $result, TIMEOUT_LIMIT_PAGE);

    // the value table for Company
    /*
    $wrd_company = New word_dsp;
    $wrd_company->name = "TEST_WORD";
    $wrd_company->usr = $usr;
    $wrd_company->load();
    $wrd_ratios = New word_dsp;
    $wrd_ratios->name = "Company main ratio";
    $wrd_ratios->usr = $usr;
    $wrd_ratios->load();
    $target = zut_dsp_list_wrd_val($wrd_company->id, $wrd_ratios->id, $usr->id);
    $target = substr($target,0,200);
    $result = $wrd_company->dsp_val_list ($wrd_ratios, $back);
    $result = substr($result,0,200);
    $t->dsp('word_dsp->dsp_val_list compare to old for '.$wrd_company->name, $target, $result);
    */


    $t->header('Test the display selector class (web/html/selector.php)');

    // for testing the selector display a company selector and select ABB
    $phr_corp = $t->load_phrase(word::TN_COMPANY);
    $phr_ZH_INS = $t->load_phrase(phrase::TN_ZH_COMPANY);
    $sel = new html_selector;
    $sel->form = 'test_form';
    $sel->name = 'select_company';
    $sel->sql = $phr_corp->sql_list($phr_corp);
    $sel->selected = $phr_ZH_INS->id;
    $sel->dummy_text = '... please select';
    $result .= $sel->display();
    $target = phrase::TN_ZH_COMPANY;
    $t->dsp_contains(', display_selector->display of all ' . $phr_corp->name . ' with ' . $phr_ZH_INS->name() . ' selected', $target, $result);

}