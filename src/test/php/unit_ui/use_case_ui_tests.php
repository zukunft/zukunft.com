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

use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
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
use Zukunft\ZukunftCom\main\php\web\helper\number_format;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source_list as source_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\headless_browser;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use DateTime;

class use_case_ui_tests
{

    // a save button for the test of the buttons of the input list
    const string CALC_SAVE_HTML = '<form id="save"></form>';
    // the element of the page with the message of the save (PUT_MESSAGE_ID of calculator_editor_scenarios.js and table_editor_scenarios.js)
    const string PUT_MESSAGE_ID = 'putMessage';
    // the reason of the pod that refuses the save of the table editor (REFUSED_MESSAGE of table_editor_scenarios.js)
    const string TABLE_REFUSED_MESSAGE = 'the user is not allowed to change this table';
    // the time of the save is replaced by this fixed time, so the message can be compared with the expected file
    const string PUT_TIMESTAMP = '2026-10-25T18:00:00.000Z';
    // the result of a scenario that has not ended in the browser e.g. because a selector no longer matches
    const string SCENARIO_NOT_ENDED = 'the scenario has not ended in the headless browser';
    // the fields of the message of the PDF tab scenarios (pressCreatePdf and pdf_tab_offline of calculator_editor_scenarios.js)
    const string PDF_POST_ROUTE = 'route';
    const string PDF_POST_TOKEN_SENT = 'token_sent';
    const string PDF_POST_BODY = 'body';
    const string PDF_BUTTON_DISABLED = 'disabled';
    // the fields of a section of the page structure in the message of the arguments_collapsed scenario and the key of the arguments
    const string TREE_LIST = 'list';
    const string TREE_LIST_OPEN = 'open';
    const string TREE_LIST_ARGUMENT = 'argument';
    // the clicks of the preview_parts scenario and the settings that each click shows by their kind and label
    const string CLICK_ARGUMENT = 'argument';
    const string CLICK_VERDICT = 'verdict';
    const string CLICK_FORMULA = 'formula';
    const string CLICK_COMPARE = 'compare';
    const string SHOWN_FORMULA = 'alternative cost formula';
    const string CLICK_FIELD = 'field';
    const string CLICK_PRESET = 'preset';
    const string SHOWN_FIELD = 'Input field: Construction cost (overnight)';
    const string SHOWN_PRESET = 'Preset: Academies pessimistic';
    const string CLICK_ROW_GROUP = 'rowGroup';
    const string CLICK_ROW = 'row';
    const string CLICK_COLUMN = 'column';
    const string CLICK_LEVEL = 'level';
    const string CLICK_SOURCE = 'source';
    const string CLICK_SOURCE_STAYS = 'stays';
    const string CLICK_HEADING = 'heading';
    const string SHOWN_SOURCE_NPP = 'Source: Fraunhofer ISE LCOE 2024 via Faktenfackel';
    const string SHOWN_SOURCE_PV = "Source: Swissolar: Fiche d'information Photovoltaïque, Feb 2024 (European residual mix)";
    const string CLICK_NOTE = 'note';
    const string SHOWN_NOTE = 'Note: Model limits';
    const string SHOWN_DETAIL_COLUMN = 'Detail column: Direct';
    const string SHOWN_DETAIL_TABLE = 'Detail table: Monthly energy balance, average year';
    const string SHOWN_ROW_GROUP = 'Row group: New nuclear plant';
    const string SHOWN_ROW = 'Result row: Annual production';
    const string SHOWN_COLUMN = 'Result column: with new plant';
    const string SHOWN_LEVEL = "Axis value: 5'000 CHF/kW";
    const string CLICK_MORE = 'more';
    const string CLICK_MORE_OPEN = 'moreOpen';
    const string SHOWN_ARGUMENT = 'Argument: Winter supply gap';
    const string SHOWN_VERDICT = 'Verdict state: Undecided on cost - decide on the other arguments';
    const string SHOWN_PAGE = 'Page: New nuclear power plants in Switzerland: the vote of 28 February 2027';

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
        // the min function e.g. of the direct use of a month is given as tokens like the operators
        $test_name = 'the min function of a calculation is given to the browser with its argument separator';
        $frm_min = new formula_ui();
        $frm_min->set_id(formula_names::CITY_POPULATION_ID);
        $frm_min->set_usr_text('"total" = min( "' . triple_names::CITY_ZH_NAME . '" ; "' . triple_names::COMPANY_ZURICH . '" )');
        $frm_min_lst = new formula_list_ui();
        $frm_min_lst->add($frm_min, $msg);
        $tokens = new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_min_lst);
        $t->assert($test_name, $tokens, [
            [formula_calc::TOKEN_OP => chars::FUNC_MIN],
            [formula_calc::TOKEN_OP => '('],
            [formula_calc::TOKEN_REF => $dto->val_lst->get_by_names([triple_names::CITY_ZH_NAME])->html_id()],
            [formula_calc::TOKEN_OP => chars::FUNC_ARG_SEPARATOR],
            [formula_calc::TOKEN_REF => $dto->val_lst->get_by_names([triple_names::COMPANY_ZURICH])->html_id()],
            [formula_calc::TOKEN_OP => ')']]);
        $test_name = 'the max function of a calculation is given to the browser with its argument separator';
        $frm_max = new formula_ui();
        $frm_max->set_id(formula_names::CITY_POPULATION_ID);
        $frm_max->set_usr_text('"total" = max( "' . triple_names::CITY_ZH_NAME . '" ; "' . triple_names::COMPANY_ZURICH . '" )');
        $frm_max_lst = new formula_list_ui();
        $frm_max_lst->add($frm_max, $msg);
        $tokens = new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_max_lst);
        $t->assert($test_name, $tokens, [
            [formula_calc::TOKEN_OP => chars::FUNC_MAX],
            [formula_calc::TOKEN_OP => '('],
            [formula_calc::TOKEN_REF => $dto->val_lst->get_by_names([triple_names::CITY_ZH_NAME])->html_id()],
            [formula_calc::TOKEN_OP => chars::FUNC_ARG_SEPARATOR],
            [formula_calc::TOKEN_REF => $dto->val_lst->get_by_names([triple_names::COMPANY_ZURICH])->html_id()],
            [formula_calc::TOKEN_OP => ')']]);
        // negative: a char outside the arithmetic of the page is never given to the browser
        $test_name = '... and no calculation for an expression with a function that the page does not have';
        $frm_fn = new formula_ui();
        $frm_fn->set_id(formula_names::CITY_POPULATION_ID);
        $frm_fn->set_usr_text('"total" = tan("' . triple_names::CITY_ZH_NAME . '")');
        $frm_fn_lst = new formula_list_ui();
        $frm_fn_lst->add($frm_fn, $msg);
        $t->assert_null($test_name, new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_fn_lst));
        // a list function takes all values of the operand and the context, without a second number of the same fact
        $test_name = 'a list function calculates with all values of its operand';
        $list_val_lst = $t_val->value_list_list_function_ui();
        $frm_list = new formula_ui();
        $frm_list->set_id(formula_names::CITY_POPULATION_ID);
        $frm_list->set_usr_text('"total" = sum( "' . word_names::INHABITANTS . '" ) / count( "' . word_names::INHABITANTS . '" )');
        $frm_list_lst = new formula_list_ui();
        $frm_list_lst->add($frm_list, $msg);
        $t->assert($test_name, new formula_calc()->result($t_res->result_zh_sum_ui($sum), $list_val_lst, $frm_list_lst), 15.0);
        $test_name = '... which the browser repeats with the html id of each value';
        $list_ids = array_column(array_filter(
            new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $list_val_lst, $frm_list_lst),
            fn(array $token) => array_key_exists(formula_calc::TOKEN_REF, $token)), formula_calc::TOKEN_REF);
        $t->assert($test_name, count($list_ids), 4);
        // negative: a calculation inside a list would need pairs of values, so it never gives a number
        $test_name = '... but no number for a calculation inside a list function';
        $frm_list->set_usr_text('"total" = sum( "' . word_names::INHABITANTS . '" * 2 )');
        $t->assert_null($test_name, new formula_calc()->result($t_res->result_zh_sum_ui($sum), $list_val_lst, $frm_list_lst));
        $t->assert_null($test_name, new formula_calc()->tokens($t_res->result_zh_sum_ui($sum), $list_val_lst, $frm_list_lst));

        $t->subheader($ts . 'result tooltip');
        // the tooltip of a result shows the result number with the numbers of the page used and below the formula with the term names
        $test_name = 'the result number with the numbers used and the formula expression with the term names';
        $num_txt = (string)round(values::SAMPLE_FLOAT, 2);
        $res_txt = $t_res->result_zh_sum_ui($sum)->val_formatted($msg);
        $exp_num = '"' . $res_txt . '" = ' . $num_txt . ' + ' . $num_txt . "\n" . $frm->get_usr_text();
        $tip = new formula_calc()->expression_with_numbers($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_lst, $msg);
        $t->assert($test_name, $tip, $exp_num);
        $test_name = '... and is the tooltip of the result link in a table with the hint to click';
        $res_tip = $t_res->result_zh_sum_ui($sum);
        $res_lst = new result_list_ui();
        $res_lst->add($res_tip, $msg);
        $dto->set_result_list($res_lst);
        $tbl_html = $this->render($t_cmp->component_table(), $wrd, $msk, $dto, $msg);
        $title = htmlspecialchars($exp_num . "\n" . msg_id::RESULT_TOOLTIP_DETAILS->text(), ENT_QUOTES);
        $res_html = $res_tip->value_edit($msg);
        $t->assert_text_contains($test_name, $res_html, 'title="' . $title . '"');
        // in a table each number keeps its own tooltip, so the cell title does not repeat it
        $test_name = '... which in a table is the title of the link and not of the cell';
        $t->assert_text_contains($test_name, $tbl_html, 'title="' . $title . '"');
        $t->assert_text_not_contains($test_name, $tbl_html, 'title="' . $title . '"><a href=');
        // the latex is not needed, because the tooltip shows the expression
        $test_name = '... also for a formula with latex';
        $frm->set_latex('\text{total} = \text{' . triple_names::CITY_ZH_NAME . '} + \text{' . triple_names::COMPANY_ZURICH . '}');
        $tip_latex = new formula_calc()->expression_with_numbers($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_lst, $msg);
        $t->assert($test_name, $tip_latex, $exp_num);
        // a number that no row of the table shows, e.g. the world GDP of a potential loss, is loaded by its phrases
        $test_name = 'the phrases of the operands without a number are loaded for the tooltip';
        $missing = new formula_calc()->missing_operand_phrases($t_res->result_zh_sum_ui($sum), new value_list_ui(), $frm_lst);
        $t->assert_contains($test_name, $missing->names(), [triple_names::CITY_ZH_NAME, triple_names::COMPANY_ZURICH]);
        // negative: the operands with a number on the page need no load
        $test_name = '... but not the phrases of the operands with a number on the page';
        $missing_none = new formula_calc()->missing_operand_phrases($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_lst);
        $t->assert($test_name, $missing_none->is_empty(), true);
        // each missing operand with its own phrases, so that a page can keep an operand that names only a unit
        $test_name = 'the missing phrases are listed per operand';
        $opr_lsts = new formula_calc()->missing_operands($t_res->result_zh_sum_ui($sum), new value_list_ui(), $frm_lst);
        $opr_names = array_map(fn($opr_lst) => $opr_lst->names(), $opr_lsts);
        $t->assert($test_name, $opr_names, [[triple_names::CITY_ZH_NAME], [triple_names::COMPANY_ZURICH]]);
        // negative: no operand list for the operands with a number on the page
        $test_name = '... but no list for the operands with a number on the page';
        $opr_none = new formula_calc()->missing_operands($t_res->result_zh_sum_ui($sum), $dto->val_lst, $frm_lst);
        $t->assert($test_name, $opr_none, []);
        // negative: a result that no table has calculated keeps the link without a tooltip
        $test_name = '... and no tooltip for a result without its calculation';
        $res_plain_html = $t_res->result_zh_sum_ui($sum)->value_edit($msg);
        $t->assert_text_not_contains($test_name, $res_plain_html, msg_id::RESULT_TOOLTIP_DETAILS->text());
        // negative: a value is not calculated, so its link keeps the tooltip of the table cell
        $test_name = '... and no formula tooltip for a value';
        $val_html = $dto->val_lst->lst()[0]->value_edit($msg);
        $t->assert_text_not_contains($test_name, $val_html, msg_id::RESULT_TOOLTIP_DETAILS->text());
        // the tooltip of a value is its source with the url or else the doi
        $test_name = 'the tooltip of a value is the name and the url of its source';
        $val_tip = $dto->val_lst->lst()[0];
        $val_tip->src = new source_ui($t_src->source_reserved()->api_json());
        $src_title = htmlspecialchars(sources::SIB . "\n" . sources::SIB_URL, ENT_QUOTES);
        $t->assert_text_contains($test_name, $val_tip->value_edit($msg), 'title="' . $src_title . '"');
        $test_name = '... or the doi of a source without url';
        $doi_json = json_decode($t_src->source_filled_included()->api_json(), true);
        unset($doi_json[json_fields::URL]);
        $val_tip->src = new source_ui(json_encode($doi_json));
        $doi_title = htmlspecialchars($val_tip->src->name() . "\n" . def::LINK_DOI . sources::TEST_DOI, ENT_QUOTES);
        $t->assert_text_contains($test_name, $val_tip->value_edit($msg), 'title="' . $doi_title . '"');
        // negative: a source known only by its id has no name to show, so the tooltip of the table cell stays
        $test_name = '... but no tooltip for a source without name';
        $val_tip->src = new source_ui();
        $val_tip->src->set_id(sources::SIB_ID);
        $t->assert_text_not_contains($test_name, $val_tip->value_edit_link($msg), 'title=');
        $val_tip->src = new source_ui($t_src->source_reserved()->api_json());

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
        // a number field shows a stored share in percent, also its limits, with the unit %
        $test_name = 'a number field shows a share in percent';
        $pct_shown = $list->field_shown(values::SAMPLE_FLOAT, $pct_val, $msg);
        $t->assert($test_name, $pct_shown, values::SAMPLE_FLOAT * 100);
        $test_name = '... with the unit %';
        $pct_unit = $list->field_unit($pct_val, $msg);
        $t->assert($test_name, $pct_unit, sandbox_value::UNIT_PERCENT);
        $test_name = '... and a field without a number stays empty';
        $no_num_shown = $list->field_shown(null, $pct_val, $msg);
        $t->assert_null($test_name, $no_num_shown);
        // negative: another number is shown as it is and keeps its unit
        $test_name = '... but another number is shown as it is';
        $inh_shown = $list->field_shown(values::SAMPLE_FLOAT, $inh_val, $msg);
        $t->assert($test_name, $inh_shown, values::SAMPLE_FLOAT);
        $test_name = '... without the unit %';
        $inh_unit = $list->field_unit($inh_val, $msg);
        $t->assert_text_not_contains($test_name, $inh_unit, sandbox_value::UNIT_PERCENT);
        // negative: a ratio unit is a percent phrase, whose number is never shown times 100
        $test_name = '... and a number with the ratio unit share is shown as it is';
        $ratio_val = new test_lib()->ui_value($t_val->value_share());
        $ratio_shown = $list->field_shown(values::SAMPLE_FLOAT, $ratio_val, $msg);
        $t->assert($test_name, $ratio_shown, values::SAMPLE_FLOAT);
        // a row can set the decimals of a share, which the row then always shows
        $test_name = 'a share with the decimals of its row shows them';
        $row_pct_txt = $list->calculator_cell_text($pct_val, new number_format(), false, $msg, 3);
        $t->assert_text_contains($test_name, $row_pct_txt, '100.000');
        // negative: without them the percent decimals of the user apply
        $test_name = '... but without them not';
        $user_pct_txt = $list->calculator_cell_text($pct_val, new number_format(), false, $msg);
        $t->assert_text_not_contains($test_name, $user_pct_txt, '100.000');
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
        // negative: unit_name names the percent word, a field shows the symbol by field_unit
        $test_name = '... but the name and not the symbol of percent';
        $ui_sys->phr_lst = $t_phr->list_percent_symbol_cache_ui();
        $t->assert($test_name, $list->unit_name($pct_val, $msg), words::PCT);
        // the symbol of the percent word is shown behind the number times 100 instead
        $test_name = '... which is the percent symbol of the number';
        $t->assert($test_name, $pct_val->percent_symbol($msg), word_names::PERCENT_SYMBOL);
        // one unit of all numbers is named by the first header, and a share does not count
        $test_name = 'a result table with one unit names it';
        $one_unit = $list->calculation_table_unit([[[$unit_val, $pct_val, null]]], $msg);
        $t->assert($test_name, $one_unit, word_names::EUR);
        // negative: two units or no unit stay in the cells
        $test_name = '... but not with a second number without this unit';
        $two_units = $list->calculation_table_unit([[[$unit_val, $inh_val]]], $msg);
        $t->assert($test_name, $two_units, '');
        $test_name = '... and not without a unit';
        $no_unit = $list->calculation_table_unit([[[$inh_val]]], $msg);
        $t->assert($test_name, $no_unit, '');
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
        // the editor is built by its script from the phrase data, which an offline page embeds empty
        $test_name = 'the calculator editor of a phrase loads the editor script';
        $editor_html = $this->render($t_cmp->component_calculator_editor(), $wrd, $msk, $dto, $msg);
        $t->assert_text_contains($test_name, $editor_html, $html->script_src(files::CALCULATOR_EDITOR_SCRIPT));
        $test_name = '... with the phrase data as the seed of the script';
        $t->assert_text_contains($test_name, $editor_html, $html->script_data(system_page::CALC_EDITOR_SEED_ID, []));
        $test_name = '... and the session token, which the save sends to the api in the csrf header';
        $t->assert_text_contains($test_name, $editor_html, $html->form_session_token());
        // a selected formula is changed in the formula editor, which the calculator editor starts with its own hosts
        $test_name = '... with the formula editor script before the calculator editor script, which starts it';
        $t->assert_text_contains($test_name, $editor_html,
            $html->script_src(files::FORMULA_EDITOR_SCRIPT) . $html->script_src(files::CALCULATOR_EDITOR_SCRIPT));
        // the page preview is the calculator page itself, which the script of the page builds from the edited data
        $test_name = '... with the script of the calculator page before them, which builds the page preview';
        $t->assert_text_contains($test_name, $editor_html,
            $html->script_src(files::CALCULATOR_JSON_SCRIPT) . $html->script_src(files::FORMULA_EDITOR_SCRIPT));
        $test_name = '... and the settings of the calculator page';
        $t->assert_text_contains($test_name, $editor_html, 'id="' . system_page::CALC_PAGE_SETTINGS_ID . '"');
        // negative: a save of the preview would store its inputs as a case of the page
        $test_name = '... but without the save of the calculator page';
        $t->assert_text_contains($test_name, $editor_html, '"' . system_page::CALC_PAGE_SAVE . '":""');
        $test_name = '... and the style of the formula editor';
        $t->assert_text_contains($test_name, $editor_html, $html->link_style(files::FORMULA_EDITOR_STYLE));
        // negative: the host of the formula editor view would start the editor a second time without the calculator editor
        $test_name = '... but without the host of the formula editor view';
        $t->assert_text_not_contains($test_name, $editor_html,
            $html->div('', styles::FORMULA_EDITOR, system_page::FORMULA_EDITOR_HOST_ID));
        // negative: a component without a page phrase e.g. in a preview has no editor to show
        $test_name = '... but nothing for a component without a phrase';
        $t->assert($test_name, $this->render($t_cmp->component_calculator_editor(), new word_ui(), $msk, $dto, $msg), '');
        // the table editor is built by its script into the host from the phrase data like the calculator editor
        $test_name = 'the table editor of a phrase loads the editor script';
        $tbl_editor_html = new system_page()->table_editor($wrd, $msg, $dto);
        $t->assert_text_contains($test_name, $tbl_editor_html, $html->script_src(files::TABLE_EDITOR_SCRIPT));
        $test_name = '... into its host with the style of the editor';
        $t->assert_text_contains($test_name, $tbl_editor_html, $html->div('', '', system_page::TABLE_EDITOR_HOST_ID));
        $test_name = '... with the phrase data as the seed of the script';
        $t->assert_text_contains($test_name, $tbl_editor_html, $html->script_data(system_page::CALC_EDITOR_SEED_ID, []));
        $test_name = '... and the session token, which the save sends to the api in the csrf header';
        $t->assert_text_contains($test_name, $tbl_editor_html, $html->form_session_token());
        $test_name = '... and the keyword of each parameter field in the settings';
        $kwd_json = json_encode([system_page::TABLE_EDITOR_KEYWORDS => triples::TABLE_EDITOR_KEYWORDS]);
        $t->assert_text_contains($test_name, $tbl_editor_html, substr($kwd_json, 1, -1));
        $test_name = '... and the texts of the top bar that the table editor shares with the calculator editor';
        $t->assert_text_contains($test_name, $tbl_editor_html, json_encode(msg_id::CALC_EDITOR_UNDO->value) . ':');
        // negative: a page without a phrase shows the page of the server, so it has no settings
        $test_name = '... but nothing for a page without a phrase';
        $t->assert($test_name, new system_page()->table_editor(new word_ui(), $msg, $dto), '');
        // negative: without the request cache the page of the server is shown
        $test_name = '... and nothing without the request cache';
        $t->assert($test_name, new system_page()->table_editor($wrd, $msg, null), '');
        // the formula editor is built by its script into the host like the calculator editor
        $test_name = 'the formula editor of a phrase loads the editor script';
        $frm_editor_html = $this->render($t_cmp->component_formula_editor(), $wrd, $msk, $dto, $msg);
        $t->assert_text_contains($test_name, $frm_editor_html, $html->script_src(files::FORMULA_EDITOR_SCRIPT));
        $test_name = '... into its host with the style of the editor';
        $t->assert_text_contains($test_name, $frm_editor_html, $html->div('', styles::FORMULA_EDITOR, system_page::FORMULA_EDITOR_HOST_ID));
        $test_name = '... with the phrase data as the seed of the script';
        $t->assert_text_contains($test_name, $frm_editor_html, $html->script_data(system_page::CALC_EDITOR_SEED_ID, []));
        $test_name = '... and the session token, which the save sends to the api in the csrf header';
        $t->assert_text_contains($test_name, $frm_editor_html, $html->form_session_token());
        // negative: a component without a page phrase e.g. in a preview has no editor to show
        $test_name = '... but nothing for a component without a phrase';
        $t->assert($test_name, $this->render($t_cmp->component_formula_editor(), new word_ui(), $msk, $dto, $msg), '');
        // the formula selector and the result compare are only the hosts, which the script of the formula editor fills
        $test_name = 'the formula selector of a phrase is the host that the formula editor script fills';
        $frm_selector_html = $this->render($t_cmp->component_formula_selector(), $wrd, $msk, $dto, $msg);
        $t->assert($test_name, $frm_selector_html, $html->div('', styles::FORMULA_EDITOR, system_page::FORMULA_SELECTOR_HOST_ID));
        $test_name = '... without a second editor script';
        $t->assert_text_not_contains($test_name, $frm_selector_html, $html->script_src(files::FORMULA_EDITOR_SCRIPT));
        $test_name = '... and nothing for a component without a phrase';
        $t->assert($test_name, $this->render($t_cmp->component_formula_selector(), new word_ui(), $msk, $dto, $msg), '');
        $test_name = 'the formula result compare of a phrase is the host that the formula editor script fills';
        $frm_compare_html = $this->render($t_cmp->component_formula_result_compare(), $wrd, $msk, $dto, $msg);
        $t->assert($test_name, $frm_compare_html, $html->div('', styles::FORMULA_EDITOR, system_page::FORMULA_COMPARE_HOST_ID));
        $test_name = '... and nothing for a component without a phrase';
        $t->assert($test_name, $this->render($t_cmp->component_formula_result_compare(), new word_ui(), $msk, $dto, $msg), '');
        // the seed carries the time of the phrase data, which the snapshot replaces by the fixed test time
        $test_name = 'the snapshot replaces the time of the phrase data seed by the test time';
        $later = (new DateTime(test_const::DUMMY_DATETIME_LATER))->format(phrase_data::TIME_FORMAT);
        $fixed = (new DateTime(test_const::DUMMY_DATETIME))->format(phrase_data::TIME_FORMAT);
        $seed_html = $html->script_data(system_page::CALC_EDITOR_SEED_ID, [json_fields::TIME => $later]);
        $fixed_html = $html->script_data(system_page::CALC_EDITOR_SEED_ID, [json_fields::TIME => $fixed]);
        $t->assert($test_name, $t->fix_volatile_phrase_data_time($seed_html), $fixed_html);
        // negative: a time outside the seed is a real part of the page and stays
        $test_name = '... but not a time outside the seed';
        $other_html = $html->script_data(ui_list::CALC_DATA_ID, [json_fields::TIME => $later]);
        $t->assert($test_name, $t->fix_volatile_phrase_data_time($other_html), $other_html);

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
        // a triple to a proposed keyword e.g. a download button is a triple from the calculator keyword
        // as well, so it is no input item, also before the page shows the proposed keyword
        $cac_kwd = $t_phr->list_calculator_proposed_ui();
        $test_name = 'the request cache of the proposed keyword test has the triple to the proposed keyword';
        $t->assert_true($test_name, $cac_kwd->get_by_name(triple_names::CALC_ZH_INPUTS_DOWNLOAD, $msg) != null);
        $test_name = '... which is no input item';
        $t->assert($test_name, $this->zh_input_names($cac_kwd, $msg), [
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
        // a side result is a number of its own, so it is selected without the filter of the row
        $list_ctx = new ui_list();
        $test_name = 'a side result is selected by the page and the row phrase';
        $side_ctx = $list_ctx->calculation_side_context($calc, $wrd, $rows[1]);
        $t->assert($test_name, array_map(fn($phr) => $phr->name(), $side_ctx), [word_names::ZH, word_names::INHABITANTS]);
        // negative: the cells of the row are selected with the filter
        $test_name = '... but a cell also by the filter of the row';
        $cell_ctx = $list_ctx->calculation_row_context($calc, $wrd, $rows[1]);
        $t->assert($test_name, array_map(fn($phr) => $phr->name(), $cell_ctx), [word_names::ZH, word_names::INHABITANTS, word_names::CANTON]);
        // the view fills the request cache with the general triples of these phrases (view_exe::add_page_cache)
        $test_name = 'the phrases of the general triples are the item, option, row and filter phrases and the other phrase of a row, each once';
        // an option may have a label of its own, so the side result of the canton and the kinds of the city are included
        $t->assert($test_name, array_map(fn($phr) => $phr->name(), $calc->phrases()), [
            word_names::INHABITANTS, word_names::CANTON, word_names::YEAR_2020, word_names::YEAR_2019, word_names::CITY,
            word_names::BE, word_names::ZH, triple_names::CITY_BE]);
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
        $t->assert($test_name, $page->calculator_title(new test_triples($t)->zh_city_ui(), $msg, $dto_calc),
            $html->block(html_base::H4, triple_names::CALC_ZH_LABEL_COM) . $html->p(triple_names::CALC_ZH_HINT_COM));
        // negative: a page without a phrase has no calculator title
        $test_name = '... and a page without a phrase has no calculator title';
        $t->assert($test_name, $page->calculator_title(null, $msg, $dto_calc), '');
        // the page sets the width of its result panel e.g. the wide table of the portfolio split
        $test_name = 'a page with a result width sets it as a style';
        $dto_width = new data_object();
        $dto_width->val_lst = new test_values($t)->value_list_result_width_ui();
        $width_style = $page->calculator_result_width_style(new test_triples($t)->zh_city_ui(), $dto_width);
        $t->assert_text_contains($test_name, $width_style, styles::CALC_RESULT_WIDTH_VAR . ':' . triple_names::CALC_RESULT_WIDTH . 'px');
        // negative: a page without the value has the standard width e.g. the pv lifetime balance
        $test_name = '... and another page keeps the standard width';
        $other_style = $page->calculator_result_width_style(new test_words($t)->zh_ui(), $dto_width);
        $t->assert($test_name, $other_style, '');
        $test_name = '... and a page without a request cache too';
        $t->assert($test_name, $page->calculator_result_width_style(new test_triples($t)->zh_city_ui(), null), '');

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
        // a text shows its placeholders and escapes the text around them
        $text_html = new ui_list()->calculator_text('<b> {num:36 %} {source:unknown}', $zh_city, $dto_in, $dto_in->val_lst, [], $msg);
        $test_name = 'a text shows a number placeholder in the style of a number';
        $t->assert_text_contains($test_name, $text_html, $html->span('36 %', styles::CALC_NUM));
        // negative: no number instead of a wrong one for a source that the page does not list
        $test_name = '... and nothing for an unknown source';
        $t->assert_text_not_contains($test_name, $text_html, '{source');
        $test_name = '... and escapes the text around it';
        $t->assert_text_not_contains($test_name, $text_html, '<b>');
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
        // an option without a number selects only its phrase e.g. the walk order, so its value is no number that a result could read
        $test_name = 'a selector option without a number is offered and selected by its phrase';
        $opt_id = $dto_in->phr_lst->get_by_name(word_names::YEAR_2020, $msg)->id();
        $opt_html = '<option value="' . ui_list::CALC_OPTION_PHRASE . $opt_id . '"';
        $dto_no_num = clone $dto_in;
        $dto_no_num->val_lst = new value_list_ui();
        $no_num_html = $page->calculator_input($zh_city, $msg, $dto_no_num, $form, views::CALCULATOR, []);
        $t->assert_text_contains($test_name, $no_num_html, $opt_html . '  selected');
        // negative: an option with a number keeps the number as its value, which a result of the page reads
        $test_name = '... but an option with a number is selected by its number';
        $dto_opt_num = clone $dto_in;
        $dto_opt_num->val_lst = $t_val->value_list_calculator_option_ui();
        $opt_num_html = $page->calculator_input($zh_city, $msg, $dto_opt_num, $form, views::CALCULATOR, []);
        $t->assert_text_not_contains($test_name, $opt_num_html, $opt_html);
        // several different numbers of the option phrase belong to other phrases, so the option has no number
        $test_name = '... and an option with several numbers of other phrases has no number';
        $dto_opt_tie = clone $dto_in;
        $dto_opt_tie->val_lst = $t_val->value_list_calculator_option_tie_ui();
        $opt_tie_html = $page->calculator_input($zh_city, $msg, $dto_opt_tie, $form, views::CALCULATOR, []);
        $t->assert_text_contains($test_name, $opt_tie_html, $opt_html);
        $test_name = 'a row is ranked by its number of the phrase of the ranking';
        $rank_bern = $dto_in->phr_lst->get_by_name(word_names::BE, $msg);
        $rank_year = $dto_in->phr_lst->get_by_name(word_names::YEAR_2020, $msg);
        $rank_zh = $dto_in->phr_lst->get_by_name(word_names::ZH, $msg);
        $rank_num = $list->rank_number($rank_bern, $rank_year, $dto_opt_tie->val_lst, $msg);
        $t->assert($test_name, $rank_num, (float)values::SAMPLE_INT);
        // negative: a row without a number is the last row
        $test_name = '... but a row without a number is the last row';
        $no_rank_num = $list->rank_number($rank_zh, $rank_year, $dto_opt_tie->val_lst, $msg);
        $t->assert($test_name, $no_rank_num, -INF);
        $test_name = 'the buttons of a list show their label and only if the list has them';
        $btn_calc = new calculator($zh_city->phrase(), $t_phr->list_calculator_button_ui());
        $btn_html = $list->calculator_buttons($btn_calc, $btn_calc->group(triples::CALCULATOR_INPUT));
        $t->assert_text_contains($test_name, $btn_html, '>' . triple_names::CALC_BUTTON_COM . '</button>');
        // the input list shows two buttons per line with the save button in front of the download
        $test_name = 'the save button is part of the two buttons per line of the input list';
        $save_html = $list->calculator_buttons($btn_calc, $btn_calc->group(triples::CALCULATOR_INPUT), self::CALC_SAVE_HTML);
        $t->assert_text_contains($test_name, $save_html, '<div class="' . styles::CALC_ACTIONS . '">' . self::CALC_SAVE_HTML);
        // negative: a list without the save button keeps its buttons without the two per line
        $test_name = '... but not the buttons of another list';
        $t->assert_text_not_contains($test_name, $btn_html, styles::CALC_ACTIONS);
        // the page of the server shows the case name, which only the page of the browser can change
        $test_name = 'a page built from another phrase shows the field of the case name';
        $case_html = $list->calculator_case_field($btn_calc);
        $t->assert_text_contains($test_name, $case_html, 'id="' . ui_list::CALC_CASE_FIELD_ID . '"');
        $test_name = '... which cannot be changed without javascript';
        $t->assert_text_contains($test_name, $case_html, ' disabled>');
        // negative: a list without a button keyword has no button, also not without a list
        $test_name = '... but a list without the keyword has none';
        $no_btn_calc = new calculator($zh_city->phrase(), $dto_in->phr_lst);
        $no_btn_html = $list->calculator_buttons($no_btn_calc, $no_btn_calc->group(triples::CALCULATOR_INPUT));
        $t->assert($test_name, $no_btn_html, '');
        $test_name = '... and inputs without the case keyword have no field of the case name';
        $t->assert($test_name, $list->calculator_case_field($no_btn_calc), '');
        $test_name = '... and no list has none';
        $null_btn_html = $list->calculator_buttons($no_btn_calc, null);
        $t->assert($test_name, $null_btn_html, '');
        // the other field types: the inhabitants read only, 2019 as a checkbox and the canton repeated with Bern
        $test_name = 'a read only field shows its number without an input';
        $dto_fld = clone $dto_in;
        $dto_fld->phr_lst = $t_phr->list_calculator_fields_ui();
        $fld_html = $page->calculator_input($zh_city, $msg, $dto_fld, $form, views::CALCULATOR, []);
        $t->assert_text_contains($test_name, $fld_html, '<div class="' . styles::INPUT_READ_ONLY . '">');
        $test_name = '... and its label points to no field';
        $ro_label = '<label>' . triple_names::CALC_INHABITANTS_LABEL_COM . '</label>';
        $t->assert_text_contains($test_name, $fld_html, $ro_label);
        $test_name = '... but the label of a number field does';
        $t->assert_text_not_contains($test_name, $in_html, $ro_label);
        // negative: a user cannot change a read only number, also not by the url, which the assumption field of the same phrase may use
        $test_name = '... and a number in the url does not change a read only field';
        $ro_id = $dto_fld->phr_lst->get_by_name(word_names::INHABITANTS, $msg)->id();
        $ro_url = [url_var::NUMERIC_VALUE . $ro_id => values::SAMPLE_INT];
        $ro_url_html = $page->calculator_input($zh_city, $msg, $dto_fld, $form, views::CALCULATOR, $ro_url);
        $ro_number = $this->read_only_number($fld_html);
        $ro_url_number = $this->read_only_number($ro_url_html);
        $t->assert($test_name, $ro_url_number, $ro_number);
        $test_name = 'a checkbox sends 0 by its hidden field if it is unchecked';
        $chk_name = url_var::NUMERIC_VALUE . $dto_fld->phr_lst->get_by_name(word_names::YEAR_2019, $msg)->id();
        $chk_html = $html->input_checkbox($chk_name, true, $form);
        $t->assert_text_contains($test_name, $fld_html, $html->form_hidden($chk_name, '0', $form)
            . '<input type="checkbox" name="' . $chk_name . '"');
        $test_name = '... but is not checked without a number';
        $t->assert_text_not_contains($test_name, $fld_html, $chk_html);
        $test_name = 'a checkbox is checked by the number 1 in the url';
        $chk_on_html = $page->calculator_input($zh_city, $msg, $dto_fld, $form, views::CALCULATOR, [$chk_name => '1']);
        $t->assert_text_contains($test_name, $chk_on_html, $chk_html);
        $test_name = '... and not by the number 0 of its hidden field';
        $chk_off_html = $page->calculator_input($zh_city, $msg, $dto_fld, $form, views::CALCULATOR, [$chk_name => '0']);
        $t->assert_text_not_contains($test_name, $chk_off_html, $chk_html);
        $test_name = 'a repeated field has a field per instance with the name of the instance';
        $rep_id = $dto_fld->phr_lst->get_by_name(word_names::BE, $msg)->id();
        $t->assert_text_contains($test_name, $fld_html, 'name="' . url_var::NUMERIC_VALUE . $rep_id . '"');
        $test_name = 'a warning is shown while the result of the page phrase is above zero';
        $wrn_html = '<div class="' . styles::INPUT_WARNING . '">' . triple_names::CALC_WARNING_COM;
        $dto_wrn = clone $dto_in;
        $dto_wrn->phr_lst = $t_phr->list_calculator_warning_ui();
        $dto_wrn->val_lst = $t_val->value_list_calculator_warning_ui(values::SAMPLE_FLOAT);
        $wrn_on_html = $page->calculator_input($zh_city, $msg, $dto_wrn, $form, views::CALCULATOR, []);
        $t->assert_text_contains($test_name, $wrn_on_html, $wrn_html);
        // negative: a result of zero or below shows no warning, only the empty block that the script fills after an input change
        $test_name = '... but not at zero';
        $dto_wrn->val_lst = $t_val->value_list_calculator_warning_ui(0);
        $wrn_zero_html = $page->calculator_input($zh_city, $msg, $dto_wrn, $form, views::CALCULATOR, []);
        $t->assert_text_not_contains($test_name, $wrn_zero_html, $wrn_html);
        $test_name = '... with the block for the script';
        $t->assert_text_contains($test_name, $wrn_zero_html, ui_list::CALC_WARNINGS_ID);
        $test_name = '... and not below zero';
        $dto_wrn->val_lst = $t_val->value_list_calculator_warning_ui(-values::SAMPLE_FLOAT);
        $wrn_neg_html = $page->calculator_input($zh_city, $msg, $dto_wrn, $form, views::CALCULATOR, []);
        $t->assert_text_not_contains($test_name, $wrn_neg_html, $wrn_html);
        $test_name = 'the script gets the warnings of the page';
        $wrn_calc = new calculator($zh_city->phrase(), $dto_wrn->phr_lst);
        $wrn_script = new ui_list()->calculator_script($wrn_calc, $zh_city, $dto_wrn->val_lst, $dto_wrn, $msg);
        $t->assert_text_contains($test_name, $wrn_script, '"' . ui_list::CALC_WARNINGS . '":');
        $test_name = 'a check row with the same number matches';
        $match_txt = $mtr->txt(msg_id::CALCULATOR_CHECK_MATCH);
        $differs_txt = $mtr->txt(msg_id::CALCULATOR_CHECK_DIFFERS);
        $chk_calc = new calculator($zh_city->phrase(), $t_phr->list_calculator_check_ui());
        $chk_same = $list->calculator_check($chk_calc, $t_val->value_list_calculator_check_ui(values::SAMPLE_FLOAT), $dto_in, $msg);
        $t->assert_text_contains($test_name, $chk_same, $match_txt);
        // negative: another number and a missing number differ, because a missing number is never a match
        $test_name = '... but another number differs';
        $chk_other = $list->calculator_check($chk_calc, $t_val->value_list_calculator_check_ui(values::SAMPLE_INT), $dto_in, $msg);
        $t->assert_text_contains($test_name, $chk_other, $differs_txt);
        $test_name = '... and a missing number differs';
        $chk_none = $list->calculator_check($chk_calc, $t_val->value_list_calculator_check_ui(null), $dto_in, $msg);
        $t->assert_text_contains($test_name, $chk_none, $differs_txt);
        $test_name = '... and an expected value without a number differs';
        $chk_no_exp_lst = $t_val->value_list_calculator_check_ui(values::SAMPLE_FLOAT, false);
        $chk_no_exp = $list->calculator_check($chk_calc, $chk_no_exp_lst, $dto_in, $msg);
        $t->assert_text_contains($test_name, $chk_no_exp, $differs_txt);
        // negative: without an expected number the status cannot change, so the script does not get it
        $test_name = '... whose status the script does not update';
        $no_exp_list = new ui_list();
        $no_exp_list->calculator_check($chk_calc, $chk_no_exp_lst, $dto_in, $msg);
        $no_exp_script = $no_exp_list->calculator_script($chk_calc, $zh_city, $chk_no_exp_lst, $dto_in, $msg);
        $t->assert_text_not_contains($test_name, $no_exp_script, '"' . ui_list::CALC_CHECKS . '":');
        $test_name = 'a check row ignores the float noise of a calculation';
        $t->assert_true($test_name, $list->calculator_check_same(values::SAMPLE_FLOAT * (1 + 1e-12), values::SAMPLE_FLOAT));
        // negative: a small real difference of a small number is no float noise
        $test_name = '... but not a real difference of a small number';
        $t->assert_false($test_name, $list->calculator_check_same(0.349, 0.35));
        $test_name = 'the script gets the status of a check row';
        $chk_list = new ui_list();
        $chk_lst = $t_val->value_list_calculator_check_ui(values::SAMPLE_FLOAT);
        $chk_list->calculator_check($chk_calc, $chk_lst, $dto_in, $msg);
        $chk_script = $chk_list->calculator_script($chk_calc, $zh_city, $chk_lst, $dto_in, $msg);
        $t->assert_text_contains($test_name, $chk_script, '"' . ui_list::CALC_CHECKS . '":');
        $test_name = 'a cell takes the calculated number next to an expected number';
        $exp_lst = $t_val->value_list_calculator_check_ui(values::SAMPLE_INT);
        $exp_phr = $exp_lst->lst()[0]->phr_lst()->get_by_name(word_names::INHABITANTS, $msg);
        $t->assert($test_name, $list->rank_number($exp_phr, $exp_phr, $exp_lst, $msg), (float)values::SAMPLE_INT);
        // negative: an expected number alone is no number of the cell
        $test_name = '... but never the expected number';
        $exp_none = $t_val->value_list_calculator_check_ui(null);
        $t->assert($test_name, $list->rank_number($exp_phr, $exp_phr, $exp_none, $msg), -INF);
        $test_name = '... with its hint below the table';
        $t->assert_text_contains($test_name, $chk_same, '<' . html_base::P . ' class="' . styles::INPUT_HINT . '">' . triple_names::CALC_CHECK_HINT);
        $test_name = '... and a page without the check has no table';
        $no_chk_calc = new calculator($zh_city->phrase(), $dto_in->phr_lst);
        $no_chk = $list->calculator_check($no_chk_calc, $t_val->value_list_calculator_check_ui(values::SAMPLE_FLOAT), $dto_in, $msg);
        $t->assert($test_name, $no_chk, '');
        $test_name = 'a diagram draws a point per number and names the first and the last row';
        $svg_colour = styles::CALC_COLUMN_COLOURS[1];
        $svg = $html->line_diagram([[[1.0, -2.0], $svg_colour, [word_names::BE, word_names::ZH]], [[0.0, 0.0], '', ['a', 'b']]], word_names::BE, word_names::ZH, '2');
        $t->assert_text_contains($test_name, $svg, '<polyline points="70.0,53.0 748.0,176.0"');
        $t->assert_text_contains($test_name, $svg, word_names::BE);
        $t->assert_text_contains($test_name, $svg, word_names::ZH);
        $test_name = '... with a tooltip per point';
        $t->assert_text_contains($test_name, $svg, '<title>' . word_names::ZH . '</title>');
        $test_name = '... and a line per series in its colour';
        $t->assert_text_contains($test_name, $svg, '<g class="' . $svg_colour . '"><polyline');
        $test_name = '... and a series without a colour has no style';
        $t->assert_text_contains($test_name, $svg, '<g><polyline points="70.0,');
        // negative: a page without the flag has no diagram and without the list no notes
        $test_name = '... but a page without the flag has none';
        $no_diagram = $list->calculator_diagrams($no_chk_calc, $t_val->value_list_calculator_input_ui(), $msg);
        $t->assert($test_name, $no_diagram, '');
        $test_name = '... and a page without the list has no notes';
        $no_notes = $list->calculator_notes($no_chk_calc);
        $t->assert($test_name, $no_notes, '');
        $test_name = 'a repeated field with several instances has a button per instance to remove it';
        $ins_calc = new calculator($zh_city->phrase(), $t_phr->list_calculator_instances_ui());
        $ins_item = array_values(array_filter($ins_calc->items(triples::CALCULATOR_INPUT),
            fn($item) => $item->name() == triple_names::CALC_ZH_INPUT_CANTON))[0];
        $ins_canton = $ins_calc->item_phrase($ins_item);
        $ins_first = $ins_calc->kinds($ins_canton)[0];
        $t->assert_text_contains($test_name, $list->calculator_remove_button($ins_calc, $ins_canton, $ins_first), ui_list::CALC_REMOVE_ICON);
        // negative: the last instance stays and a field that is not repeated has no button
        $test_name = '... but one instance has none';
        $one_calc = new calculator($zh_city->phrase(), $dto_fld->phr_lst);
        $t->assert($test_name, $list->calculator_remove_button($one_calc, $ins_canton, $ins_first), '');
        $test_name = '... and no instance has none';
        $t->assert($test_name, $list->calculator_remove_button($ins_calc, $ins_canton, null), '');
        $test_name = 'a column shows the plus sign and the unit if it has the flags';
        $t->assert_true($test_name, $ins_calc->columns([$ins_item])[0][6] and $ins_calc->columns([$ins_item])[0][7]);
        // negative: without the flags a column has neither
        $test_name = '... but not without the flags';
        $t->assert_false($test_name, $one_calc->columns([$ins_item])[0][6] or $one_calc->columns([$ins_item])[0][7]);
        $test_name = 'the hint of a row phrase is its sub text';
        $t->assert($test_name, $ins_calc->phrase_hint($ins_first), triple_names::CALC_ROW_HINT);
        // negative: another phrase has no hint
        $test_name = '... but a phrase without a hint has none';
        $t->assert($test_name, $ins_calc->phrase_hint($ins_canton), '');
        $test_name = 'the kinds of a row are the phrases that it is';
        $t->assert_contains($test_name, array_map(fn($phr) => $phr->name(), $ins_calc->row_kinds($ins_first)), [word_names::CANTON]);
        $test_name = 'a column can name an instance shorter';
        $ins_cols = $ins_calc->columns([$ins_item]);
        $t->assert_contains($test_name, array_column($ins_cols, 0), [triple_names::CALC_SHORT_LABEL]);
        $test_name = 'a diagram of a column filtered by an instance has a line per instance';
        $dia_col = $t_phr->list_calculator_instances_ui()->get_by_name(triple_names::CALC_DIAGRAM_COLUMN, $msg);
        $dia_lines = $ins_calc->diagram_lines($dia_col);
        $dia_labels = array_column($dia_lines, 2);
        // Zurich shows its calculator label, because it is also the page phrase of this fixture
        $t->assert_contains($test_name, $dia_labels, [mb_ucfirst(word_names::BE), triple_names::CALC_ZH_LABEL_COM]);
        // negative: a column without such a filter is one line without a colour
        $test_name = '... but another column is one line';
        $one_lines = $ins_calc->diagram_lines($ins_item);
        $one_colour = $one_lines[0][1];
        $t->assert($test_name, $one_colour, '');
        $test_name = 'the instances of a column with the colour flag start with the second colour';
        $ins_colours = array_column($ins_cols, 3);
        $t->assert_contains($test_name, $ins_colours, [styles::CALC_COLUMN_COLOURS[1]]);
        // negative: the first colour is for a single column wherever it stands e.g. the parent behind the children
        $test_name = '... and not the first colour';
        $t->assert($test_name, array_intersect($ins_colours, [styles::CALC_COLUMN_COLOURS[0]]), []);
        $test_name = 'the imports of a page are a section with a button per item';
        $imports_html = $list->calculator_imports($ins_calc);
        $t->assert_text_contains($test_name, $imports_html, triple_names::CALC_IMPORTS_LABEL);
        $t->assert_text_contains($test_name, $imports_html, '>' . triple_names::CALC_IMPORT_LABEL . '</button>');
        // negative: a page without the list has no section
        $test_name = '... but a page without the list has none';
        $t->assert($test_name, $list->calculator_imports($one_calc), '');
        $test_name = 'a column of a repeated input is shown once per instance';
        $fld_calc = new calculator($zh_city->phrase(), $dto_fld->phr_lst);
        $fld_cols = $fld_calc->columns($fld_calc->items(triples::CALCULATOR_INPUT));
        $t->assert_contains($test_name, array_column($fld_cols, 0), [mb_ucfirst(word_names::BE)]);
        // negative: without the repeat the column stays one column with its label
        $test_name = '... but another column stays one column';
        $in_calc = new calculator($zh_city->phrase(), $dto_in->phr_lst);
        $in_cols = $in_calc->columns($in_calc->items(triples::CALCULATOR_INPUT));
        $t->assert_contains($test_name, array_column($in_cols, 0), [triple_names::CALC_ZH_INPUT_CANTON_LABEL_COM]);
        $test_name = 'the rows of a detail table are the phrases at the end of the levels';
        $level_names = array_map(fn($phr) => $phr->name(), $fld_calc->row_phrases($dto_fld->phr_lst->get_by_name(word_names::CANTON, $msg)));
        $t->assert($test_name, $level_names, [word_names::ZH]);
        // negative: without a level below the phrase has no rows
        $test_name = '... and a phrase without a level has none';
        $no_level = $fld_calc->row_phrases($dto_fld->phr_lst->get_by_name(word_names::ZH, $msg));
        $t->assert($test_name, $no_level, []);
        $test_name = 'the first column with the colour flag gets the first colour';
        $fld_colours = array_column($fld_cols, 3);
        $t->assert_contains($test_name, $fld_colours, [styles::CALC_COLUMN_COLOURS[0]]);
        // negative: a column without the flag gets no colour
        $test_name = '... but a column without the flag has none';
        $in_colours = array_filter(array_column($in_cols, 3));
        $t->assert($test_name, $in_colours, []);
        $test_name = '... and the button to add an instance';
        $t->assert_text_contains($test_name, $fld_html, '>' . triple_names::CALC_ADD_LABEL . '</button>');
        // negative: without the triples of the field types the fields stay number fields and a selector
        $test_name = 'a field without a field type has no checkbox';
        $t->assert_text_not_contains($test_name, $in_html, 'type="checkbox"');
        $test_name = '... and no read only number';
        $t->assert_text_not_contains($test_name, $in_html, '<div class="' . styles::INPUT_READ_ONLY . '">');
        $test_name = '... and no button to add an instance';
        $t->assert_text_not_contains($test_name, $in_html, triple_names::CALC_ADD_LABEL);
        // the value of another use case with the same field phrase is not shown e.g. the lifetime
        // of a new NPP on the page of a PV installation, even if the preferred order of numbers would pick it
        $dto_foreign = clone $dto_in;
        $dto_foreign->val_lst = $t_val->value_list_calculator_input_foreign_ui();
        $foreign_html = $page->calculator_input($zh_city, $msg, $dto_foreign, $form, views::CALCULATOR, []);
        $test_name = 'a field shows the value with a part of the page triple';
        $t->assert_text_contains($test_name, $foreign_html, 'value="' . values::CITY_ZH_INHABITANTS_2019 . '"');
        // negative: the bigger value of Bern, which is no part of Zurich (city), is not shown
        $test_name = '... and not the value of a phrase outside the page triple';
        $t->assert_text_not_contains($test_name, $foreign_html, 'value="' . values::SAMPLE_BIG . '"');
        // but the value that a result of the page uses comes first, so that a changed field changes the result
        $frm = new formula_ui();
        $frm->set_id(formula_names::CITY_POPULATION_ID);
        $frm->set_name(formula_names::CITY_POPULATION);
        $frm->set_usr_text('"' . formula_names::CITY_POPULATION . '" = "' . word_names::INHABITANTS . '"');
        $frm_lst = new formula_list_ui();
        $frm_lst->add($frm, $msg);
        $res_lst = new result_list_ui();
        $res_lst->add($t_res->result_zh_city_bern_inhabitants_ui(values::SAMPLE_BIG), $msg);
        $dto_used = clone $dto_foreign;
        $dto_used->set_formula_list($frm_lst);
        $dto_used->set_result_list($res_lst);
        $used_html = $page->calculator_input($zh_city, $msg, $dto_used, $form, views::CALCULATOR, []);
        $test_name = 'a field shows the value that a result of the page uses before the value with a part of the page triple';
        $t->assert_text_contains($test_name, $used_html, 'value="' . values::SAMPLE_BIG . '"');
        // negative: the value with a part of the page triple, which no result uses, is not shown
        $test_name = '... and not the value that no result uses';
        $t->assert_text_not_contains($test_name, $used_html, 'value="' . values::CITY_ZH_INHABITANTS_2019 . '"');
        // two values that fit a field equally e.g. the inhabitants of Zurich and of a city are picked
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
        // the button is as wide as its text with about the same space around it on each side
        $test_name = '... as wide as its text';
        $t->assert_text_contains($test_name, $in_html, 'class="' . html_base::CLASS_BUTTON . ' ' . styles::CALC_BUTTON_SAVE . '"');
        // negative: the bootstrap column of html_base::button_bs would cut the button to a twelfth of the form
        $test_name = '... and not as wide as a bootstrap column';
        $t->assert_text_not_contains($test_name, $in_html, html_base::BS_BTN);
        $test_name = 'the assumptions follow the inputs in a collapsed block with the default heading';
        $t->assert_text_order($test_name, $in_html, '</button>',
            '<summary>' . $mtr->txt(msg_id::CALCULATOR_ASSUMPTIONS) . '</summary>');
        $test_name = '... and an assumption without a source is tagged as assumed';
        $t->assert_text_contains($test_name, $in_html,
            $html->span($mtr->txt(msg_id::FIELD_TAG_ASSUMED), styles::INPUT_TAG_ASSUMED));
        // the confidence has its own unit, so the value in kWh finds its confidence in percent
        $dto_conf = new data_object();
        $dto_conf->val_lst = $t_val->value_list_unit_confidence_ui();
        $val_conf = $dto_conf->val_lst->get_by_names([word_names::INHABITANTS, word_names::KWH]);
        $test_name = '... with the confidence of a value in another unit';
        $t->assert($test_name, new ui_list()->assumption_tag($val_conf, $dto_conf, $msg),
            $mtr->txt(msg_id::FIELD_TAG_ASSUMED) . ' ' . values::SAMPLE_FLOAT);
        // a selector option with a unit names its number and its source
        $val_conf->src = new source_ui($t_src->source_reserved()->api_json());
        $src_ids = [$val_conf->src->id() => $val_conf->src];
        $opt_txt = new ui_list()->calculator_option_text(word_names::ZH, $val_conf, $src_ids, $msg);
        $test_name = 'a selector option with a unit shows its number';
        $t->assert_text_contains($test_name, $opt_txt, word_names::ZH . ui_list::CALC_OPTION_SEP);
        $test_name = '... and the number of its source';
        $t->assert_text_contains($test_name, $opt_txt, ' [1]');
        // negative: an option without a unit e.g. a factor shows only its label
        $opt_plain = $dto_conf->val_lst->get_by_names([word_names::BE]);
        $test_name = '... but an option without a unit shows only its label';
        $t->assert($test_name, new ui_list()->calculator_option_text(word_names::ZH, $opt_plain, $src_ids, $msg), word_names::ZH);
        $val_conf->src = null;
        // negative: the confidence of another phrase is not used
        $val_other = $dto_conf->val_lst->get_by_names([word_names::BE]);
        $test_name = '... but not the confidence of another phrase';
        $t->assert($test_name, new ui_list()->assumption_tag($val_other, $dto_conf, $msg), $mtr->txt(msg_id::FIELD_TAG_ASSUMED));
        // negative: a phrase without calculator triples has no inputs
        $test_name = 'a phrase without calculator triples shows no calculator input';
        $t->assert($test_name, $page->calculator_input(new test_triples($t)->triple_bern_ui(), $msg, $dto_in, $form, views::CALCULATOR, []), '');

        $this->calculator_decision_tests($t, $ts);
        $this->table_editor_tests($t, $ts);
        $this->calculator_save_tests($t, $ts);
        $this->calculator_editor_scenario_tests($t, $ts);
        $this->calculator_editor_argument_tests($t, $ts);
        $this->table_editor_scenario_tests($t, $ts);

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
        // a list with results, which have no match of the phrase names, still finds its value
        $test_name = 'a list of values and results finds a value by its names';
        $mix_lst = clone $dto_res->val_lst;
        $mix_lst->add_results($t_res->result_list_zh_city_ui(), $msg);
        $mix_val = $mix_lst->get_by_names([word_names::ZH, word_names::INHABITANTS]);
        $t->assert_true($test_name, $mix_val != null);
        // negative: names without a value find nothing, also if the list has results
        $test_name = '... but no value for other names';
        $mix_none = $mix_lst->get_by_names([word_names::GE, word_names::CANTON]);
        $t->assert_null($test_name, $mix_none);
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
        // the row inhabitants is filtered by canton, so its label shows the number of inhabitants without the filter
        $test_name = 'a highlighted row is a key row with the number of its row phrase and the hint as sub text';
        $inh_txt = $dto_res->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS])->val_formatted($msg);
        $t->assert_text_contains($test_name, $res_html, '<' . html_base::TR . ' class="' . styles::CALC_KEY . '">'
            . $html->block(html_base::TD, triple_names::CALC_INHABITANTS_LABEL_COM . ' (' . $inh_txt . ')'
                . $html->span(triple_names::CALC_INHABITANTS_HINT_COM, styles::CALC_SUB)));
        // the number in the label is only an indication, so it shows the digits of the rough tier
        $dto_rough = clone $dto_res;
        $dto_rough->val_lst = $t_val->value_list_calculator_result_ui();
        $dto_rough->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS])->number = values::SAMPLE_FLOAT;
        $rough_html = $page->calculation_result($wrd, $msg, $dto_rough);
        $test_name = '... with the digits of the rough tier';
        $t->assert_text_contains($test_name, $rough_html,
            triple_names::CALC_INHABITANTS_LABEL_COM . ' (' . round(values::SAMPLE_FLOAT) . ')');
        // negative: not with the decimals of the default tier
        $test_name = '... and not with the decimals of the default tier';
        $t->assert_text_not_contains($test_name, $rough_html,
            triple_names::CALC_INHABITANTS_LABEL_COM . ' (' . round(values::SAMPLE_FLOAT) . '.');
        // a row with a number note shows the note behind the unit of its number e.g. PV-weighted
        $dto_note = clone $dto_res;
        $dto_note->phr_lst = $t_phr->list_calculator_number_note_ui();
        $note_html = $page->calculation_result($wrd, $msg, $dto_note);
        $test_name = '... and the number note of the row behind the number';
        $t->assert_text_contains($test_name, $note_html,
            triple_names::CALC_INHABITANTS_LABEL_COM . ' (' . $inh_txt . ' ' . triple_names::CALC_INHABITANTS_NOTE_COM . ')');
        // negative: a row without a number note shows only the number
        $test_name = '... but no note without a number note triple';
        $t->assert_text_not_contains($test_name, $res_html, triple_names::CALC_INHABITANTS_NOTE_COM);
        // a row phrase that a formula calculates has no value, so its label shows the result of the formula
        $frm_inh = new formula_ui();
        $frm_inh->set_id(formula_names::CITY_POPULATION_ID);
        $frm_inh->set_usr_text('"' . word_names::INHABITANTS . '" = "' . triple_names::CITY_ZH_NAME . '"');
        $frm_inh_lst = new formula_list_ui();
        $frm_inh_lst->add($frm_inh, $msg);
        $res_inh_lst = new result_list_ui();
        $res_inh_lst->add($t_res->result_zh_sum_ui(values::SAMPLE_FLOAT), $msg);
        $dto_frm = clone $dto_res;
        $dto_frm->val_lst = new value_list_ui();
        $dto_frm->set_result_list($res_inh_lst);
        $dto_frm->set_formula_list($frm_inh_lst);
        $frm_html = $page->calculation_result($wrd, $msg, $dto_frm);
        $test_name = '... and the result of the formula of a row phrase without a value';
        $t->assert_text_contains($test_name, $frm_html,
            triple_names::CALC_INHABITANTS_LABEL_COM . ' (' . round(values::SAMPLE_FLOAT) . ')');
        // negative: a result that only names the row phrase is not its number e.g. the emission payback of a mix
        $frm_other = new formula_ui();
        $frm_other->set_id(formula_names::CITY_POPULATION_ID);
        $frm_other->set_usr_text('"total" = "' . triple_names::CITY_ZH_NAME . '"');
        $frm_other_lst = new formula_list_ui();
        $frm_other_lst->add($frm_other, $msg);
        $dto_frm->set_formula_list($frm_other_lst);
        $other_html = $page->calculation_result($wrd, $msg, $dto_frm);
        $test_name = '... but not the result of a formula that only names the row phrase';
        $t->assert_text_not_contains($test_name, $other_html, triple_names::CALC_INHABITANTS_LABEL_COM . ' (');
        // negative: a result that names the filter of the row is the number of its cells e.g. the battery effect
        $res_cell_lst = new result_list_ui();
        $res_cell_lst->add($t_res->result_zh_canton_ui(values::SAMPLE_FLOAT), $msg);
        $dto_frm->set_result_list($res_cell_lst);
        $dto_frm->set_formula_list($frm_inh_lst);
        $cell_html = $page->calculation_result($wrd, $msg, $dto_frm);
        $test_name = '... and not the result that names the filter of the row';
        $t->assert_text_not_contains($test_name, $cell_html, triple_names::CALC_INHABITANTS_LABEL_COM . ' (');
        // negative: of several results of the row phrase none is picked, because the number would depend on the sort
        $res_two_lst = new result_list_ui();
        $res_two_lst->add($t_res->result_zh_sum_ui(values::SAMPLE_FLOAT), $msg);
        $res_two_lst->add($t_res->result_zh_year_ui(values::SAMPLE_INT), $msg);
        $dto_frm->set_result_list($res_two_lst);
        $two_html = $page->calculation_result($wrd, $msg, $dto_frm);
        $test_name = '... and no number if the formula has several results of the row phrase';
        $t->assert_text_not_contains($test_name, $two_html, triple_names::CALC_INHABITANTS_LABEL_COM . ' (');
        $test_name = '... and the number of a signed group with its sign';
        $t->assert_text_contains($test_name, $res_html, '<span class="' . styles::CALC_POSITIVE . '">');
        // negative: a flagged group shows the sign by its style only e.g. the savings of the pv page
        $plus_span = '<span class="' . styles::CALC_POSITIVE . '">+';
        $test_name = '... but without the plus sign';
        $t->assert_text_not_contains($test_name, $res_html, $plus_span);
        // a flagged row shows the plus sign e.g. the deviation of the portfolio split
        $test_name = 'the number of a signed row shows the plus sign';
        $dto_row = new data_object();
        $dto_row->online = false;
        $dto_row->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_row->phr_lst = $t_phr->list_calculator_signed_row_ui();
        $dto_row->val_lst = $t_val->value_list_calculator_result_ui();
        $row_html = $page->calculation_result($wrd, $msg, $dto_row);
        $t->assert_text_contains($test_name, $row_html, $plus_span);
        // a detail table shows the colour of its column in the cells and its hint below the table
        $dto_style = new data_object();
        $dto_style->online = false;
        $dto_style->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_style->phr_lst = $t_phr->list_calculator_detail_style_ui();
        $dto_style->val_lst = $t_val->value_list_calculator_result_ui();
        $style_html = $page->calculation_result($wrd, $msg, $dto_style);
        $colour_cell = '<' . html_base::TD . ' class="' . styles::CALC_NUMBER . ' ' . styles::CALC_COLUMN_COLOURS[0] . '"';
        $test_name = 'a cell of a detail column with the colour flag has the colour';
        $t->assert_text_contains($test_name, $style_html, $colour_cell);
        $detail_hint = '<' . html_base::P . ' class="' . styles::INPUT_HINT . '">' . triple_names::CALC_DETAIL_HINT;
        $test_name = 'the hint of a detail table is shown below the table';
        $t->assert_text_contains($test_name, $style_html, $detail_hint);
        // negative: without the flag and the hint neither is shown
        $test_name = '... but a cell without the flag has no colour';
        $t->assert_text_not_contains($test_name, $res_html, $colour_cell);
        $test_name = '... and a table without a hint has none';
        $t->assert_text_not_contains($test_name, $res_html, $detail_hint);
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
        // a detail table shows the digits of the rough tier
        $dto_det = clone $dto_res;
        $dto_det->val_lst = $t_val->value_list_calculator_result_ui();
        $det_val = $dto_det->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS, word_names::CANTON, word_names::YEAR_2019]);
        $det_val->number = values::SAMPLE_FLOAT;
        $det_html = $page->calculation_result($wrd, $msg, $dto_det);
        $det_cell = ui_list::CALC_CELL_REPEAT . '2">';
        $test_name = 'a detail table shows the digits of the rough tier';
        $t->assert_text_contains($test_name, $det_html, $det_cell . round(values::SAMPLE_FLOAT) . '</td>');
        // negative: not with the decimals of the default tier
        $test_name = '... and not the decimals of the default tier';
        $t->assert_text_not_contains($test_name, $det_html, $det_cell . round(values::SAMPLE_FLOAT) . '.');
        // a hint of a row names phrases, which are shown with the live numbers of the page
        $list_hint = new ui_list();
        $lst_hint = $dto_res->val_lst;
        $inh_num = '{' . word_names::INHABITANTS . '|number}';
        $test_name = 'a phrase of a row hint is shown as its number without a unit, but with the thousands separator';
        $hint_txt = $list_hint->calculator_hint_numbers('a ' . $inh_num . ' b', $wrd, $dto_res, $lst_hint, $msg);
        $inh_txt = number_format($dto_res->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS])->number(), 0, '', shared_config::DEFAULT_THOUSAND_SEP);
        $t->assert($test_name, $hint_txt, 'a ' . $inh_txt . ' b');
        $test_name = '... and a hint without a phrase stays as it is';
        $t->assert($test_name, $list_hint->calculator_hint_numbers('no number', $wrd, $dto_res, $lst_hint, $msg), 'no number');
        // negative: half a calculation is misleading, so a phrase without a number hides the hint
        $test_name = '... but a hint with a phrase without a number is empty';
        $missing_hint = '{' . word_names::INHABITANTS . '} {System Test Phrase Without A Number}';
        $t->assert($test_name, $list_hint->calculator_hint_numbers($missing_hint, $wrd, $dto_res, $lst_hint, $msg), '');
        $msg->reset();
        // the source number of a field is at the marker of its hint and else behind the hint
        $test_name = 'the source number is shown at the marker of a hint';
        $t->assert($test_name, $list_hint->calculator_hint('a ' . ui_list::CALC_HINT_SOURCE . '; b', '[1]'), $html->span('a [1]; b', styles::INPUT_HINT));
        $test_name = '... and behind a hint without a marker';
        $t->assert($test_name, $list_hint->calculator_hint('a; b', '[1]'), $html->span('a; b [1]', styles::INPUT_HINT));
        // negative: without a source the marker is not shown
        $test_name = '... but the marker is removed without a source';
        $t->assert($test_name, $list_hint->calculator_hint('a ' . ui_list::CALC_HINT_SOURCE . '; b'), $html->span('a; b', styles::INPUT_HINT));
        // a fact that does not name the page e.g. the Swiss consumption mix of a month is shown in its detail cell
        $bern_cell = $html->block(html_base::TD, word_names::BE) . '<' . html_base::TD . ' class="' . styles::CALC_NUMBER . '" id="';
        $dto_gen = clone $dto_res;
        $dto_gen->val_lst = $t_val->value_list_bern_canton_ui(false);
        $gen_html = $page->calculation_result($wrd, $msg, $dto_gen);
        $test_name = 'a detail cell shows the one value that does not name the page';
        $t->assert_text_contains($test_name, $gen_html, $bern_cell);
        // negative: of several values none is picked, because the number would depend on the list
        $dto_gen->val_lst = $t_val->value_list_bern_canton_ui(true);
        $two_gen_html = $page->calculation_result($wrd, $msg, $dto_gen);
        $test_name = '... but not one of several values';
        $t->assert_text_not_contains($test_name, $two_gen_html, $bern_cell);
        $test_name = 'the sources are numbered in the order of the first use';
        $t->assert_text_contains($test_name, $res_html, '<div class="' . styles::CALC_SOURCES . '">'
            . $html->block(html_base::H2, $mtr->txt(msg_id::CALCULATOR_SOURCES)) . '<' . html_base::OL . '>');
        $t->assert_text_contains($test_name, $res_html, $src->name());
        $test_name = '... and the hint of a field with a value of a source names its number';
        $t->assert_text_contains($test_name, $page->calculator_input($wrd, $msg, $dto_res, $form, views::CALCULATOR, []),
            $html->span(triple_names::CALC_INHABITANTS_HINT_COM . ' [1]', styles::INPUT_HINT));
        // an assumed field value is based on the measured values of its field phrase with the same unit,
        // so its hint names their sources e.g. the literature values of a battery production emission
        $dto_basis = clone $dto_res;
        $dto_basis->val_lst = $t_val->value_list_calculator_result_ui();
        $dto_basis->val_lst->get_by_names([word_names::ZH, word_names::INHABITANTS, word_names::CANTON, word_names::YEAR_2019])->src = $src;
        $test_name = 'the hint of an assumed field names the source of a measured value of its phrase and unit';
        $t->assert_text_contains($test_name, $page->calculator_input($wrd, $msg, $dto_basis, $form, views::CALCULATOR, []),
            $html->span(triple_names::CALC_INHABITANTS_HINT_COM . ' [1]', styles::INPUT_HINT));
        // negative: a property of the field e.g. its lower limit is no measured value that the field value is based on
        $dto_prop = clone $dto_res;
        $dto_prop->val_lst = $t_val->value_list_calculator_result_ui();
        $dto_prop->val_lst->get_by_names([word_names::INHABITANTS, triple_names::FIELD_LIMIT_DOWN])->src = $src;
        $test_name = '... but not the source of a property of the field';
        $t->assert_text_contains($test_name, $page->calculator_input($wrd, $msg, $dto_prop, $form, views::CALCULATOR, []),
            $html->span(triple_names::CALC_INHABITANTS_HINT_COM, styles::INPUT_HINT));
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
        // the results of a missing operand are loaded only within the context of the results that need them
        $dto_ctx = new data_object();
        $wide_ctx = [$zh_city_id, word_names::INHABITANT_ID];
        $narrow_ctx = [$zh_city_id];
        $test_name = 'the results within a context are not loaded before their load';
        $has_before = $dto_ctx->has_results_within($zh_city_id, $narrow_ctx);
        $t->assert_false($test_name, $has_before);
        $dto_ctx->add_results_within([$zh_city_id], $wide_ctx, $zh_res_lst);
        $test_name = '... but after the load within a context that includes it';
        $has_narrow = $dto_ctx->has_results_within($zh_city_id, $narrow_ctx);
        $t->assert_true($test_name, $has_narrow);
        // negative: a wider context can need results that the load has left out
        $test_name = '... and not for a wider context';
        $has_wider = $dto_ctx->has_results_within($zh_city_id, [...$wide_ctx, word_names::YEAR_2019_ID]);
        $t->assert_false($test_name, $has_wider);
        $test_name = '... and not as all results of the phrase';
        $has_all = $dto_ctx->has_results_of($zh_city_id);
        $t->assert_false($test_name, $has_all);
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
        $t->assert($test_name, $list->calculation_table($calc_empty, $wrd, new value_list_ui(), $dto_off, $msg), '');
        $test_name = 'an offline page without values has no calculator numbers';
        $t->assert($test_name, $list->calculator_numbers($wrd, $dto_off, $msg)->count(), 0);
        $msg->reset();
        // with javascript for the calculator the page gets its calculation as data and the script
        // that repeats it in the browser when an input changes (docs/llm/frontend.md)
        $lib = new library();
        $js_list = new ui_list();
        $js_calc = new calculator($wrd->phrase(), $dto_res->phr_lst);
        $js_lst = $js_list->calculator_numbers($wrd, $dto_res, $msg);
        $js_list->calculation_table($js_calc, $wrd, $js_lst, $dto_res, $msg);
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
        // a page that the script builds saves the same as the page of the server, so the plan names
        // the save vars of each number field (calculator_save_vars)
        $test_name = '... the save vars of each number field';
        $inh_save = $plan[ui_list::CALC_FIELDS][$inh_name][ui_list::CALC_FIELD_SAVE] ?? [];
        $t->assert($test_name, $inh_save, [
            url_var::GROUP . $inh_id => $inh_val_id,
            url_var::PRE . url_var::NUMERIC_VALUE . $inh_id => $inh_shown,
            url_var::NAME . $inh_id => triple_names::CALC_INHABITANTS_LABEL_COM
        ]);
        // a selector sets the number of the option that the page phrase uses, which is Zurich (city)
        // with "Zurich (city) uses 2020" and not Zurich, so the plan is the one of Zurich (city), whose
        // calculator of the input test has the same inputs as the calculation result test
        $test_name = '... and of a phrase selector the number of the option that the page uses';
        $city_script = new ui_list()->calculator_script($calc_in, $zh_city, $js_lst, $dto_res, $msg);
        $city_plan = json_decode($lib->str_left_of($lib->str_right_of($city_script, ui_list::CALC_DATA_ID . '">'), '</script>'), true);
        $canton_fld = $city_plan[ui_list::CALC_FIELDS][url_var::NUMERIC_VALUE . $canton_id] ?? [];
        $canton_num = $canton_fld[ui_list::CALC_CELL_NUMBER] ?? '';
        $canton_opt_id = $dto_res->val_lst->get_by_names([word_names::CANTON, word_names::YEAR_2020])->html_id();
        $t->assert($test_name, $canton_num, $canton_opt_id);
        // negative: a phrase selector selects a phrase and is never saved as a number
        $test_name = '... but no save vars of a phrase selector';
        $t->assert_key_not_exists($test_name, ui_list::CALC_FIELD_SAVE, $canton_fld);
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
        // the page embeds the phraseData answer of its cache and the verbs and units with the ids of
        // the editor, so that one page script can build the editor preview and the calculator page
        $test_name = '... the phrase data of the page as the seed';
        $dto_seed = clone $dto_res;
        $dto_seed->page_data = [json_fields::SELECTION => [[json_fields::NAME => word_names::ZH]]];
        $seed_html = $page->calculation_result($wrd, $msg, $dto_seed);
        $t->assert_text_contains($test_name, $seed_html, $html->script_data(system_page::CALC_EDITOR_SEED_ID, $dto_seed->page_data));
        $test_name = '... and the verbs and units of the type cache';
        $t->assert_text_contains($test_name, $seed_html, '<script type="application/json" id="' . system_page::CALC_EDITOR_TYPES_ID . '">');
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
     * the own columns and the own sorting of a table and the page of the table editor
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     */
    private function table_editor_tests(test_cleanup $t, string $ts): void
    {
        global $ui_sys;
        $html = new html_base();
        $page = new system_page();
        $msg = new user_message();
        $t_phr = new test_phrases($t);
        $zh_city = new test_triples($t)->zh_city_ui();
        $dto = new data_object();
        $dto->online = false;
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto->phr_lst = $t_phr->list_table_definition_ui();
        $dto->val_lst = new test_values($t)->value_list_calculator_input_ui();
        $calc = new calculator($zh_city->phrase(), $dto->phr_lst);
        $calc_base = new calculator(new test_words($t)->zh_ui()->phrase(), $dto->phr_lst);
        $no_def_lst = $t_phr->list_calculator_input_ui();

        $t->subheader($ts . 'table editor');
        $test_name = 'a table has its own columns in their order';
        $t->assert($test_name, $calc->table_columns(), [word_names::YEAR_2020, word_names::YEAR_2019]);
        $test_name = '... and its own sorting';
        $sorts = $calc->table_sorts();
        $t->assert($test_name, $sorts[0]?->name(), triple_names::TABLE_SORT_ORDER);
        // negative: a definition is only valid for its own phrase, not for the phrase it is built from
        $test_name = '... but another phrase has none';
        $t->assert($test_name, $calc_base->table_columns(), []);
        $t->assert($test_name, $calc_base->table_sorts(), []);
        $test_name = '... and a table without a definition too';
        $t->assert($test_name, new calculator($zh_city->phrase(), $no_def_lst)->table_columns(), []);

        $editor_html = $page->table_editor($zh_city, $msg, $dto);
        $test_name = 'the table editor loads its script';
        $t->assert_text_contains($test_name, $editor_html, $html->script_src(files::TABLE_EDITOR_SCRIPT));
        $test_name = '... the seed with the keywords';
        $t->assert_text_contains($test_name, $editor_html, triples::TABLE_COLUMN);
        $test_name = '... and the page of the server for a browser without javascript';
        $t->assert_text_contains($test_name, $editor_html, url_var::JAVASCRIPT . '=' . url_var::FALSE);
        // negative: without a phrase the view shows its phrase selector
        $test_name = '... but no script without a phrase';
        $t->assert($test_name, $page->table_editor(null, $msg, $dto), '');
        $test_name = '... and without a request cache';
        $t->assert($test_name, $page->table_editor($zh_city, $msg, null), '');
    }

    /**
     * the parts of a page for a decision, each compared with a page that does not have it
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     */
    private function calculator_decision_tests(test_cleanup $t, string $ts): void
    {
        global $mtr;
        global $ui_sys;
        $html = new html_base();
        $list = new ui_list();
        $page = new system_page();
        $msg = new user_message();
        $t_phr = new test_phrases($t);
        $t_val = new test_values($t);
        $t_wrd = new test_words($t);
        $zh_city = new test_triples($t)->zh_city_ui();
        $dto = new data_object();
        $dto->online = false;
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto->phr_lst = $t_phr->list_calculator_decision_ui();
        $dto->val_lst = $t_val->value_list_calculator_input_ui();
        $calc = new calculator($zh_city->phrase(), $dto->phr_lst);
        $dto_no = new data_object();
        $dto_no->online = false;
        $dto_no->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto_no->phr_lst = $t_phr->list_calculator_input_ui();
        $dto_no->val_lst = $t_val->value_list_calculator_input_ui();
        $calc_no = new calculator($zh_city->phrase(), $dto_no->phr_lst);

        $t->subheader($ts . 'calculator arguments');
        $more = $mtr->txt(msg_id::AND_MORE);
        $arg_html = $list->calculator_arguments($calc, $zh_city, $dto, $msg);
        $test_name = 'the lead text is collapsed';
        $t->assert_text_contains($test_name, $arg_html, $html->details($html->esc($more), $html->p(triple_names::CALC_MORE_COM)));
        $test_name = 'the first argument of a side is shown';
        $first_li = $html->block(html_base::LI, $html->bold(triple_names::CALC_PRO_FIRST) . ' ' . triple_names::CALC_PRO_FIRST_COM);
        $t->assert_text_contains($test_name, $arg_html, $html->block(html_base::UL, $first_li, styles::CALC_ARGUMENT_LIST));
        $test_name = '... and the others are collapsed';
        $second_li = $html->block(html_base::LI, $html->bold(triple_names::CALC_PRO_SECOND) . ' ' . triple_names::CALC_PRO_SECOND_COM);
        $collapsed = $html->details($html->esc($more), $html->block(html_base::UL, $second_li, styles::CALC_ARGUMENT_LIST));
        $t->assert_text_contains($test_name, $arg_html, $collapsed);
        $test_name = 'a limiting argument is on the other side';
        $t->assert_text_contains($test_name, $arg_html, triple_names::CALC_CONTRA_COM);
        $test_name = '... which has its own heading';
        $t->assert_text_contains($test_name, $arg_html, $html->block(html_base::H2, 'Against'));
        // negative: a page without the keywords has neither the lead text nor the arguments
        $test_name = 'a page without arguments shows nothing';
        $t->assert($test_name, $list->calculator_arguments($calc_no, $zh_city, $dto_no, $msg), '');
        $test_name = 'the title shows the arguments';
        $t->assert_text_contains($test_name, $page->calculator_title($zh_city, $msg, $dto), triple_names::CALC_CONTRA_COM);
        $test_name = '... but not without a request cache';
        $t->assert_text_not_contains($test_name, $page->calculator_title($zh_city, $msg, null), triple_names::CALC_CONTRA_COM);

        // a preset and a slider need the script, so the page of the server has no control without a function
        $t->subheader($ts . 'calculator preset and slider');
        $input_html = $page->calculator_input($zh_city, $msg, $dto, views::CALCULATOR, views::CALCULATOR, []);
        $test_name = 'the page of the server shows the input with a slider flag';
        $t->assert_text_contains($test_name, $input_html, 'type="number"');
        $test_name = '... but no slider';
        $t->assert_text_not_contains($test_name, $input_html, 'type="range"');
        $test_name = '... and no preset';
        $t->assert_text_not_contains($test_name, $input_html, triple_names::CALC_PRESET_LABEL);

        $t->subheader($ts . 'calculator verdict');
        $yes_class = '<div class="' . styles::CALC_VERDICT . ' ' . ui_list::CALC_VERDICT_STATES[0] . '"';
        $dto->val_lst = $t_val->value_list_calculator_verdict_ui(-50);
        $vd_html = $list->calculator_verdict($calc, $zh_city, $dto, $dto->val_lst, $msg);
        $test_name = 'a number below minus the threshold selects the first state';
        $t->assert_text_contains($test_name, $vd_html, $yes_class);
        $test_name = '... with its label';
        $t->assert_text_contains($test_name, $vd_html, triple_names::CALC_VERDICT_YES);
        $test_name = '... and the note';
        $t->assert_text_contains($test_name, $vd_html, triple_names::CALC_VERDICT_NOTE);
        $test_name = '... and the needle at its place on the meter';
        $t->assert_text_contains($test_name, $vd_html, 'left: 25%');
        $dto->val_lst = $t_val->value_list_calculator_verdict_ui(50);
        $test_name = 'a number above the threshold selects the second state';
        $vd_no_html = $list->calculator_verdict($calc, $zh_city, $dto, $dto->val_lst, $msg);
        $t->assert_text_contains($test_name, $vd_no_html, '<div class="' . styles::CALC_VERDICT . ' ' . ui_list::CALC_VERDICT_STATES[1] . '"');
        $test_name = '... and not the first';
        $t->assert_text_not_contains($test_name, $vd_no_html, $yes_class);
        $test_name = 'a result placeholder shows the named decimals';
        $t->assert($test_name, $list->calculator_text('{result:' . word_names::INHABITANTS . ':1}', $zh_city, $dto, $dto->val_lst, [], $msg), '50.0');
        $test_name = '... and no decimals if it names none';
        $t->assert($test_name, $list->calculator_text('{result:' . word_names::INHABITANTS . '}', $zh_city, $dto, $dto->val_lst, [], $msg), '50');
        // a result placeholder names a number of the page, which a study value of the same phrase must not hide
        $test_name = 'a result placeholder shows the number of the page before the value of a study';
        $page_lst = $dto->val_lst;
        $dto->val_lst = $t_val->value_list_calculator_study_ui();
        $result_text = $list->calculator_text('{result:' . word_names::INHABITANTS . '}', $zh_city, $dto, $page_lst, [], $msg);
        $t->assert($test_name, $result_text, '50');
        // negative: without a number of the page the placeholder shows what a field of the phrase would show
        $test_name = '... but the value of the study without a number of the page';
        $no_page_lst = $t_val->value_list_calculator_option_ui();
        $study_text = $list->calculator_text('{result:' . word_names::INHABITANTS . '}', $zh_city, $dto, $no_page_lst, [], $msg);
        $t->assert($test_name, $study_text, '70');
        $dto->val_lst = $t_val->value_list_calculator_verdict_ui(5);
        $test_name = 'a number within the threshold selects the third state';
        $vd_open_html = $list->calculator_verdict($calc, $zh_city, $dto, $dto->val_lst, $msg);
        $t->assert_text_contains($test_name, $vd_open_html, '<div class="' . styles::CALC_VERDICT . ' ' . ui_list::CALC_VERDICT_STATES[2] . '"');
        $dto->val_lst = $t_val->value_list_calculator_verdict_ui(500);
        $test_name = 'a number outside the meter keeps the needle on the meter';
        $t->assert_text_contains($test_name, $list->calculator_verdict($calc, $zh_city, $dto, $dto->val_lst, $msg), 'left: 99%');
        // negative: no state instead of a wrong one if the numbers are missing
        $test_name = 'a verdict without a number shows nothing';
        $dto->val_lst = $t_val->value_list_calculator_option_ui();
        $t->assert($test_name, $list->calculator_verdict($calc, $zh_city, $dto, $dto->val_lst, $msg), '');
        $test_name = '... and a page without a verdict too';
        $t->assert($test_name, $list->calculator_verdict($calc_no, $zh_city, $dto_no, $dto_no->val_lst, $msg), '');

        $t->subheader($ts . 'calculator cell and difference');
        $y2019 = $dto->phr_lst->get_by_name(word_names::YEAR_2019, $msg);
        $y2020 = $dto->phr_lst->get_by_name(word_names::YEAR_2020, $msg);
        $row_be = $dto->phr_lst->get_by_name(triple_names::CALC_ZH_ROW_BE, $msg);
        $row_inh = $dto->phr_lst->get_by_name(triple_names::CALC_ZH_ROW_INHABITANTS, $msg);
        $test_name = 'a cell shows the phrase of its column';
        $t->assert($test_name, $calc->cell_phrase($row_be, $y2019)?->name(), word_names::INHABITANTS);
        $test_name = '... but not in another column';
        $t->assert_null($test_name, $calc->cell_phrase($row_be, $y2020)?->name());
        $test_name = '... or without a column';
        $t->assert_null($test_name, $calc->cell_phrase($row_be, null)?->name());
        $test_name = '... and a row without a cell has none';
        $t->assert_null($test_name, $calc->cell_phrase($row_inh, $y2019)?->name());
        $test_name = 'a column with the flag is a difference';
        $t->assert_true($test_name, $calc->is_difference($y2020));
        $test_name = '... and a column without the flag is none';
        $t->assert_false($test_name, $calc->is_difference($y2019));
        $test_name = '... and no column too';
        $t->assert_false($test_name, $calc->is_difference(null));
        $vals = $t_val->value_list_calculator_verdict_ui(50);
        $first = $vals->get_by_names([$zh_city->name(), word_names::INHABITANTS]);
        $second = $vals->get_by_names([$zh_city->name(), word_names::YEAR_2019]);
        $test_name = 'the difference is the first minus the second number';
        $diff = $list->calculator_difference($first, $second);
        $t->assert($test_name, $diff?->number(), 40.0);
        $test_name = '... and keeps the first number';
        $t->assert($test_name, $first->number(), 50.0);
        $test_name = '... and is empty if a number is missing';
        $t->assert_null($test_name, $list->calculator_difference($first, null)?->number());
        $t->assert_null($test_name, $list->calculator_difference(null, $second)?->number());

        $t->subheader($ts . 'calculator sensitivity');
        $sens_html = $list->calculator_sensitivity($calc);
        $test_name = 'the sensitivity table is collapsed with its label';
        $t->assert_text_contains($test_name, $sens_html, triple_names::CALC_SENSITIVITY_LABEL);
        $test_name = '... the label of a column';
        $t->assert_text_contains($test_name, $sens_html, triple_names::CALC_LEVEL_COLUMN_LABEL);
        $test_name = '... the label of a row';
        $t->assert_text_contains($test_name, $sens_html, triple_names::CALC_LEVEL_ROW_LABEL);
        $test_name = '... and an empty cell, which only the script of the page fills';
        $t->assert_text_contains($test_name, $sens_html, $html->block(html_base::TD, '', styles::CALC_NUMBER));
        $test_name = 'a page without the table has none';
        $t->assert($test_name, $list->calculator_sensitivity($calc_no), '');

        $t->subheader($ts . 'calculator case');
        $test_name = 'a page built from another phrase has a base';
        $t->assert_true($test_name, $calc->has_base());
        $test_name = '... and a word has none';
        $calc_word = new calculator($t_wrd->zh_ui()->phrase(), $dto->phr_lst);
        $t->assert_false($test_name, $calc_word->has_base());
        $test_name = 'a page with the verb of is the case of its to phrase';
        $case_phr = $dto->phr_lst->get_by_name(triple_names::CALC_CASE_PAGE, $msg);
        $t->assert($test_name, new calculator($case_phr, $dto->phr_lst)->case_name(), word_names::BE);
        $test_name = '... but another triple is no case';
        $t->assert($test_name, $calc->case_name(), '');
        $test_name = '... and a word too';
        $t->assert($test_name, $calc_word->case_name(), '');
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
        $test_name = 'a number entered in percent is saved as the stored ratio';
        $pct_form = $form;
        $pct_form[url_var::NUMERIC_VALUE . $changed] = '35';
        $pct_form[url_var::PHRASE . $changed] = sandbox_value::UNIT_PERCENT;
        $pct_changes = $ui->calculator_changes($pct_form, $msg);
        $pct_number = $pct_changes[$changed]->number();
        $t->assert($test_name, $pct_number, 0.35);
        // negative: the number of a field with another unit is saved as it is
        $test_name = '... but a number with another unit as it is';
        $unit_form = $form;
        $unit_form[url_var::NUMERIC_VALUE . $changed] = '35';
        $unit_changes = $ui->calculator_changes($unit_form, $msg);
        $unit_number = $unit_changes[$changed]->number();
        $t->assert($test_name, $unit_number, 35.0);

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
        // negative: an entered number with a trailing zero is shown in the format of the shown number
        $test_name = '... in the same format as the shown number';
        $zero_form = $no_unit_form;
        $zero_form[url_var::NUMERIC_VALUE . $changed] = values::SAMPLE_FLOAT_TRAILING_ZERO_FORM;
        $zero_txt = $lib->msg_var_text([$ui->calculator_saved_text($zero_form, $changed)]);
        $t->assert($test_name, $zero_txt, $no_unit_expected);
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
        $act_msg = new user_message();
        $act_msg->usr = new user_ui();
        $next_url = $ui->url_to_action($same_form, $act_msg, $ui->dto, false);
        $t->assert($test_name, $next_url, [url_var::MASK => views::CALCULATOR_ID, url_var::ID => -triple_names::CITY_ZH_ID]);
        $test_name = '... and tells the user that nothing has been saved';
        $info = $act_msg->get_last_info();
        $t->assert($test_name, $info, $mtr->txt(msg_id::CALCULATOR_NOTHING_CHANGED));
    }

    /**
     * the user actions of a scenario in the calculator editor snapshot create the update put message of
     * the save, which is compared with the expected message in the test resources; the actions run in a
     * headless browser, so without a browser the tests are skipped with a warning
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     */
    private function calculator_editor_scenario_tests(test_cleanup $t, string $ts): void
    {
        $brw = new headless_browser();
        $browser_name = $brw->start();
        $t->subheader($ts . 'calculator editor scenario in ' . $browser_name);
        if ($browser_name == '') {
            $t->dsp_warning('no headless browser found, so the calculator editor scenario tests are skipped');
        } else {
            $page = $this->scenario_page($brw, test_files::CALCULATOR_EDITOR, test_files::CALCULATOR_EDITOR_SCENARIOS);
            // the scenarios that save a change by the description of the test
            $saved = [
                test_files::SCENARIO_AUTARKY_FORMULA => 'the changed expression of a formula is sent as its user text',
                test_files::SCENARIO_PV_CAPACITY_LABEL => 'the general label of a phrase is sent as the description of its label triple',
                test_files::SCENARIO_PV_CAPACITY_LABEL_UNDO => 'an undone change is not sent, but the change before it',
                test_files::SCENARIO_PV_CAPACITY_RENAME => 'a renamed triple is sent with the triples that link to it',
                test_files::SCENARIO_PV_CAPACITY_NUMBER => 'the changed shown number of a field is sent as the number of its value',
                test_files::SCENARIO_PV_CAPACITY_MAXIMUM => 'a new limit of a field is sent as a new value',
                test_files::SCENARIO_PV_CAPACITY_MINIMUM_CLEARED => 'a cleared limit of a field is sent as a deleted value',
                test_files::SCENARIO_ADD_INPUT => 'a new input field with a new phrase is sent as a new word and new triples',
                test_files::SCENARIO_REMOVE_INPUT => 'a removed input field is sent as deleted triples and a new order triple',
                test_files::SCENARIO_PV_CAPACITY_DOWN => 'a field moved down is sent as new and deleted order triples',
                test_files::SCENARIO_AUTARKY_UP => 'a row moved up is sent as two new and two deleted order triples',
                test_files::SCENARIO_EMBODIED_EMISSION_DOWN => 'a row group moved down is sent as two new and two deleted order triples',
            ];
            foreach ($saved as $scenario => $test_name) {
                $put = $this->scenario_message($brw, $page, $scenario);
                $t->assert_file($test_name, $put,
                    test_paths::WEB_CALCULATOR_EDITOR_RES . test_files::UI_PUT_PREFIX . $scenario . test_files::JSON,
                    test_files::JSON);
            }
            // negative: the scenarios that end without a change send nothing
            $unchanged = [
                test_files::SCENARIO_AUTARKY_FORMULA_UNCHANGED => '... but nothing is sent for the loaded expression typed again',
                test_files::SCENARIO_PV_CAPACITY_LABEL_UNDO_ALL => '... nor for a change that is undone',
                test_files::SCENARIO_ADD_INPUT_WITHOUT_PHRASE => '... nor for a new input field without a phrase',
                test_files::SCENARIO_PV_CAPACITY_UP => '... nor for the first field moved up',
                test_files::SCENARIO_PV_CAPACITY_DOWN_UP => '... nor for a field moved down and up again',
            ];
            foreach ($unchanged as $scenario => $test_name) {
                $t->assert($test_name, $this->scenario_message($brw, $page, $scenario), '');
            }
            // the Definition tab shows the data of the page with the unsaved changes, like the export
            $test_name = 'the Definition tab shows the data of the page as json';
            $json_txt = $this->scenario_message($brw, $page, test_files::SCENARIO_JSON_TAB_LABEL);
            $json = json_decode($json_txt, true) ?? [];
            $t->assert_key_exists($test_name, json_fields::TRIPLES, $json);
            $test_name = '... with the unsaved changes';
            $t->assert_text_contains($test_name, $json_txt, triple_names::PV_CAPACITY_LABEL_COM_CHANGED);
            // negative: without a change the json has the label of the database
            $test_name = '... but not a change that has not been made';
            $plain_txt = $this->scenario_message($brw, $page, test_files::SCENARIO_JSON_TAB);
            $t->assert_text_not_contains($test_name, $plain_txt, triple_names::PV_CAPACITY_LABEL_COM_CHANGED);
            // the PDF tab posts the data of the page with the unsaved changes and the token of the session to the pod
            $test_name = 'the PDF tab posts the data of the page to the pdf entry point of the pod';
            $pdf_post = json_decode($this->scenario_message($brw, $page, test_files::SCENARIO_PDF_TAB_LABEL), true) ?? [];
            $pdf_route = $pdf_post[self::PDF_POST_ROUTE] ?? '';
            $t->assert($test_name, $pdf_route, api::CALCULATOR_PDF_SCRIPT);
            $test_name = '... with the token of the session';
            $token_sent = $pdf_post[self::PDF_POST_TOKEN_SENT] ?? false;
            $t->assert_true($test_name, $token_sent);
            $test_name = '... and the unsaved changes';
            $pdf_data = $pdf_post[self::PDF_POST_BODY] ?? [];
            $pdf_body = json_encode($pdf_data, JSON_UNESCAPED_UNICODE);
            $t->assert_text_contains($test_name, $pdf_body, triple_names::PV_CAPACITY_LABEL_COM_CHANGED);
            // negative: a page that is not on a pod cannot create the pdf
            $test_name = '... but a page that is not on a pod offers no pdf';
            $offline = json_decode($this->scenario_message($brw, $page, test_files::SCENARIO_PDF_TAB_OFFLINE), true) ?? [];
            $disabled = $offline[self::PDF_BUTTON_DISABLED] ?? false;
            $t->assert_true($test_name, $disabled);
            $t->assert_true($test_name, $offline[self::PDF_BUTTON_DISABLED] ?? false);
            // a click on a detail table in the preview selects its column, and a row its table, which defines the rows
            $clicks = json_decode($this->scenario_message($brw, $page, test_files::SCENARIO_PREVIEW_DETAIL_PARTS), true) ?? [];
            $test_name = 'a click on a column heading of a detail table in the preview selects the detail column';
            $detail_column = $clicks[self::CLICK_COLUMN] ?? '';
            $t->assert($test_name, $detail_column, self::SHOWN_DETAIL_COLUMN);
            $test_name = '... and on a row of the table phrase the table';
            $detail_row = $clicks[self::CLICK_ROW] ?? '';
            $t->assert($test_name, $detail_row, self::SHOWN_DETAIL_TABLE);
            // negative: the heading of the sources has no part, so the click keeps what is selected
            $test_name = '... but a click on the heading of the sources keeps the selection';
            $heading = $clicks[self::CLICK_HEADING] ?? '';
            $t->assert($test_name, $heading, self::SHOWN_DETAIL_TABLE);
            $test_name = 'a click on a source selects the source';
            $source = $clicks[self::CLICK_SOURCE] ?? '';
            $t->assert($test_name, $source, self::SHOWN_SOURCE_PV);
            $test_name = '... and its link does not leave the editor';
            $stays = $clicks[self::CLICK_SOURCE_STAYS] ?? false;
            $t->assert_true($test_name, $stays);
            unlink($page);
        }
        $brw->stop();
    }

    /**
     * the pro and contra arguments in the calculator editor of the Swiss NPP vote: the section starts collapsed above
     * the inputs, and a changed, moved, added or removed argument is sent as the change of its claim triple, like the
     * changed hideable lead text of the page as the change of its triple; the
     * actions run in a headless browser, so without a browser the tests are skipped with a warning
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     */
    private function calculator_editor_argument_tests(test_cleanup $t, string $ts): void
    {
        $brw = new headless_browser();
        $browser_name = $brw->start();
        $t->subheader($ts . 'calculator editor arguments in ' . $browser_name);
        if ($browser_name == '') {
            $t->dsp_warning('no headless browser found, so the calculator editor argument tests are skipped');
        } else {
            $page = $this->scenario_page($brw, test_files::NPP_CH_VOTE_EDITOR, test_files::CALCULATOR_EDITOR_SCENARIOS);
            $lists = json_decode($this->scenario_message($brw, $page, test_files::SCENARIO_ARGUMENTS_COLLAPSED), true) ?? [];
            $test_name = 'the arguments are the first section of the page structure';
            $first_list = $lists[0][self::TREE_LIST] ?? '';
            $t->assert($test_name, $first_list, self::TREE_LIST_ARGUMENT);
            $test_name = '... and collapsed';
            $first_open = $lists[0][self::TREE_LIST_OPEN] ?? true;
            $t->assert_false($test_name, $first_open);
            // negative: the inputs below stay open, so only the arguments are collapsed
            $test_name = '... but the inputs below them are open';
            $second_open = $lists[1][self::TREE_LIST_OPEN] ?? false;
            $t->assert_true($test_name, $second_open);
            $saved = [
                test_files::SCENARIO_ARGUMENT_TEXT => 'the changed text of an argument is sent as the description of its claim',
                test_files::SCENARIO_ARGUMENT_SIDE => 'an argument moved to the other side is sent as its removed triples and the added ones with the other verb',
                test_files::SCENARIO_ADD_ARGUMENT => 'a new argument with a new reason is sent as a new word and new triples',
                test_files::SCENARIO_REMOVE_ARGUMENT => 'a removed argument is sent as its deleted triple and order triple, while its claim stays',
                test_files::SCENARIO_MORE_TEXT => 'the changed hideable lead text is sent as the description of its triple',
                test_files::SCENARIO_VERDICT_LABEL => 'the changed headline of a verdict state is sent as the label of the state',
                test_files::SCENARIO_VERDICT_SCALE_NOTE => 'the changed scale and note of the verdict box are sent as the descriptions of their triples',
                test_files::SCENARIO_VERDICT_RESULT => 'another result of the verdict is sent as the removed result triple and the added one with the other phrase',
                test_files::SCENARIO_VERDICT_SCALE_LIMITS => 'the new ends of the meter are sent as the changed limit values of the scale',
                test_files::SCENARIO_SENSITIVITY_NUMBER => 'the changed number of an axis value of the sensitivity table is sent as its value',
                test_files::SCENARIO_SENSITIVITY_ADD_LEVEL => 'a new axis value is sent as a new word, its triples and a value with the unit of the axis',
                test_files::SCENARIO_SENSITIVITY_CELL_HINT => 'another result in brackets is sent as the removed and the added cell triple, the hint of the table as its changed triple',
                test_files::SCENARIO_PRESET_LABEL => 'the changed text of a preset button is sent as the label of the preset',
                test_files::SCENARIO_PRESET_VALUE => 'a changed number of a preset is sent as its value',
                test_files::SCENARIO_PRESET_NEW_VALUE => 'a new number of a preset is sent as a new value with the unit of the field',
                test_files::SCENARIO_PRESET_VALUE_CLEARED => 'a cleared number of a preset is sent as a deleted value',
                test_files::SCENARIO_ADD_PRESET => 'a new preset is sent as a new word, its triples and its numbers',
                test_files::SCENARIO_REMOVE_PRESET => 'a removed preset is sent as its deleted triples, while the numbers of its scenario stay',
                test_files::SCENARIO_NOTE_TEXT => 'the changed text of a note is sent as the description of its triple',
                test_files::SCENARIO_SOURCE_DESCRIPTION => 'the changed description of a source is sent as the changed field of the source',
                test_files::SCENARIO_SOURCE_RENAME => 'a renamed source is sent with the texts that name it by its placeholder',
            ];
            foreach ($saved as $scenario => $test_name) {
                $put = $this->scenario_message($brw, $page, $scenario);
                $t->assert_file($test_name, $put,
                    test_paths::WEB_CALCULATOR_EDITOR_RES . test_files::UI_PUT_PREFIX . $scenario . test_files::JSON,
                    test_files::JSON);
            }
            // negative: an argument without a reason has no claim, so nothing is sent
            $test_name = '... but nothing for a new argument without a reason';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_ADD_ARGUMENT_WITHOUT_REASON), '');
            // negative: the loaded lead text typed again is no change
            $test_name = '... nor for the loaded hideable lead text typed again';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_MORE_TEXT_UNCHANGED), '');
            $test_name = '... nor for the loaded result of the verdict typed again';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_VERDICT_RESULT_UNCHANGED), '');
            $test_name = '... nor for the loaded end of the meter typed again';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_VERDICT_SCALE_LIMITS_UNCHANGED), '');
            $test_name = '... nor for a new axis value with a number but without a phrase';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_SENSITIVITY_ADD_LEVEL_WITHOUT_PHRASE), '');
            $test_name = '... nor for a new preset with a button text but without a scenario';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_ADD_PRESET_WITHOUT_PHRASE), '');
            $test_name = '... nor for the loaded text of a note typed again';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_NOTE_TEXT_UNCHANGED), '');
            $test_name = '... nor for the loaded link of a source typed again';
            $t->assert($test_name, $this->scenario_message($brw, $page, test_files::SCENARIO_SOURCE_URL_UNCHANGED), '');
            $this->assert_preview_parts($t, $brw, $page);
            unlink($page);
        }
        $brw->stop();
    }

    /**
     * a click on a part of the page preview selects where it is changed: an argument, the verdict, a field, a preset
     * button, a row group, a result row, a result column and a row of the sensitivity table their item, the hideable
     * lead text the page settings without closing it again, a source the source, a note of the notes
     * list the note, a note of a formula the formula, and a part without an item e.g. the heading of the sources keeps
     * the selection
     *
     * @param test_cleanup $t the test environment with the test users
     * @param headless_browser $brw the started headless browser
     * @param string $page the path of the editor page with the scenarios
     */
    private function assert_preview_parts(test_cleanup $t, headless_browser $brw, string $page): void
    {
        $clicks = json_decode($this->scenario_message($brw, $page, test_files::SCENARIO_PREVIEW_PARTS), true) ?? [];
        $test_name = 'a click on an argument in the preview selects the argument';
        $argument = $clicks[self::CLICK_ARGUMENT] ?? '';
        $t->assert($test_name, $argument, self::SHOWN_ARGUMENT);
        $test_name = '... on the verdict the state that it shows';
        $verdict = $clicks[self::CLICK_VERDICT] ?? '';
        $t->assert($test_name, $verdict, self::SHOWN_VERDICT);
        // negative: the heading of the sources is no part, so the click keeps what is selected
        $test_name = '... but a click on the heading of the sources keeps the selection';
        $heading = $clicks[self::CLICK_HEADING] ?? '';
        $t->assert($test_name, $heading, self::SHOWN_VERDICT);
        $test_name = 'a click on a source selects the source';
        $source = $clicks[self::CLICK_SOURCE] ?? '';
        $t->assert($test_name, $source, self::SHOWN_SOURCE_NPP);
        $test_name = 'a click on a field selects the input field';
        $field = $clicks[self::CLICK_FIELD] ?? '';
        $t->assert($test_name, $field, self::SHOWN_FIELD);
        $test_name = '... on a preset button the preset';
        $preset = $clicks[self::CLICK_PRESET] ?? '';
        $t->assert($test_name, $preset, self::SHOWN_PRESET);
        $test_name = 'a click on a row group of the result table selects the row group';
        $row_group = $clicks[self::CLICK_ROW_GROUP] ?? '';
        $t->assert($test_name, $row_group, self::SHOWN_ROW_GROUP);
        $test_name = '... on a result row the row';
        $row = $clicks[self::CLICK_ROW] ?? '';
        $t->assert($test_name, $row, self::SHOWN_ROW);
        $test_name = '... and on the heading of a column the result column';
        $column = $clicks[self::CLICK_COLUMN] ?? '';
        $t->assert($test_name, $column, self::SHOWN_COLUMN);
        $test_name = 'a click on a row of the sensitivity table selects the value of its axis';
        $level = $clicks[self::CLICK_LEVEL] ?? '';
        $t->assert($test_name, $level, self::SHOWN_LEVEL);
        $test_name = 'a click on the hideable lead text selects the page settings';
        $more = $clicks[self::CLICK_MORE] ?? '';
        $t->assert($test_name, $more, self::SHOWN_PAGE);
        $test_name = '... and opens the text, because the preview is not drawn again';
        $more_open = $clicks[self::CLICK_MORE_OPEN] ?? false;
        $t->assert_true($test_name, $more_open);
        $test_name = 'a click on a note of the notes list selects the note';
        $note = $clicks[self::CLICK_NOTE] ?? '';
        $t->assert($test_name, $note, self::SHOWN_NOTE);
        $test_name = 'a click on a note of a formula selects the formula';
        $formula = $clicks[self::CLICK_FORMULA] ?? '';
        $t->assert($test_name, $formula, self::SHOWN_FORMULA);
        $test_name = '... and shows its compare like a click on the formula in the page structure';
        $compare = $clicks[self::CLICK_COMPARE] ?? false;
        $t->assert_true($test_name, $compare);
    }

    /**
     * the user actions of a scenario in the table editor snapshot create the update put message of the save,
     * the exported csv or the problem that the editor shows, which is compared with the expected text; the
     * actions run in a headless browser, so without a browser the tests are skipped with a warning
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     */
    private function table_editor_scenario_tests(test_cleanup $t, string $ts): void
    {
        global $mtr;
        $brw = new headless_browser();
        $browser_name = $brw->start();
        $t->subheader($ts . 'table editor scenario in ' . $browser_name);
        if ($browser_name == '') {
            $t->dsp_warning('no headless browser found, so the table editor scenario tests are skipped');
        } else {
            $page = $this->scenario_page($brw, test_files::TABLE_EDITOR, test_files::TABLE_EDITOR_SCENARIOS);
            $res_path = test_paths::WEB_RES . test_files::TABLE_EDITOR . DIRECTORY_SEPARATOR;
            // the scenarios that save a change by the description of the test
            $saved = [
                test_files::TABLE_SCENARIO_ADD_COLUMN_TO_TIER => 'a column added to another tier is sent as new follower and tier triples',
                test_files::TABLE_SCENARIO_MOVE_COLUMN => 'a column moved to the left is sent as new and deleted follower triples',
                test_files::TABLE_SCENARIO_REMOVE_COLUMN => 'a removed column is sent as its deleted triples',
                test_files::TABLE_SCENARIO_SORT_COLUMN => 'a sort by a column header is sent as the description of the row order triple',
                test_files::TABLE_SCENARIO_COLUMN_ORDER_RULE => 'a changed choice is sent as a new choice triple and the deleted old one',
                test_files::TABLE_SCENARIO_HEADER_ROTATE => 'a flag that is turned on is sent as a new flag triple',
                test_files::TABLE_SCENARIO_ROW_LIMIT => 'a changed row limit is sent as the number of its value',
                test_files::TABLE_SCENARIO_UNDO => 'an undone change is not sent, but the change before it',
                test_files::TABLE_SCENARIO_REDO => 'a redone change is sent',
                test_files::TABLE_SCENARIO_DISCARD_UNDO => 'an undone discard sends the discarded changes',
                test_files::TABLE_SCENARIO_TITLE => 'a changed title is sent as the description of its parameter triple',
            ];
            foreach ($saved as $scenario => $test_name) {
                $put = $this->scenario_message($brw, $page, $scenario);
                $t->assert_file($test_name, $put,
                    $res_path . test_files::UI_PUT_PREFIX . $scenario . test_files::JSON, test_files::JSON);
            }
            // the scenarios that export the table as csv
            $exported = [
                test_files::TABLE_SCENARIO_EXPORT => 'the export is the shown table as csv with its sources',
                test_files::TABLE_SCENARIO_EXPORT_NO_ROWS => '... and has no row if the search matches no row',
            ];
            foreach ($exported as $scenario => $test_name) {
                $csv = $this->scenario_message($brw, $page, $scenario);
                $t->assert_file($test_name, $csv, $res_path . $scenario . test_files::CSV);
            }
            // negative: the scenarios that end without a change send nothing
            $unchanged = [
                test_files::TABLE_SCENARIO_ADD_COLUMN_USED => '... but nothing is sent for a column of another tier typed into the picker',
                test_files::TABLE_SCENARIO_MOVE_COLUMN_FIRST => '... nor for the first column moved to the left',
                test_files::TABLE_SCENARIO_REMOVE_COLUMN_ADDED_AGAIN => '... nor for a column removed and added again',
                test_files::TABLE_SCENARIO_SORT_LABEL => '... nor for a sort by the label, which is the saved order',
                test_files::TABLE_SCENARIO_ROW_LIMIT_BACK => '... nor for the saved row limit typed again',
                test_files::TABLE_SCENARIO_UNDO_ALL => '... nor for a change that is undone',
                test_files::TABLE_SCENARIO_REDO_AFTER_NEW_STEP => '... nor for a redo after a new step',
                test_files::TABLE_SCENARIO_DISCARD => '... nor for discarded changes',
                test_files::TABLE_SCENARIO_SAVE_UNCHANGED => '... nor for a save without a change',
            ];
            foreach ($unchanged as $scenario => $test_name) {
                $t->assert($test_name, $this->scenario_message($brw, $page, $scenario), '');
            }
            // negative: a refused save shows the reason of the pod
            $test_name = 'a refused save shows the reason of the pod to the user';
            $refused = $this->scenario_message($brw, $page, test_files::TABLE_SCENARIO_SAVE_REFUSED);
            $t->assert($test_name, $refused, $mtr->txt(msg_id::TABLE_EDITOR_REFUSED) . ' ' . self::TABLE_REFUSED_MESSAGE);
            unlink($page);
        }
        $brw->stop();
    }

    /**
     * a copy of an editor snapshot that loads the scripts of this checkout and the scenarios
     *
     * @param headless_browser $brw the started headless browser
     * @param string $page the name of the snapshot in the use case folder e.g. test_files::CALCULATOR_EDITOR
     * @param string $scenarios the path of the scenarios script e.g. test_files::CALCULATOR_EDITOR_SCENARIOS
     * @return string the path of the copy in the temp folder, which the caller removes
     */
    private function scenario_page(headless_browser $brw, string $page, string $scenarios): string
    {
        $html = file_get_contents(test_paths::RESOURCE . test_paths::HTML . test_paths::USE_CASES
            . $page . test_files::HTML);
        return $brw->page_copy($html, $scenarios);
    }

    /**
     * run the user actions of a scenario in the headless browser and read the message of the save
     *
     * @param headless_browser $brw the started headless browser
     * @param string $page the path of the editor page with the scenarios
     * @param string $scenario the name of the scenario e.g. test_files::SCENARIO_AUTARKY_FORMULA
     * @return string the update put message as formatted json with a fixed time, an empty string if nothing is saved,
     *                the text of a scenario that ends without a message e.g. the exported csv
     *                or SCENARIO_NOT_ENDED if the scenario has failed, so that a failure never passes as nothing saved
     */
    private function scenario_message(headless_browser $brw, string $page, string $scenario): string
    {
        $result = $brw->text('file://' . $page . '#' . $scenario, self::PUT_MESSAGE_ID);
        if ($result === null) {
            $result = self::SCENARIO_NOT_ENDED;
        } elseif ($result != '') {
            $json = json_decode($result, true);
            if (is_array($json)) {
                $json[json_fields::TIMESTAMP] = self::PUT_TIMESTAMP;
                $result = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        return $result;
    }

    /**
     * @param phrase_list_ui $cac_kwd the request cache of a calculator test with the page phrase Zurich (city)
     * @param user_message $msg to collect the messages of the reader
     * @return array the names of the input items of the calculator of Zurich (city)
     */
    private function zh_input_names(phrase_list_ui $cac_kwd, user_message $msg): array
    {
        $calc = new calculator($cac_kwd->get_by_name(triple_names::CITY_ZH_NAME, $msg), $cac_kwd);
        return array_map(fn($item) => $item->name(), $calc->items(triples::CALCULATOR_INPUT));
    }

    /**
     * @param string $html the html code of the input fields
     * @return string the text of the first read only number
     */
    private function read_only_number(string $html): string
    {
        $lib = new library();
        $start = '<div class="' . styles::INPUT_READ_ONLY . '">';
        return $lib->str_left_of($lib->str_right_of($html, $start), '</div>');
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
