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

include_once SHARED_ENUM_PATH . 'change_tables.php';

use shared\enum\change_tables;
use test\all_tests;

function run_db_link_test(all_tests $t): void
{
    global $cng_tbl_cac;

    $t->header('Test database link functions');

    // test code link
    $id = change_tables::WORD;
    $target = 5;
    $result = $cng_tbl_cac->id($id);
    $t->display(", sql_code_link " . $id, $target, $result);

}