<?php

/*

  test_word.php - TESTing of the word class
  -------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function  run_user_sandbox_test()
{

    global $usr1;
    global $exe_start_time;

    test_header('Test the user sandbox class (classes/user_sandbox.php)');

    test_subheader('Test the is_same and is_similar function');

    // a word is not the same as the same word that represents a formula
    $wrd1 = new word;
    $wrd1->type_id = cl(DBL_WORD_TYPE_FORMULA_LINK);
    $wrd1->name = TW_MIO;
    $wrd2 = new word;
    $wrd2->type_id = cl(DBL_WORD_TYPE_NORMAL);
    $wrd2->name = TW_MIO;
    $target = false;
    $result = $wrd1->is_same($wrd2);
    $exe_start_time = test_show_result("a word is not the same as the same word that represents a formula", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... but it is similar
    $target = true;
    $result = $wrd1->is_similar($wrd2);
    $exe_start_time = test_show_result("... but it is similar", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    test_subheader('Test the saving function');

    // create a new source (user_sandbox->save case 1)
    $src = new source;
    $src->name = TS_IPCC_AR6_SYNTHESIS;
    $src->usr = $usr1;
    $result = num2bool($src->save());
    $target = true;
    $exe_start_time = test_show_result('user_sandbox->save create a new source', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // remember the id
    $src_id = 0;
    if ($result) {
        $src_id = $src->id;
    }

    // check if the source has been saved (check user_sandbox->save case 1)
    $src = new source;
    $src->id = $src_id;
    $src->usr = $usr1;
    if ($src->load()) {
        $result = $src->name;
    }
    $target = TS_IPCC_AR6_SYNTHESIS;
    $exe_start_time = test_show_result('user_sandbox->save check created source', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // update the source url by name (user_sandbox->save case 2)
    $src = new source;
    $src->name = TS_IPCC_AR6_SYNTHESIS;
    $src->usr = $usr1;
    $src->url = TS_IPCC_AR6_SYNTHESIS_URL;
    $result = num2bool($src->save());
    $target = true;
    $exe_start_time = test_show_result('user_sandbox->save update the source url by name', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // remember the id
    $src_id = 0;
    if ($result) {
        $src_id = $src->id;
    }

    // check if the source url has been updates (check user_sandbox->save case 2)
    $src = new source;
    $src->id = $src_id;
    $src->usr = $usr1;
    if ($src->load()) {
        $result = $src->url;
    }
    $target = TS_IPCC_AR6_SYNTHESIS_URL;
    $exe_start_time = test_show_result('user_sandbox->save check if the source url has been updates', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}

