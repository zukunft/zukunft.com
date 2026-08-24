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
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
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
        $trp_zh_city = new triple_ui($t_trp->zh_city()->api_json());
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
        $trp_problem = new triple_ui($t_trp->global_problem()->api_json());
        $test_page .= $html->text_h2($trp_problem->name() . ' as a table');
        $test_page .= 'as table: ' . $list->table_with_related_columns(
                $trp_problem, $msg, $dto_prio, true, false) . '<br>';
        $t->html_page_test($test_page, 'phrase', 'phrase', $msg);

        $t->subheader($ts . 'table with related columns');
        $tbl_html = $list->table_with_related_columns($trp_zh_city, $msg, $dto);
        $test_name = 'the values of a triple are shown as a table';
        $t->assert_text_contains($test_name, $tbl_html, '<table');
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
        // the problem per unit; the unit belongs to the value like in the target layout of the
        // view-validation of solution_prio.json
        $test_name = 'the scaling of a value does not name a row';
        $t->assert_text_not_contains($test_name, $tbl_html, '>' . word_names::BILLION . '</a>');
        $test_name = 'the measure of a value does not name a row';
        $t->assert_text_not_contains($test_name, $tbl_html, '>' . word_names::EUR . '</a>');
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