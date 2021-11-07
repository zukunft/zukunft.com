<?php

/*

  test/unit_db/sql_db.php - unit testing of the SQL abstraction layer functions with the current database
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

function run_sql_db_unit_db_tests(testing $t)
{

    global $db_con;

    $t->header('Unit database tests of the SQL abstraction layer class (database/sql_db.php)');

    $t->subheader('Database upgrade functions');

    $result = $db_con->has_column('user_values','user_value');
    $target = false;
    $t->dsp('sql_db->change_column_name', $target, $result);

    $result = $db_con->has_column('user_values','word_value');
    $target = true;
    $t->dsp('sql_db->change_column_name', $target, $result);

    $result = $db_con->change_column_name('user_values','user_value','word_value');
    $target = '';
    $t->dsp('sql_db->change_column_name', $target, $result);

}

