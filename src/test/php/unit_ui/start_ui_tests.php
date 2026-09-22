<?php

/*

    test/unit/html/view.php - testing of the html frontend functions for view
    -----------------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::SHARED_CONST . 'results.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::VALUE . 'value_list.php';

use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class start_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $msg = new user_message();
        $t_phr = new test_phrases($t);
        $t_val = new test_values($t);
        $t_res = new test_results($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html start page ';
        $t->header($ts);

        // load th cache used for the start page
        /*
        $json_str = file_get_contents(files::MESSAGE_PATH . files::START_PAGE_DATA_FILE);
        $json_msg_array = json_decode($json_str, true);
        $ctrl = new controller();
        $json_array = $ctrl->check_api_msg($json_msg_array);
        $imp = new import();
        $dto = $imp->get_data_object($json_array, $t->usr1);
        */
        // the start page shows the values of "global problem" as a table, so its cache needs the
        // problem links, the column definitions and the values of the global issues
        $dto_ui = new data_object();
        $dto_ui->online = false;
        $dto_ui->add_phrases($t_phr->list_global_problems_ui(), $msg);
        $dto_ui->val_lst = $t_val->value_list_solution_prio_ui();

        $list = new ui_list();
        $test_page = $html->text_h2('start page display test');
        $start_html = $list->start_list($dto_ui, $msg);
        $test_page .= $start_html;
        $t->html_page_test($test_page, 'start page', 'start_page', $msg);

        // the start page shows the values of the global problems as a table built from the data,
        // instead of the spreadsheet with the hard coded column headers and rows
        $test_name = 'the start page shows the global problems as a table';
        $t->assert_text_contains($test_name, $start_html, '<' . html_base::TABLE);
        $test_name = '... headed by the problem the row names';
        $t->assert_text_contains($test_name, $start_html, '>' . word_names::PROBLEM . '</a>');
        $test_name = '... and the solution of each problem';
        $t->assert_text_contains($test_name, $start_html,
            '>' . triple_names::REDUCE_EMISSIONS . '</a>');

        // a measured figure of a problem that fits no column of the ranking, e.g. the global
        // temperature of a year, stays on the page of the problem instead of adding a row
        $test_name = 'a measured value of a problem without a ranking column adds no row';
        $dto_ui->val_lst = $t_val->value_list_solution_prio_measured_ui();
        $t->assert_text_not_contains($test_name, $list->start_list($dto_ui, $msg),
            '>' . word_names::YEAR_2024 . '</a>');

        // a number of the ranking can be calculated instead of measured, e.g. the happy time
        // points that the loss of a problem costs, and such a result is shown in the same table;
        // the full table is asked, because it shows every column that the numbers suggest
        $test_name = 'the start page shows a calculated number of a problem';
        $col_url = [url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_ALL];
        $dto_ui->val_lst = $t_val->value_list_solution_prio_ui();
        $dto_ui->set_result_list($t_res->result_list_solution_prio_ui());
        $t->assert_text_contains($test_name, $list->start_list($dto_ui, $msg, $col_url),
            (string)results::TV_PRIO_LOSS_HTP);

        // negative: without a result the table shows the measured numbers only
        $test_name = '... and without a result only the measured numbers';
        $dto_ui->set_result_list(new result_list_ui());
        $t->assert_text_not_contains($test_name, $list->start_list($dto_ui, $msg, $col_url),
            (string)results::TV_PRIO_LOSS_HTP);

        // a problem whose target values are all calculated has no value of its own in any column,
        // so its row is built from the results alone, which is how the start page shows a problem
        // file that states only the inputs behind its numbers
        $test_name = 'the start page shows a row whose numbers are all results';
        $dto_ui->val_lst = $t_val->value_list_solution_prio_other_rows_ui();
        $dto_ui->set_result_list($t_res->result_list_solution_prio_first_row_ui());
        $row_html = $list->start_list($dto_ui, $msg, $col_url);
        $t->assert_text_contains($test_name, $row_html,
            '>' . triple_names::GLOBAL_WARMING . '</a>');
        $test_name = '... with the solution of that row';
        $t->assert_text_contains($test_name, $row_html,
            '>' . triple_names::REDUCE_EMISSIONS . '</a>');

        // negative: without the results the row of that problem is gone
        $test_name = '... and without the results the row is not shown';
        $dto_ui->set_result_list(new result_list_ui());
        $t->assert_text_not_contains($test_name, $list->start_list($dto_ui, $msg, $col_url),
            '>' . triple_names::GLOBAL_WARMING . '</a>');

        // the start page opens with the simple table, whose "..." header links to the same page
        // with every column and the range of each number; the full page has no "..." header
        $test_name = 'the simple start page links to the full table';
        $url_array = [url_var::MASK => views::START_ID];
        $t->assert_text_contains($test_name, $list->start_list($dto_ui, $msg, $url_array),
            url_var::DISPLAY_LIST_COLUMNS . '=' . value_list_ui::COLUMN_TIERS_ALL);
        $test_name = 'the full start page has no "..." header';
        $url_array[url_var::DISPLAY_LIST_COLUMNS] = value_list_ui::COLUMN_TIERS_ALL;
        $url_array[url_var::DISPLAY_LIST_RANGE] = url_var::TRUE;
        $t->assert_text_not_contains($test_name, $list->start_list($dto_ui, $msg, $url_array),
            '<' . html_base::TH . '>' . msg_id::THREE_POINTS->text());

        // negative: without the values of the global issues the start page shows no table at all
        // instead of an empty header row
        $test_name = 'without values the start page shows no table';
        $dto_empty = new data_object();
        $dto_empty->online = false;
        $dto_empty->add_phrases($t_phr->list_global_problems_ui(), $msg);
        $t->assert($test_name, $list->start_list($dto_empty, $msg), '');
    }

}