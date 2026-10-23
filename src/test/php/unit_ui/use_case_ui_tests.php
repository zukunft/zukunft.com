<?php

/*

    unit_ui/use_case_ui_tests.php - testing of the components of a use case page e.g. the PV lifetime balance
    -----------------------------


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

use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\calculator;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_calc;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source_list as source_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class use_case_ui_tests
{

    /**
     * the components of a use case page e.g. the PV lifetime balance: the view title, the section
     * titles, the notes, the collapsed blocks, the input fields and the sources of the page phrase
     */
    function run(test_cleanup $t): void
    {
        global $ui_sys;
        global $mtr;
        $html = new html_base();
        $t_cmp = new test_components($t);
        $t_msk = new test_views($t);
        $t_wrd = new test_words($t);
        $t_val = new test_values($t);
        $t_src = new test_sources($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html use case ';
        $t->header($ts);

        // the page phrase with its values and the request cache with the view of the page
        $wrd = $t_wrd->zh_ui();
        $msk = new view_ui($t_msk->view()->api_json());
        $dto = new data_object();
        $dto->online = false;
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto->val_lst = $t_val->value_list_zh_impact_ui();
        $dto->msk = $msk;

        $title_html = $this->render($t_cmp->component_view_title(), $wrd, $msk, $dto, $msg);
        $sub_title_html = $this->render($t_cmp->component_name_sub_title(), $wrd, $msk, $dto, $msg);
        $note_html = $this->render($t_cmp->component_description_text(), $wrd, $msk, $dto, $msg);
        $start_html = $this->render($t_cmp->component_details_start(), $wrd, $msk, $dto, $msg);
        $end_html = $this->render($t_cmp->component_details_end(), $wrd, $msk, $dto, $msg);
        $fields_html = $this->render($t_cmp->component_value_input_fields(), $wrd, $msk, $dto, $msg);
        $test_page = $html->text_h2('Use case page components');
        $test_page .= 'view title: ' . $title_html . '<br>';
        $test_page .= 'section title: ' . $sub_title_html . '<br>';
        $test_page .= 'note: ' . $note_html . '<br>';
        $test_page .= 'collapsed block: ' . $start_html . $note_html . $end_html . '<br>';
        $test_page .= 'input fields: ' . $fields_html . '<br>';
        $t->html_page_test($test_page, 'use case html components', 'use_case', $msg);

        $t->subheader($ts . 'view title');
        $test_name = 'the view title shows the name of the view of the page';
        $t->assert_text_contains($test_name, $title_html, $msk->name());
        // negative: a component without a page e.g. in a preview has no view to show
        $test_name = '... and nothing for a component without a view';
        $t->assert($test_name, $this->render($t_cmp->component_view_title(), $wrd, new view_ui(), $dto, $msg), '');

        $t->subheader($ts . 'section title and note');
        $test_name = 'the section title is the name of the component as a heading';
        $t->assert_text_contains($test_name, $sub_title_html, '>' . components::TEST_SUB_TITLE_NAME . '<');
        $test_name = 'the note is the description of the component as a paragraph';
        $t->assert_text_contains($test_name, $note_html, '<p>' . components::TEST_ADD_COM . '</p>');

        $t->subheader($ts . 'collapsed block');
        $test_name = 'the block starts with the name of the component as the summary line';
        $t->assert_text_contains($test_name, $start_html, '<summary>' . components::TEST_DETAILS_START_NAME . '</summary>');
        $test_name = '... and the end component closes it';
        $t->assert_text_contains($test_name, $end_html, '</details>');

        $t->subheader($ts . 'input fields');
        $test_name = 'each value of the page phrase is an input field named by its other phrase';
        $t->assert_text_contains($test_name, $fields_html, triple_names::COMPANY_ZURICH);
        $t->assert_text_contains($test_name, $fields_html, triple_names::CITY_ZH_NAME);
        $test_name = '... with the number as the field value';
        $t->assert_text_contains($test_name, $fields_html, 'value="');
        // negative: a page without values e.g. a new word has no input to show
        $test_name = 'a page phrase without values shows no field';
        $dto_empty = new data_object();
        $dto_empty->online = false;
        $dto_empty->typ_lst_cache = $ui_sys->typ_lst_cache;
        $t->assert($test_name, $this->render($t_cmp->component_value_input_fields(), $wrd, $msk, $dto_empty, $msg), '');

        $t->subheader($ts . 'sources');
        // negative first: the values of the test word have no source, so the list is empty
        $test_name = 'values without a source show no source list';
        $t->assert($test_name, $this->render($t_cmp->component_sources_used(), $wrd, $msk, $dto, $msg), '');
        $test_name = 'the source of a value is listed with a link to its url';
        $dto->val_lst->lst()[0]->src = new source_ui($t_src->source_reserved()->api_json());
        $sources_html = $this->render($t_cmp->component_sources_used(), $wrd, $msk, $dto, $msg);
        $t->assert_text_contains($test_name, $sources_html, sources::SIB);
        $t->assert_text_contains($test_name, $sources_html, sources::SIB_URL);

        $t->subheader($ts . 'page calculation');
        // the page repeats the calculation of a result with its own values and shows that number,
        // so a stale stored result is reported and never shown silently (docs/llm/frontend.md
        // "The frontend calculates, a save job verifies"); the formula sums the two Zurich values
        $t_res = new test_results($t);
        $frm = new formula_ui();
        $frm->set_id(formula_names::CITY_POPULATION_ID);
        $frm->set_usr_text('"total" = "' . triple_names::CITY_ZH_NAME . '" + "' . triple_names::COMPANY_ZURICH . '"');
        $frm_lst = new formula_list_ui();
        $frm_lst->add($frm, $msg);
        $dto->set_formula_list($frm_lst);
        $sum = values::SAMPLE_FLOAT + values::SAMPLE_FLOAT;
        $res_stale = $t_res->result_zh_sum_ui(values::SAMPLE_INT);
        $res_lst = new result_list_ui();
        $res_lst->add($res_stale, $msg);
        $dto->set_result_list($res_lst);
        $msg_stale = new user_message();
        $this->render($t_cmp->component_table(), $wrd, $msk, $dto, $msg_stale);
        $test_name = 'a stored result that differs from the calculation with the page values is reported';
        $t->assert_text_contains($test_name, $msg_stale->var_message_text(), (string)values::SAMPLE_INT);
        $test_name = '... and the table shows the calculated number instead';
        $t->assert($test_name, round($res_stale->number(), 2), round($sum, 2));
        // negative: a stored result that matches the calculation is shown without a message
        $test_name = 'a stored result that matches the calculation is shown as it is';
        $res_lst = new result_list_ui();
        $res_lst->add($t_res->result_zh_sum_ui($sum), $msg);
        $dto->set_result_list($res_lst);
        $msg_same = new user_message();
        $this->render($t_cmp->component_table(), $wrd, $msk, $dto, $msg_same);
        $t->assert_true($test_name, $msg_same->is_ok());
        // negative: an operand value with a phrase outside the context of the result is not used,
        // e.g. the gross avoided emission of another mix for the net avoided emission of a mix
        $test_name = 'a value with a phrase outside the context of the result is no operand';
        $frm_be = new formula_ui();
        $frm_be->set_id(formula_names::CITY_POPULATION_ID);
        $frm_be->set_usr_text('"total" = "' . word_names::BE . '"');
        $frm_be_lst = new formula_list_ui();
        $frm_be_lst->add($frm_be, $msg);
        $num_be = new formula_calc()->result($t_res->result_zh_sum_ui($sum), $t_val->value_list_assumptions_ui(), $frm_be_lst);
        $t->assert_null($test_name, $num_be);
        // a calculator page repeats the calculation in the browser, so the expression is given as
        // tokens with the html id of each operand instead of the formula text, which the browser
        // never runs as code
        $test_name = 'the calculation of a result is given to the browser with the html ids of its operands';
        $tokens = new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_lst);
        $t->assert($test_name, $tokens, [
            [formula_calc::TOKEN_REF => $dto->val_lst->get_by_names([triple_names::CITY_ZH_NAME])->html_id()],
            [formula_calc::TOKEN_OP => '+'],
            [formula_calc::TOKEN_REF => $dto->val_lst->get_by_names([triple_names::COMPANY_ZURICH])->html_id()]]);
        // negative: without a value for an operand the browser cannot repeat the calculation
        $test_name = '... but no calculation if an operand has no value on the page';
        $t->assert_null($test_name, new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $t_val->value_list_assumptions_ui(), $frm_be_lst));
        // negative: a char outside the arithmetic of the page is never given to the browser
        $test_name = '... and no calculation for an expression with other chars than numbers, + - * / and brackets';
        $frm_fn = new formula_ui();
        $frm_fn->set_id(formula_names::CITY_POPULATION_ID);
        $frm_fn->set_usr_text('"total" = max("' . triple_names::CITY_ZH_NAME . '")');
        $frm_fn_lst = new formula_list_ui();
        $frm_fn_lst->add($frm_fn, $msg);
        $t->assert_null($test_name, new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_fn_lst));

        $t->subheader($ts . 'input fields');
        // a phrase of the values of the page that is assigned a field type is an input of the page:
        // the inhabitants are a number field with the limit and the step of the field values and the
        // number of the page, and the city a phrase selector of the cities with a value, with Bern
        // selected because the page phrase Zurich uses it (process_catch_fields.json)
        $page = new system_page();
        $t_phr = new test_phrases($t);
        $dto_in = new data_object();
        $dto_in->online = false;
        $dto_in->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_in->phr_lst = $t_phr->list_input_fields_ui();
        $dto_in->val_lst = $t_val->value_list_input_fields_ui();
        $form = 'refresh';
        $num_name = url_var::NUMERIC_VALUE . word_names::INHABITANT_ID;
        $sel_name = url_var::PHRASE . word_names::CITY_ID;
        $fields = $page->process_catch_fields(components::TEST_INPUT_FIELDS_NAME, null, [], $wrd, $dto_in, $form, $msg);
        $test_name = 'a number field shows the number of the page with the limit and the step of the field';
        $t->assert_text_contains($test_name, $fields, 'name="' . $num_name . '" id="' . $num_name
            . '" value="' . values::CITY_ZH_INHABITANTS_2019 . '" min="0" step="100"');
        $test_name = '... labelled by the field phrase';
        $t->assert_text_contains($test_name, $fields, '>' . word_names::INHABITANTS . '</label>');
        $test_name = '... and joined to the refresh form';
        $t->assert_text_contains($test_name, $fields, 'form="' . $form . '"');
        $test_name = 'a phrase selector offers the phrases of the values of the field phrase with the one selected that the page phrase uses';
        $t->assert_text_contains($test_name, $fields, 'name="' . $sel_name . '"');
        $t->assert_text_contains($test_name, $fields, 'value="' . word_names::BE_ID . '"  selected >' . word_names::BE . '</option>');
        $t->assert_text_contains($test_name, $fields, 'value="' . word_names::ZH_ID . '"  >' . word_names::ZH . '</option>');
        // negative: the percent of the selector values is a unit and no option
        $test_name = 'a percent phrase of the selector values is not offered as an option';
        $t->assert_text_not_contains($test_name, $fields, 'value="' . words::PCT_ID . '"');
        // a share is written into a field with the percent decimals of the user e.g. 1.00 and 0.90,
        // so all options of a selector of shares have the same number of decimals
        $test_name = 'the field value of a share has the percent decimals of the user';
        $list = new ui_list();
        $pct_val = $dto_in->val_lst->get_by_names([word_names::CITY, word_names::BE]);
        $t->assert($test_name, $html->number_value($pct_val->number(), $list->field_decimals($pct_val, $msg)), '1.00');
        // negative: the decimals are a minimum, because the field number is the input of the
        // calculation and two rounded options could even get the same value
        $test_name = '... but a share with more decimals is not rounded';
        $t->assert($test_name, $html->number_value(values::SAMPLE_FLOAT, $list->field_decimals($pct_val, $msg)),
            (string)values::SAMPLE_FLOAT);
        // negative: another number is written as it is
        $test_name = '... and another number without added decimals';
        $inh_val = $dto_in->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS]);
        $t->assert($test_name, $html->number_value($inh_val->number(), $list->field_decimals($inh_val, $msg)),
            (string)values::CITY_ZH_INHABITANTS_2019);
        // a unit is shown by its symbol e.g. "kWh/a" for kilowatthours per year like behind a
        // number, so also the symbol "€" of Euro for the unit EUR that stands for Euro
        $test_name = 'a field shows the symbol of its unit';
        $unit_val = new test_lib()->ui_value($t_val->value_mio_eur());
        $phr_sys_keep = $ui_sys->typ_lst_cache->phr_sys;
        $phr_lst_keep = $ui_sys->phr_lst;
        $ui_sys->phr_lst = $t_phr->list_unit_symbol_cache_ui();
        $t->assert($test_name, $list->unit_name($unit_val, $msg), word_names::EURO_SIGN);
        // the null safe calls report a missing unit phrase as a failed assert and not as a fatal
        $test_name = '... because the unit EUR is a symbol of Euro';
        $eur_phr = $unit_val->phr_lst()->get_by_name(word_names::EUR, $msg);
        $t->assert($test_name, $eur_phr?->symbol_owner()->name(), word_names::EURO);
        $test_name = '... whose symbol is the unit text';
        $t->assert($test_name, $eur_phr?->unit_text($msg), word_names::EURO_SIGN);
        // negative: without the symbol triple in any cache the unit is named
        $test_name = '... and the name of a unit without a symbol';
        $ui_sys->typ_lst_cache->phr_sys = new phrase_list_ui();
        $ui_sys->phr_lst = new phrase_list_ui();
        $t->assert($test_name, $list->unit_name($unit_val, $msg), word_names::EUR);
        // negative: without the triple the unit is no symbol of another phrase
        $test_name = '... which is its own symbol owner';
        $t->assert($test_name, $eur_phr?->symbol_owner()->name(), word_names::EUR);
        $test_name = '... and its name is the unit text';
        $t->assert($test_name, $eur_phr?->unit_text($msg), word_names::EUR);
        // negative: a field shows the stored ratio e.g. 0.35, so "%" would read as 0.35 %
        $test_name = '... but the name and not the symbol of percent';
        $ui_sys->phr_lst = $t_phr->list_percent_symbol_cache_ui();
        $t->assert($test_name, $list->unit_name($pct_val, $msg), words::PCT);
        // the symbol of the percent word is shown behind the number times 100 instead
        $test_name = '... which is the percent symbol of the number';
        $t->assert($test_name, $pct_val->percent_symbol($msg), word_names::PERCENT_SYMBOL);
        // a qualified percent unit has a symbol that names the stored ratio
        $test_name = '... and the symbol of a qualified percent unit e.g. share (ratio)';
        $ui_sys->phr_lst = $t_phr->list_share_symbol_cache_ui();
        $share_val = new test_lib()->ui_value($t_val->value_share());
        $t->assert($test_name, $list->unit_name($share_val, $msg), word_names::SHARE);
        // negative: the symbol of the ratio is not written behind the number times 100 e.g. "35share"
        $test_name = '... which is not the percent symbol of the number';
        $t->assert($test_name, $share_val->percent_symbol($msg), '');
        $test_name = 'the percent triple share (ratio) is a ratio unit';
        $t->assert($test_name, $share_val->phr_lst()->percent_phrase($msg)?->is_ratio_unit($msg), true);
        // negative: the percent word is no ratio unit, because its symbol "%" is for the number times 100
        $test_name = '... but not the percent word';
        $t->assert($test_name, $pct_val->phr_lst()->percent_phrase($msg)?->is_ratio_unit($msg), false);
        $test_name = 'an entered number and phrase of the url are shown again';
        // the caches of the page again after the symbol tests above
        $ui_sys->typ_lst_cache->phr_sys = $phr_sys_keep;
        $ui_sys->phr_lst = $phr_lst_keep;
        $entered = [$num_name => 5, $sel_name => word_names::ZH_ID];
        $fields_url = $page->process_catch_fields(components::TEST_INPUT_FIELDS_NAME, null, $entered, $wrd, $dto_in, $form, $msg);
        $t->assert_text_contains($test_name, $fields_url, 'value="5"');
        $t->assert_text_contains($test_name, $fields_url, 'value="' . word_names::ZH_ID . '"  selected >');
        // a field phrase in several values, e.g. the annual and the monthly household consumption of
        // the PV page: the field shows the value with the field value phrase 2019 in any value order
        $dto_vp = new data_object();
        $dto_vp->online = false;
        $dto_vp->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_vp->phr_lst = $t_phr->list_input_field_value_phrases_ui();
        foreach ([false, true] as $year_2020_first) {
            $dto_vp->val_lst = $t_val->value_list_input_field_value_phrases_ui($year_2020_first);
            $fields_vp = $page->process_catch_fields(components::TEST_INPUT_FIELDS_NAME, null, [], $wrd, $dto_vp, $form, $msg);
            $test_name = 'a number field shows the value with the field value phrase';
            $t->assert_text_contains($test_name, $fields_vp, 'id="' . $num_name . '" value="' . values::CITY_ZH_INHABITANTS_2019 . '"');
            // negative: the value of the other year with the same number of phrases is not shown
            $test_name = '... and not the value without the field value phrase';
            $t->assert_text_not_contains($test_name, $fields_vp, 'value="' . values::SAMPLE_INT . '"');
        }
        // negative: a page whose phrase has no field says so with the name of the component
        $test_name = 'a page without a field says so';
        $t->assert_text_contains($test_name,
            $page->process_catch_fields(components::TEST_INPUT_FIELDS_NAME, null, [], $wrd, $dto, $form, $msg),
            components::TEST_INPUT_FIELDS_NAME);

        $t->subheader($ts . 'table of the row phrase');
        // a table component with a row phrase shows the values of that phrase instead of the page
        // phrase, e.g. the monthly mix on the PV balance page: the city table on the Zurich page
        // lists the cities with a value and not the inhabitants of Zurich
        $cmp_city = new component_ui($t_cmp->component_table_city()->api_json());
        $tbl_city = $cmp_city->dsp_entries($wrd, $msg, '', 0, $dto_in, null, '', true);
        $test_name = 'the table of the row phrase lists the values of that phrase';
        $t->assert_text_contains($test_name, $tbl_city, '>' . word_names::BE . '</a>');
        $test_name = '... and not the values of the page phrase';
        $t->assert_text_not_contains($test_name, $tbl_city, word_names::INHABITANTS);
        // a table component with own columns, e.g. lifetime and annual of the PV balance page,
        // shows them first without a system column tier that would change every other table
        $cmp_own = new component_ui($t_cmp->component_table_own_columns()->api_json());
        $tbl_own = $cmp_own->dsp_entries($wrd, $msg, '', 0, $dto_in, null, '', true);
        $test_name = 'the own column of a table component is its first column';
        $head_own = library::str_right_of($tbl_own, '<th></th><th>');
        $t->assert_text_contains($test_name, library::str_left_of($head_own, '</th>'), '>' . word_names::INHABITANTS . '</a>');

        $t->subheader($ts . 'assumptions');
        // the values that the result of the page uses without the page phrase are its assumptions:
        // the inhabitants have no source, so they are assumed, and Bern has a source, so it is
        // measured with the source as the hint; the value of the page itself is no assumption
        $frm_scaled = new formula_ui();
        $frm_scaled->set_id(formula_names::JOULE_DEF_ID);
        $frm_scaled->set_name(formula_names::JOULE_DEF);
        $frm_scaled->set_usr_text('"scaled" = "' . triple_names::CITY_ZH_NAME . '" * "' . word_names::INHABITANTS
            . '" + "' . word_names::BE . '"');
        $frm_lst = new formula_list_ui();
        $frm_lst->add($frm_scaled, $msg);
        $dto_as = new data_object();
        $dto_as->online = false;
        $dto_as->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_as->val_lst = $t_val->value_list_assumptions_ui();
        // the value api message carries only the source id, so the source name is taken from the source cache
        $src_lst = new source_list_ui();
        $src_lst->add(new source_ui($t_src->source_reserved()->api_json()), $msg);
        $dto_as->src_lst = $src_lst;
        $dto_as->set_formula_list($frm_lst);
        $res_lst = new result_list_ui();
        $res_lst->add($t_res->result_zh_scaled_ui(values::SAMPLE_FLOAT * values::SAMPLE_INT + values::SAMPLE_FLOAT), $msg);
        $dto_as->set_result_list($res_lst);
        $cmp_as = new component_ui($t_cmp->component_assumption_fields()->api_json());
        $assumptions = $cmp_as->dsp_entries($wrd, $msg, $form, $msk->id(), $dto_as, null, '', true);
        $test_name = 'a value without a source that the result uses is an assumed assumption';
        $t->assert_text_contains($test_name, $assumptions, word_names::INHABITANTS . ' <span class="' . styles::INPUT_TAG . '">'
            . $mtr->txt(msg_id::FIELD_TAG_ASSUMED) . '</span></label>');
        $t->assert_text_contains($test_name, $assumptions, 'value="' . values::SAMPLE_INT . '"');
        $test_name = 'a value with a source is a measured assumption with the source as the hint';
        $t->assert_text_contains($test_name, $assumptions, word_names::BE . ' <span class="' . styles::INPUT_TAG . '">'
            . $mtr->txt(msg_id::FIELD_TAG_MEASURED) . '</span></label>');
        $t->assert_text_contains($test_name, $assumptions, '<span class="' . styles::INPUT_HINT . '">' . sources::SIB . '</span>');
        $test_name = '... and the fields join the refresh form';
        $t->assert_text_contains($test_name, $assumptions, 'form="' . $form . '"');
        // negative: the value of the page phrase is an input, not an assumption
        $test_name = 'the value of the page phrase is no assumption';
        $t->assert_text_not_contains($test_name, $assumptions, triple_names::CITY_ZH_NAME);

        $t->subheader($ts . 'sources and notes of the calculation');
        // the sources of the page are the sources of the values that its results use as well, named
        // by the source cache of the page when a value carries only the source id
        foreach ($dto_as->val_lst->lst() as $val) {
            if ($val->src != null) {
                $val->src = new source_ui();
                $val->src->set_id(sources::SIB_ID);
            }
        }
        $sources_html = $this->render($t_cmp->component_sources_used(), $wrd, $msk, $dto_as, $msg);
        $test_name = 'the source of a value that a result of the page uses is listed by the name of the source cache';
        $t->assert_text_contains($test_name, $sources_html, '>' . sources::SIB . '</a>');
        // the sources that only the results use are numbered in the order of the results, which the
        // backend returns in any order, so the results are taken in the preferred order: the bigger
        // impact first, then within the same unit the bigger number, then by name, which unlike the
        // group id does not change with a new database (docs/llm/frontend.md)
        $res_sum = $t_res->result_zh_sum_ui(values::SAMPLE_FLOAT);
        $res_scaled = $t_res->result_zh_scaled_ui(values::SAMPLE_FLOAT);
        $res_lst_a = new result_list_ui();
        $res_lst_a->add($res_sum, $msg);
        $res_lst_a->add($res_scaled, $msg);
        $res_lst_b = new result_list_ui();
        $res_lst_b->add($res_scaled, $msg);
        $res_lst_b->add($res_sum, $msg);
        $names_a = array_map(fn($res) => $res->name(), $res_lst_a->sorted_by_impact($msg));
        $names_b = array_map(fn($res) => $res->name(), $res_lst_b->sorted_by_impact($msg));
        $test_name = 'the results of the page are taken in the same order whatever order the backend returns';
        $t->assert($test_name, $names_a, $names_b);
        // with the same impact and the same number Zurich city comes before Zurich inhabitants by the name
        $test_name = '... which is the order of the name if impact and number are the same';
        $t->assert($test_name, $names_a, [$res_scaled->name(), $res_sum->name()]);
        $test_name = '... but the bigger number first if the unit is the same';
        $res_big = $t_res->result_zh_sum_ui(values::SAMPLE_INT);
        $res_lst_c = new result_list_ui();
        $res_lst_c->add($res_scaled, $msg);
        $res_lst_c->add($res_big, $msg);
        $t->assert($test_name, array_map(fn($res) => $res->name(), $res_lst_c->sorted_by_impact($msg)),
            [$res_big->name(), $res_scaled->name()]);
        $test_name = '... and the bigger impact before the bigger number';
        $res_scaled->grp->phr_lst()->get_by_name(word_names::CITY, $msg)->obj()->impact = impacts::MAX;
        $t->assert($test_name, array_map(fn($res) => $res->name(), $res_lst_c->sorted_by_impact($msg)),
            [$res_scaled->name(), $res_big->name()]);
        // the notes of the page are the descriptions of the formulas of its results
        $frm_scaled->set_description(formula_names::JOULE_DEF_COM);
        $notes_html = $this->render($t_cmp->component_formula_notes(), $wrd, $msk, $dto_as, $msg);
        $test_name = 'the description of a formula of the page is a note with the formula name in front';
        $t->assert_text_contains($test_name, $notes_html,
            '<b>' . formula_names::JOULE_DEF . '</b> ' . formula_names::JOULE_DEF_COM . '</p>');
        // negative: a formula without a description adds no note
        $test_name = 'a formula without a description adds no note';
        $frm_scaled->set_description('');
        $t->assert($test_name, $this->render($t_cmp->component_formula_notes(), $wrd, $msk, $dto_as, $msg), '');

        $t->subheader($ts . 'calculator');
        // a phrase without calculator triples shows its name as the title (rule 6 of
        // docs/llm/new_view_sample_pv_balance.md), so every phrase can be shown as a calculator
        $test_name = 'the calculator title of a phrase without a calculator label is the phrase name';
        // the title is a h4, which the use case style of style_html.css formats
        $t->assert($test_name, $this->render($t_cmp->component_calculator_title(), $wrd, $msk, $dto, $msg),
            $html->block(html_base::H4, $wrd->name()));
        // negative: a component without a page phrase e.g. in a preview has no title to show
        $test_name = '... and nothing for a component without a phrase';
        $t->assert($test_name, $this->render($t_cmp->component_calculator_title(), new word_ui(), $msk, $dto, $msg), '');
        // the inputs and the results are dispatched, so they never report missing program code
        $test_name = 'the calculator input is a known component type';
        $t->assert($test_name, $this->render($t_cmp->component_calculator_input(), $wrd, $msk, $dto, $msg), '');
        $test_name = 'the calculation result is a known component type';
        $t->assert($test_name, $this->render($t_cmp->component_calculation_result(), $wrd, $msk, $dto, $msg), '');

        $t->subheader($ts . 'calculator reader');
        // the page phrase Zurich (city) has no own calculator triples, so it gets the calculator
        // of Zurich, the phrase it is built from (test_triples::calculator_zh)
        $cac = $t_phr->list_calculator_ui();
        $calc = new calculator($cac->get_by_name(triple_names::CITY_ZH_NAME, $msg), $cac);
        $test_name = 'a page phrase built from a calculator phrase is a calculator';
        $t->assert_true($test_name, $calc->is_calculator());
        $test_name = '... with the label of the phrase it is built from as the title';
        $t->assert($test_name, $calc->title(), triple_names::CALC_ZH_LABEL_COM);
        $test_name = '... and its hint as the lead text';
        $t->assert($test_name, $calc->lead(), triple_names::CALC_ZH_HINT_COM);
        $inputs = $calc->items(triples::CALCULATOR_INPUT);
        $test_name = 'the items follow the order chain and an item without order triple comes after it';
        $t->assert($test_name, array_map(fn($item) => $item->name(), $inputs), [
            triple_names::CALC_ZH_INPUT_INHABITANTS, triple_names::CALC_ZH_INPUT_CANTON, triple_names::CALC_ZH_INPUT_YEAR]);
        $test_name = 'an item without a label shows the label of its phrase';
        $t->assert($test_name, $calc->label($inputs[0]), triple_names::CALC_INHABITANTS_LABEL_COM);
        $test_name = 'the label of the item wins over the label of its phrase';
        $t->assert($test_name, $calc->label($inputs[1]), triple_names::CALC_ZH_INPUT_CANTON_LABEL_COM);
        $test_name = 'an item and a phrase without a label show the phrase name';
        $t->assert($test_name, $calc->label($inputs[2]), word_names::YEAR_2019);
        $test_name = 'an item without a hint shows the hint of its phrase';
        $t->assert($test_name, $calc->hint($inputs[0]), triple_names::CALC_INHABITANTS_HINT_COM);
        $test_name = '... and nothing if its phrase has none';
        $t->assert($test_name, $calc->hint($inputs[1]), '');
        $grp = $calc->items(triples::CALCULATOR_ROW_GROUP)[0];
        $rows = $calc->sub_items($grp, verbs::HAS_ID);
        $test_name = 'the rows of a row group follow the order chain';
        $t->assert($test_name, array_map(fn($item) => $item->name(), $rows), [
            triple_names::CALC_ZH_ROW_BE, triple_names::CALC_ZH_ROW_INHABITANTS]);
        // negative: the row group has no filter, so it has no sub-items with the filter verb
        $test_name = 'a row group has no filter sub-items';
        $grp_flt = $calc->sub_items($grp, verbs::WITH_ID);
        $t->assert($test_name, $grp_flt, []);
        $test_name = 'a row built from the group phrase shows the other phrase';
        $t->assert($test_name, $calc->label($rows[0], $grp), word_names::BE);
        $test_name = 'a row shows the label of its phrase and never the label of its filter phrase';
        $t->assert($test_name, $calc->label($rows[1], $grp), triple_names::CALC_INHABITANTS_LABEL_COM);
        $test_name = 'a row has the highlight of its filter phrase';
        $t->assert_true($test_name, $calc->has_flag($rows[1], triples::CALCULATOR_HIGHLIGHT));
        $test_name = '... and a row without such a phrase has none';
        $t->assert_false($test_name, $calc->has_flag($rows[0], triples::CALCULATOR_HIGHLIGHT));
        $test_name = 'a row has the side results of its filter phrase without the calculator properties';
        $t->assert($test_name, array_map(fn($phr) => $phr->name(), $calc->side_results($rows[1])), [word_names::YEAR_2020]);
        $test_name = '... and a row without such a phrase has none';
        $t->assert($test_name, $calc->side_results($rows[0]), []);
        // the view fills the request cache with the general triples of these phrases (view_exe::add_page_cache)
        $test_name = 'the phrases of the general triples are the item, row and filter phrases and the other phrase of a row, each once';
        $t->assert($test_name, array_map(fn($phr) => $phr->name(), $calc->phrases()), [
            word_names::INHABITANTS, word_names::CANTON, word_names::YEAR_2019, word_names::CITY,
            triple_names::CITY_BE, word_names::BE]);
        // negative: a phrase without calculator triples is no calculator and shows its name
        $calc_be = new calculator($cac->get_by_name(triple_names::CITY_BE, $msg), $cac);
        $test_name = 'a phrase without calculator triples is no calculator';
        $t->assert_false($test_name, $calc_be->is_calculator());
        $test_name = '... with its name as the title';
        $t->assert($test_name, $calc_be->title(), triple_names::CITY_BE);
        $test_name = '... and no lead text';
        $t->assert($test_name, $calc_be->lead(), '');
        $test_name = 'the calculator title shows the title and the lead text of the reader';
        $dto_calc = new data_object();
        $dto_calc->online = false;
        $dto_calc->phr_lst = $cac;
        $t->assert($test_name, $page->calculator_title(new test_triples($t)->zh_city_ui(), $dto_calc),
            $html->block(html_base::H4, triple_names::CALC_ZH_LABEL_COM) . $html->p(triple_names::CALC_ZH_HINT_COM));
        // negative: a page without a phrase has no calculator title
        $test_name = '... and a page without a phrase has no calculator title';
        $t->assert($test_name, $page->calculator_title(null, $dto_calc), '');

        $t->subheader($ts . 'calculator input');
        // the inputs of Zurich (city) are the inputs of Zurich, the phrase it is built from: the
        // inhabitants as a number field, the canton as a phrase selector and 2019 without a field type
        $dto_in = new data_object();
        $dto_in->online = false;
        $dto_in->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_in->phr_lst = $t_phr->list_calculator_input_ui();
        $dto_in->val_lst = $t_val->value_list_calculator_input_ui();
        $zh_city = new test_triples($t)->zh_city_ui();
        $form = views::CALCULATOR;
        $in_html = $page->calculator_input($zh_city, $msg, $dto_in, $form, views::CALCULATOR, []);
        $test_name = 'the calculator input starts with the default heading in the inputs frame';
        $t->assert_text_contains($test_name, $in_html, '<div class="' . styles::INPUTS . '">'
            . $html->block(html_base::H2, $mtr->txt(msg_id::CALCULATOR_INPUTS)));
        $inh_id = $dto_in->phr_lst->get_by_name(word_names::INHABITANTS, $msg)->id();
        $inh_name = url_var::NUMERIC_VALUE . $inh_id;
        // the save writes a changed number to the value that the field shows, so the field sends
        // the group id and the shown number of the value and the label to name the saved number
        $inh_val_id = (string)$dto_in->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS])->id();
        $inh_shown = $html->number_value(values::CITY_ZH_INHABITANTS_2019);
        $test_name = 'a number field shows the label, the number, the limit, the step and the hint of the input';
        $t->assert_text_contains($test_name, $in_html, $html->div(
            $html->label(triple_names::CALC_INHABITANTS_LABEL_COM, $inh_name)
            . $html->div($html->input_number($inh_name, values::CITY_ZH_INHABITANTS_2019, 0, null, 100, $form)
                . $html->form_hidden(url_var::GROUP . $inh_id, $inh_val_id, $form)
                . $html->form_hidden(url_var::PRE . url_var::NUMERIC_VALUE . $inh_id, $inh_shown, $form)
                . $html->form_hidden(url_var::NAME . $inh_id, triple_names::CALC_INHABITANTS_LABEL_COM, $form), styles::INPUT_ROW)
            . $html->span(triple_names::CALC_INHABITANTS_HINT_COM, styles::INPUT_HINT),
            styles::INPUT_FIELD));
        // negative: a phrase selector selects a phrase and not a value, so it sends no group id
        $test_name = '... but a phrase selector sends no group id';
        $canton_id = $dto_in->phr_lst->get_by_name(word_names::CANTON, $msg)->id();
        $t->assert_text_not_contains($test_name, $in_html, 'name="' . url_var::GROUP . $canton_id . '"');
        // two values that fit a field equally e.g. the inhabitants of Zurich and of Bern are picked
        // by the preferred order of numbers and not by the order of the list, which the backend
        // returns in any order; here the bigger number of the same unit
        $dto_tie = clone $dto_in;
        $dto_tie->val_lst = $t_val->value_list_calculator_input_tie_ui();
        $tie_html = $page->calculator_input($zh_city, $msg, $dto_tie, $form, views::CALCULATOR, []);
        $dto_tie->val_lst->set_lst(array_reverse($dto_tie->val_lst->lst()));
        $tie_rev_html = $page->calculator_input($zh_city, $msg, $dto_tie, $form, views::CALCULATOR, []);
        $test_name = 'a field shows the same value whatever order the values have';
        $t->assert($test_name, $tie_rev_html, $tie_html);
        $test_name = '... which is the value first in the preferred order of numbers';
        $t->assert_text_contains($test_name, $tie_html, 'value="' . values::CITY_ZH_INHABITANTS_2019 . '"');
        $test_name = 'the inputs follow the order of the calculator';
        $t->assert_text_order($test_name, $in_html, triple_names::CALC_INHABITANTS_LABEL_COM, triple_names::CALC_ZH_INPUT_CANTON_LABEL_COM);
        $t->assert_text_order($test_name, $in_html, triple_names::CALC_ZH_INPUT_CANTON_LABEL_COM, '>' . word_names::YEAR_2019 . '<');
        $test_name = 'a phrase selector offers the options of its phrase with their number';
        $t->assert_text_contains($test_name, $in_html, 'value="' . values::SAMPLE_FLOAT . '"');
        $test_name = '... and selects the option that the page phrase uses';
        $t->assert_text_contains($test_name, $in_html, ' selected >' . word_names::YEAR_2020 . '</option>');
        // a result row of a selector phrase e.g. replacement mix shows the numbers of the used option
        $calc_in = new calculator($dto_in->phr_lst->get_by_name(triple_names::CITY_ZH_NAME, $msg), $dto_in->phr_lst);
        $test_name = 'a selector phrase stands for the option that the page phrase uses';
        $t->assert($test_name, $calc_in->chosen($dto_in->phr_lst->get_by_name(word_names::CANTON, $msg))?->name(), word_names::YEAR_2020);
        // negative: a phrase that is no selector or whose options the page phrase does not use stands for itself
        $test_name = '... and a phrase without a used option stands for itself';
        $t->assert($test_name, $calc_in->chosen($dto_in->phr_lst->get_by_name(word_names::INHABITANTS, $msg))?->name(), word_names::INHABITANTS);
        // the field gives the selector its layout, so the selector follows its label directly
        $test_name = '... directly behind its label';
        $t->assert_text_contains($test_name, $in_html, '</label><select class="form-control" name="'
            . url_var::NUMERIC_VALUE . $canton_id . '"');
        // negative: the bootstrap form group around a selector is not used in a calculator field
        $test_name = '... without a form group around it';
        $t->assert_text_not_contains($test_name, $in_html, 'form-group');
        // the save changes data, so it is a post form whose named submit runs the save action
        $test_name = 'the save button posts the inputs to the save of the calculator page';
        $t->assert_text_contains($test_name, $in_html, 'method="' . html_base::METHOD_POST . '" id="' . $form . '"');
        $t->assert_text_contains($test_name, $in_html, $html->form_hidden(url_var::MASK, views::CALCULATOR));
        $t->assert_text_contains($test_name, $in_html, $html->form_hidden(url_var::ACTION, url_var::ACTION_SAVE));
        $t->assert_text_contains($test_name, $in_html, 'name="' . url_var::POST_SUBMIT . '">'
            . $mtr->txt(msg_id::CALCULATOR_SAVE) . '</button>');
        $test_name = 'the assumptions follow the inputs in a collapsed block with the default heading';
        $t->assert_text_order($test_name, $in_html, '</button>',
            '<summary>' . $mtr->txt(msg_id::CALCULATOR_ASSUMPTIONS) . '</summary>');
        $test_name = '... and an assumption without a source is tagged as assumed';
        $t->assert_text_contains($test_name, $in_html,
            $html->span($mtr->txt(msg_id::FIELD_TAG_ASSUMED), styles::INPUT_TAG_ASSUMED));
        // negative: a phrase without calculator triples has no inputs
        $test_name = 'a phrase without calculator triples shows no calculator input';
        $t->assert($test_name, $page->calculator_input(new test_triples($t)->triple_bern_ui(), $msg, $dto_in, $form, views::CALCULATOR, []), '');

        $this->calculator_save_tests($t, $ts);

        $t->subheader($ts . 'calculation result');
        // the page phrase Zurich has the result column 2019, the signed row group city with the rows
        // Bern (city) and inhabitants filtered by canton, and the detail table city
        $dto_res = new data_object();
        $dto_res->online = false;
        $dto_res->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_res->phr_lst = $t_phr->list_calculator_result_ui();
        $dto_res->val_lst = $t_val->value_list_calculator_result_ui();
        $src = new source_ui($t_src->source_reserved()->api_json());
        $dto_res->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS])->src = $src;
        $res_html = $page->calculation_result($wrd, $msg, $dto_res);
        $test_name = 'the result starts with the label of the result columns as the heading';
        $t->assert_text_contains($test_name, $res_html, $html->block(html_base::H2, triple_names::CALC_ZH_COLUMNS_LABEL_COM)
            . '<div class="' . styles::CALC_SCROLL . '"><' . html_base::TABLE . '>');
        $test_name = '... and the header with the default first header and the label of each column';
        $t->assert_text_contains($test_name, $res_html, $html->block(html_base::TR,
            $html->block(html_base::TH, $mtr->txt(msg_id::CALCULATOR_RESULT))
            . $html->block(html_base::TH, word_names::YEAR_2019, styles::CALC_NUMBER)));
        $test_name = 'a row group is a section row over all columns';
        $t->assert_text_contains($test_name, $res_html, $html->block(html_base::TR,
            '<' . html_base::TD . ' colspan="2">City</' . html_base::TD . '>', styles::CALC_SECTION));
        $test_name = 'a row built from the group phrase shows the other phrase and an empty cell without a number';
        $t->assert_text_contains($test_name, $res_html, $html->block(html_base::TR,
            $html->block(html_base::TD, word_names::BE) . $html->block(html_base::TD, '', styles::CALC_NUMBER)));
        $test_name = 'a highlighted row is a key row with the hint as sub text';
        $t->assert_text_contains($test_name, $res_html, '<' . html_base::TR . ' class="' . styles::CALC_KEY . '">'
            . $html->block(html_base::TD, triple_names::CALC_INHABITANTS_LABEL_COM
                . $html->span(triple_names::CALC_INHABITANTS_HINT_COM, styles::CALC_SUB)));
        $test_name = '... and the number of a signed group with its sign';
        $t->assert_text_contains($test_name, $res_html, '<span class="' . styles::CALC_POSITIVE . '">');
        // the save compares the results of the page by their group id, so a cell names the number it shows
        $cell_id = $dto_res->val_lst->get_by_names(
            [word_names::ZH, word_names::INHABITANTS, word_names::CANTON, word_names::YEAR_2019])->html_id();
        $test_name = '... and the cell carries the group id of its number';
        $t->assert_text_contains($test_name, $res_html, '<' . html_base::TD . ' class="' . styles::CALC_NUMBER
            . '" id="' . $cell_id . '">');
        // negative: the extra row 2019 of the detail table shows the same number again, but an id is unique on a page
        $test_name = '... but only once, also if a detail table shows the number again';
        $t->assert($test_name, (string)substr_count($res_html, 'id="' . $cell_id . '"'), '1');
        $test_name = 'the details follow the main table in a collapsed block with the default summary';
        $t->assert_text_order($test_name, $res_html, '</table>',
            '<summary>' . $mtr->txt(msg_id::CALCULATOR_DETAILS) . '</summary><div class="' . styles::CALC_MORE . '">');
        $test_name = 'a detail table has the table phrase as the first header and a row per phrase that is the table phrase';
        $t->assert_text_contains($test_name, $res_html, $html->block(html_base::TH, 'City')
            . $html->block(html_base::TH, triple_names::CALC_INHABITANTS_LABEL_COM, styles::CALC_NUMBER));
        // the row Zurich shows the calculator label of Zurich (rule 6), which is also the title of
        // this test page, because the page phrase is one of the cities
        $zh_row = '<td>' . triple_names::CALC_ZH_LABEL_COM . '</td><td class';
        $t->assert_text_order($test_name, $res_html, '<td>' . word_names::BE . '</td><td class', $zh_row);
        $test_name = '... and an extra row per with sub-item';
        $t->assert_text_order($test_name, $res_html, $zh_row, '<td>' . word_names::YEAR_2019 . '</td><td class');
        // the column inhabitants has the filter canton, so the row Zurich shows the inhabitants of
        // the canton and not the inhabitants of the city, which have no canton phrase
        // the cell id is the html id of the number that the cell shows, and the main table shows
        // the inhabitants of the canton first, so the cell of the detail table is the second one
        $zh_cell = '<td>' . triple_names::CALC_ZH_LABEL_COM . '</td><td class="' . styles::CALC_NUMBER . '" id="';
        $canton_val = $dto_res->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS, word_names::CANTON, word_names::YEAR_2019]);
        $city_val = $dto_res->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS]);
        $test_name = 'a detail column with a filter shows only the number with the filter phrase';
        $t->assert_text_contains($test_name, $res_html, $zh_cell . $canton_val->html_id()
            . ui_list::CALC_CELL_REPEAT . '2">' . $canton_val->val_formatted($msg) . '</td>');
        // negative: the number without the filter phrase is not shown in the column
        $test_name = '... and not the number without it';
        $t->assert_text_not_contains($test_name, $res_html, $zh_cell . $city_val->html_id());
        $test_name = 'the sources are numbered in the order of the first use';
        $t->assert_text_contains($test_name, $res_html, '<div class="' . styles::CALC_SOURCES . '">'
            . $html->block(html_base::H2, $mtr->txt(msg_id::CALCULATOR_SOURCES)) . '<' . html_base::OL . '>');
        $t->assert_text_contains($test_name, $res_html, $src->name());
        $test_name = '... and the hint of a field with a value of a source names its number';
        $t->assert_text_contains($test_name, $page->calculator_input($wrd, $msg, $dto_res, $form, views::CALCULATOR, []),
            $html->span(triple_names::CALC_INHABITANTS_HINT_COM . ' [1]', styles::INPUT_HINT));
        // negative: the hint of a phrase selector names no source, also if the selected option
        // has one, because an option is chosen and not measured on the page
        $opt_val = $dto_res->val_lst->get_by_names([word_names::CANTON, word_names::YEAR_2020]);
        $opt_val->src = $src;
        $test_name = '... but the hint of a phrase selector names no source';
        $t->assert_text_not_contains($test_name, $page->calculator_input($wrd, $msg, $dto_res, $form, views::CALCULATOR, []),
            $html->span('[1]', styles::INPUT_HINT));
        $opt_val->src = null;
        // negative: a phrase without calculator triples has no results
        $test_name = 'a phrase without calculator triples shows no calculation result';
        $t->assert($test_name, $page->calculation_result(new test_triples($t)->triple_bern_ui(), $msg, $dto_res), '');
        // a cell shows the number that its phrases select with the fewest other phrases
        $msg->reset();
        $amb_lst = $t_val->value_list_calculator_ambiguous_ui();
        $amb_phr = $amb_lst->lst()[0]->phr_lst();
        $zh_phr = $amb_phr->get_by_name(word_names::ZH, $msg);
        $inhab_phr = $amb_phr->get_by_name(word_names::INHABITANTS, $msg);
        $y19_phr = $amb_phr->get_by_name(word_names::YEAR_2019, $msg);
        $list = new ui_list();
        $test_name = 'a calculator cell shows the number that its phrases select';
        $t->assert($test_name, (string)$list->calculator_result([$zh_phr, $inhab_phr, $y19_phr], $amb_lst, $msg)?->number(),
            (string)values::SAMPLE_INT);
        // negative: two numbers that the phrases select equally would make the cell depend on the
        // order of the list, so the cell stays empty and the user is told which phrase is missing
        $test_name = '... but no number if the phrases select two numbers equally';
        $t->assert_null($test_name, $list->calculator_result([$zh_phr, $inhab_phr], $amb_lst, $msg));
        $test_name = '... and reports the ambiguous cell';
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::CALCULATOR_CELL_AMBIGUOUS));
        $msg->reset();

        // the request cache can hold the results of another phrase e.g. of a table on the same page,
        // so the results of the page phrase are loaded by the phrase and not by an empty result list
        $dto_lst = new data_object();
        $dto_lst->set_result_list($t_res->result_list_solution_prio_first_row_ui());
        $zh_city_id = new test_triples($t)->zh_city_ui()->phrase()->id();
        $test_name = 'the results of a phrase are not loaded if the cache has only the results of another phrase';
        $t->assert_false($test_name, $dto_lst->has_results_of($zh_city_id));
        $zh_res_lst = $t_res->result_list_zh_city_ui();
        $dto_lst->add_results_of($zh_city_id, $zh_res_lst);
        $test_name = '... but after their load';
        $t->assert_true($test_name, $dto_lst->has_results_of($zh_city_id));
        $test_name = '... with the results of both phrases in the cache';
        $res_count = $t_res->result_list_solution_prio_first_row_ui()->count() + $zh_res_lst->count();
        $t->assert($test_name, (string)$dto_lst->res_lst->count(), (string)$res_count);
        // negative: a result that the cache has already is not added a second time
        $dto_lst->add_results_of($zh_city_id, $zh_res_lst);
        $test_name = '... and a result that the cache has already is not added again';
        $t->assert($test_name, (string)$dto_lst->res_lst->count(), (string)$res_count);
        // many cells and results of a calculator page ask the values of the same phrases, so the
        // request cache loads the values of the same phrases once
        $test_name = 'the values of phrases are not loaded if the cache has not asked them yet';
        $zh_phr_lst = $t_phr->list_zh_ui();
        $t->assert_false($test_name, $dto_lst->has_values_of($zh_phr_lst));
        $test_name = '... but after their load';
        $zh_val_lst = $t_val->value_list_zh_ui();
        $dto_lst->add_values_of($zh_phr_lst, $zh_val_lst, $msg);
        $t->assert_true($test_name, $dto_lst->has_values_of($zh_phr_lst));
        $test_name = '... also if the same phrases are asked in another order';
        $zh_reversed = new phrase_list_ui();
        foreach (array_reverse($zh_phr_lst->lst()) as $phr) {
            $zh_reversed->add_phrase($phr);
        }
        $t->assert_true($test_name, $dto_lst->has_values_of($zh_reversed));
        $test_name = '... with the loaded values in the value list';
        $t->assert($test_name, (string)$dto_lst->val_lst->count(), (string)$zh_val_lst->count());
        // negative: the cache key is the complete list of phrases, so a part of the list is asked again
        $test_name = '... but not for a part of the phrases';
        $zh_part = new phrase_list_ui();
        $zh_part->add_phrase($zh_phr_lst->lst()[0]);
        $t->assert_false($test_name, $dto_lst->has_values_of($zh_part));
        // negative: a value that the cache has already is not added a second time
        $test_name = '... and a value that the cache has already is not added again';
        $dto_lst->add_values_of($zh_part, $zh_val_lst, $msg);
        $t->assert($test_name, (string)$dto_lst->val_lst->count(), (string)$zh_val_lst->count());
        // an empty answer is kept too, so that phrases without values are not asked again
        $test_name = 'phrases without values are not asked again';
        $no_val_phr_lst = $t_phr->list_global_problems_ui();
        $dto_lst->add_values_of($no_val_phr_lst, new value_list_ui(), $msg);
        $t->assert_true($test_name, $dto_lst->has_values_of($no_val_phr_lst));
        // negative: phrases that the online cache has asked already are not loaded again
        $test_name = 'an online cache loads asked phrases not again';
        $val_count = $dto_lst->val_lst->count();
        $dto_lst->load_values_of($no_val_phr_lst, $msg);
        $t->assert($test_name, $dto_lst->val_lst->count(), $val_count);
        $msg->reset();
        // negative: a new value list holds none of the asked phrases
        $test_name = 'a new value list holds the values of no asked phrases';
        $dto_lst->set_value_list($zh_val_lst);
        $t->assert_false($test_name, $dto_lst->has_values_of($zh_phr_lst));
        // negative: without the api the cache asks nothing and remembers nothing
        $test_name = 'an offline cache loads no values';
        $dto_off = new data_object();
        $dto_off->online = false;
        $dto_off->load_values_of($zh_phr_lst, $msg);
        $t->assert_false($test_name, $dto_off->has_values_of($zh_phr_lst));
        $msg->reset();
        // negative: a phrase without calculator triples has no table, and an offline page without values no numbers
        $test_name = 'a calculator without columns and rows shows no table';
        $calc_empty = new calculator($wrd->phrase(), new phrase_list_ui());
        $t->assert($test_name, $list->calculation_table($calc_empty, $wrd, new value_list_ui(), $msg), '');
        $test_name = 'an offline page without values has no calculator numbers';
        $t->assert($test_name, $list->calculator_numbers($wrd, $dto_off, $msg)->count(), 0);
        $msg->reset();
        // with javascript for the calculator the page gets its calculation as data and the script
        // that repeats it in the browser when an input changes (docs/llm/frontend.md)
        $lib = new library();
        $js_list = new ui_list();
        $js_calc = new calculator($wrd->phrase(), $dto_res->phr_lst);
        $js_lst = $js_list->calculator_numbers($wrd, $dto_res, $msg);
        $js_list->calculation_table($js_calc, $wrd, $js_lst, $msg);
        $script = $js_list->calculator_script($js_calc, $wrd, $js_lst, $dto_res, $msg);
        $test_name = 'the calculator script gets the numbers of the page as data';
        $t->assert_text_contains($test_name, $script, '<script type="application/json" id="' . ui_list::CALC_DATA_ID . '">');
        $plan = json_decode($lib->str_left_of($lib->str_right_of($script, ui_list::CALC_DATA_ID . '">'), '</script>'), true);
        $canton_key = $canton_val->html_id();
        $test_name = '... with the number of each value';
        $t->assert($test_name, (string)$plan[ui_list::CALC_NUMBERS][$canton_key], (string)values::SAMPLE_INT);
        // the numbers and the results are sorted by their html id, so that the page does not
        // depend on the load order of the values and results (docs/llm/frontend.md)
        $num_keys = array_keys($plan[ui_list::CALC_NUMBERS]);
        $sorted_keys = $num_keys;
        sort($sorted_keys);
        $test_name = '... sorted by the html id';
        $t->assert_true($test_name, count($num_keys) > 1 and $num_keys === $sorted_keys);
        $test_name = '... the number that each cell shows';
        $t->assert($test_name, $plan[ui_list::CALC_CELLS][$canton_key][ui_list::CALC_CELL_NUMBER], $canton_key);
        $test_name = '... the value of each number field';
        $t->assert($test_name, $plan[ui_list::CALC_INPUTS][$inh_name],
            $dto_res->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS])->html_id());
        // negative: the browser reads only the numbers that a result, an input or a cell uses, so a
        // value of the request cache that the page does not use e.g. a value of a related phrase
        // that another test has added is not written into the page and the page does not change
        $unused_val = $t_val->value_ch_ui();
        $js_lst->add($unused_val, $msg);
        $unused_script = $js_list->calculator_script($js_calc, $wrd, $js_lst, $dto_res, $msg);
        $unused_plan = json_decode($lib->str_left_of($lib->str_right_of($unused_script, ui_list::CALC_DATA_ID . '">'), '</script>'), true);
        $test_name = '... but not a number that no result, input or cell uses';
        $unused_numbers = $unused_plan[ui_list::CALC_NUMBERS];
        $t->assert_key_not_exists($test_name, $unused_val->html_id(), $unused_numbers);
        $test_name = '... while the numbers that the page uses stay';
        $t->assert($test_name, (string)$unused_plan[ui_list::CALC_NUMBERS][$canton_key], (string)values::SAMPLE_INT);
        // the browser calculates only the results that a cell shows and the results these use, so a
        // result of the request cache that no cell needs is not written into the page
        $test_name = 'the calculator calculates the result of a cell';
        [$shown_res, $operand_res] = $t_res->result_list_solution_prio_first_row_ui()->lst();
        $unused_res = $t_res->result_list_zh_city_ui()->lst()[0];
        $all_res_plan = [
            $shown_res->html_id() => [[formula_calc::TOKEN_REF => $operand_res->html_id()]],
            $operand_res->html_id() => [[formula_calc::TOKEN_REF => $canton_key]],
            $unused_res->html_id() => [[formula_calc::TOKEN_REF => $canton_key]],
        ];
        // calculator_result_plan sorts its plan by the html id
        ksort($all_res_plan);
        $cell_plan = [$shown_res->html_id() => [ui_list::CALC_CELL_NUMBER => $shown_res->html_id()]];
        $needed_plan = $js_list->calculator_needed_results($all_res_plan, $cell_plan);
        $t->assert_key_exists($test_name, $shown_res->html_id(), $needed_plan);
        $test_name = '... and the result that this result uses';
        $t->assert_key_exists($test_name, $operand_res->html_id(), $needed_plan);
        // negative: a result that no cell shows and no shown result uses is left out
        $test_name = '... but not a result that no cell needs';
        $t->assert_key_not_exists($test_name, $unused_res->html_id(), $needed_plan);
        $test_name = '... sorted by the html id';
        $res_keys = array_keys($needed_plan);
        $sorted_keys = $res_keys;
        sort($sorted_keys);
        $t->assert_true($test_name, count($res_keys) > 1 and $res_keys === $sorted_keys);
        // negative: two results that use each other must not loop forever and are taken once each
        $test_name = 'results that use each other are calculated once each';
        $cycle_plan = $all_res_plan;
        $cycle_plan[$operand_res->html_id()] = [[formula_calc::TOKEN_REF => $shown_res->html_id()]];
        $cycle_needed = $js_list->calculator_needed_results($cycle_plan, $cell_plan);
        $t->assert($test_name, array_keys($cycle_needed), $res_keys);
        // negative: a page without cells calculates no result
        $test_name = 'a calculator without cells calculates no result';
        $t->assert($test_name, $js_list->calculator_needed_results($all_res_plan, []), []);
        $test_name = '... and the script that repeats the calculation behind the data';
        $t->assert_text_contains($test_name, $script, '<script src="' . files::CALCULATOR_SCRIPT . '"></script>');
        // config.yaml allows javascript for the calculator, so also a pod without the config value
        // gets the script (shared Config::DEFAULT_CALCULATOR_JAVASCRIPT)
        $test_name = 'the calculation result has the calculator script by default';
        $t->assert_text_contains($test_name, $res_html, '<script src="' . files::CALCULATOR_SCRIPT . '"></script>');
        // negative: a pod that switches the javascript of the calculator off gets no script, like every other page
        $cfg_keep = $ui_sys->cfg;
        $ui_sys->cfg = new config_ui($t_val->value_list_calculator_javascript_off()->api_json([api_types::INCL_PHRASES]));
        $test_name = 'a calculation result without javascript has no script';
        $t->assert_text_not_contains($test_name, $page->calculation_result($wrd, $msg, $dto_res), '<script');
        $ui_sys->cfg = $cfg_keep;

        $t->subheader($ts . 'number format digits');
        // without a config value each precision tier has the digits of its shared config const
        $cfg_none = new config_ui();
        $test_name = 'the default tier has the default digits without a config value';
        $t->assert($test_name, $cfg_none->digits(), shared_config::DEFAULT_DIGITS);
        $test_name = '... and each tier the digits of its const';
        $t->assert($test_name, $cfg_none->digits(triples::VERY_ROUGH), shared_config::VERY_ROUGH_DIGITS);
        // a pod overwrites the digits in config.yaml; every user default value has "default" in its
        // path, so also the precise tier matches the names of the default tier
        $cfg_digits = new config_ui($t_val->value_list_config_digits()->api_json([api_types::INCL_PHRASES]));
        $test_name = 'the config value overwrites the digits of a tier';
        $t->assert($test_name, $cfg_digits->digits(words::PRECISE), shared_config::PRECISE_DIGITS + 1);
        $test_name = '... and the default tier reads its own value, not the first value with its names';
        $t->assert($test_name, $cfg_digits->digits(), shared_config::DEFAULT_DIGITS + 1);
        // negative: a tier without a config value keeps its const, an unknown tier gets the default tier
        $test_name = '... but a tier without a config value keeps the digits of its const';
        $t->assert($test_name, $cfg_digits->digits(words::ROUGH), shared_config::ROUGH_DIGITS);
        $test_name = 'an unknown tier has the digits of the default tier';
        $t->assert($test_name, $cfg_digits->digits(words::DIGITS), shared_config::DEFAULT_DIGITS + 1);

        $t->subheader($ts . 'calculator page title');
        // the calculator page is named by the calculator label of the page phrase instead of the view name
        $msk_calc = new view_ui($t_msk->view()->api_json());
        $msk_calc->code_id = views::CALCULATOR;
        $test_name = 'the page title of a calculator names the calculator label of the page phrase';
        $t->assert_text_contains($test_name, $msk_calc->title($wrd, $msg, $dto_res), triple_names::CALC_ZH_LABEL_COM);
        // negative: another view keeps its own name in the title
        $test_name = '... and another view its own name';
        $t->assert_text_contains($test_name, $msk->title($wrd, $msg, $dto_res), $msk->name());
    }

    /**
     * the save of a calculator page: only the number fields with a changed number are saved,
     * and the page is shown again; the write itself is simulated, so no database row is changed
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     */
    private function calculator_save_tests(test_cleanup $t, string $ts): void
    {
        global $mtr;
        $ui = new frontend();

        $t->subheader($ts . 'calculator save');
        $changed = (string)word_names::INHABITANT_ID;
        $same = (string)word_names::YEAR_2020_ID;
        $shown_key = url_var::PRE . url_var::NUMERIC_VALUE;
        $form = [
            url_var::MASK => views::CALCULATOR_ID,
            // the phrase id of the triple, which is negative
            url_var::ID => -triple_names::CITY_ZH_ID,
            url_var::ACTION => url_var::ACTION_SAVE,
            // a field whose number the user has changed
            url_var::NUMERIC_VALUE . $changed => (string)values::SAMPLE_FLOAT,
            url_var::GROUP . $changed => (string)values::PI_MATH_ID,
            $shown_key . $changed => (string)values::CITY_ZH_INHABITANTS_2019,
            url_var::NAME . $changed => triple_names::CALC_INHABITANTS_LABEL_COM,
            url_var::PHRASE . $changed => word_names::METRE,
            // a field that the user has not changed
            url_var::NUMERIC_VALUE . $same => (string)values::CITY_ZH_INHABITANTS_2019,
            url_var::GROUP . $same => (string)values::E_ID,
            $shown_key . $same => (string)values::CITY_ZH_INHABITANTS_2019,
            // a phrase selector, which sends neither a group id nor a shown number
            url_var::NUMERIC_VALUE . word_names::CANTON_ID => (string)word_names::YEAR_2020_ID,
        ];
        $msg = new user_message();
        $changes = $ui->calculator_changes($form, $msg);
        $test_name = 'the calculator save writes only the field with a changed number';
        $change_count = count($changes);
        $t->assert($test_name, $change_count, 1);
        $test_name = '... to the value of the field';
        $change_id = $changes[$changed]->id();
        $t->assert($test_name, $change_id, values::PI_MATH_ID);
        $test_name = '... with the entered number';
        $change_number = $changes[$changed]->number();
        $t->assert($test_name, $change_number, values::SAMPLE_FLOAT);

        $lib = new library();
        $html = new html_base();
        // the new number links to the default view of the saved value
        $val_url = $html->url_back(views::VALUE, (string)values::PI_MATH_ID, $form);
        $test_name = 'the confirmation names the label, the new number as a link and the old number with the unit';
        $saved_txt = $lib->msg_var_text([$ui->calculator_saved_text($form, $changed)]);
        $expected = triple_names::CALC_INHABITANTS_LABEL_COM . ' changed to '
            . $html->ref($val_url, values::SAMPLE_FLOAT . ' ' . word_names::METRE)
            . ' (from ' . values::CITY_ZH_INHABITANTS_2019 . ' ' . word_names::METRE . ')';
        $t->assert($test_name, $saved_txt, $expected);
        // negative: a number without unit gets no unit separator
        $test_name = '... and only the numbers for a field without unit';
        $no_unit_form = $form;
        unset($no_unit_form[url_var::PHRASE . $changed]);
        $no_unit_txt = $lib->msg_var_text([$ui->calculator_saved_text($no_unit_form, $changed)]);
        $no_unit_expected = triple_names::CALC_INHABITANTS_LABEL_COM . ' changed to '
            . $html->ref($val_url, (string)values::SAMPLE_FLOAT)
            . ' (from ' . values::CITY_ZH_INHABITANTS_2019 . ')';
        $t->assert($test_name, $no_unit_txt, $no_unit_expected);
        // negative: the label is free text of the form, so a placeholder in it is never filled
        $test_name = '... and keeps a placeholder in the label as text';
        $marker = msg_id::VAR_START . msg_id::VAR_VALUE . msg_id::VAR_END;
        $marker_form = array_merge($form, [url_var::NAME . $changed => $marker]);
        $marker_txt = $lib->msg_var_text([$ui->calculator_saved_text($marker_form, $changed)]);
        $t->assert_text_contains($test_name, $marker_txt, $marker);
        // negative: the confirmation is shown as html, so a tag in the label is escaped and never shown as html
        $test_name = '... and escapes a tag in the label';
        $tag = '<' . html_base::A . '>';
        $tag_form = array_merge($form, [url_var::NAME . $changed => $tag]);
        $tag_txt = $lib->msg_var_text([$ui->calculator_saved_text($tag_form, $changed)]);
        $t->assert_text_not_contains($test_name, $tag_txt, $tag);

        // negative: a text is not a number, so it is reported and not saved
        $test_name = 'an entered text is not saved';
        $txt_form = $form;
        $txt_form[url_var::NUMERIC_VALUE . $changed] = word_names::INHABITANTS;
        $txt_msg = new user_message();
        $txt_changes = $ui->calculator_changes($txt_form, $txt_msg);
        $t->assert($test_name, $txt_changes, []);
        $test_name = '... but reported';
        $t->assert_false($test_name, $txt_msg->is_ok());

        // the simulated save shows the calculator page again without the inputs, which the page
        // then shows with the saved numbers
        $test_name = 'a save without a changed number shows the calculator page again';
        $same_form = $form;
        $same_form[url_var::NUMERIC_VALUE . $changed] = (string)values::CITY_ZH_INHABITANTS_2019;
        $act_backend = clone $t->usr1;
        $act_msg = new user_message();
        $act_msg->usr = new user_ui();
        $next_url = $ui->url_to_action($same_form, $act_backend, $act_msg, $ui->dto, false);
        $t->assert($test_name, $next_url, [url_var::MASK => views::CALCULATOR_ID, url_var::ID => -triple_names::CITY_ZH_ID]);
        $test_name = '... and tells the user that nothing has been saved';
        $info = $act_msg->get_last_info();
        $t->assert($test_name, $info, $mtr->txt(msg_id::CALCULATOR_NOTHING_CHANGED));
    }

    /**
     * @param \Zukunft\ZukunftCom\main\php\cfg\component\component $cmp the backend component fixture
     * @param word_ui $wrd the phrase of the page
     * @param view_ui $msk the view of the page
     * @param data_object $dto the request cache of the page
     * @param user_message $msg to collect the messages of the render
     * @return string the html code of the component as the page would show it
     */
    private function render($cmp, word_ui $wrd, view_ui $msk, data_object $dto, user_message $msg): string
    {
        $cmp_ui = new component_ui($cmp->api_json());
        return $cmp_ui->dsp_entries($wrd, $msg, '', $msk->id(), $dto, null, '', true);
    }

}
