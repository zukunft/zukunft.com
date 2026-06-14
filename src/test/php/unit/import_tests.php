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
include_once paths::SHARED . 'json_fields.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\import\convert_wikipedia_table;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\import\import_convert_xbrl;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
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

        // the main stock triples have a distinct impact (the market capitalisation)
        // so that the related phrases of e.g. CHF are always shown in the same order
        $json_str = file_get_contents(test_files::IMPORT_PORTFOLIO_INSTRUMENTS);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $impacts = [];
        foreach (['ROG main trading currency', 'UBSG main trading currency', 'ABBN main trading currency',
                     'CFR main trading currency', 'ZURN main trading currency'] as $trp_name) {
            $impacts[] = $dto->triple_list()->get_by_name($trp_name)?->get_impact();
        }
        $test_name = 'JSON import sets a distinct impact for each main stock triple';
        $t->assert($test_name, count(array_unique($impacts)), 5);
        $test_name = '... and no main stock triple is without an impact';
        $t->assert_false($test_name, in_array(null, $impacts));

        // covers the simple "total = price * quantity" calculation in result_calc_simple.json:
        // the importer must populate values, the formula and the pre-calculated result
        $json_str = file_get_contents(test_files::IMPORT_RESULT_CALC . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $test_name = 'JSON import result_calc word count';
        $t->assert($test_name, $dto->word_list()->count(), 5);
        $test_name = 'JSON import result_calc value count';
        $t->assert($test_name, $dto->value_list()->count(), 2);
        $test_name = 'JSON import result_calc formula count';
        $t->assert($test_name, $dto->formula_list()->count(), 1);
        $test_name = 'JSON import result_calc result count';
        $t->assert($test_name, $dto->result_list()->count(), 1);

        // covers the validation of the import based on the pre-calculated results:
        // the result of "total = price * quantity" must be reproducible
        // based on the values and formulas of the import file
        $test_name = 'JSON import calc validation confirms a consistent import file';
        $usr_msg = new user_message($usr);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $t->assert($test_name, $dto->result_check_list()->count(), 1);
        $test_name = '... and reports no problem';
        $t->assert_true($test_name, $usr_msg->is_ok());

        $test_name = 'JSON import calc validation reports a result mismatch';
        $usr_msg = new user_message($usr);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_MISMATCH . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $target = 'the imported result 11 of ' . $dto->result_check_list()->lst()[0]->grp()->phrase_list()->dsp_name()
            . ' does not match the result 10 calculated based on the imported values';
        $t->assert($test_name, $usr_msg->all_message_text(), $target);

        $test_name = 'JSON import calc validation reports a missing value';
        $usr_msg = new user_message($usr);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_VALUE_MISSING . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $usr_msg);
        $target = 'the value for "quantity" to validate the result of '
            . $dto->result_check_list()->lst()[0]->grp()->phrase_list()->dsp_name()
            . ' is missing in the import message';
        $t->assert($test_name, $usr_msg->all_message_text(), $target);

        $test_name = 'JSON import warning creation';
        $usr_msg = new user_message($usr);
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
        $test_name = 'XBRL zip unpacker extracts the fileset into the folder named like the fileset';
        $conv_xbrl = new import_convert_xbrl;
        $folder = $conv_xbrl->unzip(
            test_files::IMPORT_XBRL_ABB_2013_ZIP,
            test_paths::IMPORT_XBRL,
            ''
        );
        $t->assert($test_name, $folder, test_files::IMPORT_XBRL_ABB_2013_DIR);

        $test_name = 'XBRL zip unpacker extracts at least one file';
        $extracted = array_diff(scandir($folder), ['.', '..']);
        $t->assert($test_name, count($extracted) > 0, true);

        // read the segment sales values from the unpacked fileset
        // and check that the created import json matches the expected json
        $test_name = 'XBRL fileset values are converted to the expected import json';
        $usr_msg = new user_message($usr);
        $conv_str = $conv_xbrl->convert_folder($folder, $conv_xbrl->instance_file_name('2013'), test_base::TEST_TIMESTAMP, $usr_msg);
        $result = json_decode($conv_str, true);
        $target = json_decode(file_get_contents(test_files::IMPORT_XBRL_ABB_2013), true);
        $t->assert_json($test_name, $result, $target);

        // check that a fact with dimension members is converted to a value
        // e.g. the 2013 level 1 assets of the recurring fair value measurements
        $test_name = 'XBRL fact with dimension members is converted to a value';
        $target_words = [import_convert_xbrl::ISSUER_ABB, 'assets fair value disclosure',
            'fair value inputs level 1', 'fair value measurements recurring', 'USD', '2013'];
        $fact_number = '';
        foreach ($result[json_fields::VALUES] as $val_json) {
            if ($val_json[json_fields::WORDS] == $target_words) {
                $fact_number = $val_json[json_fields::NUMBER];
            }
        }
        $t->assert($test_name, $fact_number, '129000000');

        // check that the calculation linkbase concept names are split into words
        // e.g. "us-gaap_OtherComprehensiveIncomeLossNetOfTax" leads to the word "comprehensive"
        $test_name = 'XBRL concept names are split into words';
        $wrd_names = array_column($result[json_fields::WORDS], json_fields::NAME);
        $t->assert($test_name, in_array('comprehensive', $wrd_names), true);

        // check that a verb within a concept name leads to a triple
        // e.g. "...NetOfTax" leads to the triple "net" "of" "tax"
        $test_name = 'XBRL concept name verbs lead to triples';
        $trp_found = false;
        foreach ($result[json_fields::TRIPLES] as $trp_json) {
            if (($trp_json[json_fields::EX_FROM] ?? '') == 'net'
                and ($trp_json[json_fields::EX_VERB] ?? '') == 'of'
                and ($trp_json[json_fields::EX_TO] ?? '') == 'tax') {
                $trp_found = true;
            }
        }
        $t->assert($test_name, $trp_found, true);

        // save the created json in the fileset folder
        // and leave the unpacked files in the folder for manual checks
        $test_name = 'XBRL convert job saves the json in the fileset folder';
        $usr_msg = new user_message($usr);
        $json_path = $conv_xbrl->convert_folder_to_file($folder, $conv_xbrl->instance_file_name('2013'), test_base::TEST_TIMESTAMP, $usr_msg);
        $t->assert($test_name, $json_path, test_files::IMPORT_XBRL_ABB_2013);

        // convert an XBRL facts snippet to an import json
        // the facts transformer reports inconsistencies via the user message
        // instead of throwing exceptions
        $test_name = 'XBRL facts xml is converted to the expected import json';
        $usr_msg = new user_message($usr);
        $facts_xml = file_get_contents(test_files::IMPORT_XBRL_SAMPLE_XML);
        $conv_str = $conv_xbrl->convert_facts($facts_xml, test_base::TEST_TIMESTAMP, $usr_msg);
        $result = json_decode($conv_str, true);
        $target = json_decode(file_get_contents(test_files::IMPORT_XBRL_SAMPLE_JSON), true);
        $t->assert_json($test_name, $result, $target);
        $test_name = '... and consistent facts report no problem';
        $t->assert_true($test_name, $usr_msg->is_ok());

        $test_name = 'XBRL facts converter saves the json next to the facts file';
        $usr_msg = new user_message($usr);
        $json_path = $conv_xbrl->convert_facts_to_file(
            test_files::IMPORT_XBRL_ABB_2013_DIR,
            test_files::IMPORT_XBRL_SAMPLE_NAME . test_files::XML,
            test_base::TEST_TIMESTAMP, $usr_msg);
        $t->assert($test_name, $json_path, test_files::IMPORT_XBRL_SAMPLE_JSON);

        $test_name = 'XBRL facts converter reports conflicting fact values';
        $usr_msg = new user_message($usr);
        $conflict_xml = file_get_contents(test_files::IMPORT_XBRL_CONFLICT_XML);
        $conv_xbrl->convert_facts($conflict_xml, test_base::TEST_TIMESTAMP, $usr_msg);
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), 'conflicting values');

        $test_name = 'XBRL facts of a part year context are skipped';
        $usr_msg = new user_message($usr);
        $period_xml = file_get_contents(test_files::IMPORT_XBRL_PERIOD_XML);
        $conv_str = $conv_xbrl->convert_facts($period_xml, test_base::TEST_TIMESTAMP, $usr_msg);
        $result = json_decode($conv_str, true);
        $t->assert($test_name, count($result[json_fields::VALUES]), 1);

        $test_name = 'XBRL facts converter reports when no facts are found';
        $usr_msg = new user_message($usr);
        $conv_xbrl->convert_facts('', test_base::TEST_TIMESTAMP, $usr_msg);
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), 'no XBRL facts');

        $test_name = 'XBRL facts converter reports a missing facts file';
        $usr_msg = new user_message($usr);
        $json_path = $conv_xbrl->convert_facts_to_file(
            test_files::IMPORT_XBRL_ABB_2013_DIR,
            test_files::IMPORT_XBRL_MISSING_NAME . test_files::XML,
            test_base::TEST_TIMESTAMP, $usr_msg);
        $t->assert($test_name, $json_path, '');
        $test_name = '... and the user message reports the problem';
        $t->assert_false($test_name, $usr_msg->is_ok());

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