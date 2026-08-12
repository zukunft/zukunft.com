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
include_once paths::SHARED_CONST . 'views.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\import\convert_wikipedia_table;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\import\import_convert_xbrl;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class import_tests
{
    function run(test_cleanup $t): void
    {
        $sc = new sql_creator();
        $imp = new import(test_files::SYSTEM_CONFIG_SAMPLE);
        $imp->usr = $t->usr1;
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'unit import ';
        $t->header($ts);

        $test_name = 'YAML import word count';
        $yaml_str = file_get_contents(test_files::SYSTEM_CONFIG_SAMPLE);
        $json_array = yaml_parse($yaml_str);
        $dto = $imp->get_data_object_yaml($json_array);
        // reading and mapping the yaml sample file takes longer than a normal unit function, so a file timeout is used
        $t->assert($test_name, $dto->word_list()->count(), 79, $t::TIMEOUT_LIMIT_FILE);
        $test_name = 'YAML import triple count';
        $t->assert($test_name, $dto->triple_list()->count(), 24);
        $test_name = 'YAML import value count';
        $t->assert($test_name, $dto->value_list()->count(), 47);
        $test_name = 'YAML import sql function count';
        // building the sql insert calls from the imported data takes longer than a normal unit function
        $t->assert($test_name, $dto->word_list()->sql_insert_call_with_par($sc, $msg)->count(), 1, $t::TIMEOUT_LIMIT_FILE);

        $test_name = 'JSON import word count';
        $json_str = file_get_contents(test_files::IMPORT_WORDS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->word_list()->count(), 4);

        $test_name = 'JSON import verbs count';
        $json_str = file_get_contents(test_files::IMPORT_VERBS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->verb_list()->count(), 1);

        $test_name = 'JSON import triple count';
        $json_str = file_get_contents(test_files::IMPORT_TRIPLES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->triple_list()->count(), 6);

        $test_name = 'JSON import source count';
        $json_str = file_get_contents(test_files::IMPORT_SOURCES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->source_list()->count(), 3);

        $test_name = 'JSON import value count';
        $json_str = file_get_contents(test_files::IMPORT_VALUES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        // reading and mapping the json value file takes longer than a normal unit function, so a file timeout is used
        $t->assert($test_name, $dto->value_list()->count(), 4, $t::TIMEOUT_LIMIT_FILE);

        // the compact "value-list" field shares a context and source for many values that
        // differ only by one phrase and the number; each of the 1653 "values" entries (across
        // 10 lists) is expanded to one value and added to the 7 plain values of the same file
        $test_name = 'JSON import value-list count';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_TRAVEL_SCORING_VALUE_LIST);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        // expanding the 1653 compact value-list entries to single values is import-heavy, so an import timeout is used
        $t->assert($test_name, $dto->value_list()->count(), 1660, $t::TIMEOUT_LIMIT_IMPORT);

        // the compact "phrase-values" map assigns a number directly to a single phrase
        // (here three "<city> inhabitants" triples), expanded to one value per entry
        $test_name = 'JSON import phrase-values count';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_PHRASE_VALUES . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->value_list()->count(), 3);

        $test_name = 'JSON import formula count';
        $json_str = file_get_contents(test_files::IMPORT_FORMULAS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->formula_list()->count(), 4);

        // the main stock triples have a distinct impact (the market capitalisation)
        // so that the related phrases of e.g. CHF are always shown in the same order
        $json_str = file_get_contents(test_files::IMPORT_PORTFOLIO_INSTRUMENTS);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $impacts = [];
        foreach (['ROG main trading currency', 'UBSG main trading currency', 'ABBN main trading currency',
                     'CFR main trading currency', 'ZURN main trading currency'] as $trp_name) {
            $impacts[] = $dto->triple_list()->get_by_name($trp_name, $msg)?->get_impact();
        }
        $test_name = 'JSON import sets a distinct impact for each main stock triple';
        // reading and mapping the portfolio json file takes longer than a normal unit function, so a file timeout is used
        $t->assert($test_name, count(array_unique($impacts)), 5, $t::TIMEOUT_LIMIT_FILE);
        $test_name = '... and no main stock triple is without an impact';
        $t->assert_false($test_name, in_array(null, $impacts));

        // covers the simple "total = price * quantity" calculation in result_calc_simple.json:
        // the importer must populate values, the formula and the pre-calculated result
        $json_str = file_get_contents(test_files::IMPORT_RESULT_CALC . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
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
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->result_check_list()->count(), 1);
        $test_name = '... and reports no problem';
        $t->assert_true($test_name, $msg->is_ok());

        $test_name = 'JSON import calc validation reports a result mismatch';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_MISMATCH . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $target = 'the imported result 11 of ' . $dto->result_check_list()->lst()[0]->grp()->phrase_list()->dsp_name()
            . ' does not match the result 10 calculated based on the imported values';
        $t->assert($test_name, $msg->all_message_text(), $target);

        $test_name = 'JSON import calc validation reports a missing value';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_VALUE_MISSING . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $target = 'the value for "quantity" to validate the result of '
            . $dto->result_check_list()->lst()[0]->grp()->phrase_list()->dsp_name()
            . ' is missing in the import message';
        $t->assert($test_name, $msg->all_message_text(), $target);

        $test_name = 'JSON import warning creation';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_WARNING);
        $imp = new import(test_paths::IMPORT . 'warning_and_error_test.json');
        $imp->put_json_direct($json_str, $msg);
        $target = 'Unknown element "test"';
        $t->assert($test_name, $msg->get_last_message_translated(), $target);

        $test_name = 'JSON import newer version detection';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_VERSION_NEWER_TEST);
        $imp = new import(test_files::IMPORT_VERSION_NEWER_TEST);
        $imp->put_json_direct($json_str, $msg);
        $target = 'Import file has been created with version "9.9.9"';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        $t->subheader($ts . 'duplicate component check');
        $imp = new import(test_files::SYSTEM_CONFIG_SAMPLE);
        $imp->usr = $t->usr1;

        // a component name is the key the views use, so the same name twice in one import is reported
        $test_name = 'JSON import reports a component defined twice';
        $msg = new user_message($t->usr1);
        $json_array = [json_fields::COMPONENTS => [
            [json_fields::NAME => components::TEST_VALUES_NAME, json_fields::TYPE_NAME => component_types::VALUES_RELATED],
            [json_fields::NAME => components::TEST_VALUES_NAME, json_fields::TYPE_NAME => component_types::PHRASES_RELATED]
        ]];
        $imp->get_data_object($json_array, $msg);
        $target = 'The view component with the name "' . components::TEST_VALUES_NAME
            . '" is defined more than once in the same import.';
        $t->assert($test_name, $msg->all_message_text(), $target);

        // two components with different names are a valid import
        $test_name = 'JSON import accepts components with unique names';
        $msg = new user_message($t->usr1);
        $json_array = [json_fields::COMPONENTS => [
            [json_fields::NAME => components::TEST_VALUES_NAME, json_fields::TYPE_NAME => component_types::VALUES_RELATED],
            [json_fields::NAME => components::TEST_RESULTS_NAME, json_fields::TYPE_NAME => component_types::RESULTS_RELATED]
        ]];
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());

        $t->subheader($ts . 'view term assignment check');

        // the terms of the "assigned" list are added to the database by their own import step, so
        // at this point they usually have no id yet and are told apart by their name
        $test_name = 'JSON import accepts a view with two terms that are not yet in the database';
        $msg = new user_message($t->usr1);
        $json_array = [json_fields::VIEWS => [[
            json_fields::NAME => views::TEST_ADD_NAME,
            json_fields::ASSIGNED => [word_names::PI, word_names::E]
        ]]];
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_not_contains($test_name, $msg->all_message_text(), 'more than once');

        // the same term twice in the "assigned" list of one view is a real double
        $test_name = 'JSON import reports a term assigned twice to the same view';
        $msg = new user_message($t->usr1);
        $json_array[json_fields::VIEWS][0][json_fields::ASSIGNED] = [word_names::PI, word_names::PI];
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'more than once');

        $t->subheader($ts . 'view row balance check');

        // a view that opens a row with row_right but never closes it with row_end is reported
        $test_name = 'JSON import reports a view with an unclosed row';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_VIEW_ROW_NOT_CLOSED . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'are not balanced';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        // the same view is a valid import once the row is closed with a row_end component
        $test_name = 'JSON import accepts a view with a closed row';
        $msg = new user_message($t->usr1);
        $json_array[json_fields::VIEWS][0][json_fields::COMPONENTS][] = [
            json_fields::POSITION => 3,
            json_fields::NAME => 'system formatter row end'
        ];
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());

        $t->subheader($ts . 'view component position check');

        // a view that uses the same component position twice (and so misses one) is reported as an error
        $test_name = 'JSON import reports a double component position';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_VIEW_COMPONENT_POS_DOUBLE . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'is used more than once';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        // the same view is a valid import once every component has a unique position from 1 to n
        $test_name = 'JSON import accepts a view with complete component positions';
        $msg = new user_message($t->usr1);
        $json_array[json_fields::VIEWS][0][json_fields::COMPONENTS][1][json_fields::POSITION] = 2;
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());

        // json has no order, so a position that differs from the json order is only a warning
        // that does not block the import but is reported because it could confuse the user
        $test_name = 'JSON import reports a position differing from the json order as a warning only';
        $msg = new user_message($t->usr1);
        $json_array[json_fields::VIEWS][0][json_fields::COMPONENTS][0][json_fields::POSITION] = 2;
        $json_array[json_fields::VIEWS][0][json_fields::COMPONENTS][1][json_fields::POSITION] = 1;
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());
        $test_name = 'the json order warning names the unexpected position';
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'Unexpected position');

        $t->subheader($ts . 'triple link uniqueness check');

        // two triples that share the same from/verb/to link but carry different names give an
        // ambiguous link id, so the import reports it (see docs/llm/json_structure.md)
        $test_name = 'JSON import reports two triples with the same link but different names';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_TRIPLE_LINK_AMBIGUOUS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'the from/verb/to link "elevation kind of rank" is used by two triples with different names';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        // the same two names are a valid import once their links differ (here the second uses "of")
        $test_name = 'JSON import accepts two triples with different names and different links';
        $msg = new user_message($t->usr1);
        $json_array[json_fields::TRIPLES][1][json_fields::EX_VERB] = 'of';
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());

        $t->subheader($ts . 'convert');

        $test_name = 'wikipedia table to zukunft.com JSON string';
        $in_table = file_get_contents(test_files::IMPORT_DEMOCRACY_INDEX_TXT);
        $json_str = file_get_contents(test_files::IMPORT_DEMOCRACY_INDEX);
        $conv_wiki = new convert_wikipedia_table;
        $conv_str = $conv_wiki->convert($in_table, $t->usr1, test_base::TEST_TIMESTAMP,
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
        $conv_str = $conv_wiki->convert_wiki_json($in_table, $t->usr1, test_base::TEST_TIMESTAMP, $context_str,
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
            $in_table, $t->usr1, test_base::TEST_TIMESTAMP, $context_str);
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
        // unpacking the xbrl zip archive is a file operation, so a file timeout is used to avoid a false timeout
        $t->assert($test_name, is_dir($folder), true, $t::TIMEOUT_LIMIT_FILE);

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

        $t->subheader($ts . 'time estimate');

        // the estimated total import time must always stay positive and never drop below the time
        // already elapsed (which would show a negative estimate or a progress above 100%);
        // the elapsed time is fixed deterministically by setting the start time and passing the
        // matching check time, so the cases below do not depend on the real execution speed
        $imp = new import();
        $imp->est_time_total = 3;
        $imp->est_time_step = 2.4;
        $imp->micro_steps = 1000;
        $base = 1000.0;
        $imp->set_start_time($base);

        // at the start nothing is processed yet, so the estimate is the full expected time and positive
        $imp->done_time_main_step = 0;
        $imp->done_time_step = 0;
        $est = $imp->calc_total_time($base + 0.001, 0);
        $test_name = 'import time estimate at the start is positive';
        $t->assert_true($test_name, $est > 0);

        // half way through the estimate stays positive and at least the elapsed time
        $imp->done_time_main_step = 0;
        $imp->done_time_step = 0;
        $elapsed = 1.5;
        $est = $imp->calc_total_time($base + $elapsed, 250);
        $test_name = 'import time estimate while running is positive';
        $t->assert_true($test_name, $est > 0);
        $test_name = 'import time estimate while running is not below the elapsed time';
        $t->assert_true($test_name, $est >= $elapsed);

        // the import is bigger than the original estimate (the case that produced a negative estimate):
        // more estimated work is done than the 3s budget, so the raw percentage done exceeds 100%
        $imp->done_time_main_step = 4.0;
        $imp->done_time_step = 1.0;
        $elapsed = 2.732;
        $est = $imp->calc_total_time($base + $elapsed, 1500);
        $test_name = 'import time estimate stays positive when the import takes longer than expected';
        $t->assert_true($test_name, $est > 0);
        $test_name = 'import time estimate is at least the elapsed time when over the estimate';
        $t->assert_true($test_name, $est >= $elapsed - 0.0001);
        $pct_done = $elapsed / $est * 100;
        $test_name = 'import progress percentage does not exceed 100% when over the estimate';
        $t->assert_true($test_name, $pct_done <= 100.0001);

        // a zero original estimate must not divide by zero and still returns a positive estimate
        $imp->est_time_total = 0;
        $imp->done_time_main_step = 0;
        $imp->done_time_step = 0;
        $elapsed = 0.5;
        $est = $imp->calc_total_time($base + $elapsed, 100);
        $test_name = 'import time estimate handles a zero original estimate';
        $t->assert_true($test_name, $est > 0);

    }

}