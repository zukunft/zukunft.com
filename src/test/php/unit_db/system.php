<?php

/*

  test/unit_db/system.php - database unit testing of the system functions
  -----------------------


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

function run_system_unit_db_tests()
{

    global $db_con;

    test_header('Unit database tests of the system functions');

    test_subheader('System error log tests');

    // load the log status list
    $lst = new sys_log_status();
    $result = $lst->load($db_con);
    $target = true;
    test_dsp('unit_db_sys_log->load_stati', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::LOG_STATUS, sys_log_status::NEW);
    $target = 1;
    test_dsp('unit_db_sys_log->check ' . sys_log_status::NEW, $result, $target);

}

