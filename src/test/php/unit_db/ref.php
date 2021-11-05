<?php

/*

  test/unit_db/ref.php - database unit testing of reference types
  --------------------


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

function run_ref_unit_db_tests(testing $t)
{

    global $db_con;

    $t->header('Unit database tests of the ref class (src/main/php/model/ref/ref.php)');

    $t->subheader('Reference types tests');

    // load the ref types
    $lst = new ref_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_ref->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::WORD_TYPE, word_type_list::DBL_NORMAL);
    $target = 1;
    $t->dsp('unit_db_ref->check ' . word_type_list::DBL_NORMAL, $result, $target);

}

