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
include_once paths::MODEL_IMPORT . 'convert_portfolio_csv.php';
include_once paths::MODEL_IMPORT . 'convert_csv.php';
include_once paths::MODEL_IMPORT . 'import_convert_xbrl.php';
include_once paths::MODEL_IMPORT . 'update_put.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::SHARED_CONST . 'files.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::SHARED_TYPES . 'view_relation_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\import\convert_wikipedia_table;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\import\import_convert_xbrl;
use Zukunft\ZukunftCom\main\php\cfg\import\convert_portfolio_csv;
use Zukunft\ZukunftCom\main\php\cfg\import\convert_csv;
use Zukunft\ZukunftCom\main\php\cfg\import\update_put;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\main\php\shared\const\files as files_shared;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class import_tests
{

    // the group id of a value as the calculator editor sends it and as the database uses it, e.g. of word 289
    // and the triples 1997 and 1999; a prime id is at most group_id::JS_INT_MAX, so javascript reads it exactly
    const string TV_GROUP_ID_TEXT = '1243523942351';
    const int TV_GROUP_ID = 1243523942351;
    // a text id like the one of a value with more than four phrases, which must not become a number
    const string TV_GROUP_ID_KEY = '-2780+1295+1296+1297+1298';
    // the description of the unit of the EUR exchange rate in portfolio_split_calculator.json, pasted verbatim, and
    // the general description that convert_portfolio_csv gives a currency pair that this home file does not have
    const string CSV_PAIR_EUR_COM = 'the unit of the EUR/CHF exchange rate: how many Swiss francs one euro buys';
    const string CSV_PAIR_NOK_COM = 'the unit of the exchange rate: CHF per unit of NOK';
    // the phrase of the format of the bank csv in bank_position_csv.json, the triple that convert_csv names a hint by
    // and the sections of the import json that the report of the difference of the two conversions compares
    const string CSV_FORMAT = 'bank position csv';
    const string CSV_HINT_NAME = 'CH0012221716 hint';
    const array CSV_DIFF_SECTIONS = [json_fields::SELECTION, json_fields::WORDS, json_fields::TRIPLES,
        json_fields::SOURCES, json_fields::VALUES];

    function run(test_cleanup $t): void
    {
        global $sys;

        $sc = new sql_creator();
        $imp = new import(test_files::SYSTEM_CONFIG_SAMPLE);
        // use the dev user, because the import files set code ids (e.g. of the sources) and
        // the virtual dev user is allowed to set a code_id whereas the unit test usr1
        // acts like a normal user (see docs/llm/testing.md)
        $imp->usr = $t->usr_dev;
        $msg = new user_message($t->usr_dev);

        // start the test section (ts)
        $ts = 'unit import ';
        $t->header($ts);

        $test_name = 'YAML import word count';
        $yaml_str = file_get_contents(test_files::SYSTEM_CONFIG_SAMPLE);
        $json_array = yaml_parse($yaml_str);
        $dto = $imp->get_data_object_yaml($json_array, $msg);
        // reading and mapping the yaml sample file takes longer than a normal unit function, so a file timeout is used
        $t->assert($test_name, $dto->word_list()->count(), 79, $t::TIMEOUT_LIMIT_FILE);
        $test_name = 'YAML import triple count';
        $t->assert($test_name, $dto->triple_list()->count(), 24);
        $test_name = 'YAML import value count';
        $t->assert($test_name, $dto->value_list()->count(), 47);
        $test_name = 'YAML import sql function count';
        // building the sql insert calls from the imported data takes longer than a normal unit function
        $t->assert($test_name, $dto->word_list()->sql_insert_call_with_par($sc, $msg)->count(), 1, $t::TIMEOUT_LIMIT_FILE);

        $test_name = 'YAML that starts with a tooltip imports no word';
        $yml_arr = [words::TOOLTIP_COMMENT => word_names::MATH_COM];
        $dto = $imp->get_data_object_yaml($yml_arr, $msg);
        $t->assert($test_name, $dto->word_list()->count(), 0);
        $msg->reset();

        $test_name = 'an empty word list creates no sql function';
        $sql_lst = $dto->word_list()->sql_insert_call_with_par($sc, $msg);
        $t->assert($test_name, $sql_lst->count(), 0);
        $msg->reset();

        // e.g. the url of a pinned view that is created from its view name and phrase name
        $test_name = 'a YAML text imports a value';
        $dto = $imp->get_data_object_yaml([words::SYSTEM => [words::URL => api::MAIN_SCRIPT]], $msg);
        $t->assert($test_name, $dto->value_list()->count(), 1);
        // negative: an empty text would be sent without a text field, so the config could not be read
        $test_name = '... but an empty YAML text imports no value';
        $dto = $imp->get_data_object_yaml([words::SYSTEM => [words::URL => '']], $msg);
        $t->assert($test_name, $dto->value_list()->count(), 0);
        $msg->reset();

        $test_name = 'JSON import word count';
        $json_str = file_get_contents(test_files::IMPORT_WORDS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->word_list()->count(), 4);

        // the import work is counted as import and then the section of the caller continues,
        // so that e.g. the seed import of the database recreation is not reported as not specified
        $test_name = 'the creation of an import data object is counted as import';
        $sys->times->reset_section();
        $sys->times->switch(system_time_type::LOAD_USER_DATA);
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $sys->times->section_report(), system_time_type::IMPORT);
        $test_name = '... and then returns to the time section around it';
        $t->assert($test_name, $sys->times->current(), system_time_type::LOAD_USER_DATA);
        $sys->times->switch();

        // beside the "words" with the parameters a file can name the words in a "word-list",
        // which is the compact format that convert_wikipedia_table creates for the countries
        // TODO Prio 3 use a word name list from a test/create class
        $test_name = 'JSON import word-list count';
        $msg = new user_message($t->usr_dev);
        $wrd_lst_json = [json_fields::WORD_LIST => [words::MIO, word_names::YEAR_2019, word_names::YEAR_2020]];
        $dto = $imp->get_data_object($wrd_lst_json, $msg);
        $t->assert($test_name, $dto->word_list()->count(), 3);
        $test_name = '... and the word-list words have the expected name';
        $t->assert($test_name, $dto->word_list()->names(), [words::MIO, word_names::YEAR_2019, word_names::YEAR_2020]);

        // a word named in the word-list must be usable by a value of the same file, so that
        // e.g. the countries of the democracy index table get a phrase group (see the
        // self-consistency rule in docs/llm/json_structure.md)
        $test_name = 'JSON import value with a word-list phrase';
        $msg = new user_message($t->usr_dev);
        $wrd_lst_val_json = [
            json_fields::WORD_LIST => [words::MIO, word_names::YEAR_2019],
            json_fields::VALUES => [[
                json_fields::WORDS => [words::MIO, word_names::YEAR_2019],
                json_fields::NUMBER => 8.88
            ]]
        ];
        $dto = $imp->get_data_object($wrd_lst_val_json, $msg);
        $t->assert($test_name, $dto->value_list()->count(), 1);
        $test_name = '... and the value has all its phrases';
        $val = $dto->value_list()->lst()[0] ?? null;
        $t->assert($test_name, $val?->phrase_list()->count(), 2);

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
        // a triple can carry a phrase type like a word, e.g. the unit triple "hertz per second"
        // is a measure, so that a table header shows it behind the "in" (see value_list::is_unit)
        $test_name = 'JSON import keeps the measure type of a triple';
        $trp = $dto->triple_list()->get_by_name('hertz per second', $msg);
        $t->assert_true($test_name, $trp?->is_measure() ?? false);
        // negative: a triple without a type in the json stays a normal phrase
        $test_name = '... and a triple without a type is no measure';
        $trp = $dto->triple_list()->get_by_name('global warming', $msg);
        $t->assert_false($test_name, $trp?->is_measure() ?? true);

        // a triple that an import json names is filled from the cache like a word, so that e.g.
        // the triple of a term view keeps its id (see sandbox_link_named::import_mapper); the measure
        // type proves that the cached triple is returned and not the name-only triple given
        $test_name = 'the import cache finds a triple by its name';
        $trp_by_name = new triple($t->usr1);
        $trp_by_name->set_name('hertz per second');
        $trp_cac = $dto->get_object_by_name($trp_by_name, $msg);
        $t->assert_true($test_name, $trp_cac?->is_measure() ?? false);
        // negative: a triple that the cache does not have is not replaced by a placeholder
        // the math const triple is a seed triple that this import json does not contain
        $test_name = '... but a triple that the cache does not have is not found';
        $trp_missing = new triple($t->usr1);
        $trp_missing->set_name(triple_names::MATH_CONST);
        $t->assert_null($test_name, $dto->get_object_by_name($trp_missing, $msg)?->name());

        // a view relation names its views, which the import finds in the views of the same file
        $test_name = 'JSON import view relation count';
        $msg = new user_message($t->usr_dev);
        $mrl_lst_json = [[
            json_fields::PARENT => views::TABLE_NAME,
            json_fields::CHILD => views::TABLE_EDITOR_NAME,
            json_fields::TYPE_NAME => view_relation_types::EDITOR
        ]];
        $mrl_json = [
            json_fields::VIEWS => [[json_fields::NAME => views::TABLE_NAME], [json_fields::NAME => views::TABLE_EDITOR_NAME]],
            json_fields::VIEW_RELATIONS => $mrl_lst_json
        ];
        $dto = $imp->get_data_object($mrl_json, $msg);
        $t->assert($test_name, $dto->view_relation_list()->count(), 1);
        $test_name = '... and the relation is the editor of the view of the same file';
        $mrl = $dto->view_relation_list()->lst()[0] ?? null;
        $t->assert($test_name, $mrl?->get_predicate_code_id(), view_relation_types::EDITOR);
        $t->assert_true($test_name, $mrl?->parent() === $dto->get_view_by_name(views::TABLE_NAME, $msg));
        $test_name = 'a view relation whose views are missing in the import is reported';
        $msg = new user_message($t->usr_dev);
        $dto = $imp->get_data_object([json_fields::VIEW_RELATIONS => $mrl_lst_json], $msg);
        $t->assert_text_contains($test_name, $msg->text(), views::TABLE_EDITOR_NAME);
        $test_name = '... and not added';
        $t->assert($test_name, $dto->view_relation_list()->count(), 0);

        // the keyword, the properties and the types of an input field of the process catch views
        // are system phrases, so the code reads them by their code id (triples::FIELD_PROPERTIES)
        $json_array = json_decode(file_get_contents(cfg_files::PROCESS_CATCH_FIELDS_FILE), true);
        $msg = new user_message($t->usr_dev);
        $dto = $imp->get_data_object($json_array, $msg);
        $test_name = 'the process catch field system file is imported without a message';
        $t->assert($test_name, $msg->is_ok() ? '' : $msg->all_message_text(), '');
        $trp_lst = $dto->triple_list();
        $keywords = [
            triples::SYSTEM_PROCESS_CATCH_FIELD => triples::SYSTEM_PROCESS_CATCH_FIELD_CODE_ID,
            triples::SYSTEM_PROCESS_CATCH_PAGE => triples::SYSTEM_PROCESS_CATCH_PAGE_CODE_ID,
            triples::SYSTEM_PROCESS_CATCH_BUTTON => triples::SYSTEM_PROCESS_CATCH_BUTTON_CODE_ID,
        ];
        foreach ([...$keywords, ...triples::FIELD_PROPERTIES, ...triples::FIELD_TYPES, ...triples::PROCESS_CATCH_PAGES,
                     ...triples::BUTTON_PROPERTIES, ...triples::BUTTON_TYPES] as $name => $code_id) {
            $test_name = 'the import sets the code id of the process catch phrase ' . $name;
            $t->assert($test_name, $trp_lst->get_by_name($name, $msg)?->get_code_id(), $code_id);
        }
        // a property belongs to its keyword and a type to its keyword or type property, like the column tiers
        $test_name = 'a property of a process catch field is linked to the field keyword';
        $t->assert($test_name, $trp_lst->get_by_name(triples::FIELD_UNIT, $msg)?->get_from()?->name(),
            triples::SYSTEM_PROCESS_CATCH_FIELD);
        $test_name = 'a field type is linked to the field type property';
        $t->assert($test_name, $trp_lst->get_by_name(triples::FIELD_LOCATION_SELECTOR, $msg)?->get_from()?->name(),
            triples::FIELD_TYPE);
        $test_name = 'the label of a phrase selector is a property of the phrase selector and not of the field';
        $t->assert($test_name, $trp_lst->get_by_name(triples::FIELD_PHRASE_SELECTOR_LABEL, $msg)?->get_from()?->name(),
            triples::FIELD_PHRASE_SELECTOR);
        $test_name = 'a page of a process catch is linked to the page keyword';
        $t->assert($test_name, $trp_lst->get_by_name(triples::PROCESS_CATCH_MAIN_PAGE, $msg)?->get_from()?->name(),
            triples::SYSTEM_PROCESS_CATCH_PAGE);
        $test_name = 'a button type is linked to the button keyword';
        $t->assert($test_name, $trp_lst->get_by_name(triples::BUTTON_NEXT, $msg)?->get_from()?->name(),
            triples::SYSTEM_PROCESS_CATCH_BUTTON);
        // each button type has its own properties, so a question phrase can give each button its own tooltip
        $test_name = 'a button property is linked to its button type';
        $t->assert($test_name, $trp_lst->get_by_name(triples::BUTTON_BACK_TOOLTIP, $msg)?->get_from()?->name(),
            triples::BUTTON_BACK);
        // the same property of another button type is a triple of its own and not shared with the back button
        $test_name = '... so the next button has its own tooltip';
        $t->assert($test_name, $trp_lst->get_by_name(triples::BUTTON_NEXT_TOOLTIP, $msg)?->get_from()?->name(),
            triples::BUTTON_NEXT);
        // negative: the building blocks of the keywords are no system phrases, so they have no code id
        // found first, because a missing triple would also return no code id
        $test_name = 'the building block of the field keyword is imported';
        $trp = $trp_lst->get_by_name(triple_names::PROCESS_CATCH_FIELD, $msg);
        $t->assert($test_name, $trp?->name(), triple_names::PROCESS_CATCH_FIELD);
        $test_name = '... without a code id';
        $t->assert_null($test_name, $trp?->get_code_id());
        // negative: only a system, system test or developer user may set a code id, so the import of
        // the system file by a user with the normal profile is refused; the refused triple and every
        // following one are not added to the data object, because the import mapper returns the
        // verdict of the shared message (import::dto_get_triples)
        $test_name = 'a normal user cannot import the code id of a process catch field phrase';
        $msg = new user_message($t->usr_normal);
        $imp->get_data_object($json_array, $msg);
        $t->assert_false($test_name, $msg->is_ok());

        // the four process catch pages are system views, which the next and the back button find by the code id
        $json_array = json_decode(file_get_contents(cfg_files::PROCESS_CATCH_VIEWS_FILE), true);
        $msg = new user_message($t->usr_dev);
        $dto = $imp->get_data_object($json_array, $msg);
        $test_name = 'the process catch view file is imported without a message';
        $t->assert($test_name, $msg->is_ok() ? '' : $msg->all_message_text(), '');
        // the start and the main page have a title, an explain text, the fields and a button; the detail
        // and the result page have five tabs and the fields of each tab instead of the fields and the
        // detail page has the refresh button to calculate with the changed assumptions
        $pages = [
            views::PROCESS_CATCH_START_NAME => [views::PROCESS_CATCH_START, 4],
            views::PROCESS_CATCH_MAIN_NAME => [views::PROCESS_CATCH_MAIN, 4],
            views::PROCESS_CATCH_DETAIL_NAME => [views::PROCESS_CATCH_DETAIL, 14],
            views::PROCESS_CATCH_RESULT_NAME => [views::PROCESS_CATCH_RESULT, 13],
        ];
        foreach ($pages as $name => [$code_id, $cmp_count]) {
            $msk = $dto->get_view_by_name($name, $msg);
            $test_name = 'the import sets the code id of the ' . $name;
            $t->assert($test_name, $msk?->get_code_id(), $code_id);
            $test_name = '... and links its components';
            $t->assert($test_name, $msk?->component_links() ?? 0, $cmp_count);
        }
        // the geolocator keeps the entered place with its row phrase, which the system file defines
        $cmp_geo = $dto->get_component_by_name(components::GEOLOCATOR_NAME, $msg);
        $test_name = 'the geolocator component has the geolocator type';
        $t->assert($test_name, $cmp_geo?->type_code_id(), component_types::GEOLOCATOR);
        $test_name = '... and the place of the user as value phrase';
        $t->assert($test_name, $cmp_geo?->row_phrase?->name(), triple_names::PLACE_OF_USER);
        // negative: a component without a row in the json e.g. the refresh button has no value phrase
        $test_name = 'the refresh button is imported';
        $cmp_rfr = $dto->get_component_by_name(components::PROCESS_CATCH_REFRESH_NAME, $msg);
        $t->assert($test_name, $cmp_rfr?->type_code_id(), component_types::PROCESS_CATCH_BUTTON_REFRESH);
        $test_name = '... without a value phrase';
        $t->assert_null($test_name, $cmp_rfr?->row_phrase?->name());
        $test_name = 'a normal user cannot import the code id of a process catch view';
        $msg = new user_message($t->usr_normal);
        $imp->get_data_object($json_array, $msg);
        $t->assert_false($test_name, $msg->is_ok());

        // the keywords of the calculator view are system phrases, so the calculator reader finds the
        // lists, the texts and the flags of a page by the code id (triples::CALCULATOR_LISTS)
        $json_array = json_decode(file_get_contents(cfg_files::CALCULATOR_KEYWORDS_FILE), true);
        $msg = new user_message($t->usr_dev);
        $dto = $imp->get_data_object($json_array, $msg);
        $test_name = 'the calculator keyword system file is imported without a message';
        $t->assert($test_name, $msg->is_ok() ? '' : $msg->all_message_text(), '');
        $trp_lst = $dto->triple_list();
        $keywords = [triples::SYSTEM_CALCULATOR => triples::SYSTEM_CALCULATOR_CODE_ID];
        foreach ([...$keywords, ...triples::CALCULATOR_LISTS, ...triples::CALCULATOR_PROPERTIES] as $name => $code_id) {
            $test_name = 'the import sets the code id of the calculator keyword ' . $name;
            $t->assert($test_name, $trp_lst->get_by_name($name, $msg)?->get_code_id(), $code_id);
            // every list and property hangs from the root, so one load of the root gives all keywords
            if ($name != triples::SYSTEM_CALCULATOR) {
                $test_name = '... and links it to the calculator keyword';
                $t->assert($test_name, $trp_lst->get_by_name($name, $msg)?->get_from()?->name(),
                    triples::SYSTEM_CALCULATOR);
            }
        }
        // the from walk of a page finds these keywords by their code id
        $by_code_id = [
            ...triples::CALCULATOR_FIELD_KINDS,
            ...triples::CALCULATOR_DETAIL_FLAGS,
            ...triples::CALCULATOR_BLOCKS,
            ...triples::CALCULATOR_BUTTONS,
            ...triples::CALCULATOR_PROPOSED,
            triples::CALCULATOR_DECIMALS => triples::CALCULATOR_DECIMALS_CODE_ID,
            triples::CALCULATOR_RESULT_WIDTH => triples::CALCULATOR_RESULT_WIDTH_CODE_ID,
            triples::CALCULATOR_ALTERNATIVE_ORDER => triples::CALCULATOR_ALTERNATIVE_ORDER_CODE_ID
        ];
        foreach ($by_code_id as $name => $code_id) {
            $test_name = 'the import sets the code id of the calculator keyword ' . $name;
            $kwd_code_id = $trp_lst->get_by_name($name, $msg)?->get_code_id();
            $t->assert($test_name, $kwd_code_id, $code_id);
        }
        // the sample of the calculator editor json uses every proposed element at least once
        $sample = json_decode(file_get_contents(test_files::CALCULATOR_EDITOR_SAMPLE), true);
        $sample_to = array_column($sample[json_fields::TRIPLES] ?? [], json_fields::EX_TO);
        $test_name = 'the calculator editor sample uses every proposed element';
        $t->assert($test_name, array_values(array_diff(array_keys(triples::CALCULATOR_PROPOSED), $sample_to)), []);
        // negative: a difference column of the result columns replaces the former scenario column
        $test_name = '... and not the replaced scenario column';
        $t->assert_false($test_name, in_array('calculator scenario column', $sample_to));
        // negative: only a system, system test or developer user may set a code id
        $test_name = 'a normal user cannot import the code id of a calculator keyword';
        $msg = new user_message($t->usr_normal);
        $imp->get_data_object($json_array, $msg);
        $t->assert_false($test_name, $msg->is_ok());

        // the calculator is a system view with the three components that are the same for every
        // calculator page; the url selects it by its code id and the edit menu offers the editor
        $json_array = json_decode(file_get_contents(cfg_files::CALCULATOR_VIEWS_FILE), true);
        $msg = new user_message($t->usr_dev);
        $dto = $imp->get_data_object($json_array, $msg);
        $test_name = 'the calculator view file is imported without a message';
        $t->assert($test_name, $msg->is_ok() ? '' : $msg->all_message_text(), '');
        // the import with a database refuses the file if a view links a component that the file
        // does not define, which the import without a database does not report
        $cmp_names = array_column($json_array[json_fields::COMPONENTS], json_fields::NAME);
        $missing = [];
        foreach ($json_array[json_fields::VIEWS] as $msk_json) {
            foreach ($msk_json[json_fields::COMPONENTS] ?? [] as $lnk_json) {
                if (!in_array($lnk_json[json_fields::NAME], $cmp_names)) {
                    $missing[] = $lnk_json[json_fields::NAME];
                }
            }
        }
        $test_name = 'every component of the calculator views is defined in the same file';
        $t->assert($test_name, implode(', ', $missing), '');
        // a view or a component of system_views.json that this file repeats must be repeated
        // unchanged, because the import refuses a divergent field e.g. another description;
        // only the components of the calculator view are added here
        $sys_json = json_decode(file_get_contents(files_shared::SYSTEM_VIEWS), true);
        $diff = [];
        foreach ([json_fields::VIEWS, json_fields::COMPONENTS] as $key) {
            $sys_lst = array_column($sys_json[$key], null, json_fields::NAME);
            foreach ($json_array[$key] as $obj_json) {
                $sys_obj = $sys_lst[$obj_json[json_fields::NAME]] ?? null;
                unset($obj_json[json_fields::COMPONENTS]);
                if ($sys_obj !== null and $obj_json != $sys_obj) {
                    $diff[] = $obj_json[json_fields::NAME];
                }
            }
        }
        $test_name = 'the views and components of system_views.json are repeated unchanged';
        $t->assert($test_name, implode(', ', $diff), '');
        $msk = $dto->get_view_by_name(views::CALCULATOR_NAME, $msg);
        $test_name = 'the import sets the code id of the calculator view';
        $t->assert($test_name, $msk?->get_code_id(), views::CALCULATOR);
        $test_name = '... and links the title, the input and the result component';
        $t->assert($test_name, $msk?->component_links() ?? 0, 3);
        $test_name = '... and sets the use case style for the header and the grid of the page';
        $t->assert($test_name, $msk?->get_style()?->get_code_id(), view_styles::USE_CASE);
        foreach ([
                     components::CALCULATOR_TITLE_NAME => [components::CALCULATOR_TITLE, component_types::CALCULATOR_TITLE],
                     components::CALCULATOR_INPUT_NAME => [components::CALCULATOR_INPUT, component_types::CALCULATOR_INPUT],
                     components::CALCULATION_RESULT_NAME => [components::CALCULATION_RESULT, component_types::CALCULATION_RESULT],
                 ] as $name => [$code_id, $type_code_id]) {
            $cmp = $dto->get_component_by_name($name, $msg);
            $test_name = 'the import sets the code id of the component ' . $name;
            $t->assert($test_name, $cmp?->get_code_id(), $code_id);
            $test_name = '... and its type';
            $t->assert($test_name, $cmp?->type_code_id(), $type_code_id);
        }
        // the comparison table is created by this file, so that the calculator keeps the id of the former view
        $test_name = 'the import sets the code id of the comparison table';
        $t->assert($test_name, $dto->get_view_by_name(views::COMPARISON_TABLE_NAME, $msg)?->get_code_id(), views::COMPARISON_TABLE);
        $mrl = $dto->view_relation_list()->lst()[0] ?? null;
        $test_name = 'the edit menu of the calculator offers the calculator editor';
        $t->assert($test_name, $mrl?->get_predicate_code_id(), view_relation_types::EDITOR);
        $t->assert($test_name, $mrl?->child()?->name(), views::CALCULATOR_EDITOR_NAME);
        $test_name = 'a normal user cannot import the code id of the calculator view';
        $msg = new user_message($t->usr_normal);
        $imp->get_data_object($json_array, $msg);
        $t->assert_false($test_name, $msg->is_ok());

        $test_name = 'JSON import source count';
        $msg = new user_message($t->usr_dev);
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
        $msg = new user_message($t->usr_dev);
        $json_str = file_get_contents(test_files::IMPORT_TRAVEL_SCORING_VALUE_LIST);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        // expanding the 1653 compact value-list entries to single values is import-heavy, so an import timeout is used
        $t->assert($test_name, $dto->value_list()->count(), 1660, $t::TIMEOUT_LIMIT_IMPORT);

        // beside the list of single entry objects a value-list may give its values as one map of
        // the entity to the number, which is the format that convert_wikipedia_table creates
        // (e.g. democracy_index_table.json), so both formats must add the same values
        // TODO Prio 3 use a value map list from a test/create class
        $test_name = 'JSON import value-list given as a map';
        $msg = new user_message($t->usr_dev);
        $val_lst_map = [json_fields::VALUE_LIST => [[
            json_fields::CONTEXT => [words::MIO],
            json_fields::VALUES => [word_names::YEAR_2019 => '8.88', word_names::YEAR_2020 => '8.87']
        ]]];
        $dto = $imp->get_data_object($val_lst_map, $msg);
        $t->assert($test_name, $dto->value_list()->count(), 2);
        $test_name = '... and the map format reports no error';
        $t->assert_true($test_name, $msg->is_ok());

        $test_name = 'JSON import value-list given as a list of one entry objects';
        $msg = new user_message($t->usr_dev);
        $val_lst_obj = [json_fields::VALUE_LIST => [[
            json_fields::CONTEXT => [words::MIO],
            json_fields::VALUES => [[word_names::YEAR_2019 => '8.88'], [word_names::YEAR_2020 => '8.87']]
        ]]];
        $dto = $imp->get_data_object($val_lst_obj, $msg);
        $t->assert($test_name, $dto->value_list()->count(), 2);

        // a value-list entry that is neither a map nor a one entry object must be reported
        // instead of causing a php error (see docs/llm/structure.md)
        $test_name = 'JSON import reports an unexpected value-list entry';
        $msg = new user_message($t->usr_dev);
        $val_lst_bad = [json_fields::VALUE_LIST => [[
            json_fields::CONTEXT => [words::MIO],
            json_fields::VALUES => [['too', 'many', 'entries']]
        ]]];
        $imp->get_data_object($val_lst_bad, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'unexpected json value name');

        // the description of a value is kept in its phrase group (see
        // sandbox_value::set_description), so the import must map it like any other field
        $test_name = 'JSON import value description';
        $test_desc = 'the number of the unit test value';
        $msg = new user_message($t->usr_dev);
        $val_with_desc = [json_fields::VALUES => [[
            json_fields::WORDS => [words::MIO, word_names::YEAR_2019],
            json_fields::NUMBER => 8.88,
            json_fields::DESCRIPTION => $test_desc
        ]]];
        $dto = $imp->get_data_object($val_with_desc, $msg);
        $val = $dto->value_list()->lst()[0] ?? null;
        $t->assert($test_name, $val?->get_description(), $test_desc);

        // negative: a value without a description must not get one, because a partial object
        // must never overwrite a field that it does not carry
        $test_name = '... and a value without a description has none';
        $msg = new user_message($t->usr_dev);
        $val_no_desc = [json_fields::VALUES => [[
            json_fields::WORDS => [words::MIO, word_names::YEAR_2019],
            json_fields::NUMBER => 8.88
        ]]];
        $dto = $imp->get_data_object($val_no_desc, $msg);
        $val = $dto->value_list()->lst()[0] ?? null;
        $t->assert_null($test_name, $val?->get_description());

        // the compact "phrase-values" map assigns a number directly to a single phrase
        // (here three "<city> inhabitants" triples), expanded to one value per entry
        $test_name = 'JSON import phrase-values count';
        $msg = new user_message($t->usr_dev);
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
        // a checked result is a result like any other, so it is stored with the results of
        // the file and not only used for the check
        $test_name = '... and the checked result is stored with the results';
        $t->assert($test_name, $dto->result_list()->count(), 1);

        // an operand of a formula can be a group of phrases e.g. "price","CHF", which names one
        // value, so the validation must replace the complete group with one number
        $test_name = 'JSON import calc validation fills a phrase group with one number';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_GROUP . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->result_check_list()->count(), 1);
        $test_name = '... and reports no problem';
        $t->assert_true($test_name, $msg->is_ok());

        // negative: the missing value is reported by the complete group and not by a single phrase
        $test_name = 'JSON import calc validation reports a missing phrase group value';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_GROUP_MISSING . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $target = 'the value for "price,CHF" to validate the result of '
            . $dto->result_check_list()->lst()[0]->grp()->phrase_list()->dsp_name()
            . ' is missing in the import message';
        $t->assert($test_name, $msg->all_message_text(), $target);

        // a result that has been reproduced is the operand of a later result, so that a file can
        // calculate a target value first and its probability range from that target afterwards
        $test_name = 'JSON import calc validation uses a checked result as an operand';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_CHAIN . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $dto->result_check_list()->count(), 2);
        $test_name = '... and reports no problem';
        $t->assert_true($test_name, $msg->is_ok());

        // negative: a result that could not be reproduced is no operand, else a wrong number
        // would confirm the next result instead of being reported
        $test_name = 'JSON import calc validation uses no failed result as an operand';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_CHAIN_BROKEN . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'the value for "total,CHF"');

        // of two numbers in the context the one with fewer phrases that neither the operand nor the result names is
        // the operand, like the page calculates it (formula_calc::value_of), e.g. the share of the pear and not the
        // one of the whole basket, also if the share of the pear is a checked result and the other one a value
        $test_name = 'JSON import calc validation takes the operand with the fewest foreign phrases';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_OPERAND_CHOICE . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        // negative: a number with fewer foreign phrases is no operand if it is outside the context
        $test_name = '... but never one outside the context';
        $msg = new user_message($t->usr1);
        $ctx = $json_array[json_fields::CALC_VALIDATION][1][json_fields::CONTEXT];
        $json_array[json_fields::CALC_VALIDATION][1][json_fields::CONTEXT] = array_values(array_diff($ctx, ['pear']));
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match the result 100');

        // a list function sums the values and the checked results of its operand like the page and the backend
        // (formula::list_numbers), e.g. the value of the apple and the checked weight of the pear
        $test_name = 'JSON import calc validation sums a value and a checked result';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_LIST_RESULT . test_files::JSON);
        $json_list = json_decode($json_str, true);
        $imp->get_data_object($json_list, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        // negative: a result that the file has not checked before is no number of the list
        $test_name = '... but only a result that the file has checked before';
        $msg = new user_message($t->usr1);
        $json_unchecked = $json_list;
        $json_unchecked[json_fields::CALC_VALIDATION] = [$json_list[json_fields::CALC_VALIDATION][1]];
        $imp->get_data_object($json_unchecked, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match the result 2');
        // negative: a checked result of the formula that is checked is never a number of its own list
        $test_name = '... and never a result of the formula that is checked';
        $msg = new user_message($t->usr1);
        $json_again = $json_list;
        $json_again[json_fields::CALC_VALIDATION][] = $json_list[json_fields::CALC_VALIDATION][1];
        $imp->get_data_object($json_again, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        // negative: the expected weight of the apple has all phrases of its weight and more, so the sum stays 5 and not 9
        $test_name = '... and without a number that includes all phrases of another number';
        $msg = new user_message($t->usr1);
        $json_qualified = $json_list;
        $apple_words = $json_list[json_fields::VALUES][0][json_fields::WORDS];
        $json_qualified[json_fields::WORDS][] = [json_fields::NAME => words::EXPECTED];
        $json_qualified[json_fields::VALUES][] = [
            json_fields::WORDS => [...$apple_words, words::EXPECTED],
            json_fields::NUMBER => '4'
        ];
        $imp->get_data_object($json_qualified, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        // negative: a calculation inside a list function would need pairs of numbers, so the import refuses it like the backend
        $test_name = 'JSON import calc validation refuses a calculation inside a list function';
        $msg = new user_message($t->usr1);
        $json_calc = $json_list;
        $json_calc[json_fields::FORMULAS][1][json_fields::EXPRESSION] = '"total","weight" = sum( "weight" * 2 )';
        $imp->get_data_object($json_calc, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'cannot be calculated');
        // negative: the numbers of a list replace every use of the operand, so it cannot also be used outside the list
        $test_name = '... and an operand inside and outside a list function';
        $msg = new user_message($t->usr1);
        $json_outside = $json_list;
        $json_outside[json_fields::FORMULAS][1][json_fields::EXPRESSION] = '"total","weight" = sum( "weight" ) / "weight"';
        $imp->get_data_object($json_outside, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'cannot be calculated');

        // a list takes the numbers of a kind of a phrase of its context over several levels of is a, e.g. the cherry is a
        // stone fruit, which is a fruit, like the walk rows of LISP for the stocks of the portfolio split
        $test_name = 'JSON import calc validation sums the numbers of the kinds of a phrase';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(test_files::IMPORT_CALC_VALIDATION_LIST_KIND . test_files::JSON);
        $json_kind = json_decode($json_str, true);
        $imp->get_data_object($json_kind, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        // negative: without the is a triples no number of the file names the fruit, so the list has no number
        $test_name = '... but only by the is a triples of the file';
        $msg = new user_message($t->usr1);
        $json_no_kind = $json_kind;
        $json_no_kind[json_fields::TRIPLES] = [];
        $imp->get_data_object($json_no_kind, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'the value for "weight"');

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

        // the use case file states the avoided emission of PV in Switzerland as values and as
        // formulas with the same result, so the calc validation of the file must reproduce the
        // two values from the reference mixes and the PV emission of the same file
        // the messages are checked first, so that a failure shows what the import reports
        // a use case is user data, so its file carries no code id and must import for a normal
        // user (docs/llm/json_structure.md "Use case files"); usr1 is that normal user here
        $test_name = 'the import of the PV in Switzerland use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::PV_SWITZERLAND_CO2_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces both results';
        $t->assert($test_name, $dto->result_check_list()->count(), 2);
        // negative: a changed reference mix breaks the pre-calculated result
        $test_name = '... and a changed reference mix is reported';
        $msg = new user_message($t->usr1);
        foreach ($json_array[json_fields::VALUES] as $i => $val) {
            if (in_array('consumption mix', $val[json_fields::WORDS])
                and in_array('electricity', $val[json_fields::WORDS])) {
                $json_array[json_fields::VALUES][$i][json_fields::NUMBER] = '130';
            }
        }
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match');

        // the PV lifetime balance use case chains its results: the production, the dispatch, the
        // embodied emissions, the gross and net avoided emission and the payback, so every
        // pre-calculated result of the file must be reproduced from its values and formulas
        $test_name = 'the import of the PV lifetime balance use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::PV_LIFETIME_BALANCE_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result of the chain';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));
        // the page is the system view Calculator, so the use case brings no view and no component
        $test_name = '... and brings no view of its own';
        $t->assert($test_name, $dto->view_list()->count(), 0);
        $test_name = '... but shows the worked example with the calculator view';
        $trp = $dto->get_triple_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $t->assert($test_name, $trp?->get_view()?->name(), views::CALCULATOR_NAME);
        // every installation built from PV installation gets the page, so the lists are triples of PV installation
        $test_name = '... and PV installation has every list of the calculator';
        $lists = [];
        foreach ($dto->triple_list()->lst() as $trp) {
            if ($trp->from_name() == triple_names::PV_INSTALLATION
                and array_key_exists($trp->to_name(), triples::CALCULATOR_LISTS)) {
                $lists[] = $trp->to_name();
            }
        }
        $t->assert($test_name, $lists, array_keys(triples::CALCULATOR_LISTS));
        // the import of a normal user finds a phrase only by the names of the file, so the file repeats the keywords
        // a keyword that the page does not use e.g. the calculator warning is not needed in the file
        $test_name = '... and repeats every calculator keyword that it uses without its code id';
        $keywords = [...array_keys(triples::CALCULATOR_LISTS), ...array_keys(triples::CALCULATOR_PROPERTIES)];
        $used = array_values(array_intersect($keywords, array_column($json_array[json_fields::TRIPLES], json_fields::EX_TO)));
        $repeated = array_values(array_intersect($used, array_column($json_array[json_fields::TRIPLES], json_fields::NAME)));
        $t->assert($test_name, $repeated, $used);
        // negative: without a used keyword the comparison above would be empty and pass without a check
        $test_name = '... and uses the keywords of the page';
        $t->assert_true($test_name, in_array(triples::CALCULATOR_LABEL, $used));
        // negative: a triple that the file does not define is not in the import
        $test_name = '... but has no triple of another use case';
        $trp_none = $dto->get_triple_by_name(triple_names::PI, $msg);
        $t->assert_null($test_name, $trp_none?->name());

        // the Swiss NPP vote use case chains the levelised cost of the new plant, the cost of the
        // alternative and the average cost difference per kWh, so every pre-calculated result of
        // the file must be reproduced from its values and formulas
        $test_name = 'the import of the Swiss NPP vote use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::NPP_CH_VOTE_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result of the chain';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));

        // the CBAM calculator use case chains the embedded emission, the free allocation
        // adjustment, the carbon price deduction, the obligation and the cost of an import, so
        // every pre-calculated result of the file must be reproduced from its values and formulas
        $test_name = 'the import of the CBAM calculator use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::CBAM_CALCULATOR_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result of the chain';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));
        // negative: a changed certificate price breaks the pre-calculated deduction and cost
        $test_name = '... and a changed certificate price is reported';
        $msg = new user_message($t->usr1);
        foreach ($json_array[json_fields::VALUES] as $i => $val) {
            if (in_array(triple_names::CBAM_CERTIFICATE_PRICE, $val[json_fields::WORDS])) {
                $json_array[json_fields::VALUES][$i][json_fields::NUMBER] = '90';
            }
        }
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match');

        // the calc validation of the portfolio split use case must reproduce the holding values, the
        // target, the count and the deviation of every row of the split walk, the remainder of the
        // parent in every holding, the target values and the final split deviation
        $test_name = 'the import of the portfolio split use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::PORTFOLIO_SPLIT_CALCULATOR_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));
        // negative: another target share of the first child that does not fit the calculated walk
        $test_name = '... and a target share that does not fit the walk is reported';
        $msg = new user_message($t->usr1);
        foreach ($json_array[json_fields::VALUES] as $i => $val) {
            if (in_array(triple_names::FIRST_CHILD_PORTFOLIO, $val[json_fields::WORDS])) {
                $json_array[json_fields::VALUES][$i][json_fields::NUMBER] = '1';
            }
        }
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match');

        // the Swiss AHV VAT vote use case chains the extra VAT revenue, its replacement by payroll
        // and federal money and the annual cost of a household, so every pre-calculated result of
        // the file must be reproduced from its values and formulas
        $test_name = 'the import of the Swiss AHV VAT vote use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::AHV_VAT_VOTE_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result of the chain';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));
        // negative: a changed VAT yield breaks the pre-calculated extra VAT revenue
        $test_name = '... and a changed VAT yield is reported';
        $msg = new user_message($t->usr1);
        foreach ($json_array[json_fields::VALUES] as $i => $val) {
            if (in_array(triple_names::AHV_VAT_YIELD, $val[json_fields::WORDS])) {
                $json_array[json_fields::VALUES][$i][json_fields::NUMBER] = '4';
            }
        }
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match');

        // the ABB share use case derives the target price from several valuation methods and
        // compares the weighted target with the price, so every pre-calculated result of the file
        // must be reproduced from its values and formulas
        $test_name = 'the import of the ABB share buy or sell use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::SHARE_BUY_SELL_ABB_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result of the chain';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));
        // negative: a changed fair P/E breaks the pre-calculated earnings multiple target
        $test_name = '... and a changed fair P/E is reported';
        $msg = new user_message($t->usr1);
        foreach ($json_array[json_fields::VALUES] as $i => $val) {
            if (in_array(triple_names::ABB_FAIR_PE, $val[json_fields::WORDS])) {
                $json_array[json_fields::VALUES][$i][json_fields::NUMBER] = '30';
            }
        }
        $imp->get_data_object($json_array, $msg);
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'does not match');

        // the unique keys use case is user data as well, so a normal user must import it
        $test_name = 'the import of the unique keys use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::UNIQUE_KEYS_FILE);
        $dto = $imp->get_data_object(json_decode($json_str, true), $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and the ISBN is typed unique key';
        $wrd = $dto->word_list()->get_by_name(word_names::ISBN, $msg);
        $t->assert_true($test_name, $wrd?->is_type(phrase_type_shared::UNIQUE_KEY) ?? false);
        // negative: a sample entry is an instance of a unique key, not the key itself
        $test_name = '... but its sample entry is not';
        $wrd = $dto->word_list()->get_by_name(word_names::ISIN_MERCEDES, $msg);
        $t->assert_false($test_name, $wrd?->is_type(phrase_type_shared::UNIQUE_KEY) ?? true);

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
        // the import user must be the same as the user of the message, because a component link
        // is created with the message user and added to a list of the import user, so a mismatch
        // silently drops every component link (see docs/llm/testing.md);
        // the dev user is used because the view fixtures below set component code ids
        $imp = new import(test_files::SYSTEM_CONFIG_SAMPLE);
        $imp->usr = $t->usr_dev;

        // a component name is the key the views use, so the same name twice in one import is reported
        $test_name = 'JSON import reports a component defined twice';
        $msg = new user_message($t->usr_dev);
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
        $msg = new user_message($t->usr_dev);
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
            json_fields::ASSIGNED => [word_names::PI, triple_names::EULER_NUMBER]
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

        // a view that opens a row with row_right but never closes it with row_end is reported;
        // the fixture uses system components with a code id, so the import needs the dev user
        // that is allowed to set a code_id (see docs/llm/testing.md)
        $test_name = 'JSON import reports a view with an unclosed row';
        $msg = new user_message($t->usr_dev);
        $json_str = file_get_contents(test_files::IMPORT_VIEW_ROW_NOT_CLOSED . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'are not balanced';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        // the same view is a valid import once the row is closed with a row_end component
        $test_name = 'JSON import accepts a view with a closed row';
        $msg = new user_message($t->usr_dev);
        $json_array[json_fields::VIEWS][0][json_fields::COMPONENTS][] = [
            json_fields::POSITION => 3,
            json_fields::NAME => 'system formatter row end'
        ];
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());

        $t->subheader($ts . 'view component position check');

        // a view that uses the same component position twice (and so misses one) is reported as an
        // error; this fixture also sets component code ids, so the dev user is used again
        $test_name = 'JSON import reports a double component position';
        $msg = new user_message($t->usr_dev);
        $json_str = file_get_contents(test_files::IMPORT_VIEW_COMPONENT_POS_DOUBLE . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'is used more than once';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        // the same view is a valid import once every component has a unique position from 1 to n
        $test_name = 'JSON import accepts a view with complete component positions';
        $msg = new user_message($t->usr_dev);
        $json_array[json_fields::VIEWS][0][json_fields::COMPONENTS][1][json_fields::POSITION] = 2;
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());

        // json has no order, so a position that differs from the json order is only a warning
        // that does not block the import but is reported because it could confuse the user
        $test_name = 'JSON import reports a position differing from the json order as a warning only';
        $msg = new user_message($t->usr_dev);
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
        $msg = new user_message($t->usr_dev);
        $json_str = file_get_contents(test_files::IMPORT_TRIPLE_LINK_AMBIGUOUS . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'the from/verb/to link "elevation kind of rank" is used by two triples with different names';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);

        // the same two names are a valid import once their links differ (here the second uses "of")
        $test_name = 'JSON import accepts two triples with different names and different links';
        $msg = new user_message($t->usr_dev);
        $json_array[json_fields::TRIPLES][1][json_fields::EX_VERB] = 'of';
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());


        $t->subheader($ts . 'value source check');

        // the source of a value is the name of a source declared in the "sources" section, so a
        // source given as a json object is reported instead of ending the import with a fatal
        // (see value_base::import_mapper)
        $test_name = 'JSON import reports a value source that is no name';
        $msg = new user_message($t->usr_dev);
        $json_str = file_get_contents(test_files::IMPORT_VALUE_SOURCE_NOT_A_NAME . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'the source of a value must be the name of a source';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);
        $test_name = '... and names the json of the source that is no name';
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'The World Factbook');

        // the same value is a valid import once its source is the name of a declared source
        $test_name = 'JSON import accepts a value that names its source';
        $msg = new user_message($t->usr_dev);
        $json_array[json_fields::VALUES][1][json_fields::SOURCE_NAME] = 'The World Factbook';
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());


        $t->subheader($ts . 'phrase reference check');

        // a reference of a word or a triple is a json object with a name and a type, so the
        // compact form that names the type as the key is reported instead of ending the import
        // with a fatal (see ref::import_list_valid)
        $test_name = 'JSON import reports a word reference that is no object';
        $msg = new user_message($t->usr_dev);
        $json_str = file_get_contents(test_files::IMPORT_REFS_NOT_A_LIST . test_files::JSON);
        $json_array = json_decode($json_str, true);
        $imp->get_data_object($json_array, $msg);
        $target = 'must be a list of objects each with a name and a type';
        $t->assert_text_contains($test_name, $msg->all_message_text(), $target);
        $test_name = '... and names the phrase and the unexpected reference format';
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'wikipedia');

        // the references of a triple are checked the same way as the references of a word
        $test_name = '... and reports a triple reference that is no object';
        $t->assert_text_contains($test_name, $msg->all_message_text(),
            'highest elevation of Zurich (canton)');

        // the same phrases are a valid import once the references are a list of objects
        $test_name = 'JSON import accepts references given as a list of objects';
        $msg = new user_message($t->usr_dev);
        // each phrase gets an external key of its own, because one key identifies one phrase
        $json_array[json_fields::WORDS][0][json_fields::REFS] =
            [[json_fields::NAME => refs::PI_KEY, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE]];
        $json_array[json_fields::TRIPLES][0][json_fields::REFS] =
            [[json_fields::NAME => refs::MATH_KEY, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE]];
        $imp->get_data_object($json_array, $msg);
        $t->assert_true($test_name, $msg->is_ok());


        $t->subheader($ts . 'types cache clear');

        // an imported symbol triple or unit word is part of the system phrases of the cached types
        // api message, so saving it clears that cache (see data_object::save_triples and save_words)
        $t_trp = new test_triples($t);
        $t_wrd = new test_words($t);
        $test_name = 'an alias triple clears the types cache';
        $t->assert_true($test_name, $t_trp->triple_pi()->has_preload_verb());
        $test_name = '... but an is a triple does not';
        $t->assert_false($test_name, $t_trp->triple_gwp()->has_preload_verb());
        $test_name = 'a triple list with an alias triple clears the types cache';
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->add($t_trp->triple_gwp());
        $trp_lst->add($t_trp->triple_pi());
        $t->assert_true($test_name, $trp_lst->has_preload_verb());
        $test_name = '... but a triple list without one does not';
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->add($t_trp->triple_gwp());
        $t->assert_false($test_name, $trp_lst->has_preload_verb());
        $test_name = 'a percent word clears the types cache';
        $t->assert_true($test_name, $t_wrd->word_percent()->has_preload_type());
        $test_name = '... and a scaling word too';
        $t->assert_true($test_name, $t_wrd->word_billion()->has_preload_type());
        $test_name = '... but a factor word does not';
        $t->assert_false($test_name, $t_wrd->word_factor()->has_preload_type());
        $test_name = 'a word list with a scaling word clears the types cache';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->add($t_wrd->word_factor());
        $wrd_lst->add($t_wrd->word_billion());
        $t->assert_true($test_name, $wrd_lst->has_preload_type());
        $test_name = '... but a word list without one does not';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->add($t_wrd->word_factor());
        $t->assert_false($test_name, $wrd_lst->has_preload_type());

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
        // TODO Prio 2 review the parameter context
        $msg->usr = $t->usr1;
        $conv_str = $conv_wiki->convert_wiki_json($in_table, $msg, test_base::TEST_TIMESTAMP, $context_str,
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
            $in_table, $msg, test_base::TEST_TIMESTAMP, $context_str);
        $result = json_decode($conv_str, true);
        $target = json_decode($json_str, true);
        $t->assert_json($test_name, $result, $target);

        // the csv of a bank statement of the positions to the import json of the holdings (convert_portfolio_csv)
        $test_name = 'the positions of a bank csv are the rows below its header row';
        $conv_csv = new convert_portfolio_csv();
        $csv_txt = file_get_contents(test_files::IMPORT_PORTFOLIO_CSV_SAMPLE);
        $csv_src = 'bank statement 040270501213 of 06.02.26';
        // the lines above the first position and the empty cells behind the reference currency of a test line
        $csv_head = implode("\n", array_slice(explode("\n", $csv_txt), 0, 4));
        $csv_tail = ',,,,,,,,,,';
        // one message for every call of the csv tests, reset after each assert that has read it
        $csv_msg = new user_message($t->usr1);
        $pos_lst = $conv_csv->positions($csv_txt, $csv_src, $csv_msg);
        $pos_cnt = count($pos_lst);
        $t->assert($test_name, $pos_cnt, 69);
        $test_name = '... without a message';
        $t->assert_msg($test_name, $csv_msg);
        $csv_msg->reset();
        $test_name = '... by their line in the csv, so that a message can name it';
        $pos_line = array_key_first($pos_lst);
        $t->assert($test_name, (string)$pos_line, '5');
        $test_name = '... with the cells named by the header and without the spaces around a cell';
        $pos_first = $pos_lst[$pos_line];
        $pos_instrument = $pos_first[convert_portfolio_csv::COL_INSTRUMENT];
        $t->assert($test_name, $pos_instrument, '2120.001.01');
        // negative: a csv without the header row of a bank statement has no position, which the message says
        $test_name = '... but a csv without the header row has no position';
        $pos_none = $conv_csv->positions("Asset,Name\nstock,ABB\n", $csv_src, $csv_msg);
        $t->assert($test_name, $pos_none, []);
        $test_name = '... and the message names the missing header row';
        $hdr_txt = 'has no header row that starts with "' . convert_portfolio_csv::HEADER_START . '"';
        $t->assert_text_contains($test_name, $csv_msg->text(), $hdr_txt);
        $csv_msg->reset();
        // the position of an instrument, empty if the csv has none, so that a missing one fails the assert and not
        // the call of holding
        $by_instrument = fn(string $key) => array_values(array_filter($pos_lst,
            fn(array $pos) => $pos[convert_portfolio_csv::COL_INSTRUMENT] == $key))[0] ?? [];
        $test_name = 'an equity position is a stock with its shares and its price';
        $hld = $conv_csv->holding($by_instrument('CH0012221716'));
        $hld_stock = [$hld[convert_portfolio_csv::KIND], $hld[convert_portfolio_csv::QUANTITY], $hld[convert_portfolio_csv::PRICE]];
        $t->assert($test_name, $hld_stock, [convert_portfolio_csv::KIND_STOCK, 6000.0, 61.74]);
        $test_name = 'a bond position priced in percent of its face value is a bond';
        $hld = $conv_csv->holding($by_instrument('US742718FY05'));
        $hld_kind = $hld[convert_portfolio_csv::KIND];
        $t->assert($test_name, $hld_kind, convert_portfolio_csv::KIND_BOND);
        // negative: a bond fund with a price per unit is no bond, because its value is no percent of a face value
        $test_name = '... but a bond fund priced per unit is a stock';
        $hld = $conv_csv->holding($by_instrument('LU0854923223'));
        $hld_kind = $hld[convert_portfolio_csv::KIND];
        $t->assert($test_name, $hld_kind, convert_portfolio_csv::KIND_STOCK);
        $test_name = 'a cash position is cash in its currency with the exchange rate of the bank';
        $hld = $conv_csv->holding($by_instrument('2120.814.01'));
        $hld_cash = [$hld[convert_portfolio_csv::KIND], $hld[convert_portfolio_csv::CURRENCY], $hld[convert_portfolio_csv::EXCHANGE_RATE]];
        $t->assert($test_name, $hld_cash, [convert_portfolio_csv::KIND_CASH, 'EUR', 0.9323]);
        $test_name = 'the import json of a bank csv has the shares of a stock';
        $csv_json = $conv_csv->convert($csv_txt, ['portfolio 040270501213'], $csv_src, $csv_msg, test_base::TEST_TIMESTAMP);
        $csv_arr = json_decode($csv_json, true);
        $val_of = fn(array $words) => array_values(array_filter($csv_arr[json_fields::VALUES] ?? [],
            fn(array $val) => $val[json_fields::WORDS] == $words))[0][json_fields::NUMBER] ?? null;
        $stock_shares = $val_of(['CH0012221716', 'shares', 'original']);
        $t->assert($test_name, $stock_shares, '6000');
        $test_name = '... and the exchange rate of a currency of the bank';
        $eur_rate = $val_of(['exchange rate', 'CHF per EUR']);
        $t->assert($test_name, $eur_rate, '0.9323');
        // the unit of an exchange rate is the triple of the currency and not of its rate, copied verbatim from its
        // home file portfolio_split_calculator.json, so that the import of the json never changes it
        $pair_com = fn(array $trp_lst, string $currency) => array_values(array_filter($trp_lst,
            fn(array $trp) => (($trp[json_fields::EX_VERB] ?? '') == verbs::PER_NAME
                and ($trp[json_fields::EX_TO] ?? '') == $currency)))[0][json_fields::DESCRIPTION] ?? '';
        $test_name = '... and the unit of the exchange rate verbatim from its home file';
        $eur_com = $pair_com($csv_arr[json_fields::TRIPLES], 'EUR');
        $t->assert($test_name, $eur_com, self::CSV_PAIR_EUR_COM);
        // negative: a currency pair that the home file does not have gets the general description
        $test_name = '... but a currency pair that its home file does not have the general description';
        $nok_csv = $csv_head . "\nCash and short-term investments , test cash,TST0001,10,,NOK,1,NOK,,,0.08,0.8,CHF"
            . $csv_tail . "\n";
        $nok_arr = json_decode($conv_csv->convert($nok_csv, ['portfolio 040270501213'], $csv_src, $csv_msg,
            test_base::TEST_TIMESTAMP), true);
        $nok_com = $pair_com($nok_arr[json_fields::TRIPLES], 'NOK');
        $t->assert($test_name, $nok_com, self::CSV_PAIR_NOK_COM);
        $test_name = '... and one walk step for each holding but the first';
        $steps = array_filter($csv_arr[json_fields::TRIPLES] ?? [],
            fn(array $trp) => ($trp[json_fields::EX_VERB] ?? '') == verbs::FOLLOW_NAME);
        $step_cnt = count($steps);
        $t->assert($test_name, $step_cnt, 68);
        $test_name = '... and is written as the expected import json';
        $t->assert_file($test_name, $csv_json, test_files::IMPORT_PORTFOLIO_CSV_SAMPLE_JSON, test_files::JSON);
        $test_name = '... without a message';
        $t->assert_msg($test_name, $csv_msg);
        $csv_msg->reset();
        // the import reads every phrase of a value from its own file, so the json can be imported only if it
        // declares all of them (docs/llm/json_structure.md "Self-consistency")
        $test_name = '... which the import reads without a message';
        // the data object of the import is created for the user of the import, which a direct call has to set; the
        // normal test user, because the json has no code id
        $csv_imp = new import();
        $csv_imp->usr = $t->usr1;
        $csv_dto = $csv_imp->get_data_object($csv_arr, $csv_msg);
        // the import of a json with some hundred values takes longer than a function, so the import timeout is used
        $t->assert_msg($test_name, $csv_msg, $t::TIMEOUT_LIMIT_IMPORT);
        $csv_msg->reset();
        $test_name = '... and with all its values';
        $dto_val_cnt = $csv_dto->value_list()->count();
        $json_val_cnt = count($csv_arr[json_fields::VALUES]);
        $t->assert($test_name, $dto_val_cnt, $json_val_cnt, $t::TIMEOUT_LIMIT_IMPORT);
        $test_name = '... and the price of a stock as the triple "stock price" that the file declares';
        $dto_trp_names = $csv_dto->triple_list()->names();
        $t->assert_contains($test_name, $dto_trp_names, convert_portfolio_csv::STOCK_PRICE);
        // negative: the import adds a phrase that a file does not declare as a name without a message
        // (phrase_list::import_mapper), so without the declaration "stock price" would not be the price of a stock
        $test_name = '... but without its declaration "stock price" is no triple';
        $broken_arr = $csv_arr;
        $broken_arr[json_fields::TRIPLES] = array_values(array_filter($csv_arr[json_fields::TRIPLES],
            fn(array $trp) => ($trp[json_fields::NAME] ?? '') != convert_portfolio_csv::STOCK_PRICE));
        $broken_dto = $csv_imp->get_data_object($broken_arr, $csv_msg);
        $broken_trp_names = $broken_dto->triple_list()->names();
        $t->assert_contains_not($test_name, $broken_trp_names, convert_portfolio_csv::STOCK_PRICE, $t::TIMEOUT_LIMIT_IMPORT);
        $csv_msg->reset();
        // negative: a position that cannot be converted is left out, and the message names its line in the csv, the
        // position and the reason: an asset class without a kind, a cell that is not a number, an empty cell, an
        // exchange rate of zero and a line with another number of cells than the header e.g. by a comma in a name
        $at_line = ' in line 5 of the csv "' . $csv_src . '"';
        $rate_row = 'Cash and short-term investments , test cash,TST0001,10,,EUR,1,EUR,,,0,10,CHF' . $csv_tail;
        $bad_rows = [
            'Derivatives , Call option,DER0001,10,,CHF,5,CHF,,,1,50,CHF' . $csv_tail
            => 'DER0001' . $at_line . ' has the asset class "Derivatives"',
            'Cash and short-term investments , test cash,TST0001,ten,,CHF,1,CHF,,,1,10,CHF' . $csv_tail
            => '"' . convert_portfolio_csv::COL_QUANTITY . '" of the position TST0001' . $at_line . ' is "ten", which is not a number',
            'Cash and short-term investments , test cash,TST0001,10,,,1,CHF,,,1,10,CHF' . $csv_tail
            => '"' . convert_portfolio_csv::COL_CURRENCY . '" of the position TST0001' . $at_line . ' is empty',
            $rate_row
            => '"' . convert_portfolio_csv::COL_EXCHANGE_RATE . '" of the position TST0001' . $at_line . ' is 0, but',
            'Cash and short-term investments , test, cash,TST0001,10,,CHF,1,CHF,,,1,10,CHF' . $csv_tail
            => 'Line 5 of the csv "' . $csv_src . '" has 24 cells, but its header row has 23',
        ];
        foreach ($bad_rows as $bad_row => $bad_txt) {
            $test_name = 'a position that cannot be converted is left out: ' . $bad_txt;
            $bad_json = $conv_csv->convert($csv_head . "\n" . $bad_row . "\n", ['portfolio 040270501213'], $csv_src,
                $csv_msg, test_base::TEST_TIMESTAMP);
            $bad_arr = json_decode($bad_json, true);
            $bad_vals = $bad_arr[json_fields::VALUES];
            $t->assert($test_name, $bad_vals, []);
            $test_name = '... and named in the message: ' . $bad_txt;
            $t->assert_text_contains($test_name, $csv_msg->text(), $bad_txt);
            $csv_msg->reset();
        }
        // the csv of the 42 holdings of portfolio_split_calculator.json with their tickers, to compare the two json
        $test_name = 'the import json of the holdings of the portfolio split sample is written to compare it';
        $split_json = $conv_csv->convert(file_get_contents(test_files::IMPORT_PORTFOLIO_CSV_SPLIT),
            ['example portfolio split'], 'bank reporting bank leu client xy', $csv_msg, test_base::TEST_TIMESTAMP);
        $t->assert_file($test_name, $split_json, test_files::IMPORT_PORTFOLIO_CSV_SPLIT_JSON, test_files::JSON);
        $test_name = '... which the conversion and the import give without a message';
        $split_arr = json_decode($split_json, true);
        $split_dto = $csv_imp->get_data_object($split_arr, $csv_msg);
        $t->assert_msg($test_name, $csv_msg, $t::TIMEOUT_LIMIT_IMPORT);
        $csv_msg->reset();
        $test_name = '... and with all its values';
        $split_dto_cnt = $split_dto->value_list()->count();
        $split_json_cnt = count($split_arr[json_fields::VALUES]);
        $t->assert($test_name, $split_dto_cnt, $split_json_cnt, $t::TIMEOUT_LIMIT_IMPORT);

        // the format of the bank csv as phrases, which convert_csv reads instead of the consts of convert_portfolio_csv
        $test_name = 'the definition json of the bank csv format is read without a message';
        $fmt_arr = json_decode(file_get_contents(test_files::IMPORT_PORTFOLIO_CSV_FORMAT), true);
        $fmt_dto = $csv_imp->get_data_object($fmt_arr, $csv_msg);
        $t->assert_msg($test_name, $csv_msg, $t::TIMEOUT_LIMIT_IMPORT);
        $csv_msg->reset();
        $test_name = '... with every value template';
        $fmt_dto_cnt = $fmt_dto->value_list()->count();
        $fmt_json_cnt = count($fmt_arr[json_fields::VALUES]);
        $t->assert($test_name, $fmt_dto_cnt, $fmt_json_cnt, $t::TIMEOUT_LIMIT_IMPORT);
        $test_name = '... and the columns linked to the keyword "csv column" that the file declares';
        $fmt_trp_names = $fmt_dto->triple_list()->names();
        $t->assert_contains($test_name, $fmt_trp_names, triples::CSV_COLUMN);

        // the conversion of a csv by the phrases of its format, which replaces convert_portfolio_csv with change 3
        $test_name = 'convert_csv reads the format of the bank csv from its definition without a message';
        $fmt_conv = new convert_csv();
        $fmt_conv->set_format($fmt_arr, self::CSV_FORMAT, $csv_msg);
        $t->assert_msg($test_name, $csv_msg);
        $csv_msg->reset();
        $test_name = '... and writes the shares of a stock';
        $fmt_json = $fmt_conv->convert($csv_txt, ['portfolio 040270501213'], $csv_src, $csv_msg, test_base::TEST_TIMESTAMP);
        $fmt_csv_arr = json_decode($fmt_json, true);
        $fmt_val_of = fn(array $words) => array_values(array_filter($fmt_csv_arr[json_fields::VALUES] ?? [],
            fn(array $val) => $val[json_fields::WORDS] == $words))[0][json_fields::NUMBER] ?? null;
        $fmt_shares = $fmt_val_of(['CH0012221716', 'shares', 'original']);
        $t->assert($test_name, $fmt_shares, '6000');
        $test_name = '... and the exchange rate of a currency of the bank';
        $fmt_rate = $fmt_val_of(['exchange rate', 'CHF per EUR']);
        $t->assert($test_name, $fmt_rate, '0.9323');
        $test_name = '... and the unit of the exchange rate verbatim from the definition';
        $fmt_eur_com = $pair_com($fmt_csv_arr[json_fields::TRIPLES], 'EUR');
        $t->assert($test_name, $fmt_eur_com, self::CSV_PAIR_EUR_COM);
        $test_name = '... and the hint of a holding with the name of csv name';
        $fmt_trp_lst = array_map(fn(array $trp) => $trp[json_fields::NAME] ?? '', $fmt_csv_arr[json_fields::TRIPLES]);
        $t->assert_contains($test_name, $fmt_trp_lst, self::CSV_HINT_NAME);
        $test_name = '... without a message';
        $t->assert_msg($test_name, $csv_msg);
        $csv_msg->reset();
        // the walk, the bond fund and the declarations differ by the decisions of 2026-11-26, so the difference to
        // convert_portfolio_csv is a report, which changes only by a decision of the developer
        $test_name = '... and differs from convert_portfolio_csv only as the report says';
        $old_csv_arr = json_decode(file_get_contents(test_files::IMPORT_PORTFOLIO_CSV_SAMPLE_JSON), true);
        $fmt_diff = $this->csv_json_diff($old_csv_arr, $fmt_csv_arr);
        $t->assert_file($test_name, $fmt_diff, test_files::IMPORT_PORTFOLIO_CSV_SAMPLE_DIFF, test_files::MD);
        $test_name = '... which the import reads without a message, because it copies the declarations it uses';
        $csv_imp->get_data_object($fmt_csv_arr, $csv_msg);
        $t->assert_msg($test_name, $csv_msg, $t::TIMEOUT_LIMIT_IMPORT);
        $csv_msg->reset();
        // negative: a currency pair that the definition does not declare gets the description of its template
        $test_name = '... but a currency pair that the definition does not declare the description of its template';
        $fmt_nok_arr = json_decode($fmt_conv->convert($nok_csv, ['portfolio 040270501213'], $csv_src, $csv_msg,
            test_base::TEST_TIMESTAMP), true);
        $fmt_nok_com = $pair_com($fmt_nok_arr[json_fields::TRIPLES], 'NOK');
        $t->assert($test_name, $fmt_nok_com, self::CSV_PAIR_NOK_COM);
        $csv_msg->reset();
        $test_name = 'convert_csv gives the holdings of the portfolio split sample as the report says';
        $fmt_split_json = $fmt_conv->convert(file_get_contents(test_files::IMPORT_PORTFOLIO_CSV_SPLIT),
            ['example portfolio split'], 'bank reporting bank leu client xy', $csv_msg, test_base::TEST_TIMESTAMP);
        $old_split_arr = json_decode(file_get_contents(test_files::IMPORT_PORTFOLIO_CSV_SPLIT_JSON), true);
        $fmt_split_diff = $this->csv_json_diff($old_split_arr, json_decode($fmt_split_json, true));
        $t->assert_file($test_name, $fmt_split_diff, test_files::IMPORT_PORTFOLIO_CSV_SPLIT_DIFF, test_files::MD);
        $test_name = '... without a message';
        $t->assert_msg($test_name, $csv_msg);
        $csv_msg->reset();
        // negative: a row that cannot be converted is left out with the same messages as by convert_portfolio_csv
        foreach ($bad_rows as $bad_row => $bad_txt) {
            $test_name = 'convert_csv leaves out a row that cannot be converted: ' . $bad_txt;
            $fmt_bad_json = $fmt_conv->convert($csv_head . "\n" . $bad_row . "\n", ['portfolio 040270501213'], $csv_src,
                $csv_msg, test_base::TEST_TIMESTAMP);
            $fmt_bad_vals = json_decode($fmt_bad_json, true)[json_fields::VALUES];
            $t->assert($test_name, $fmt_bad_vals, []);
            $test_name = '... and names it in the message: ' . $bad_txt;
            $t->assert_text_contains($test_name, $csv_msg->text(), $bad_txt);
            $csv_msg->reset();
        }
        // a cell with the flag csv positive can be any column of any format, so the message names no exchange rate
        $test_name = 'convert_csv says that a positive cell must be above zero';
        $fmt_conv->convert($csv_head . "\n" . $rate_row . "\n", ['portfolio 040270501213'], $csv_src, $csv_msg,
            test_base::TEST_TIMESTAMP);
        $rate_txt = $csv_msg->text();
        $t->assert_text_contains($test_name, $rate_txt, 'is 0, but it must be above zero');
        $test_name = '... without the exchange rate of the bank csv';
        $t->assert_text_not_contains($test_name, $rate_txt, 'exchange rate must');
        $csv_msg->reset();
        // negative: a csv without the header row of its format has no row, which the message says
        $test_name = 'convert_csv reports a csv without the header row of its format';
        $fmt_conv->convert("Asset,Name\nstock,ABB\n", ['portfolio 040270501213'], $csv_src, $csv_msg, test_base::TEST_TIMESTAMP);
        $t->assert_text_contains($test_name, $csv_msg->text(), $hdr_txt);
        $csv_msg->reset();
        // negative: without the triple to csv column the definition has no column, which the message says, because
        // the importer alone would add the undeclared keyword as a plain word without a message
        $test_name = 'a csv format without the keyword csv column cannot be used';
        $fmt_broken = $fmt_arr;
        $fmt_broken[json_fields::TRIPLES] = array_values(array_filter($fmt_arr[json_fields::TRIPLES],
            fn(array $trp) => ($trp[json_fields::EX_TO] ?? '') != triples::CSV_COLUMN));
        $fmt_broken_ok = new convert_csv()->set_format($fmt_broken, self::CSV_FORMAT, $csv_msg);
        $t->assert($test_name, $fmt_broken_ok, false);
        $test_name = '... and the message names the keyword';
        $t->assert_text_contains($test_name, $csv_msg->text(), '"' . triples::CSV_COLUMN . '"');
        $csv_msg->reset();

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

        // the factor compares the real time with the estimated time of the work done until now:
        // half of a 10s estimate done after 10s means the rest also takes longer than estimated,
        // which the factor based on the total estimate did not see
        $imp->est_time_total = 10;
        $imp->est_time_step = 0;
        $imp->micro_steps = 0;
        $imp->done_time_main_step = 5;
        $imp->done_time_step = 0;
        $test_name = 'import time estimate grows if the work done has taken longer than estimated';
        $est = $imp->calc_total_time($base + 10, 0);
        $t->assert_true($test_name, abs($est - 17.5) < 0.0001);
        $test_name = '... but stays the original estimate if the work done has taken the estimated time';
        $est = $imp->calc_total_time($base + 5, 0);
        $t->assert_true($test_name, abs($est - 10) < 0.0001);

        // the speeds of config.yaml are in MB per second, but the file size is in bytes
        $test_name = 'the file size estimate converts the configured MB per second';
        $t->assert_true($test_name, abs(import::est_time_by_size(2100000, 2.1) - 1) < 0.0001);
        $test_name = '... so that a file of 300 kB is not expected to take more than a day';
        $t->assert_true($test_name, import::est_time_by_size(310935, 2.1) < 1);
        $test_name = '... and a speed of zero gives no estimate instead of a division by zero';
        $t->assert_true($test_name, import::est_time_by_size(1000, 0) == 0);

        // the direct import estimates the store time by the number of objects of each section
        $imp_cnt = new import();
        $test_name = 'the store time of an import is the sum of the store time of the sections';
        $est_parts = $imp_cnt->est_time_save(json_fields::COMPONENTS, 100)
            + $imp_cnt->est_time_save(json_fields::VIEWS, 10);
        $est = $imp_cnt->est_time_put([
            json_fields::VERSION => def::PRG_VERSION,
            json_fields::COMPONENTS => array_fill(0, 100, []),
            json_fields::VIEWS => array_fill(0, 10, [])
        ]);
        $t->assert_true($test_name, $est > 0 and abs($est - $est_parts) < 0.0001);
        $test_name = '... and a section without objects to store adds no time';
        $t->assert_true($test_name, $imp_cnt->est_time_put([json_fields::SELECTION => ['a', 'b']]) == 0);
        $test_name = 'a view takes longer to store than a component, because it also links its components';
        $t->assert_true($test_name,
            $imp_cnt->est_time_save(json_fields::VIEWS, 1) > $imp_cnt->est_time_save(json_fields::COMPONENTS, 1));
        $test_name = 'a section without a configured speed uses the fallback speed';
        $t->assert_true($test_name,
            abs($imp_cnt->est_time_save(json_fields::LIST_VERBS, def::FALLBACK_IMPORT_PER_SEC) - 1) < 0.0001);

        $t->subheader($ts . 'step note');

        // the note names the step that starts, so that a hang of a long import shows the step;
        // the throttle is fixed by the time of the last line: zero is long ago, so a note is due
        $note_step = 'rename the link conflicts of';
        $imp = new import('xbrl_fileset.json');
        $imp->est_time_total = import::LONG_IMPORT_SEC + 1;
        $imp->last_display_time = 0;
        $test_name = 'the start of a step of a long import is noted';
        $note = $this->step_note_text($imp, $note_step, triple::class, 8000);
        $t->assert_text_contains($test_name, $note, 'xbrl_fileset.json start ' . $note_step . ' 8000 triples');
        $test_name = '... which restarts the throttle like a progress line';
        $t->assert_true($test_name, $imp->last_display_time > 0);
        // negative: within the response time after the last line the note is skipped; the last line
        // is set in the future, so that the check never depends on the configured response time
        $test_name = 'a note within the response time after the last line is skipped by the throttle';
        $imp->last_display_time = microtime(true) + 3600;
        $note = $this->step_note_text($imp, $note_step, triple::class, 8000);
        $t->assert($test_name, $note, '');
        // the note timer shows the skipped note if the step is still running, so that a hang shows its step;
        // called directly, because the test never waits for the timer
        $test_name = '... but kept and shown with its running time by the note timer';
        $note = $this->pending_note_text($imp);
        $t->assert_text_contains($test_name, $note, 'xbrl_fileset.json start ' . $note_step . ' 8000 triples (running since ');
        // negative: a shown note is not shown again by a second timer call
        $test_name = '... and shown only once';
        $t->assert($test_name, $this->pending_note_text($imp), '');
        // negative: a short import e.g. a small file of the setup writes no note at all
        $test_name = 'the start of a step of a short import is not noted';
        $imp = new import('words.json');
        $imp->last_display_time = 0;
        $note = $this->step_note_text($imp, $note_step, triple::class, 8000);
        $t->assert($test_name, $note, '');

        // the alarm of the note timer is held back during a blocking call e.g. a wikidata request,
        // because the signal would break the wait for the answer or end the pause before a retry
        $test_name = 'a call without the note timer gives the result of the call';
        $t->assert($test_name, import::without_note_timer(fn() => 'answer'), 'answer');
        if (function_exists('pcntl_sigprocmask')) {
            $test_name = '... and holds back the alarm of the timer during the call';
            $blocked = import::without_note_timer(fn() => $this->blocked_signals());
            $t->assert_true($test_name, in_array(SIGALRM, $blocked));
            $test_name = '... also after a nested call';
            $blocked = import::without_note_timer(function () {
                import::without_note_timer(fn() => null);
                return $this->blocked_signals();
            });
            $t->assert_true($test_name, in_array(SIGALRM, $blocked));
            // negative: after the call the alarm reaches the note timer again
            $test_name = '... but not after the call';
            $t->assert_false($test_name, in_array(SIGALRM, $this->blocked_signals()));
        }


        $t->subheader($ts . 'merge two import json');

        // merging two import files must not lose data: the objects of the second file are
        // added to the first, and a matching object (same name resp. same value phrase group)
        // only fills the vars that the first file has left unset, so like on import the first
        // file wins on a conflict (see the import order rule in docs/llm/json_structure.md)
        // use an own import object, because the time estimate tests above have replaced $imp
        // with an import object without a user
        $imp = new import();
        $imp->usr = $t->usr_dev;
        $msg = new user_message($t->usr_dev);
        $json1 = [
            json_fields::WORDS => [
                [json_fields::NAME => words::MIO],
                [json_fields::NAME => word_names::YEAR_2019, json_fields::DESCRIPTION => 'first file description']
            ],
            json_fields::VALUES => [[
                json_fields::WORDS => [words::MIO, word_names::YEAR_2019],
                json_fields::NUMBER => 1.1
            ]]
        ];
        $json2 = [
            json_fields::WORDS => [
                [json_fields::NAME => words::MIO, json_fields::DESCRIPTION => 'second file description'],
                [json_fields::NAME => word_names::YEAR_2019, json_fields::DESCRIPTION => 'conflicting description'],
                [json_fields::NAME => word_names::YEAR_2020, json_fields::DESCRIPTION => 'added description']
            ],
            json_fields::VALUES => [
                [
                    json_fields::WORDS => [words::MIO, word_names::YEAR_2019],
                    json_fields::NUMBER => 2.2
                ],
                [
                    json_fields::WORDS => [words::MIO, word_names::YEAR_2020],
                    json_fields::NUMBER => 3.3
                ]
            ]
        ];
        $dto1 = $imp->get_data_object($json1, $msg);
        $dto2 = $imp->get_data_object($json2, $msg);
        $msg->merge($dto1->fill($dto2, $t->usr_dev));

        $test_name = 'merge adds the words missing in the first file without duplicating the common ones';
        $t->assert($test_name, $dto1->word_list()->count(), 3);
        $test_name = 'merge fills a description gap of the first file from the second file';
        $wrd_mio = $dto1->word_list()->get_by_name(words::MIO, $msg);
        $t->assert($test_name, $wrd_mio?->description, 'second file description');
        $test_name = 'on a description conflict the first file wins';
        $wrd_2019 = $dto1->word_list()->get_by_name(word_names::YEAR_2019, $msg);
        $t->assert($test_name, $wrd_2019?->description, 'first file description');
        $test_name = 'merge adds the values missing in the first file without duplicating the common ones';
        $t->assert($test_name, $dto1->value_list()->count(), 2);
        $test_name = 'on a number conflict the value of the first file wins';
        $val = $dto1->value_list()->get_by_names([words::MIO, word_names::YEAR_2019]);
        $t->assert($test_name, $val?->number(), 1.1);

        // the merged data object must round-trip through the export json without losing objects
        $test_name = 'the export json of the merged data object has the merged words';
        $export_json = $dto1->export_json($msg);
        $t->assert($test_name, count($export_json[json_fields::WORDS] ?? []), 3);
        $test_name = 'reading the export json back returns the merged object counts';
        $dto3 = $imp->get_data_object($export_json, $msg);
        $t->assert($test_name, $dto3->word_list()->count(), 3);
        $test_name = '... and the merged value counts';
        $t->assert($test_name, $dto3->value_list()->count(), 2);
        $test_name = '... and keeps the filled description';
        $t->assert($test_name, $dto3->word_list()->get_by_name(words::MIO, $msg)?->description, 'second file description');

        // negative: an empty data object exports only the version header
        $test_name = 'an empty data object exports only the version header';
        $dto_empty = new data_object($t->usr_dev);
        $t->assert($test_name, $dto_empty->export_json($msg), [json_fields::VERSION => def::PRG_VERSION]);

        // merge the three offline_is_better_than_online sample files and compare the result
        // with the committed merged file (same folder, no number suffix); the comparison is
        // done data object to data object via diff_msg, so the json formatting, the header
        // and the section order do not matter, only the merged content does
        $test_name = 'merging the three offline_is_better_than_online files matches the merged file';
        $msg = new user_message($t->usr_dev);
        $dto_merged = null;
        foreach ([test_files::IMPORT_MERGE_1, test_files::IMPORT_MERGE_2, test_files::IMPORT_MERGE_3] as $part_file) {
            $json_array = json_decode(file_get_contents($part_file . test_files::JSON), true);
            $dto_part = $imp->get_data_object($json_array, $msg);
            if ($dto_merged == null) {
                $dto_merged = $dto_part;
            } else {
                $msg->merge($dto_merged->fill($dto_part, $t->usr_dev));
            }
        }
        $json_array = json_decode(file_get_contents(
            test_files::IMPORT_MERGE_RESULT . test_files::JSON), true);
        $dto_expected = $imp->get_data_object($json_array, $msg);
        // compare by the sorted name lists, because for in-memory data objects all ids are
        // zero, so the id based diff_msg would match any object to any other object
        $names_merged = $dto_merged->word_list()->names();
        $names_expected = $dto_expected->word_list()->names();
        sort($names_merged);
        sort($names_expected);
        $t->assert($test_name, $names_merged, $names_expected);
        $test_name = '... and the merged triples match the merged file';
        $names_merged = $dto_merged->triple_list()->names();
        $names_expected = $dto_expected->triple_list()->names();
        sort($names_merged);
        sort($names_expected);
        $t->assert($test_name, $names_merged, $names_expected);
        $test_name = '... and the duplicated triples of the three files are merged';
        $t->assert($test_name, $dto_merged->triple_list()->count(), 62);
        $test_name = '... and the merged values match the merged file';
        $t->assert($test_name, $dto_merged->value_list()->count(), $dto_expected->value_list()->count());
        // one value per input file to prove that no file is lost in the merge
        $test_name = '... incl. the canvassing effect size of the first file';
        $val = $dto_merged->value_list()->get_by_names(
            ['narrative exchange canvassing', 'effect size on exclusionary attitude']);
        $t->assert($test_name, $val?->number(), 0.08);
        $test_name = '... incl. the smartphone inflection point of the second file';
        $val = $dto_merged->value_list()->get_by_names(
            ['weekday smartphone use', 'inflection point', 'minute']);
        $t->assert($test_name, $val?->number(), 117.0);
        $test_name = '... incl. the consumer surplus of the third file';
        $val = $dto_merged->value_list()->get_by_names(
            ['Facebook', 'consumer surplus', 'US dollar']);
        $t->assert($test_name, $val?->number(), 31000000000.0);
        $test_name = '... and the merged sources match the merged file';
        $t->assert($test_name, $dto_merged->source_list()->count(), $dto_expected->source_list()->count());
        $test_name = '... and the merged formulas match the merged file';
        $t->assert($test_name, $dto_merged->formula_list()->count(), $dto_expected->formula_list()->count());
        $test_name = '... and the merged calc validations match the merged file';
        $t->assert($test_name, $dto_merged->result_check_list()->count(), $dto_expected->result_check_list()->count());


        $t->subheader($ts . 'export');
        $t_trm = new test_terms($t);

        // data_object: a word with only a name goes into the compact word list
        $test_name = 'export a name only word in the word list';
        $dto = $t_trm->dto_minute_and_second();
        $exp = $dto->export_json($msg);
        $wrd_names = $exp[json_fields::WORD_LIST];
        $t->assert($test_name, $wrd_names[0], word_names::MINUTE);
        $msg->reset();

        // negative: a data object without words has no word list
        $test_name = 'export no word list without words';
        $dto = $t_trm->dto_scale_none();
        $exp = $dto->export_json($msg);
        $t->assert_key_not_exists($test_name, json_fields::WORD_LIST, $exp);
        $msg->reset();

        // data_object: a word with a config gets a full word entry
        $test_name = 'export a word with config as full entry';
        $dto = $t_trm->dto_scale_mio();
        $exp = $dto->export_json($msg);
        $wrd_entries = $exp[json_fields::WORDS];
        $first = $wrd_entries[0];
        $t->assert($test_name, $first[json_fields::NAME], word_names::ONE);
        $msg->reset();

        // negative: name only words get no full word entry
        $test_name = 'export no full entry for name only words';
        $dto = $t_trm->dto_minute_and_second();
        $exp = $dto->export_json($msg);
        $t->assert_key_not_exists($test_name, json_fields::WORDS, $exp);
        $msg->reset();

        $t->subheader($ts . 'update put');
        $upd = new update_put();

        $test_name = 'a group id as text, which the editor sends, is a number';
        $grp_id = $upd->db_id(self::TV_GROUP_ID_TEXT);
        $t->assert_same($test_name, $grp_id, self::TV_GROUP_ID);
        $test_name = '... and a number stays a number';
        $grp_id = $upd->db_id(self::TV_GROUP_ID);
        $t->assert_same($test_name, $grp_id, self::TV_GROUP_ID);
        $test_name = 'the text id of a value with more than four phrases stays a text';
        $grp_id = $upd->db_id(self::TV_GROUP_ID_KEY);
        $t->assert_same($test_name, $grp_id, self::TV_GROUP_ID_KEY);

    }

    /**
     * @param import $imp the import whose step note is written
     * @param string $step the step that starts e.g. 'rename the link conflicts of'
     * @param string $class the class of the objects of the step e.g. triple::class
     * @param float $total the number of objects of the step
     * @return string the line that import::step_note writes to the log, empty if none is written
     */
    private function step_note_text(import $imp, string $step, string $class, float $total): string
    {
        ob_start();
        $imp->step_note($step, $class, $total);
        return (string)ob_get_clean();
    }

    /**
     * @param import $imp the import with a step note that the throttle may have skipped
     * @return string the line that the note timer writes to the log, empty if no note is pending
     */
    private function pending_note_text(import $imp): string
    {
        ob_start();
        $imp->show_pending_note();
        return (string)ob_get_clean();
    }

    /**
     * @param array $old the import json of a csv by convert_portfolio_csv
     * @param array $new the import json of the same csv by convert_csv
     * @return string per section the entries that only one of the two has as markdown, compared as sets, so that a
     *                line of the report is a difference of the content and never of the order
     */
    private function csv_json_diff(array $old, array $new): string
    {
        $result = '# convert_csv compared with convert_portfolio_csv' . "\n";
        foreach (self::CSV_DIFF_SECTIONS as $section) {
            $old_lst = array_map(fn(mixed $entry) => $this->sorted_json($entry), $old[$section] ?? []);
            $new_lst = array_map(fn(mixed $entry) => $this->sorted_json($entry), $new[$section] ?? []);
            $result .= $this->csv_diff_lines($section . ' only by convert_portfolio_csv', array_diff($old_lst, $new_lst));
            $result .= $this->csv_diff_lines($section . ' only by convert_csv', array_diff($new_lst, $old_lst));
        }
        return $result;
    }

    /**
     * @param string $title the title of the list e.g. "triples only by convert_csv"
     * @param array $lines the entries as json
     * @return string the markdown list with the number of its entries in the title
     */
    private function csv_diff_lines(string $title, array $lines): string
    {
        $result = "\n" . '## ' . $title . ' (' . count($lines) . ')' . "\n";
        if ($lines != []) {
            $result .= "\n";
        }
        foreach ($lines as $line) {
            $result .= '- ' . $line . "\n";
        }
        return $result;
    }

    /**
     * @param mixed $entry an entry of the import json
     * @return string the entry as json with the keys of each object sorted, so that the order of the keys is no
     *                difference
     */
    private function sorted_json(mixed $entry): string
    {
        return json_encode($this->sorted_keys($entry), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param mixed $entry an entry of the import json or a part of it
     * @return mixed the entry with the keys of each object sorted, the order of a list kept
     */
    private function sorted_keys(mixed $entry): mixed
    {
        $result = $entry;
        if (is_array($entry)) {
            if (!array_is_list($entry)) {
                ksort($result);
            }
            $result = array_map(fn(mixed $part) => $this->sorted_keys($part), $result);
        }
        return $result;
    }

    /**
     * @return array the signals that are blocked at the moment e.g. [SIGALRM] during import::without_note_timer
     */
    private function blocked_signals(): array
    {
        $blocked = [];
        // a block of the alarm gives the mask before it, which is set again at once, because a block of
        // no signal to only read the mask is refused (ValueError since php 8.4)
        pcntl_sigprocmask(SIG_BLOCK, [SIGALRM], $blocked);
        pcntl_sigprocmask(SIG_SETMASK, $blocked);
        return $blocked;
    }

}