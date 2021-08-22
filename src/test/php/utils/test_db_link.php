<?php

/*

  test_db_link.php - TESTing of the DataBase LINK functions
  ----------------
  

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

function run_db_link_test()
{

    test_header('Test database link functions (zu_lib_sql_code_link.php)');

    // test code link
    $id = change_log_table::WORD;
    $target = 2;
    $result = cl(db_cl::LOG_TABLE, $id);
    test_dsp(", sql_code_link " . $id, $target, $result);

}