<?php

/*

    test/php/unit_ui/phrase_data_ui_tests.php - testing of the request cache filled from a phraseData answer
    -----------------------------------------

    the answer is the sample api/phrase_data/phrase_data_pv_install.json, so the test needs no backend


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::EXECUTE . 'calculator.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::PHRASE . 'phrase_data.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'files.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'api_types.php';
include_once html_paths::SHARED_TYPES . 'phrase_types.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\calculator;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_data;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_data_ui_tests
{

    // the id of the result that the test adds to the sample, which has no results
    const int RESULT_ID = 1;
    // a phrase name that the sample does not contain
    const string UNKNOWN_NAME = 'System Test Phrase Not In The Phrase Data';
    // a word of an answer without calculator triples
    const int NO_CALC_ID = 1;
    const string NO_CALC_NAME = 'System Test Word Without Calculator';
    // the json fields of the objects of the answer without the results
    const array OBJECT_FIELDS = [
        json_fields::WORDS,
        json_fields::TRIPLES,
        json_fields::FORMULAS,
        json_fields::SOURCES,
        json_fields::VALUES
    ];

    function run(test_cleanup $t): void
    {
        global $ui_sys;

        // start the test section (ts)
        $ts = 'unit ui phrase data ';
        $t->header($ts);

        $sample = $this->with_db_ids(json_decode(file_get_contents(test_files::PHRASE_DATA_PV_INSTALL), true));

        $t->subheader($ts . 'fill the request cache');
        $test_name = 'the sample fills the request cache without a message';
        $msg = new user_message();
        $dto = $this->filled($sample, $msg);
        $t->assert_msg($test_name, $msg);
        $test_name = '... with every word and triple as a phrase';
        $phr_count = count($sample[json_fields::WORDS]) + count($sample[json_fields::TRIPLES]);
        $t->assert($test_name, $dto->phr_lst->count(), $phr_count);
        $test_name = '... with the page triple built from its from phrase';
        $phr = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $t->assert($test_name, $phr?->get_from()?->name() ?? '', triple_names::PV_INSTALLATION);
        // the calculator reads its tree from the phrases of the cache, so the tree is complete
        $test_name = '... so that the page phrase is a calculator';
        $t->assert_true($test_name, $phr != null and new calculator($phr, $dto->phr_lst)->is_calculator());
        $test_name = '... with every value';
        $t->assert($test_name, $dto->val_lst->count(), count($sample[json_fields::VALUES]));
        $test_name = '... every formula';
        $t->assert($test_name, $dto->frm_lst->count(), count($sample[json_fields::FORMULAS]));
        $test_name = '... and every source';
        $t->assert($test_name, $dto->src_lst->count(), count($sample[json_fields::SOURCES]));
        // the sample names the type of a triple by its code id, which the type cache knows
        $test_name = '... and the type of a triple from the type cache';
        $trp = $dto->phr_lst->get_by_name(triple_names::GRAM_PER_KWH, $msg)?->obj();
        $typ_id = $ui_sys->typ_lst_cache->phr_typ->id(phrase_types::MEASURE);
        $t->assert($test_name, $trp?->type_id($msg) ?? 0, $typ_id);

        $t->subheader($ts . 'results');
        $test_name = 'a result of the answer is added with its context and formula';
        $frm = $sample[json_fields::FORMULAS][0];
        $json = $sample;
        $json[json_fields::RESULTS] = [[
            json_fields::ID => self::RESULT_ID,
            json_fields::WORDS => [triple_names::EXAMPLE_PV_INSTALLATION],
            json_fields::FORMULA_NAME => $frm[json_fields::NAME],
            json_fields::NUMBER => 1,
            json_fields::CONTEXT => [triple_names::EXAMPLE_PV_INSTALLATION, triple_names::PV_INSTALLATION]
        ]];
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $t->assert_msg($test_name, $msg);
        $res = $dto->res_lst->lst()[0] ?? null;
        $test_name = '... with the phrases of its context';
        $t->assert($test_name, $res?->src_grp?->phr_lst()->count() ?? 0, 2);
        $test_name = '... and the id of its formula';
        $t->assert($test_name, $res?->frm?->id() ?? 0, $frm[json_fields::ID]);
        // the page does not load the results of the selected phrase again
        $test_name = '... and the results of the selected phrase are complete';
        $sel_phr = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $t->assert_true($test_name, $dto->has_results_of($sel_phr?->id() ?? 0));
        // negative: without a selection the results are added, but not marked as complete
        $test_name = 'the results of an answer without selection are added';
        unset($json[json_fields::SELECTION]);
        $dto = $this->filled($json, $msg);
        $t->assert($test_name, $dto->res_lst->count(), 1);
        $test_name = '... but not as the complete results of a phrase';
        $t->assert_false($test_name, $dto->has_results_of($sel_phr?->id() ?? 0));

        // negative: a name that the answer does not contain is reported and not loaded from the backend
        $t->subheader($ts . 'names that the answer does not contain');
        $test_name = 'a value with an unknown phrase is reported';
        $json = $sample;
        $json[json_fields::VALUES][0][json_fields::WORDS][] = self::UNKNOWN_NAME;
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::PHRASE_NAME_NOT_FOUND));
        $test_name = '... and named in the message';
        $t->assert_text_contains($test_name, $msg->text(), self::UNKNOWN_NAME);
        $test_name = '... but the value is kept with its known phrases';
        $t->assert($test_name, $dto->val_lst->count(), count($sample[json_fields::VALUES]));
        $test_name = 'a result of an unknown formula is reported';
        $json = $sample;
        $json[json_fields::RESULTS] = [[
            json_fields::ID => self::RESULT_ID,
            json_fields::WORDS => [triple_names::EXAMPLE_PV_INSTALLATION],
            json_fields::FORMULA_NAME => self::UNKNOWN_NAME,
            json_fields::NUMBER => 1
        ]];
        $msg = new user_message();
        $this->filled($json, $msg);
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::IMPORT_TERM_NOT_FOUND));
        // the page loads no values of its own, if the answer has filled the cache completely
        $test_name = 'a complete page cache loads no values';
        $dto = $this->filled($sample, $msg);
        $dto->online = true;
        $dto->page_data_complete = true;
        $dto->load_values_of($dto->phr_lst, $msg);
        $t->assert_false($test_name, $dto->has_values_of($dto->phr_lst));
        // negative: the answer of a phrase without calculator triples gives no calculator
        $test_name = 'the answer of a phrase without calculator triples is no calculator';
        $json = [
            json_fields::SELECTION => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]],
            json_fields::WORDS => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]]
        ];
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $phr = $dto->phr_lst->get_by_name(self::NO_CALC_NAME, $msg);
        $t->assert_false($test_name, $phr == null or new calculator($phr, $dto->phr_lst)->is_calculator());
        $t->assert_msg($test_name, $msg);
        $test_name = 'an empty answer fills nothing';
        $msg = new user_message();
        $dto = $this->filled([], $msg);
        $t->assert($test_name, $dto->phr_lst->count(), 0);
        $t->assert_msg($test_name, $msg);

        $this->page_tests($t, $ts, $sample);
    }

    /**
     * the calculator page of the example PV installation created from the phraseData sample only:
     * the request cache is filled and marked complete like view_exe::add_calculator_data, so that
     * every label, number and source of the page comes from the json; the snapshot of the page is
     * written by the database read test (system_views_read_tests::run_pv_lifetime_balance), so this
     * test checks only the parts of the page
     *
     * @param test_cleanup $t the test environment
     * @param string $ts the name of the test section
     * @param array $sample the phraseData sample with the objects that have a database id
     */
    private function page_tests(test_cleanup $t, string $ts, array $sample): void
    {
        global $ui_sys;
        $page = new system_page();
        $list = new ui_list();

        // the browser builds the page from the json data, so the page has only the host, the data and the script
        $t->subheader($ts . 'calculator page');
        $test_name = 'the calculator page is created from the phrase data only';
        $msg = new user_message();
        $dto = $this->complete_page_cache($sample, $msg);
        $trp = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg)?->obj();
        $body = $page->calculator_json($trp, $msg, $dto, views::CALCULATOR, views::CALCULATOR);
        $html = new html_base();
        $t->assert_text_contains($test_name, $body, $html->script_data(system_page::CALC_EDITOR_SEED_ID, $sample));
        // the host is the use case page itself, so that the page has the width of the page of the server
        $test_name = '... into the empty host with the style of a use case page';
        $t->assert_text_contains($test_name, $body, $html->div('', view_styles::USE_CASE, system_page::CALC_PAGE_HOST_ID));
        $test_name = '... without a message';
        $t->assert_msg($test_name, $msg);
        $test_name = '... with the script that builds the page';
        $t->assert_text_contains($test_name, $body, $html->script_src(files::CALCULATOR_JSON_SCRIPT));
        // negative: without javascript for the calculator the page has no html of its own
        $test_name = 'a calculator page without javascript is empty';
        $cfg_keep = $ui_sys->cfg;
        $ui_sys->cfg = new config_ui(new test_values($t)->value_list_calculator_javascript_off()->api_json([api_types::INCL_PHRASES]));
        $no_js_body = $page->calculator_json($trp, $msg, $dto, views::CALCULATOR, views::CALCULATOR);
        $ui_sys->cfg = $cfg_keep;
        $t->assert($test_name, $no_js_body, '');
        // negative: a phrase without calculator triples has no calculator page, so the view shows its components
        $test_name = 'a phrase without calculator triples has no calculator page';
        $no_calc_json = [
            json_fields::SELECTION => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]],
            json_fields::WORDS => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]]
        ];
        $no_calc_dto = $this->complete_page_cache($no_calc_json, $msg);
        $no_calc_wrd = $no_calc_dto->phr_lst->get_by_name(self::NO_CALC_NAME, $msg)?->obj();
        $no_calc_body = $page->calculator_json($no_calc_wrd, $msg, $no_calc_dto, views::CALCULATOR, views::CALCULATOR);
        $t->assert($test_name, $no_calc_body, '');
        // negative: an online but complete page cache calls no api, also not for the results and
        // the sources of another phrase; every api call adds its wait (rest_call::api_call)
        $test_name = 'a complete page cache calls no api';
        $msg = new user_message();
        $dto = $this->complete_page_cache($sample, $msg);
        $dto->online = true;
        $other = $dto->phr_lst->get_by_name(triple_names::PV_INSTALLATION, $msg)?->obj();
        $wait_before = $ui_sys->api_wait;
        if ($other != null) {
            $list->calculator_numbers($other, $dto, $msg);
            $list->calculator_sources(new calculator($other->phrase(), $dto->phr_lst), $other, $msg, $dto);
        }
        $wait_after = $ui_sys->api_wait;
        $t->assert($test_name, $wait_after, $wait_before);
    }

    /**
     * @param array $sample the phraseData answer of the page
     * @param user_message $msg to collect the names that the answer does not contain
     * @return data_object an offline request cache filled from the answer and marked complete like view_exe::add_calculator_data
     */
    private function complete_page_cache(array $sample, user_message $msg): data_object
    {
        global $ui_sys;
        $dto = $this->filled($sample, $msg);
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto->page_data = $sample;
        $dto->page_data_complete = true;
        return $dto;
    }

    /**
     * the sample is the expected minimum of an answer and also lists objects that have no database
     * id yet e.g. a word added to the use case after the ids of the sample have been taken; an
     * answer of the api has an id for each object (see phrase_data_read_tests)
     *
     * @param array $sample the json of the sample
     * @return array the sample with the objects that have a database id
     */
    private function with_db_ids(array $sample): array
    {
        foreach (self::OBJECT_FIELDS as $fld) {
            $sample[$fld] = array_values(array_filter(
                $sample[$fld] ?? [], fn($obj) => array_key_exists(json_fields::ID, $obj)));
        }
        return $sample;
    }

    /**
     * @param array $json a phraseData answer
     * @param user_message $msg to collect the names that the answer does not contain
     * @return data_object a request cache without api access, filled from the answer
     */
    private function filled(array $json, user_message $msg): data_object
    {
        $dto = new data_object();
        $dto->online = false;
        new phrase_data()->fill($json, $dto, $msg);
        return $dto;
    }

}
