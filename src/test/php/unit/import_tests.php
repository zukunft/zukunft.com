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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_IMPORT . 'convert_wikipedia_table.php';
include_once paths::MODEL_IMPORT . 'import_convert_xbrl.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\import\convert_wikipedia_table;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\import\import_convert_xbrl;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class import_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $sc = new sql_creator();
        $imp = new import(test_files::SYSTEM_CONFIG_SAMPLE);
        $imp->usr = $usr;
        $usr_msg = new user_message($usr);

        // start the test section (ts)
        $ts = 'unit import ';
        $t->header($ts);

        $test_name = 'YAML import word count';
        $yaml_str = file_get_contents(test_files::SYSTEM_CONFIG_SAMPLE);
        $json_array = yaml_parse($yaml_str);
        $dto = $imp->get_data_object_yaml($json_array);
        $t->assert($test_name, $dto->word_list()->count(), 79);
        $test_name = 'YAML import triple count';
        $t->assert($test_name, $dto->triple_list()->count(), 24);
        $test_name = 'YAML import value count';
        $t->assert($test_name, $dto->value_list()->count(), 47);
        $test_name = 'YAML import sql function count';
        $t->assert($test_name, $dto->word_list()->sql_insert_call_with_par($sc, $usr_msg)->count(), 1);

        $test_name = 'JSON import word count';
        $json_str = file_get_contents(test_files::IMPORT_WORDS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->word_list()->count(), 4);

        $test_name = 'JSON import verbs count';
        $json_str = file_get_contents(test_files::IMPORT_VERBS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->verb_list()->count(), 1);

        $test_name = 'JSON import triple count';
        $json_str = file_get_contents(test_files::IMPORT_TRIPLES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->triple_list()->count(), 6);

        $test_name = 'JSON import source count';
        $json_str = file_get_contents(test_files::IMPORT_SOURCES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->source_list()->count(), 3);

        $test_name = 'JSON import value count';
        $json_str = file_get_contents(test_files::IMPORT_VALUES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->value_list()->count(), 4);

        $test_name = 'JSON import formula count';
        $json_str = file_get_contents(test_files::IMPORT_FORMULAS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->formula_list()->count(), 4);

        $test_name = 'JSON import warning creation';
        $json_str = file_get_contents(test_files::IMPORT_WARNING);
        $imp = new import(test_paths::IMPORT . 'warning_and_error_test.json');
        $imp->put_json_direct($json_str, $usr_msg);
        $target = 'Unknown element "test"';
        $t->assert($test_name, $usr_msg->get_last_message_translated(), $target);

        $test_name = 'JSON import newer version detection';
        $usr_msg = new user_message($usr);
        $json_str = file_get_contents(test_files::IMPORT_VERSION_NEWER_TEST);
        $imp = new import(test_files::IMPORT_VERSION_NEWER_TEST);
        $imp->put_json_direct($json_str, $usr_msg);
        $target = 'Import file has been created with version "9.9.9"';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $target);

        $t->subheader($ts . 'convert');

        $test_name = 'wikipedia table to zukunft.com JSON string';
        $in_table = file_get_contents(test_files::IMPORT_DEMOCRACY_INDEX_TXT);
        $json_str = file_get_contents(test_files::IMPORT_DEMOCRACY_INDEX);
        $conv_wiki = new convert_wikipedia_table;
        $conv_str = $conv_wiki->convert($in_table, $usr, test_base::TEST_TIMESTAMP,
            ['Democracy Index'],
            'country', 1,
            'year', 'time', 3);
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'wikipedia table json to zukunft.com JSON';
        $in_table = file_get_contents(test_files::IMPORT_COUNTRY_ISO_WIKI);
        $json_str = file_get_contents(test_files::IMPORT_COUNTRY_ISO);
        $context_str = file_get_contents(test_files::IMPORT_COUNTRY_ISO_CONTEXT);
        $conv_wiki = new convert_wikipedia_table;
        // TODO review the parameter context
        $conv_str = $conv_wiki->convert_wiki_json($in_table, $usr, test_base::TEST_TIMESTAMP, $context_str,
            ['country', 'ISO 3166'], [], 1,
            'English short name  (using title case)','country',
            'Alpha-3 code',      '');
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'wikipedia data table json to zukunft.com JSON';
        $in_table = file_get_contents(test_files::IMPORT_CURRENCY_WIKI);
        $json_str = file_get_contents(test_files::IMPORT_CURRENCY_CONVERT);
        $context_str = file_get_contents(test_files::IMPORT_CURRENCY_CONTEXT);
        $conv_wiki = new convert_wikipedia_table;
        $conv_str = $conv_wiki->convert_wiki_json(
            $in_table, $usr, test_base::TEST_TIMESTAMP, $context_str);
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

        // XBRL fileset unpacker (first step of import_convert_xbrl)
        $lib = new library();
        $test_name = 'XBRL zip unpacker creates a unique extraction folder';
        $conv_xbrl = new import_convert_xbrl;
        $folder = $conv_xbrl->unzip(
            test_files::IMPORT_XBRL_ABB_2013_ZIP,
            test_paths::IMPORT_XBRL,
            'unit_test'
        );
        $t->assert($test_name, is_dir($folder), true);

        $test_name = 'XBRL zip unpacker extracts at least one file';
        $extracted = array_diff(scandir($folder), ['.', '..']);
        $t->assert($test_name, count($extracted) > 0, true);

        // cleanup so the test stays repeatable without leaving the working tree dirty
        $lib->dir_remove($folder);

        $test_name = 'XBRL zip unpacker rejects a missing input file';
        $caught = false;
        try {
            $conv_xbrl->unzip(
                test_paths::IMPORT_XBRL_ZIP . 'does_not_exist' . test_files::ZIP,
                test_paths::IMPORT_XBRL,
                'unit_test'
            );
        } catch (\RuntimeException $e) {
            $caught = true;
        }
        $t->assert($test_name, $caught, true);

    }

}