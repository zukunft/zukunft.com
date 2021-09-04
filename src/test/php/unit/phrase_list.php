<?php

/*

  test/php/unit/phrase_list.php - unit tests related to a phrase list
  -----------------------------


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

/**
 * create the standard phrase list test object without database connection
 * TODO base the creation on resources
 */
function test_unit_create_phrase_list(): phrase_list
{
    global $usr;
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $wrd1 = new word();
    $wrd1->id = 1;
    $wrd1->name = word::TN_ADD;
    $wrd1->usr = $usr;
    $phr_lst->add($wrd1->phrase());
    $wrd2 = new word();
    $wrd2->id = 2;
    $wrd1->name = word::TN_RENAMED;
    $wrd2->usr = $usr;
    $wrd2->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME);
    $phr_lst->add($wrd2->phrase());
    return $phr_lst;
}

function run_phrase_list_unit_tests()
{

    test_header('Unit tests of the phrase list class (src/main/php/model/phrase/phrase_list.php)');


    test_subheader('Selection tests');

    $phr_lst = test_unit_create_phrase_list();
    $phr_lst->ex_time();
    $result = true;
    $target = true;
    test_dsp('word->import check name', $target, $result);

}

