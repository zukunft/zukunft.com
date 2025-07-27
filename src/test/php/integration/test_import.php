<?php

/*

  test_import.php - TESTing of the IMPORT functions by loading the sample import files
  ---------------
  

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

include_once TEST_CONST_PATH . 'files.php';

use cfg\import\import_file;
use test\test_cleanup;
use const\files as test_files;

function run_import_test($file_list, test_cleanup $t): void
{
    global $usr;

    $t->header('Zukunft.com integration tests by importing the sample cases');

    $import_path = test_files::IMPORT_PATH;

    foreach ($file_list as $json_test_filename) {
        $imf = new import_file();
        $result = $imf->json_file($import_path . $json_test_filename, $usr);
        $target = 'done';
        $t->dsp_contains(', import of ' . $json_test_filename . ' contains at least ' . $target, $target,
            $result->get_last_message(), $t::TIMEOUT_LIMIT_IMPORT);
    }

}
