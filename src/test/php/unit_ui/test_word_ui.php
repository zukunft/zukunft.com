<?php

/*

  test_word_ui.php - TESTing of the WORD User Interface
  ------------------
  

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

use api\word_api;
use html\word_dsp;

function run_word_ui_test(testing $t): void
{

    global $usr;
    global $phrase_types;

    $t->header('Test the word frontend scripts (e.g. /word_add.php)');

    // call the add word page and check if at least some keywords are returned
    $wrd = new word($usr);
    $wrd->load_by_name(word_api::TN_READ);
    $vrb_is = cl(db_cl::VERB, verb::IS_A);
    $wrd_type = $phrase_types->default_id();
    $result = file_get_contents('https://zukunft.com/http/word_add.php?verb=' . $vrb_is . '&word=' . $wrd->id() . '&type=1&back=' . $wrd->id());
    $target = word_api::TN_READ;
    $t->dsp_contains(', frontend word_add.php ' . $result . ' contains at least ' . $wrd->name(), $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // test the edit word frontend
    $result = file_get_contents('https://zukunft.com/http/word_edit.php?id=' . $wrd->id() . '&back=' . $wrd->id());
    $target = word_api::TN_READ;
    $t->dsp_contains(', frontend word_edit.php ' . $result . ' contains at least ' . $wrd->name(), $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // test the del word frontend
    $result = file_get_contents('https://zukunft.com/http/word_del.php?id=' . $wrd->id() . '&back=' . $wrd->id());
    $target = word_api::TN_READ;
    $t->dsp_contains(', frontend word_del.php ' . $result . ' contains at least ' . $wrd->name(), $target, $result, TIMEOUT_LIMIT_PAGE);

    $t->header('Test the display list class (classes/display_list.php)');

    // not yet used
    /*
    $phr_corp = $t->load_phrase(word::TEST_NAME_READ);
    $phr_ABB  = $t->load_phrase(TW_ABB,    );
    $sel = New selector;
    $sel->usr        = $usr;
    $sel->form       = 'test_form';
    $sel->name       = 'select_company';
    $sel->sql        = $phr_corp->sql_list ($phr_corp);
    $sel->selected   = $phr_ABB->id;
    $sel->dummy_text = '... please select';
    $result .= $sel->display ();
    $target = TW_ABB;
    $t->dsp_contains(', display_selector->display of all '.$phr_corp->name.' with '.$wrd->name.' selected', $target, $result);
    */

}
