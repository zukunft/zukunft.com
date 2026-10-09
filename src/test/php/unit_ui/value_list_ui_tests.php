<?php

/*

    test/unit/html/value_list.php - testing of the value list html frontend functions
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_value as sandbox_value_ui;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files as files_shared;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\chart_types;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\table_forms;
use Zukunft\ZukunftCom\main\php\shared\enum\table_orders;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class value_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        // init
        $html = new html_base();
        $tl = new test_lib();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_val = new test_values($t);
        $t_phr = new test_phrases($t);
        $lib = new library();
        $msg = new user_message();
        $msg_ui = new user_message_ui();
        $ui = new frontend('unit ui html reference list');
        $cac_msg = new user_message_ui();
        // the cache is created by the dev user, because the system views set a code id,
        // which the normal test user is not permitted to do (see user::can_set_code_id)
        // TODO Prio 2 check if a user with less permissions can be used
        $dto = $tl->ui_test_cache($t->usr_dev, $t, $cac_msg);
        $ui->set_cache($dto);

        // start the test section (ts)
        $ts = 'unit ui html value list ';
        $t->header($ts);

        // create a test set of phrase
        $phr_inhabitant = $t_wrd->word_inhabitant()->phrase();

        // create a test set of phrase groups
        $phr_lst_context = new phrase_list($t->usr1);
        $phr_lst_context->add($phr_inhabitant);
        $phr_lst_context_ui = new phrase_list_ui($phr_lst_context->api_json());

        // create the value list and the table to display the results
        // TODO move the measure phrase behind the number e.g. speed of light 299'792'458 m/s instead of speed of light m/s 299'792'458
        // TODO format numbers
        // TODO use one phrase for city of Zurich
        // TODO optional "(in mio)" formatting for scale words
        // TODO move time words to column headline
        // TODO use language based plural for inhabitant
        // TODO if the row phrases have parent child relations by default display sub rows e.g. countries and cantons
        // TODO if the col phrases have parent child relations by default display sub col e.g. year and quarter by using a phrase tree object?
        // TODO add buttons to or empty cells for easy adding new related values
        $lst_zh_ui = $t_val->value_list_zh_ui();
        $lst_math_ui = $t_val->value_list_math_ui();

        // TODO add a sample to show a list of words and some values related to the words e.g. all companies with the main ratios

        $test_page = $html->text_h2('Value list display test');
        $test_page .= 'as list: ' . $html->lf() .  $lst_math_ui->list($msg_ui, $phr_lst_context_ui) . '<br>';
        $test_page .= 'as long list: ' . $html->lf() .  $t_val->list_all_ui($msg)->list($msg_ui, $phr_lst_context_ui) . '<br>';
        $test_page .= 'as long list with small page: ' . $html->lf() .  $t_val->list_all_ui($msg)->list($msg_ui, $phr_lst_context_ui, [], '', 4) . '<br><br>';
        $test_page .= 'with units: ' . $html->lf() .  $t_val->list_all_ui($msg)->list_unit($msg_ui,7) . '<br><br>';
        $table_html = $t_val->value_list_most_relevant_ui()->list_most_relevant($msg_ui);
        $test_page .= 'as short and grouped list: ' . $table_html . '<br>';
        // the same values in the standard, none grouped format: all phrases of a value, then its number
        $test_page .= 'same values in standard / none grouped format: ' . $t_val->value_list_most_relevant_ui()->list($msg_ui) . '<br>';
        $test_page .= 'as table without context: ' . $lst_zh_ui->table($msg_ui) . '<br>';
        // create the same table as above, but within a context
        $header_html = $phr_lst_context_ui->headline();
        $table_html = $lst_zh_ui->table($msg_ui, $phr_lst_context_ui);
        $test_page .= 'as table with context: ' . $header_html . $table_html . '<br>';
        // the ranking of the start page as the two charts of value_list::table_to_svg
        $rank_lst = $t_phr->list_global_problems_ui();
        $rank_ctx = $t_phr->list_global_problem_context_ui();
        $svg_bars = $t_val->value_list_solution_prio_ui()->table_to_svg(
            chart_types::RANGE_BARS, $msg_ui, $rank_ctx, $rank_lst->column_names(), $rank_lst,
            value_list_ui::LIMIT_ALL);
        $test_page .= 'as range bars: ' . $html->lf() . $svg_bars . '<br>';
        $svg_points = $t_val->value_list_solution_prio_ui()->table_to_svg(
            chart_types::SCATTER, $msg_ui, $rank_ctx, $rank_lst->column_names(), $rank_lst,
            value_list_ui::LIMIT_ALL, [], false, false, [word_names::GAIN, word_names::LOSS]);
        $test_page .= 'as scatter plot: ' . $html->lf() . $svg_points . '<br>';
        $t->html_page_test($test_page, 'value_list', 'value_list', $msg_ui);

        $t->subheader($ts . 'user config');

        $cfg = new config($t_val->value_list_all($msg)->api_json([api_types::INCL_PHRASES]));
        $test_name = 'a loaded config value is returned by the phrase names';
        // get_by returns the display value, so the number is rounded for the user
        $t->assert($test_name, $cfg->get_by([word_names::PI_SYMBOL], $msg_ui), round(values::PI_LONG, 2));
        $test_name = 'a missing config value returns the given default';
        $t->assert($test_name, $cfg->get_by([words::POD], $msg_ui, 7), 7);

        // the number of entries shown at once must always come from config.yaml, so that an admin
        // can change it without a code update; the *_LIST consts are only the fallback used until
        // the config is loaded, so each list needs its own key and must never borrow the key of
        // another list (e.g. the value list used to read the link list limit)
        $yaml = yaml_parse_file(files_shared::CONFIG_YAML);
        $yaml_limits = $yaml[words::THIS_SYSTEM][triples::SYSTEM_CONFIG][words::USER][words::DEFAULT]
            [words::FRONTEND][words::LISTS][words::LIMIT] ?? [];
        $list_keys = [triples::VALUE_LIST, triples::PHRASE_LIST, triples::FORMULA_LIST];
        foreach ($list_keys as $list_key) {
            $test_name = 'the ' . $list_key . ' limit is defined in config.yaml';
            $t->assert_greater_zero($test_name, $yaml_limits[$list_key][words::SYS_CONF_VALUE] ?? 0);
        }
        $test_name = 'each list limit has its own config key';
        $t->assert($test_name, count(array_unique($list_keys)), count($list_keys));

        $t->subheader($ts . 'sort by impact');
        $impact_lst = $t_val->value_list_zh_impact_ui();
        $impact_lst->sort_by_impact();
        $test_name = 'the value of the phrase with the highest impact is first';
        $t->assert_text_order($test_name, $impact_lst->list($msg_ui), triple_names::COMPANY_ZURICH, triple_names::CITY_ZH_NAME);
        // two values with the same impact and number must not be ordered by the volatile group id
        // (packed from the seed-assigned word ids), but by the stable group name, so the rendered
        // order stays the same across test database rebuilds ("Zurich" sorts before "city")
        $test_name = 'a number tie is broken by the group name for a stable order';
        $t->assert_text_order($test_name, $t_val->value_list_number_tie_ui()->list($msg_ui), word_names::ZH, word_names::CITY);
        $test_name = 'sort by impact of an empty value list renders nothing';
        $t->assert($test_name, new value_list_ui()->list($msg_ui), '');

        $t->subheader($ts . 'most relevant');
        // a limit above the fixture size, so that these blocks test the grouping and the order;
        // the total limit and the tails are tested in the blocks below
        $mr_html = $t_val->value_list_most_relevant_ui()->list_most_relevant($msg_ui, limit: 10);
        $test_name = 'the newest time group (2022) is shown before the older one (2021)';
        $t->assert_text_order($test_name, $mr_html, word_names::YEAR_2022, word_names::YEAR_2021);
        $test_name = 'the time groups are shown before the repeated-phrase group';
        $t->assert_text_order($test_name, $mr_html, word_names::YEAR_2021, word_names::ABB);
        $test_name = 'the repeated-phrase group is shown before the ungrouped values';
        $t->assert_text_order($test_name, $mr_html, word_names::ABB, word_names::PI);
        $test_name = 'most relevant of an empty value list renders nothing';
        $t->assert($test_name, new value_list_ui()->list_most_relevant($msg_ui), '');

        // a group must not fill the whole screen, so that the user messages below the view
        // stay visible without scrolling (see docs/llm/frontend.md)
        $grp_html = $t_val->value_list_large_group_ui()->list_most_relevant($msg_ui);
        $test_name = 'a group with more values than the limit ends with the more tail';
        $t->assert_text_contains($test_name, $grp_html, msg_id::MORE->text());
        $test_name = 'a group shows only the configured number of values';
        // one list item per shown value plus one for the more tail
        $t->assert($test_name, substr_count($grp_html, '<' . html_base::LI . '>'),
            shared_config::LIMIT_VALUE_LIST + 1);
        $test_name = 'a group that fits shows all values without a more tail';
        $t->assert_text_not_contains($test_name, $mr_html, msg_id::MORE->text());

        // the limit is a page total: many small groups must not show more values than one big
        // group, so groups are rendered newest first until the total is reached and all other
        // values are behind one more tail (docs/llm/frontend.md "a page never fills the screen")
        $many_html = $t_val->value_list_many_year_groups_ui()->list_most_relevant($msg_ui);
        $test_name = 'the total of all groups is limited to the configured number of values';
        // one list item per shown value plus one for the single more tail
        $t->assert($test_name, substr_count($many_html, '<' . html_base::LI . '>'),
            shared_config::LIMIT_VALUE_LIST + 1);
        $test_name = 'the values of the skipped groups are counted in the more tail';
        $t->assert_text_contains($test_name, $many_html,
            msg_id::AND_MORE_BEFORE->text() . ' 8 ' . msg_id::MORE->text());
        $test_name = 'the newest year group is shown';
        $t->assert_text_contains($test_name, $many_html, word_names::YEAR_2025);
        $test_name = 'the oldest year group is behind the more tail';
        $t->assert_text_not_contains($test_name, $many_html, word_names::YEAR_2019);

        $t->subheader($ts . 'columns');
        $col_html = $t_val->value_list_most_relevant_ui()->columns_by_phrase($msg_ui);
        $test_name = 'the columns are combined to one wrapping row';
        $t->assert_text_contains($test_name, $col_html, 'class="row');
        $test_name = 'each column gets the min width that lets four columns fit on the widest screen';
        $t->assert_text_contains($test_name, $col_html,
            'min-width: ' . (int)round(def::FALLBACK_WIDE_SIDE_WIDTH / position_types::MAX_SIDE_COLUMNS) . 'px');
        $test_name = 'the phrase used by most values heads the first column';
        $t->assert_text_contains($test_name, $col_html, styles::VALUE_GROUP_TITLE);
        $test_name = 'the column phrase is shown before its values';
        $t->assert_text_order($test_name, $col_html, word_names::ABB, word_names::PI);
        $test_name = 'never more columns than fit on the widest screen';
        $t->assert_true($test_name,
            substr_count($col_html, 'class="col"') <= position_types::MAX_SIDE_COLUMNS);
        $test_name = 'the columns of an empty value list render nothing';
        $t->assert($test_name, new value_list_ui()->columns_by_phrase($msg_ui), '');

        $t->subheader($ts . 'table with related columns');
        // the row limit is checked below, so the column checks ask for every row; else the value
        // that shares no column phrase would be behind the limit and only reachable via the link
        $tbl_html = $t_val->value_list_most_relevant_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), [], false, true, null, value_list_ui::LIMIT_ALL);
        $test_name = 'the values are shown as a table';
        $t->assert_text_contains($test_name, $tbl_html, '<table');
        $test_name = 'the top left header cell is empty, because the row phrases differ per row';
        $t->assert_text_contains($test_name, $tbl_html, '<th></th>');
        $test_name = 'the phrase used by most values heads a column';
        $t->assert_text_contains($test_name, $tbl_html, word_names::INHABITANTS);
        $test_name = 'a second phrase used by several values heads a further column';
        $t->assert_text_contains($test_name, $tbl_html, word_names::ABB);
        $test_name = 'the value that shares no column phrase is still shown';
        $t->assert_text_contains($test_name, $tbl_html, word_names::PI);
        // one header cell per column phrase, plus the empty top left cell, the "Values" cell
        // of the values that share no column phrase and the "..." cell of the column menu
        $test_name = 'never more phrase columns than fit on the widest screen';
        $t->assert_true($test_name,
            substr_count($tbl_html, '<th') <= position_types::MAX_SIDE_COLUMNS + 3);
        $test_name = 'the header is shown before the first row';
        $t->assert_text_order($test_name, $tbl_html, '<th', '<td');

        // a table that is a grid of its columns leaves out the value that shares no column phrase
        // instead of adding a row and the "Values" column for it
        $tbl_grid = $t_val->value_list_most_relevant_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), [], false, true, null, value_list_ui::LIMIT_ALL, [], true);
        $test_name = 'a grid of the columns leaves out the value without a column phrase';
        $t->assert_text_not_contains($test_name, $tbl_grid, '>' . word_names::PI . '</a>');
        $test_name = '... and has no column for the values without a column phrase';
        $t->assert_text_not_contains($test_name, $tbl_grid,
            '<th>' . msg_id::FORM_SUB_TITLE_VALUES->text() . '</th>');

        // a page must not fill the screen, so with the configured limit the rows that do not fit
        // are reachable via the "... and n more" row instead of being shown
        $tbl_cut = $t_val->value_list_most_relevant_ui()->table_by_related_columns($msg_ui);
        $test_name = 'a table with more rows than the limit ends with a more row';
        $t->assert_text_contains($test_name, $tbl_cut, msg_id::MORE->text());
        $test_name = '... so the row behind the limit is not shown';
        $t->assert_text_not_contains($test_name, $tbl_cut, '>' . word_names::PI . '</a>');
        // the rest column exists because of the data and not because the reader asked for it, so
        // it is left out if its only value is in a row that the table does not show
        $test_name = '... and the column of the values without a column phrase stays away';
        $t->assert_text_not_contains($test_name, $tbl_cut,
            '<th>' . msg_id::FORM_SUB_TITLE_VALUES->text() . '</th>');

        // a value tagged "low" or "high" is a bound of the probability range of the value with
        // the same phrases, so it is shown behind that centre value instead of in a row of its
        // own, and the qualifier "assumed" names no row either, so the problem keeps one row
        // (see the view-validation of solution_prio.json)
        $tbl_range = $t_val->value_list_range_ui()->table_by_related_columns($msg_ui);
        // the values are assumed, so the centre value is followed by the "a" mark, which the
        // bounds share with it and do not repeat (see sandbox_value::quality_mark)
        $centre_txt = '2.2' . sandbox_value_ui::QUALITY_MARK_ASSUMED;
        $test_name = 'the range bounds are shown behind their centre value';
        $t->assert_text_contains($test_name, $lib->html_to_text($tbl_range), $centre_txt . ' (0.88 – 5.5)');
        // the four loss values (centre, both bounds and the confidence) share one row; the gain
        // is carried by one value only, so it heads no column and names a row of its own, which
        // is the header row plus two rows
        $test_name = '... so the bounds and the qualifier name no row of their own';
        $t->assert($test_name, substr_count($tbl_range, '<' . html_base::TR . '>'), 3);
        $test_name = '... and a value without a source is followed by the "-" mark';
        $t->assert_text_contains($test_name, $lib->html_to_text($tbl_range), '35.2' . sandbox_value_ui::QUALITY_MARK_NO_SOURCE);
        $test_name = '... and a value without bounds is shown without brackets';
        $t->assert_text_not_contains($test_name, $lib->html_to_text($tbl_range),
            '35.2' . sandbox_value_ui::QUALITY_MARK_NO_SOURCE . ' (');
        // a simple table shows the numbers only and the "..." header links to the same page with
        // the ranges switched on, which wins over the default of the caller
        $tbl_plain = $t_val->value_list_range_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), [], false, true, null, null,
            [url_var::MASK => views::START_ID], false, value_list_ui::COLUMN_TIERS_ALL, false);
        $test_name = 'without the range the centre value is shown alone';
        $t->assert_text_contains($test_name, $lib->html_to_text($tbl_plain), '2.2');
        $t->assert_text_not_contains($test_name, $lib->html_to_text($tbl_plain), $centre_txt . ' (');
        $test_name = '... and the "..." header links to the page with the ranges';
        $t->assert_text_contains($test_name, $tbl_plain, url_var::DISPLAY_LIST_RANGE . '=' . url_var::TRUE);
        $tbl_url = $t_val->value_list_range_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), [], false, true, null, null,
            [url_var::DISPLAY_LIST_RANGE => url_var::TRUE], false, value_list_ui::COLUMN_TIERS_ALL, false);
        $test_name = 'the url switches the range on although the caller has switched it off';
        $t->assert_text_contains($test_name, $lib->html_to_text($tbl_url), $centre_txt . ' (0.88 – 5.5)');
        $test_name = 'the estimate qualifier of a value is the tooltip of its cell';
        $t->assert_text_contains($test_name, $tbl_range, 'title="' . word_names::ASSUMED . ', ');
        // a value tagged "confidence" says how sure the value with the same subject is, so it is
        // the tooltip of that value and no value or row of its own
        $test_name = 'the confidence of a value is the tooltip of its cell';
        $t->assert_text_contains($test_name, $tbl_range, word_names::CONFIDENCE . ' ');
        $test_name = '... and not a value of the cell';
        $t->assert_text_not_contains($test_name, $lib->html_to_text($tbl_range), '5.5), ');
        // the confidence is a share, so its unit differs from the unit of the loss, but it still
        // follows the loss instead of opening a column of its own unit
        $unit_sep = ' ' . msg_id::VALUE_TBL_UNIT->text() . ' ';
        $test_name = '... nor a column of its own unit';
        $t->assert($test_name, substr_count(
            $lib->html_to_text($lib->str_left_of($tbl_range, '</tr>')), word_names::LOSS . $unit_sep), 1);

        // a confidence value often names less than the value it qualifies, e.g. the confidence of
        // the loss of a problem qualifies the loss of the solution of that problem, which names
        // the solution too; the confidence follows that value as well, because otherwise its
        // share opens a second column of the same phrase that shows no number
        // the solution is a column of its own here, so that the value and its confidence share a
        // row although only the value names the solution (see solution_prio.json)
        $wider_lst = $t_phr->list_global_problems_ui();
        $tbl_wider = $t_val->value_list_confidence_wider_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $wider_lst->column_names(), false, true, $wider_lst);
        $hdr_wider = $lib->html_to_text($lib->str_left_of($tbl_wider, '</tr>'));
        $test_name = 'a confidence that names less than its value opens no column of its own';
        $t->assert($test_name, substr_count($hdr_wider, word_names::LOSS . $unit_sep), 1);
        $test_name = '... and is the tooltip of the value it qualifies';
        $t->assert_text_contains($test_name, $tbl_wider, word_names::CONFIDENCE . ' ');
        // negative: the confidence is no value and no row of its own, so the table has the header
        // row and the one row of the problem
        $test_name = '... and no value of the cell';
        $t->assert($test_name, substr_count($tbl_wider, '<' . html_base::TR . '>'), 2);

        // a column shows one measure, so a phrase with values in two units gets one column per
        // unit, the unit of the most relevant value first, and no value is lost; the "loss"
        // column is defined here, because the two values differ in their unit only, so without
        // the definition "loss" is a phrase that every value carries and heads no column
        $loss_lst = $t_phr->list_columns_loss_ui();
        $tbl_units = $t_val->value_list_two_units_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst);
        $hdr_units = $lib->html_to_text($lib->str_left_of($tbl_units, '</tr>'));
        $test_name = 'a phrase with values in two units gets one column per unit';
        $t->assert($test_name, substr_count($hdr_units, word_names::LOSS . $unit_sep), 2);
        $test_name = '... the unit of the most relevant value first';
        $t->assert_text_order($test_name, $hdr_units, word_names::EUR, word_names::HTP);
        $test_name = '... and a cell holds the values of its unit only';
        $t->assert_text_not_contains($test_name, $lib->html_to_text($tbl_units), '2.2, ');

        // only the table of the mayor tier alone keeps one number per column, so the second
        // unit is shown as soon as the main columns are shown too
        $tbl_unit_mayor = $t_val->value_list_two_units_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst,
            null, [], false, value_list_ui::COLUMN_TIERS_EX_MAIN);
        $hdr_unit_mayor = $lib->html_to_text($lib->str_left_of($tbl_unit_mayor, '</tr>'));
        $test_name = 'the mayor columns show one unit per column';
        $t->assert($test_name, substr_count($hdr_unit_mayor, word_names::LOSS . $unit_sep), 1);
        // the unit of most values leads, so the start page shows the potential loss in trillion
        // EUR of every problem and not the one bigger potential loss in htp of a single problem
        $tbl_unit_most = $t_val->value_list_unit_majority_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst,
            null, [], false, value_list_ui::COLUMN_TIERS_EX_MAIN);
        $hdr_unit_most = $lib->html_to_text($lib->str_left_of($tbl_unit_most, '</tr>'));
        $test_name = 'the mayor column shows the unit of most values';
        $t->assert_text_contains($test_name, $hdr_unit_most, word_names::EUR);
        // negative: the unit of the single bigger number is left to the full table
        $test_name = '... and not the unit of the single biggest number';
        $t->assert_text_not_contains($test_name, $hdr_unit_most, word_names::HTP);
        // with the main columns a further unit is shown only if it has a number in every row,
        // so the potential loss in htp of global warming alone is left to the full table
        $tbl_unit_part = $t_val->value_list_unit_majority_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst,
            null, [], false, value_list_ui::COLUMN_TIERS_EX_MINOR);
        $hdr_unit_part = $lib->html_to_text($lib->str_left_of($tbl_unit_part, '</tr>'));
        $test_name = 'the main columns leave out a unit that only some rows have';
        $t->assert_text_not_contains($test_name, $hdr_unit_part, word_names::HTP);
        // negative: the full table shows every unit
        $tbl_unit_all = $t_val->value_list_unit_majority_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst);
        $hdr_unit_all = $lib->html_to_text($lib->str_left_of($tbl_unit_all, '</tr>'));
        $test_name = '... but the full table shows it';
        $t->assert_text_contains($test_name, $hdr_unit_all, word_names::HTP);
        $tbl_unit_main = $t_val->value_list_two_units_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst,
            null, [], false, value_list_ui::COLUMN_TIERS_EX_MINOR);
        $hdr_unit_main = $lib->html_to_text($lib->str_left_of($tbl_unit_main, '</tr>'));
        $test_name = '... and with the main columns every unit is shown';
        $t->assert($test_name, substr_count($hdr_unit_main, word_names::LOSS . $unit_sep), 2);

        // a unit can be a triple, e.g. "gram per kWh", which the import types "measure unit" like
        // its words (see pv_switzerland_co2.json), so the header puts it behind the "in" too
        $tbl_unit_trp = $t_val->value_list_unit_triple_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst);
        $hdr_unit_trp = $lib->html_to_text($lib->str_left_of($tbl_unit_trp, '</tr>'));
        $test_name = 'a unit triple typed measure is shown behind the "in" of the column header';
        $t->assert_text_contains($test_name, $hdr_unit_trp,
            word_names::LOSS . $unit_sep . triple_names::GRAM_PER_KWH);
        // negative: the unit triple heads no column of its own and does not name a row
        $test_name = '... and heads no column of its own';
        $t->assert($test_name, substr_count($hdr_unit_trp, triple_names::GRAM_PER_KWH), 1);

        // a phrase typed "measure non unit" names what is measured e.g. "GDP" of a loss stated as
        // a share of the GDP; it describes the number like a unit, so the header names it behind
        // the unit and the table has no row or column for it, although it is no number type
        $tbl_non_unit = $t_val->value_list_measure_non_unit_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $loss_lst->column_names(), false, true, $loss_lst);
        $hdr_non_unit = $lib->html_to_text($lib->str_left_of($tbl_non_unit, '</tr>'));
        $test_name = 'what is measured is named behind the unit of the column header';
        $t->assert_text_contains($test_name, $hdr_non_unit,
            word_names::LOSS . $unit_sep . words::PCT . ' ' . word_names::GDP);
        $test_name = '... and heads no column of its own';
        $t->assert($test_name, substr_count($hdr_non_unit, word_names::GDP), 1);
        // negative: the problems name the rows, so the table has the header row and one row
        // per problem and no row for the GDP
        $test_name = '... nor a row of its own';
        $t->assert($test_name, substr_count($tbl_non_unit, '<' . html_base::TR . '>'), 3);

        // a defined column can be a triple that no value carries but whose two parts the values
        // carry, e.g. "potential loss" for the values with "potential" and "loss"; it takes those
        // values before the column of one of its parts, because it names more of their phrases
        $rel_lst = $t_phr->list_columns_potential_loss_ui();
        $tbl_parts = $t_val->value_list_range_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $rel_lst->column_names(), false, true, $rel_lst);
        $hdr_parts = $lib->html_to_text($lib->str_left_of($tbl_parts, '</tr>'));
        $test_name = 'a column of two phrases takes the values that carry both';
        $t->assert_text_contains($test_name, $hdr_parts,
            word_names::POTENTIAL . ' ' . word_names::LOSS . $unit_sep);
        $test_name = '... and the column of one of the two phrases stays away';
        $t->assert($test_name, substr_count($hdr_parts, word_names::LOSS . $unit_sep), 1);
        // negative: without the column of the two phrases the values stay in the column of the
        // one phrase they carry
        $rel_lst = $t_phr->list_columns_loss_ui();
        $tbl_one = $t_val->value_list_range_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $rel_lst->column_names(), false, true, $rel_lst);
        $hdr_one = $lib->html_to_text($lib->str_left_of($tbl_one, '</tr>'));
        $test_name = 'without the column of two phrases the column of the one phrase is used';
        $t->assert_text_contains($test_name, $hdr_one, word_names::LOSS . $unit_sep);
        $t->assert_text_not_contains($test_name, $hdr_one, word_names::POTENTIAL . ' ' . word_names::LOSS);

        // every defined column is shown, because the tiers hide the columns per screen size, so
        // the number of columns that fit on the widest screen limits only the columns that the
        // data suggests; five defined columns give five column headers plus the row name header
        // and the "..." header with the column menu
        $rel_lst = $t_phr->list_columns_ordered_ui();
        $tbl_def = $t_val->value_list_defined_columns_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $rel_lst->column_names(), false, true, $rel_lst);
        $test_name = 'every defined column is shown even above the number that fit on the widest screen';
        $t->assert($test_name, substr_count($tbl_def, '<th'), count($rel_lst->column_names()) + 2);

        // the tier of a defined column says on which screens it is shown: a minor column carries
        // the class that hides it below a wide screen, a mayor column has no class and is shown
        // on every screen; "cost" is a minor and "loss" a mayor column
        $test_name = 'a minor column is hidden below a wide screen';
        $t->assert_text_contains($test_name, $tbl_def, '<th class="' . styles::COL_MINOR . '">');
        $test_name = 'a mayor column is shown on every screen';
        $t->assert_text_not_contains($test_name, $tbl_one, styles::COL_MINOR);
        // negative: a column that the data suggests has no tier and therefore no class
        $test_name = 'a column without a definition is shown on every screen';
        $t->assert_text_not_contains($test_name, $tbl_html, styles::COL_MINOR);

        // a simple table shows the mayor columns only, so the minor column "cost" is left to the
        // full table and the "..." header says that more columns exist
        $tbl_mayor = $t_val->value_list_defined_columns_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $rel_lst->column_names(), false, true, $rel_lst,
            null, [], false, value_list_ui::COLUMN_TIERS_EX_MAIN);
        $hdr_mayor = $lib->html_to_text($lib->str_left_of($tbl_mayor, '</tr>'));
        $hdr_def = $lib->html_to_text($lib->str_left_of($tbl_def, '</tr>'));
        $test_name = 'a simple table leaves out the minor column';
        $t->assert_text_not_contains($test_name, $hdr_mayor, word_names::COST);
        $test_name = '... but keeps the mayor columns';
        $t->assert_text_contains($test_name, $hdr_mayor, word_names::LOSS);
        $test_name = '... and ends with the "..." header';
        $t->assert_text_contains($test_name, $hdr_mayor, msg_id::THREE_POINTS->text());
        // the full table shows every column and still ends with the "..." header, because its
        // menu is the only way back to the fewer columns
        $test_name = 'the full table shows the minor column and the "..." header';
        $t->assert_text_contains($test_name, $hdr_def, word_names::COST);
        $t->assert_text_contains($test_name, $hdr_def, msg_id::THREE_POINTS->text());

        // the reason of a problem is a phrase column like the solution and the loss that the
        // reason causes a value column of its own: its value carries the problem, the word
        // "reason", the reason and the potential loss, so the defined column "potential loss of
        // reason" takes it before the "potential loss" column of the problem (see
        // value_list::column_parts); both are main columns, so the table with the main tier
        // shows them and the simple table of the mayor columns leaves them out
        // every value carries "potential", so the table header names it once and the column
        // of the problem is headed "loss" like in the ranking without a reason (see column_head)
        $tbl_reason = $t_val->value_list_solution_prio_reason_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst,
            value_list_ui::LIMIT_ALL, [], false, value_list_ui::COLUMN_TIERS_EX_MINOR);
        $hdr_reason = $lib->html_to_text($lib->str_left_of($tbl_reason, '</tr>'));
        $test_name = 'the reason column names the reason of the problem';
        $t->assert_text_contains($test_name, $tbl_reason, '>' . triple_names::CLIMATE_GAS_EMISSIONS . '</a>');
        $test_name = 'the loss of the reason has a column of its own behind the reason column';
        $t->assert_text_order($test_name, $hdr_reason, word_names::REASON, triple_names::POTENTIAL_LOSS_OF_REASON . $unit_sep);
        $test_name = '... and both stand left of the solution column';
        $t->assert_text_order($test_name, $hdr_reason, triple_names::POTENTIAL_LOSS_OF_REASON . $unit_sep, word_names::SOLUTION);
        $test_name = '... and the loss of the problem keeps its column';
        $t->assert($test_name, substr_count($hdr_reason, word_names::LOSS . $unit_sep), 1);
        $test_name = '... so the cell of the problem shows its loss alone';
        $t->assert_text_not_contains($test_name, $lib->html_to_text($tbl_reason),
            '31.5' . sandbox_value_ui::QUALITY_MARK_NO_SOURCE . ', ');
        $test_name = 'a main column is hidden on a small screen';
        $t->assert_text_contains($test_name, $tbl_reason, '<th class="' . styles::COL_MAIN . '">');
        $tbl_reason_mayor = $t_val->value_list_solution_prio_reason_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst,
            value_list_ui::LIMIT_ALL, [], false, value_list_ui::COLUMN_TIERS_EX_MAIN);
        $test_name = 'the simple table leaves out the reason and its loss';
        $t->assert_text_not_contains($test_name,
            $lib->html_to_text($lib->str_left_of($tbl_reason_mayor, '</tr>')), word_names::REASON);
        // negative: without a value that names a reason the loss of the reason has no column,
        // while the reason column is defined and therefore shown although it is empty
        $tbl_no_reason = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst,
            value_list_ui::LIMIT_ALL, [], false, value_list_ui::COLUMN_TIERS_EX_MINOR);
        $hdr_no_reason = $lib->html_to_text($lib->str_left_of($tbl_no_reason, '</tr>'));
        $test_name = 'without a reason value the loss of the reason has no column';
        $t->assert_text_not_contains($test_name, $hdr_no_reason, triple_names::POTENTIAL_LOSS_OF_REASON);
        $test_name = '... but the defined reason column is shown';
        $t->assert_text_contains($test_name, $hdr_no_reason, word_names::REASON);

        // the "..." header opens the menu that selects the columns, so it offers every column
        // tier and each of them once with and once without the ranges
        $page_url = [url_var::MASK => views::START_ID];
        $tbl_menu = $t_val->value_list_defined_columns_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), $rel_lst->column_names(), false, true, $rel_lst,
            null, $page_url, false, value_list_ui::COLUMN_TIERS_EX_MAIN);
        $test_name = 'the "..." header opens a menu without any javascript';
        $t->assert_text_contains($test_name, $tbl_menu,
            '<' . html_base::DETAILS . ' class="' . styles::MENU_COLUMN . '">');
        $t->assert_text_not_contains($test_name, $tbl_menu, '<script');
        foreach (value_list_ui::COLUMN_TIER_NAMES as $tiers => $msg_tier) {
            $test_name = 'the column menu offers ' . $msg_tier->text();
            $t->assert_text_contains($test_name, $lib->html_to_text($tbl_menu), $msg_tier->text());
            // the entry of one tier without and the entry with the range of each number
            $t->assert_text_contains($test_name, $tbl_menu,
                url_var::DISPLAY_LIST_COLUMNS . '=' . $tiers . '&amp;'
                . url_var::DISPLAY_LIST_RANGE . '=' . url_var::FALSE);
            $t->assert_text_contains($test_name, $tbl_menu,
                url_var::DISPLAY_LIST_COLUMNS . '=' . $tiers . '&amp;'
                . url_var::DISPLAY_LIST_RANGE . '=' . url_var::TRUE);
        }
        $test_name = '... and names the entry with the range';
        $t->assert_text_contains($test_name, $lib->html_to_text($tbl_menu),
            msg_id::TABLE_COLUMNS_ALL->text() . ' ' . msg_id::TABLE_COLUMNS_WITH_RANGE->text());
        // the menu selects the columns and the form of the table, each below its own sub header
        $test_name = 'the menu tooltip names the columns and the form';
        $t->assert_text_contains($test_name, $tbl_menu, 'title="' . msg_id::TABLE_COLUMNS_TIP->text() . '"');
        $test_name = 'the column entries are below the sub header columns and values';
        $t->assert_text_order($test_name, $tbl_menu,
            'class="' . styles::MENU_HEADER . '">' . msg_id::TABLE_MENU_COLUMNS->text(),
            msg_id::TABLE_COLUMNS_MAYOR->text());
        $test_name = 'the form entries are below the sub header as';
        $t->assert_text_order($test_name, $tbl_menu,
            'class="' . styles::MENU_HEADER . '">' . msg_id::TABLE_MENU_AS->text(),
            msg_id::TABLE_AS_TABLE_CHART->text());
        // the form shown is not offered again: the table page offers the chart and the table
        // with the chart, the chart page the table and the table with the chart
        $chart_url = [url_var::MASK => views::START_ID, url_var::DISPLAY_LIST_AS => table_forms::CHART->value];
        $chart_menu = $t_val->value_list_defined_columns_ui()->columns_menu($chart_url);
        foreach (table_forms::cases() as $form) {
            $form_entry = url_var::DISPLAY_LIST_AS . '=' . $form->value . '">' . $form->msg_id()->text();
            $test_name = 'the table menu offers the table ' . $form->value;
            if ($form == table_forms::TABLE) {
                $t->assert_text_not_contains($test_name, $tbl_menu, $form_entry);
            } else {
                $t->assert_text_contains($test_name, $tbl_menu, $form_entry);
            }
            $test_name = 'the chart menu offers the table ' . $form->value;
            if ($form == table_forms::CHART) {
                $t->assert_text_not_contains($test_name, $chart_menu, $form_entry);
            } else {
                $t->assert_text_contains($test_name, $chart_menu, $form_entry);
            }
        }
        // the charts of a table are data: the chart triples assigned to the default chart type;
        // compared by the chart type values, so that a difference can be printed
        $test_name = 'the ranking defines the range bars of the loss and the scatter plot of the gain against the effort';
        $ranking_charts = [
            [chart_types::RANGE_BARS->value, [triple_names::POTENTIAL_LOSS]],
            [chart_types::SCATTER->value, [triple_names::POTENTIAL_GAIN, triple_names::INITIAL_EFFORT]],
        ];
        $charts = $t_phr->list_global_problems_ui()->chart_definitions($msg_ui);
        $t->assert($test_name, array_map(fn(array $chart) => [$chart[0]->value, $chart[1]], $charts), $ranking_charts);
        // the api delivers the chart type word and the column pair nested in the chart triple of
        // the assignment, so the charts are found without a list entry for the word or the pair
        $test_name = 'the chart type and the columns are read from the phrases nested in the assignment';
        $charts = $t_phr->list_chart_assignments_ui()->chart_definitions($msg_ui);
        $t->assert($test_name, array_map(fn(array $chart) => [$chart[0]->value, $chart[1]], $charts), $ranking_charts);
        $test_name = 'a chart type word without code id is reported and its chart left out';
        $t->assert($test_name, $t_phr->list_chart_without_type_ui()->chart_definitions($msg_ui), []);
        $t->assert_text_contains($test_name, $msg_ui->get_last_message_translated(), word_names::RANGE_BARS);
        $msg_ui->reset();
        $test_name = 'a list without chart definitions defines no chart';
        $t->assert($test_name, $rel_lst->chart_definitions($msg_ui), []);

        $test_name = 'the table of an empty value list renders nothing';
        $t->assert($test_name, new value_list_ui()->table_by_related_columns($msg_ui), '');
        // with the page phrase as context the phrase of the page is not repeated in the table
        $tbl_ctx = $t_val->value_list_most_relevant_ui()
            ->table_by_related_columns($msg_ui, $phr_lst_context_ui);
        $test_name = 'the context phrase is not used as a column headline';
        $t->assert_text_not_contains($test_name, $tbl_ctx, word_names::INHABITANTS);
        // the header names the context phrase centred above the table, so that a table taken
        // out of its page still says what it is about
        $test_name = 'with the header the context phrase is linked above the table';
        $tbl_header = $t_val->value_list_most_relevant_ui()
            ->table_by_related_columns($msg_ui, $phr_lst_context_ui, [], true);
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_header, '<table'), '>' . word_names::INHABITANTS . '</a>');

        $t->subheader($ts . 'row order');
        // the url can ask for another order of the rows than the impact: the phrase id of a
        // column and the condition, e.g. the smallest potential loss first; the rows are
        // sorted before they are cut, so the short list shows the first rows of that order
        $loss_id = $t_trp->potential_loss()->phrase()->id();
        $gain_id = $t_trp->potential_gain()->phrase()->id();
        $problem_id = $t_wrd->word_problem()->phrase()->id();
        $solution_id = $t_wrd->solution()->phrase()->id();
        $rank_url = [url_var::MASK => views::START_ID];
        $asc_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => table_orders::NUMERIC_ASC->url_value($loss_id)];
        $tbl_asc = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, null, $asc_url);
        $test_name = 'the smallest number of the column is first if the url asks for it';
        $t->assert_text_order($test_name, $tbl_asc, triple_names::PROPRIETARY_SOFTWARE, triple_names::GDP_MISMEASUREMENT);
        $test_name = '... and the biggest is cut away, because the rows are sorted before the cut';
        $t->assert_text_not_contains($test_name, $tbl_asc, '>' . triple_names::GLOBAL_WARMING . '</a>');
        $test_name = '... and the links of the "..." menu keep the order of the page';
        $t->assert_text_contains($test_name, $tbl_asc,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::NUMERIC_ASC->url_value($loss_id));
        // the biggest number of another column first changes the impact order, e.g. the gain
        // of the disinformation solution is bigger than the gain of the health solution
        // although the loss of the health problem is bigger
        $desc_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => table_orders::NUMERIC_DESC->url_value($gain_id)];
        $tbl_desc = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, value_list_ui::LIMIT_ALL, $desc_url);
        $test_name = 'the biggest number of another column first changes the impact order';
        $t->assert_text_order($test_name, $tbl_desc, word_names::DISINFORMATION, word_names::HEALTH);
        $tbl_impact = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, value_list_ui::LIMIT_ALL, $rank_url);
        $test_name = '... which the table without an order keeps';
        $t->assert_text_order($test_name, $tbl_impact, word_names::HEALTH, word_names::DISINFORMATION);
        // the populism and the poverty solution have the same gain, so the sub order decides
        // between them and the sub sub order if the sub order leaves them equal as well
        $sub_url = $desc_url + [url_var::DISPLAY_LIST_ORDER_SUB => table_orders::ALPHA_DESC->url_value($problem_id)];
        $tbl_sub = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, value_list_ui::LIMIT_ALL, $sub_url);
        $test_name = 'the sub order sorts the rows that the prime order leaves equal';
        $t->assert_text_order($test_name, $tbl_sub, word_names::POVERTY, word_names::POPULISM);
        $test_name = '... which keep the impact order without the sub order';
        $t->assert_text_order($test_name, $tbl_desc, word_names::POPULISM, word_names::POVERTY);
        $sub_sub_url = $desc_url + [
                url_var::DISPLAY_LIST_ORDER_SUB => table_orders::NUMERIC_ASC->url_value($gain_id),
                url_var::DISPLAY_LIST_ORDER_SUB_SUB => table_orders::ALPHA_DESC->url_value($problem_id)];
        $tbl_sub_sub = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, value_list_ui::LIMIT_ALL, $sub_sub_url);
        $test_name = 'the sub sub order sorts the rows that the prime and the sub order leave equal';
        $t->assert_text_order($test_name, $tbl_sub_sub, word_names::POVERTY, word_names::POPULISM);
        // the row column is sorted by the names of the row phrases without regard to the case
        $alpha_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => table_orders::ALPHA_ASC->url_value($problem_id)];
        $tbl_alpha = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, value_list_ui::LIMIT_ALL, $alpha_url);
        $test_name = 'the rows are sorted by their name if the url names the row column';
        $t->assert_text_order($test_name, $tbl_alpha, word_names::EDUCATION, triple_names::GDP_MISMEASUREMENT);
        $t->assert_text_order($test_name, $tbl_alpha, triple_names::GDP_MISMEASUREMENT, triple_names::GLOBAL_WARMING);
        // a phrase column is sorted by the name of the phrase shown in the row
        $sol_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => table_orders::ALPHA_DESC->url_value($solution_id)];
        $tbl_sol = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, value_list_ui::LIMIT_ALL, $sol_url);
        $test_name = 'the rows are sorted by the phrase of a phrase column';
        $t->assert_text_order($test_name, $tbl_sol, word_names::TAXES, triple_names::REDUCE_EMISSIONS);
        // with the parents the names of the parent phrases within the related phrases come
        // before the name, so "GDP mismeasurement" without a parent is before "global problem
        // education" and the second parent "potential" moves "global warming" behind "populism"
        $parent_lst = $t_phr->list_global_problems_second_parent_ui();
        $parent_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => table_orders::ALPHA_PARENT_ASC->url_value($problem_id)];
        $tbl_parent = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $parent_lst->column_names(), false, true, $parent_lst, value_list_ui::LIMIT_ALL, $parent_url);
        $test_name = 'the names of the parents come before the name of the row phrase';
        $t->assert_text_order($test_name, $tbl_parent, triple_names::GDP_MISMEASUREMENT, word_names::EDUCATION);
        $test_name = '... so a second parent moves the row';
        $t->assert_text_order($test_name, $tbl_parent, word_names::POPULISM, triple_names::GLOBAL_WARMING);
        $t->assert_text_order($test_name, $tbl_parent, triple_names::GLOBAL_WARMING, word_names::POVERTY);
        // each column header ends with an up and a down icon, which link to the same page with
        // the rows sorted by the column: the numbers of a value column, the names of the row
        // column and of a phrase column
        $test_name = 'a value column header has the up icon that sorts the smallest number on top';
        $t->assert_text_contains($test_name, $tbl_impact, '<i class="' . icons::SORT_UP . '">');
        $t->assert_text_contains($test_name, $tbl_impact,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::NUMERIC_ASC->url_value($loss_id) . '"');
        $test_name = '... and the down icon that sorts the biggest on top';
        $t->assert_text_contains($test_name, $tbl_impact, '<i class="' . icons::SORT_DOWN . '">');
        $t->assert_text_contains($test_name, $tbl_impact,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::NUMERIC_DESC->url_value($loss_id) . '"');
        $test_name = 'the row column and a phrase column sort by the names';
        $t->assert_text_contains($test_name, $tbl_impact,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::ALPHA_ASC->url_value($problem_id) . '"');
        $t->assert_text_contains($test_name, $tbl_impact,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::ALPHA_DESC->url_value($solution_id) . '"');
        $test_name = '... with the tooltip that says what the icon does';
        $t->assert_text_contains($test_name, $tbl_impact, 'title="' . msg_id::TABLE_SORT_UP_TIP->text() . '"');
        // a click on another column keeps the previous order as the sub order, so that the
        // sub orders can be reached without typing the url; the same column sorted again
        // replaces its previous order instead of repeating it as the sub order
        $test_name = 'the icon of another column moves the shown order to the sub order';
        $t->assert_text_contains($test_name, $tbl_asc,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::NUMERIC_DESC->url_value($gain_id) . '&amp;'
            . url_var::DISPLAY_LIST_ORDER_SUB . '=' . table_orders::NUMERIC_ASC->url_value($loss_id) . '"');
        $test_name = '... and the icon of the sorted column replaces the shown order';
        $t->assert_text_contains($test_name, $tbl_asc,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::NUMERIC_DESC->url_value($loss_id) . '"');
        $test_name = '... which is marked as the order shown';
        $t->assert_text_contains($test_name, $tbl_asc, 'class="' . styles::SORT_ICON . ' ' . styles::SORT_ACTIVE . '"');
        $t->assert_text_not_contains($test_name, $tbl_impact, styles::SORT_ACTIVE);
        // negative: a table that does not know its page has no icon, because it cannot link
        $test_name = 'a table without the page url shows no sort icon';
        $t->assert_text_not_contains($test_name, $tbl_html, icons::SORT_UP);
        // the default order of a table is data: the order triple "<condition> of <column>"
        // assigned to the default sort order tier, read like the chart definitions
        $test_name = 'the ranking defines the biggest potential loss on top as its default order';
        $sorted_lst = $t_phr->list_global_problems_sorted_ui();
        $t->assert($test_name, array_map(fn(array $order) => [$order[0]->value, $order[1]],
            $sorted_lst->sort_definitions($msg_ui)), [[table_orders::NUMERIC_DESC->value, triple_names::POTENTIAL_LOSS]]);
        $test_name = 'a list without sort definitions defines no order';
        $t->assert($test_name, $rank_lst->sort_definitions($msg_ui), []);
        $test_name = 'a condition word without code id is reported and its order left out';
        $t->assert($test_name, $t_phr->list_sort_without_condition_ui()->sort_definitions($msg_ui), []);
        $t->assert_text_contains($test_name, $msg_ui->get_last_message_translated(), word_names::NUMERIC_DESCENDING);
        $msg_ui->reset();
        // without an order in the url the rows follow the default order, which puts the health
        // problem with the bigger loss before the wealth concentration with the bigger gain
        $tbl_default = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $sorted_lst->column_names(), false, true, $sorted_lst, value_list_ui::LIMIT_ALL, $rank_url);
        $test_name = 'without an order in the url the rows follow the default order of the definition';
        $t->assert_text_order($test_name, $tbl_default, word_names::HEALTH, triple_names::WEALTH_CONCENTRATION);
        $test_name = '... which differs from the impact order of a table without a definition';
        $t->assert_text_order($test_name, $tbl_impact, triple_names::WEALTH_CONCENTRATION, word_names::HEALTH);
        $test_name = '... and the down icon of the loss column is marked as the order shown';
        $t->assert_text_contains($test_name, $tbl_default, 'class="' . styles::SORT_ICON . ' ' . styles::SORT_ACTIVE . '"');
        $test_name = '... which the icon of another column keeps as the sub order';
        $t->assert_text_contains($test_name, $tbl_default,
            url_var::DISPLAY_LIST_ORDER . '=' . table_orders::NUMERIC_DESC->url_value($gain_id) . '&amp;'
            . url_var::DISPLAY_LIST_ORDER_SUB . '=' . table_orders::NUMERIC_DESC->url_value($loss_id) . '"');
        $tbl_url_wins = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $sorted_lst->column_names(), false, true, $sorted_lst, value_list_ui::LIMIT_ALL, $alpha_url);
        $test_name = 'an order in the url wins over the default order';
        $t->assert_text_order($test_name, $tbl_url_wins, word_names::EDUCATION, triple_names::GLOBAL_WARMING);
        // negative: a default order of a column that the table does not have is reported
        $t_val->value_list_most_relevant_ui()->table_by_related_columns(
            $msg_ui, new phrase_list_ui(), [], false, true, $sorted_lst, null, $rank_url);
        $test_name = 'a default order of a column that the table does not have is reported';
        $t->assert_true($test_name, $msg_ui->has_msg_id(msg_id::TABLE_ORDER_UNKNOWN));
        $t->assert_text_contains($test_name, $msg_ui->get_last_message_translated(), triple_names::POTENTIAL_LOSS);
        $msg_ui->reset();
        // the chart shows the same rows as the table, so it draws them in the order of the url
        $svg_asc = $t_val->value_list_solution_prio_ui()->table_to_svg(
            chart_types::RANGE_BARS, $msg_ui, $rank_ctx, $rank_lst->column_names(), $rank_lst,
            value_list_ui::LIMIT_ALL, $asc_url);
        $test_name = 'the chart draws the rows in the order of the url like the table';
        $t->assert_text_order($test_name, $svg_asc, triple_names::PROPRIETARY_SOFTWARE, triple_names::GLOBAL_WARMING);
        // negative: an order that names no column of the table or an unknown condition is
        // reported and the rows keep the impact order
        $bad_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => table_orders::NUMERIC_ASC->url_value(999999)];
        $tbl_bad = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, null, $bad_url);
        $test_name = 'an order that names no column of the table is reported';
        $t->assert_true($test_name, $msg_ui->has_msg_id(msg_id::TABLE_ORDER_UNKNOWN));
        $t->assert_text_contains($test_name, $msg_ui->get_last_message_translated(), '999999');
        $msg_ui->reset();
        $test_name = '... and the rows keep the impact order';
        $t->assert_text_order($test_name, $tbl_bad, triple_names::GLOBAL_WARMING, word_names::POPULISM);
        $cond_url = $rank_url + [url_var::DISPLAY_LIST_ORDER => $loss_id . table_orders::ID_SEP . 'biggest'];
        $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg_ui, $rank_ctx, $rank_lst->column_names(), false, true, $rank_lst, null, $cond_url);
        $test_name = 'an unknown condition is reported';
        $t->assert_true($test_name, $msg_ui->has_msg_id(msg_id::TABLE_ORDER_UNKNOWN));
        $msg_ui->reset();
        $test_name = 'a table without an order in the url reports nothing';
        $t->assert_false($test_name, $msg_ui->has_msg_id(msg_id::TABLE_ORDER_UNKNOWN));
        // the url value names the phrase id of the column and the condition
        $test_name = 'an order url value is read back to the phrase id and the condition';
        [$phr_id, $cond] = table_orders::parse(table_orders::ALPHA_DESC->url_value(-45));
        $t->assert($test_name, $phr_id, -45);
        $t->assert($test_name, $cond?->value ?? '', table_orders::ALPHA_DESC->value);
        // the id and the condition are read on their own, so the caller can say which part is wrong
        $test_name = 'a value without a phrase id names no column but still the condition';
        [$phr_id, $cond] = table_orders::parse('x' . table_orders::ID_SEP . table_orders::ALPHA_DESC->value);
        $t->assert($test_name, $phr_id, 0);
        $t->assert($test_name, $cond?->value ?? '', table_orders::ALPHA_DESC->value);
        $test_name = 'a value with an unknown condition names the column but no condition';
        [$phr_id, $cond] = table_orders::parse('12' . table_orders::ID_SEP . 'biggest');
        $t->assert($test_name, $phr_id, 12);
        $t->assert($test_name, $cond?->value ?? '', '');
        $test_name = 'a row without a key is behind the rows with a key for both directions';
        $t->assert($test_name, table_orders::NUMERIC_DESC->compare(null, 1.0), 1);
        $t->assert($test_name, table_orders::NUMERIC_ASC->compare(1.0, null), -1);
        $t->assert($test_name, table_orders::ALPHA_ASC->compare(null, null), 0);
        $test_name = 'the names are compared without regard to the case';
        $t->assert_true($test_name, table_orders::ALPHA_ASC->compare(word_names::EDUCATION, triple_names::GDP_MISMEASUREMENT) < 0);
        $t->assert_true($test_name, table_orders::ALPHA_DESC->compare(word_names::EDUCATION, triple_names::GDP_MISMEASUREMENT) > 0);

        $t->subheader($ts . 'more tail');
        $tail_html = $t_val->list_all_ui($msg)->list($msg_ui, $phr_lst_context_ui, [], '', 1);
        $test_name = 'the more tail is a link to the phrase values view';
        $t->assert_text_contains($test_name, $tail_html, url_var::MASK . '=' . views::PHRASE_VALUES_ID);
        $test_name = 'the more tail link selects the page phrase';
        $t->assert_text_contains($test_name, $tail_html, 'id=' . $phr_inhabitant->id());
        $lst_all_ui = $t_val->list_all_ui($msg);
        $tail_plain = $lst_all_ui->list($msg_ui, new phrase_list_ui(), [], '', 1);
        $test_name = 'without a page phrase the more tail has no link';
        $t->assert_text_not_contains($test_name, $tail_plain, url_var::MASK . '=' . views::PHRASE_VALUES_ID);
        $test_name = 'without a page phrase the more count is still shown';
        $t->assert_text_contains($test_name, $tail_plain, msg_id::MORE->text());

        // the tail tells the user that the list is shortened, so it must be the last entry
        // and it must only be there if the list really does not show every value
        $test_name = 'a shortened list ends with the more tail';
        $t->assert_text_ends($test_name, $tail_plain, msg_id::MORE->text());
        $test_name = 'the more tail counts the values that are not shown';
        $t->assert_text_contains($test_name, $tail_plain,
            msg_id::AND_MORE_BEFORE->text() . ' ' . ($lst_all_ui->count() - 1) . ' ' . msg_id::MORE->text());
        $full_html = $lst_all_ui->list($msg_ui, $phr_lst_context_ui, [], '', $lst_all_ui->count());
        $test_name = 'a list that shows every value has no more tail';
        $t->assert_text_not_contains($test_name, $full_html, msg_id::MORE->text());

        $t->subheader($ts . 'table to svg');
        // the chart shows the rows of the table, so the column definitions of the page phrase
        // select the plotted column like a table column ($svg_bars and $svg_points see above)
        $test_name = 'the ranking is drawn as range bars';
        $t->assert_text_contains($test_name, $svg_bars, '<svg');
        $test_name = '... with one row per problem';
        $t->assert($test_name, substr_count($svg_bars, 'class="bar"'), count($t_val->solution_prio_rows()));
        $test_name = '... never named like the bootstrap row class, which would stretch the bars';
        $t->assert_text_not_contains($test_name, $svg_bars, 'class="row"');
        $test_name = '... the biggest loss on top';
        $t->assert_text_order($test_name, $svg_bars, triple_names::GLOBAL_WARMING, word_names::POPULISM);
        $test_name = '... and the solution of the problem in the tooltip';
        $t->assert_text_contains($test_name, $svg_bars, triple_names::REDUCE_EMISSIONS);
        // the range of a number is the bar behind the dot and the bounds are named in the
        // tooltip like in the table cell, with the assumed mark behind the centre number
        $svg_range = $t_val->value_list_range_ui()->table_to_svg(chart_types::RANGE_BARS, $msg_ui);
        $test_name = 'the range bounds are named in the tooltip like in the table';
        $t->assert_text_contains($test_name, $svg_range, $centre_txt . ' ' . word_names::TRILLION . ' '
            . word_names::EUR . value_list_ui::RANGE_START . '0.88' . value_list_ui::RANGE_SEP . '5.5' . value_list_ui::RANGE_END);
        $test_name = '... and drawn as the bar of the row';
        $t->assert($test_name, substr_count($svg_range, 'class="rng"'), 1);
        $test_name = 'the ranking is drawn as a scatter plot of the gain against the loss';
        $t->assert($test_name, substr_count($svg_points, 'class="pt"'), count($t_val->solution_prio_rows()));
        $test_name = '... with the solutions numbered in the legend';
        $t->assert_text_contains($test_name, $svg_points, '<tspan class="n">1</tspan>  ' . triple_names::REDUCE_EMISSIONS);
        // the reason column stands before the solution column but no value of the ranking
        // names a reason, so the empty column neither names the points nor the chart
        $test_name = '... named by the solution column and not by the empty reason column before it';
        $t->assert_text_contains($test_name, $svg_points, 'aria-label="' . word_names::SOLUTION);
        $t->assert_text_not_contains($test_name, $svg_points, word_names::REASON);
        $test_name = '... and the axes named by the plotted columns';
        $t->assert_text_contains($test_name, $svg_points,
            word_names::GAIN . ' ' . msg_id::CHART_VERSUS->text() . ' ' . word_names::LOSS);
        // negative: a column that the table does not have is reported instead of plotting another
        $svg_none = $t_val->value_list_range_ui()->table_to_svg(
            chart_types::RANGE_BARS, $msg_ui, chart_cols: [word_names::PI]);
        $test_name = 'a column that the table does not have draws no chart';
        $t->assert($test_name, $svg_none, '');
        $test_name = '... and is reported to the user';
        $t->assert_true($test_name, $msg_ui->has_msg_id(msg_id::CHART_COLUMN_NOT_FOUND));
        $msg_ui->reset();
        $test_name = 'an empty value list draws no chart';
        $t->assert($test_name, new value_list_ui()->table_to_svg(chart_types::RANGE_BARS, $msg_ui), '');

        // TODO add a test that if a view contains beside the "2023 (year)"
        //      no other phrase that contains the word "2023"
        //      the "(year)" is not shown to the user, because the user will assume i

        // TODO add s test that if a view contains the word "city"
        //      or many cities and never a "canton"
        //      and the phrase "Zurich (city)" is shown
        //      only "Zurich" without "(city)" is used
        //      because the user will assume "city of Zurich"
        //      on mouseover show the complete phrase name with the description


    }

}