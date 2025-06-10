<?php

/*
 *
    test/php/integration/import_tests.php - testing of the import functions by loading the sample import files
    -------------------------------------

    similar to the unit/import_tests but including the actual database import


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

namespace integration;

include_once TEST_CONST_PATH . 'files.php';

use cfg\import\import_file;
use test\test_cleanup;
use const\files as test_files;

class import_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;

        // init
        $imf = new import_file();
        $t->name = 'integration import->';

        // start the test section (ts)
        $ts = 'integration import ';
        $t->header($ts);

        $t->subheader($ts . 'warnings');
        $test_name = 'double formula';
        $result = $imf->json_file(test_files::IMPORT_DOUBLE_FORMULA, $usr);
        $target = 'formula scale million to one is twice in ' . test_files::IMPORT_DOUBLE_FORMULA;
        // TODO activate
        //$t->assert($test_name, $result->get_last_message(), $target, $t::TIMEOUT_LIMIT_IMPORT);
    }

}
