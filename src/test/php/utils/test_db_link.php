<?php

/*

  test_db_link.php - TESTing of the DataBase LINK functions
  ----------------
  

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

use cfg\change_log_table;
use test\test_cleanup;

function run_db_link_test(test_cleanup $t): void
{
    global $change_log_tables;

    $t->header('Test database link functions (zu_lib_sql_code_link.php)');

    // test code link
    $id = change_log_table::WORD;
    $target = 5;
    $result = $change_log_tables->id($id);
    $t->display(", sql_code_link " . $id, $target, $result);

}