<?php

/*

  test_reset_db.php - To reset the main database table: NEVER use this in production! Just for the development process
  -----------------


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

function run_db_truncate()
{
    global $db_con;

    //$sql = 'TRUNCATE $1 CASCADE;';
    //$db_con->exe($sql, 'truncate', array($db_con->get_table_name(DB_TYPE_WORD)));
    //$db_con->exe($sql, 'truncate', array($db_con->get_table_name(DB_TYPE_WORD_LINK)));
    $sql = 'TRUNCATE ' . $db_con->get_table_name(DB_TYPE_WORD) . ' CASCADE;';
    $db_con->exe($sql);
    $sql = 'TRUNCATE ' . $db_con->get_table_name(DB_TYPE_WORD_LINK) . ' CASCADE;';
    $db_con->exe($sql);
}

function run_db_seq_reset()
{
    global $db_con;

    //$sql = 'ALTER SEQUENCE $1 RESTART 1;';
    //$db_con->exe($sql, 'reset_seq', array('words_word_id_seq'));
    //$db_con->exe($sql, 'reset_seq', array('word_links_word_link_id_seq'));
    $sql = 'ALTER SEQUENCE words_word_id_seq RESTART 1;';
    $db_con->exe($sql);
    $sql = 'ALTER SEQUENCE word_links_word_link_id_seq RESTART 1;';
    $db_con->exe($sql);

}

include_once '../src/main/php/zu_lib.php';

// open database and display header
$db_con = prg_start("test_reset_db");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // load the testing base functions
        include_once '../src/test/php/test_base.php';

        // use the system user for the database updates
        $usr = new user;
        $usr->id = SYSTEM_USER_ID;

        // run reset the main database tables
        run_db_truncate();
        run_db_seq_reset();

        // reload the base configuration
        import_base_config();

        /*
         * For testing the system setup
         */

        // drop the database

        // create the database from the sql structure file

        // reload the system database rows (all db rows, that have a code id)
    }
}