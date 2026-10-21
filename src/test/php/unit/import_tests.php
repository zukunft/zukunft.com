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
include_once paths::SHARED_CONST . 'files.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_WORD . 'triple.php';
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
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\cfg\const\files as cfg_files;
use Zukunft\ZukunftCom\main\php\shared\const\files as files_shared;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class import_tests
{
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
        $test_name = '... and repeats every calculator keyword without its code id';
        $keywords = [...array_keys(triples::CALCULATOR_LISTS), ...array_keys(triples::CALCULATOR_PROPERTIES)];
        $repeated = array_values(array_intersect($keywords, array_column($json_array[json_fields::TRIPLES], json_fields::NAME)));
        $t->assert($test_name, $repeated, $keywords);
        // negative: a triple that the file does not define is not in the import
        $test_name = '... but has no triple of another use case';
        $trp_none = $dto->get_triple_by_name(triple_names::PI, $msg);
        $t->assert_null($test_name, $trp_none?->name());

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

        // the portfolio split use case stores the allocation of the split walk as modelled values,
        // so the calc validation must reproduce the holding values, the remainder of the parent
        // in every holding, the target values and the final split deviation
        $test_name = 'the import of the portfolio split use case reports no problem';
        $msg = new user_message($t->usr1);
        $json_str = file_get_contents(cfg_files::PORTFOLIO_SPLIT_CALCULATOR_FILE);
        $json_array = json_decode($json_str, true);
        $dto = $imp->get_data_object($json_array, $msg);
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = '... and its calc validation reproduces every result';
        $t->assert($test_name, $dto->result_check_list()->count(), count($json_array[json_fields::CALC_VALIDATION]));
        // negative: an allocation of the first child that breaks the conservation of a holding
        $test_name = '... and an allocation that does not conserve the holding is reported';
        $msg = new user_message($t->usr1);
        foreach ($json_array[json_fields::VALUES] as $i => $val) {
            if (in_array(triple_names::FIRST_CHILD_PORTFOLIO, $val[json_fields::WORDS])) {
                $json_array[json_fields::VALUES][$i][json_fields::NUMBER] = '1';
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

    }

}