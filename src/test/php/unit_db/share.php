<?php

/*

  test/unit_db/share.php - database unit testing of the share handling
  ---------------------


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

function run_share_unit_db_tests(testing $t)
{

    global $db_con;

    $t->header('Unit database tests of the share handling');

    $t->subheader('Protection types tests');

    // load the share types
    $lst = new share_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_share->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC);
    $target = 1;
    $t->dsp('unit_db_share->check ' . share_type_list::DBL_PUBLIC, $result, $target);

}

