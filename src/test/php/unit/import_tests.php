<?php

/*

    test/unit/import.php - testing of the import functions
    --------------------
  

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

namespace unit;

include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_IMPORT_PATH . 'convert_wikipedia_table.php';

use cfg\import\convert_wikipedia_table;
use cfg\import\import;
use html\html_base;
use test\test_cleanup;

class import_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Import unit tests');

        $test_name = 'JSON import warning creation';
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'warning_and_error_test.json');
        $file_import = new import;
        $result = $file_import->put($json_str, $usr);
        $target = 'Unknown element test';
        $t->assert($test_name, $result->get_last_message(), $target);

        $t->subheader('Convert unit tests');

        $test_name = 'wikipedia table to zukunft.com JSON string';
        $in_table = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/democratie_index_table.txt');
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/democratie_index_table.json');
        $conv_wiki = new convert_wikipedia_table;
        $conv_str = $conv_wiki->convert($in_table);
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);
    }

}