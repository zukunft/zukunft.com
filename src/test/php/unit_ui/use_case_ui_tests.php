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
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_calc;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source_list as source_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

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
        $test_name = 'an entered number and phrase of the url are shown again';
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
