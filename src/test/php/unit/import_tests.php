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
include_once MODEL_CONST_PATH . 'files.php';

use cfg\const\files;
use cfg\db\sql_creator;
use cfg\import\convert_wikipedia_table;
use cfg\import\import;
use html\html_base;
use test\test_base;
use test\test_cleanup;

class import_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $sc = new sql_creator();
        $imp = new import;

        $t->subheader('Import unit tests');

        $test_name = 'YAML import word count';
        $yaml_str = file_get_contents(files::SYSTEM_CONFIG);
        $json_array = yaml_parse($yaml_str);
        $dto = $imp->get_data_object_yaml($json_array, $usr);
        $t->assert($test_name, $dto->word_list()->count(), 73);
        $test_name = 'YAML import triple count';
        $t->assert($test_name, $dto->triple_list()->count(), 22);
        $test_name = 'YAML import value count';
        $t->assert($test_name, $dto->value_list()->count(), 41);
        $test_name = 'YAML import sql function count';
        $t->assert($test_name, $dto->word_list()->sql_call_with_par($sc)->count(), 1);

        $test_name = 'JSON import word count';
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . '/unit_tests/words.json');
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr);
        $t->assert($test_name, $dto->word_list()->count(), 3);

        $test_name = 'JSON import warning creation';
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'warning_and_error_test.json');
        $imp = new import;
        $result = $imp->put_json_direct($json_str, $usr);
        $target = 'Unknown element test';
        $t->assert($test_name, $result->get_last_message(), $target);

        $t->subheader('Convert unit tests');

        $test_name = 'wikipedia table to zukunft.com JSON string';
        $in_table = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/democratie_index_table.txt');
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/democratie_index_table.json');
        $conv_wiki = new convert_wikipedia_table;
        $conv_str = $conv_wiki->convert($in_table, $usr, test_base::TEST_TIMESTAMP,
            ['Democracy Index'],
            'Country', 1,
            'Year', 'time', 3);
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'wikipedia table json to zukunft.com JSON';
        $in_table = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/country-ISO-3166-wiki.json');
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/country-ISO-3166.json');
        $context_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/country-ISO-3166-context.json');
        $conv_wiki = new convert_wikipedia_table;
        // TODO review the parameter context
        $conv_str = $conv_wiki->convert_wiki_json($in_table, $usr, test_base::TEST_TIMESTAMP, $context_str,
            ['Country', 'ISO 3166'], [], 1,
            'English short name  (using title case)','Country',
            'Alpha-3 code',      '');
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'wikipedia data table json to zukunft.com JSON';
        $in_table = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/currency-wiki.json');
        $json_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/currency.json');
        $context_str = file_get_contents(PATH_TEST_IMPORT_FILES . 'wikipedia/currency-context.json');
        $conv_wiki = new convert_wikipedia_table;
        $conv_str = $conv_wiki->convert_wiki_json(
            $in_table, $usr, test_base::TEST_TIMESTAMP, $context_str);
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

    }

}