<?php

/*

    test/unit/html/phrase.php - testing of the phrase display functions
    -------------------------
  

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

use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_val = new test_values($t);
        $t_phr = new test_phrases($t);
        $t_frm = new test_formulas($t);
        $list = new ui_list();
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html phrase ';
        $t->header($ts);

        $wrd = new phrase($t_wrd->word()->phrase()->api_json());
        $trp = new phrase($t_trp->triple_pi()->phrase()->api_json());
        $test_page = $html->text_h2('Phrase display test');
        $test_page .= 'word phrase with tooltip: ' . $wrd->name_tip() . '<br>';
        $test_page .= 'word phrase with link: ' . $wrd->name_link() . '<br>';
        $test_page .= 'triple phrase with tooltip: ' . $trp->name_tip() . '<br>';
        $test_page .= 'triple phrase with link: ' . $trp->name_link() . '<br>';

        // the table view renders the values of a phrase with one column per related phrase;
        // word.html shows the word case, so here the triple case, because the component type
        // is used by one view for both phrase classes
        $dto = new data_object();
        $dto->val_lst = $t_val->value_list_zh_impact_ui();
        $trp_zh_city = $t_trp->zh_city_ui();
        $test_page .= $html->text_h2('values of ' . $trp_zh_city->name() . ' as a table');
        // the page shows the table with the header that names the selected phrase, but without
        // the border, because the page already groups the tables by a title
        $test_page .= 'as table: ' . $list->table_with_related_columns(
                $trp_zh_city, $msg, $dto, true, false) . '<br>';

        // the table of the global issues as shown on the start page: the start phrase is the
        // "global problem" triple, and the data object carries the solution_prio.json data via
        // the create factories - the cost and gain values of the problems, the triples that
        // link each problem to "global problem" and the table column definitions
        $dto_prio = new data_object();
        $dto_prio->val_lst = $t_val->value_list_solution_prio_ui();
        $dto_prio->phr_lst = $t_phr->list_global_problems_ui();
        $trp_problem = $t_trp->global_problem_ui();
        $test_page .= $html->text_h2($trp_problem->name() . ' as a table');
        $test_page .= 'as table: ' . $list->table_with_related_columns(
                $trp_problem, $msg, $dto_prio, true, false) . '<br>';
        $t->html_page_test($test_page, 'phrase', 'phrase', $msg);

        $t->subheader($ts . 'table with related columns');
        $tbl_html = $list->table_with_related_columns($trp_zh_city, $msg, $dto);
        $test_name = 'the values of a triple are shown as a table';
        $t->assert_text_contains($test_name, $tbl_html, '<table');
        // negative: this table has fewer rows than the configured limit, so nothing is cut off
        // and a "... and n more" row would tell the user about rows that do not exist
        $test_name = 'a table below the row limit has no more row';
        $t->assert_text_not_contains($test_name, $tbl_html, msg_id::MORE->text());
        // the page phrase is the context of every row, so the row is named by the remaining
        // phrase of the value, e.g. "Zurich" for the value of "Zurich" and "Zurich (city)"
        $test_name = 'the other phrase of the value is shown as the row name';
        $t->assert_text_contains($test_name, $tbl_html, '>' . word_names::ZH . '</a>');
        // the page phrase names the table in the header, so it must not be repeated in a row;
        // the rows are the part behind the header row and the row shows the name of a phrase,
        // not its key, so the check uses the phrase name
        $test_name = 'the phrase of the page is not repeated as a row name';
        $lib = new library();
        $t->assert_text_not_contains($test_name,
            $lib->str_right_of($tbl_html, '</tr>'), triple_names::CITY_ZH_NAME);

        // the header names the selected phrase centred above the table, so that a table taken
        // out of its page still says what it is about
        $test_name = 'with the header the selected phrase is linked above the table';
        $tbl_header = $list->table_with_related_columns($trp_zh_city, $msg, $dto, true, true);
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_header, '<table'), '>' . triple_names::CITY_ZH_NAME . '</a>');
        $test_name = '... and the header is centred';
        $t->assert_text_contains($test_name, $tbl_header, styles::TEXT_CENTER);
        $test_name = 'without the header the table starts with the column row';
        $tbl_no_header = $list->table_with_related_columns($trp_zh_city, $msg, $dto, false, true);
        $t->assert_text_not_contains($test_name,
            $lib->str_left_of($tbl_no_header, '<table'), triple_names::CITY_ZH_NAME);
        $test_name = '... but the rows are still shown';
        $t->assert_text_contains($test_name, $tbl_no_header, '<table');

        // the border is switched off where the page already groups the tables by a title
        $test_name = 'with the border the table has the lines between the cells';
        $t->assert_text_contains($test_name, $tbl_header, 'table-bordered');
        $test_name = 'without the border the table has no lines between the cells';
        $tbl_no_border = $list->table_with_related_columns($trp_zh_city, $msg, $dto, true, false);
        $t->assert_text_not_contains($test_name, $tbl_no_border, 'table-bordered');

        // one row shows one item of the phrase, so the header stays in the singular and small
        $test_name = 'a table with one row names the phrase in the singular';
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_header, '<table'), '>' . triple_names::CITY_ZH_NAME . '</a>');
        $test_name = '... in the smaller label size';
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_header, '<table'), '<' . html_base::H5 . '>');

        $t->subheader($ts . 'global issues table');
        // the "global problem" phrase is in no value group: the values of "global warming" and
        // "populism" are found via the problem triples of the data object phrase list
        $tbl_html = $list->table_with_related_columns($trp_problem, $msg, $dto_prio);
        $test_name = 'the values of the problems linked to the page phrase are shown';
        $t->assert_text_contains($test_name, $tbl_html, '<table');
        $test_name = '... incl. the global warming problem';
        $t->assert_text_contains($test_name, $tbl_html, triple_names::GLOBAL_WARMING);
        $test_name = '... and the populism problem';
        $t->assert_text_contains($test_name, $tbl_html, word_names::POPULISM);
        $test_name = '... with the cost of the global warming problem';
        $t->assert_text_contains($test_name, $tbl_html, '31.5');
        // a page must not fill the screen, because the user messages are shown below the view,
        // so the table shows at most the configured number of rows; the same config key and the
        // same fallback as value_list::configured_row_limit, because the unit tests use an
        // empty frontend config, so that here the fallback is the effective limit
        global $ui_sys;
        $row_limit = Config::LIMIT_SHORT_LIST;
        if ($ui_sys?->cfg !== null) {
            $row_limit = (int)$ui_sys->cfg->get_by(
                [words::ENTRIES, words::INITIAL, words::SELECT], $msg, Config::LIMIT_SHORT_LIST);
        }
        // one row per problem plus the header row; only the problems linked to "global problem"
        // are rows of this table, so it stays within the limit and is not cut
        $test_name = 'the table shows at most the configured number of rows';
        $t->assert_true($test_name,
            substr_count($tbl_html, '<' . html_base::TR . '>') <= $row_limit + 1);

        // with more rows than the limit the table ends with a "... and n more" row, so that the
        // rows which do not fit are not lost but reachable
        $cut_limit = 2;
        $rel_lst = $t_phr->list_global_problems_ui();
        $tbl_cut = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg, $t_phr->list_global_problem_context_ui(), '', $rel_lst->column_names(),
            false, true, $rel_lst, $cut_limit);
        // the shown rows plus the header row and the "... and n more" row
        $test_name = 'a table with more rows than the limit is cut to the limit';
        $t->assert($test_name,
            substr_count($tbl_cut, '<' . html_base::TR . '>'), $cut_limit + 2);
        $test_name = '... and the last row tells that more rows exist';
        $t->assert_text_contains($test_name, $tbl_cut, msg_id::MORE->text());
        $test_name = '... as a link to the view with all values of the page phrase';
        $t->assert_text_contains($test_name, $tbl_cut,
            url_var::MASK . '=' . views::PHRASE_VALUES_ID);

        // if the page of the table is known, the url names the list size and the tail calls
        // the same page with the size of the next list version instead (docs/llm/frontend.md
        // "Short, more and all"), so the reader stays on the page and sees more rows of it;
        // the more size is the config.yaml value, which the unit test reads from the cache
        // (see value_list::configured_more_limit), so it is expected to equal the fallback const
        $page_url = [url_var::MASK => views::START_ID, url_var::DISPLAY_LIST_SIZE => $cut_limit];
        $tbl_page = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg, $t_phr->list_global_problem_context_ui(), '', $rel_lst->column_names(),
            false, true, $rel_lst, null, $page_url);
        $test_name = 'the url names the number of rows shown';
        $t->assert($test_name, substr_count($tbl_page, '<' . html_base::TR . '>'), $cut_limit + 2);
        $test_name = '... and the more tail calls the same page with the size of the more list';
        $t->assert_text_contains($test_name, $tbl_page,
            url_var::DISPLAY_LIST_SIZE . '=' . Config::LIMIT_MORE_LIST);
        // negative: the tail leads to the same page and not to the view with all values
        $test_name = '... and not the view with all values of the page phrase';
        $t->assert_text_not_contains($test_name, $tbl_page,
            url_var::MASK . '=' . views::PHRASE_VALUES_ID);
        // the url names the list page as well: the second page starts behind the rows of the
        // first page and its tail counts only the rows behind itself
        $test_name = 'the first list page starts with the most relevant problem';
        $t->assert_text_contains($test_name, $tbl_page, '>' . triple_names::GLOBAL_WARMING . '</a>');
        $page_url[url_var::DISPLAY_LIST_PAGE] = 1;
        $tbl_page_two = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg, $t_phr->list_global_problem_context_ui(), '', $rel_lst->column_names(),
            false, true, $rel_lst, null, $page_url);
        $test_name = 'the second list page does not repeat the first page';
        $t->assert_text_not_contains($test_name, $tbl_page_two, '>' . triple_names::GLOBAL_WARMING . '</a>');
        $test_name = '... but starts with the row behind the first page';
        $t->assert_text_contains($test_name, $tbl_page_two, '>' . word_names::POVERTY . '</a>');
        // the rows behind the second page are all rows without the two pages, so the count is
        // taken from the table with every row instead of assuming the size of the fixture
        $tbl_all = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg, $t_phr->list_global_problem_context_ui(), '', $rel_lst->column_names(),
            false, true, $rel_lst, value_list_ui::LIMIT_ALL);
        $rest = substr_count($tbl_all, '<' . html_base::TR . '>') - 1 - 2 * $cut_limit;
        $test_name = '... and its tail counts the rows behind the second page only';
        $t->assert_text_contains($test_name, $tbl_page_two,
            msg_id::AND_MORE_BEFORE->text() . ' ' . $rest . ' ' . msg_id::MORE->text());
        // more than one row shows more than one item of the phrase, so the header uses the
        // plural; the "global problem" triple has none, so the English plural is guessed
        $test_name = 'a table with several rows names the phrase in the plural';
        $tbl_plural = $list->table_with_related_columns($trp_problem, $msg, $dto_prio, true, false);
        $t->assert_text_contains($test_name, $lib->str_left_of($tbl_plural, '<table'),
            '>' . triple_names::GLOBAL_PROBLEM . languages::DEFAULT_PLURAL_SUFFIX . '</a>');
        $test_name = '... centred above the table';
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_plural, '<table'), styles::TEXT_CENTER);
        // a table of several items is a list of its own, so its header is a bigger headline
        $test_name = '... in the bigger headline size';
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_plural, '<table'), '<' . html_base::H4 . '>');
        $test_name = '... and the gain of the populism solution';
        $t->assert_text_contains($test_name, $tbl_html, '34.1');
        // a scaling or measure phrase describes the number, so the row is the problem and not
        // the problem per unit; the unit belongs to the column like in the target layout of the
        // view-validation of solution_prio.json, which heads a column "potential loss
        // (trillion EUR / year)"
        $test_name = 'the scaling of a value does not name a row';
        $t->assert_text_not_contains($test_name,
            $lib->str_right_of($tbl_html, '</tr>'), '>' . word_names::BILLION . '</a>');
        $test_name = 'the measure of a value does not name a row';
        $t->assert_text_not_contains($test_name,
            $lib->str_right_of($tbl_html, '</tr>'), '>' . word_names::EUR . '</a>');
        // every value of this table is a potential loss or a potential gain, so "potential"
        // describes the whole table and cannot tell one row from another; it is named once in
        // the header and the row is left with the problem alone, like the solution column shows
        // only the solution; a list of one value keeps its phrases (see the zh row name above)
        $test_name = 'the header names a phrase of every value behind the phrase of the page';
        $t->assert($test_name, $lib->html_to_text($lib->str_left_of($tbl_plural, '<table')),
            triple_names::GLOBAL_PROBLEM . languages::DEFAULT_PLURAL_SUFFIX
            . ', ' . word_names::POTENTIAL);
        $test_name = '... and no row of the table repeats it';
        $t->assert_text_not_contains($test_name, $lib->str_right_of($tbl_plural, '<table'),
            '>' . word_names::POTENTIAL . '</a>');
        // without the header there is no place to name it, so it is left out instead of being
        // repeated in every row
        $test_name = 'without a header a phrase of every value is not shown';
        $t->assert_text_not_contains($test_name, $tbl_html, '>' . word_names::POTENTIAL . '</a>');
        // the values name the measure with the words "potential" and "loss", so "column loss"
        // of solution_prio.json is the definition that heads the potential loss column
        $test_name = 'the defined loss column heads the potential loss';
        $tbl_header_row = $lib->str_left_of($tbl_html, '</tr>');
        $t->assert_text_contains($test_name, $tbl_header_row, '>' . word_names::LOSS . '</a>');
        // without the definition the impact ranking would head that column by "potential", which
        // every value of the table carries and which therefore tells the reader nothing
        $test_name = '... instead of a phrase that every value carries';
        $t->assert_text_not_contains($test_name, $tbl_header_row, '>' . word_names::POTENTIAL . '</a>');
        // no value carries the phrase "solution", but the solutions are linked to it, so that
        // column names the solution of the problem row instead of a number
        $test_name = 'the solution column is headed by the solution phrase';
        $t->assert_text_contains($test_name, $tbl_header_row, '>' . word_names::SOLUTION . '</a>');
        $test_name = '... and shows the solution of the problem';
        $t->assert_text_contains($test_name, $tbl_html, '>' . triple_names::REDUCE_EMISSIONS . '</a>');
        // the solution is shown in its column, so it does not name the row any more; the text up
        // to the first closing cell is the header row plus the label of the first row
        $test_name = '... which is therefore no longer part of the row name';
        $t->assert_text_not_contains($test_name,
            $lib->str_left_of($tbl_html, '</' . html_base::TD . '>'), triple_names::REDUCE_EMISSIONS);
        // a phrase column is defined by the same column tiers as a value column, so it stands
        // where the definition puts it: the solution column between the loss and the gain column
        // instead of behind every column that holds a value; the cost column is defined too, but
        // no value carries it and no phrase is linked to it, so the table shows four columns
        // each headed by its own unit behind a translatable "in", except the solution column,
        // which holds no number and therefore no unit
        $test_name = 'the header shows the columns in the defined order with their unit';
        $unit_sep = ' ' . msg_id::VALUE_TBL_UNIT->text() . ' ';
        $t->assert($test_name, $lib->html_to_text($tbl_header_row),
            word_names::PROBLEM
            . ' ' . word_names::LOSS . $unit_sep . word_names::TRILLION . ' ' . word_names::EUR
            . ' ' . word_names::SOLUTION
            . ' ' . word_names::GAIN . $unit_sep . word_names::BILLION . ' ' . word_names::HTP);

        // negative: a phrase column exists only where a definition names it, so without the
        // definitions the same values show no solution column and the impact ranking alone
        // decides which phrase heads which column
        $test_name = 'without a column definition no column names a phrase of the row';
        $tbl_ranked = $t_val->value_list_solution_prio_ui()->table_by_related_columns(
            $msg, $t_phr->list_global_problem_context_ui(), '', [], false, true,
            $t_phr->list_global_problems_ui());
        $ranked_header = $lib->str_left_of($tbl_ranked, '</tr>');
        $t->assert_text_not_contains($test_name, $ranked_header, '>' . word_names::SOLUTION . '</a>');
        // the page phrase heads the row column only if its own phrase is a defined column, so
        // without the definitions that header stays empty although the page phrase is unchanged
        $test_name = '... and the row column has no header';
        $t->assert_text_contains($test_name, $ranked_header,
            '<' . html_base::TH . '></' . html_base::TH . '>');
        $test_name = '... but no value is dropped from the table';
        $t->assert_text_contains($test_name, $tbl_ranked, '31.5');

        $test_name = 'without the problem links no value matches the page phrase';
        $dto_no_links = new data_object();
        $dto_no_links->val_lst = $t_val->value_list_solution_prio_ui();
        $t->assert($test_name, $list->table_with_related_columns($trp_problem, $msg, $dto_no_links), '');

        // negative: the component type can be assigned to any object by a view, so a class
        // that is not a phrase must say so instead of silently showing nothing
        // this block stays last, because the frontend user_message has no reset() to clear
        // the expected warning for a following test
        $test_name = 'a class that is not a phrase shows no table';
        $frm = $t_frm->formula_increase_ui();
        $t->assert($test_name, $list->table_with_related_columns($frm, $msg, $dto), '');
        // the report is a warning, which informs the user but keeps the message ok,
        // so check for the message id instead of the ok status
        $test_name = 'a class that is not a phrase reports that the table is not implemented';
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::TABLE_COLUMNS_NOT_IMPLEMENTED));
    }

}