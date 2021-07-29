<?php

/*

  test/unit_db/view.php - database unit testing of the view functions
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

function run_view_unit_db_tests()
{

    global $db_con;
    global $exe_start_time;

    global $view_types_hash;

    test_header('Unit database tests of the view class (src/main/php/model/value/view.php)');

    test_subheader('View types tests');

    // load the view types
    $result = init_view_types();
    $target = true;
    $exe_start_time = test_show_result('unit_db_view->init_view_types', $target, $result, $exe_start_time);

    // ... and check if at least the most critical is loaded
    $result = $view_types_hash[DBL_VIEW_TYPE_ENTRY];
    $target = 1;
    $exe_start_time = test_show_result('unit_db_view->check ' . DBL_VIEW_TYPE_ENTRY, $result, $target, $exe_start_time);

}

