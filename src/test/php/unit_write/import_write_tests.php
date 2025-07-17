<?php

/*

    test/php/unit_write/import_tests.php - testing of the import functions
    ------------------------------------
  

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

namespace unit_write;

include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_IMPORT_PATH . 'convert_wikipedia_table.php';
include_once MODEL_CONST_PATH . 'files.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\import\import_file;
use cfg\verb\verb;
use shared\types\verbs;
use test\test_cleanup;
use const\files as test_files;

class import_write_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;

        $imf = new import_file();


        // start the test section (ts)
        $ts = 'db write import ';
        $t->header($ts);

        $test_name = 'import the test verb';
        $imp_msg = $imf->json_file(test_files::IMPORT_VERBS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the verb has been added to the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert_greater_zero($test_name, $vrb->id());

        $test_name = 'add the description to the test verb via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_VERBS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert($test_name, $vrb->description(), verbs::TEST_ADD_COM);

        $test_name = 'remove the test verb via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_VERBS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        // TODO prio 3 maybe activate but ate least should be activated for normal sandbox objects
        /*
        $test_name = 'test if the test verb has been deleted from the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert($test_name, $vrb->id(), 0);
        */

        $test_name = 'remove the test verb directly as fallback to cleanup the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        if ($vrb->id() > 0) {
            $vrb->set_user($usr);
            $vrb->del();
        }
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert($test_name, $vrb->id(), 0);


    }

}