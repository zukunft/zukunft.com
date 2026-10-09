<?php

/*

    test/php/unit_ui/start_page_ui_tests.php - snapshot the start page ranking for every selection of its sub menus
    ----------------------------------------

    the "... more" tail raises the rows shown, the "..." header selects the column tiers with
    or without the ranges and the form of the ranking (table, chart or both); every combination
    that the sub menus offer is rendered from the unit fixtures and compared with a fixed html
    page in src/test/resources/web/html/start_page/, so a change of the ranking is checked fast
    and without a database (see docs/llm/testing.md)


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'table_forms.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value_list.php';
include_once test_paths::CONST . 'paths.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_phrases.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\table_forms;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class start_page_ui_tests
{

    // the snapshot name of the default start page, followed by the url vars of a selection
    const string FILE_NAME = 'start_page';

    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $msg = new user_message();
        $t_phr = new test_phrases($t);
        $t_val = new test_values($t);

        // start the test section (ts)
        $ts = 'unit ui html start page selections ';
        $t->header($ts);

        // the start page shows the ranking of the global problems from the request cache, so
        // the cache gets the problem links, the column definitions and the values of the ranking
        $dto_ui = new data_object();
        $dto_ui->online = false;
        // every problem is linked to "global problem", else the page shows the linked five only
        $dto_ui->add_phrases($t_phr->list_global_problems_all_ui(), $msg);
        $dto_ui->val_lst = $t_val->value_list_start_page_ui();
        $list = new ui_list();

        // the default of each sub menu is the page without the url var, so the default is
        // tested once and every selection against it
        $rows = [null, Config::LIMIT_MORE_LIST, value_list_ui::LIMIT_ALL];
        $columns = [[null, null]];
        foreach (array_keys(value_list_ui::COLUMN_TIER_NAMES) as $tiers) {
            $columns[] = [$tiers, url_var::FALSE];
            $columns[] = [$tiers, url_var::TRUE];
        }
        $forms = [null, table_forms::CHART, table_forms::TABLE_AND_CHART];
        // the problems of the ranking, which a list with more rows shows all
        $problems = [];
        foreach ($t_val->solution_prio_rows() as [$problem]) {
            $problems[] = $problem->name();
        }
        sort($problems);
        $files = [];
        foreach ($rows as $size) {
            foreach ($columns as [$tiers, $range]) {
                foreach ($forms as $form) {
                    $url_array = $this->selection_url($size, $tiers, $range, $form);
                    $file_name = $this->selection_file($url_array);
                    $page_html = $list->start_list($dto_ui, $msg, $url_array);
                    $t->html_start_page_test($html->text_h2($file_name) . $page_html, $file_name, $msg);
                    $this->assert_selection($t, $page_html, $file_name, $size, $tiers, $range, $form, $problems, $msg);
                    $files[] = test_paths::RESOURCE . test_paths::HTML . test_paths::START_PAGE . $file_name . test_files::HTML;
                    $msg->reset();
                }
            }
        }
        $this->delete_unused_snapshots($t, $files);
    }

    /**
     * the url of the start page with the given selections of its sub menus
     *
     * @param int|null $size the rows shown or null for the default
     * @param int|null $tiers the column tiers left out (value_list_ui::COLUMN_TIERS_*) or null for the default
     * @param string|null $range url_var::TRUE to show the ranges, url_var::FALSE for the numbers only or null for the default
     * @param table_forms|null $form the form of the ranking or null for the table
     * @return array the url vars of the start page with the selections
     */
    private function selection_url(?int $size, ?int $tiers, ?string $range, ?table_forms $form): array
    {
        $result = [url_var::MASK => views::START_ID];
        if ($size !== null) {
            $result[url_var::DISPLAY_LIST_SIZE] = $size;
        }
        if ($tiers !== null) {
            $result[url_var::DISPLAY_LIST_COLUMNS] = $tiers;
            $result[url_var::DISPLAY_LIST_RANGE] = $range;
        }
        if ($form != null) {
            $result[url_var::DISPLAY_LIST_AS] = $form->value;
        }
        return $result;
    }

    /**
     * @param array $url_array the url vars of a selection
     * @return string the snapshot name, e.g. "start_page_dls_20_dlc_0_dlr_1_dla_chart", so that the
     *                file of a selection can be found by its url
     */
    private function selection_file(array $url_array): string
    {
        $result = self::FILE_NAME;
        foreach ($url_array as $key => $value) {
            if ($key != url_var::MASK) {
                $result .= '_' . $key . '_' . $value;
            }
        }
        return $result;
    }

    /**
     * the checks of one selection that do not depend on the snapshot: the parts that the form
     * shows, the rows that the size shows, the range and the main columns that are selected
     *
     * @param test_cleanup $t the test environment
     * @param string $page_html the rendered ranking
     * @param string $file_name the snapshot name, which names the selection in the test name
     * @param int|null $size the rows shown or null for the default
     * @param int|null $tiers the column tiers left out or null for the default
     * @param string|null $range url_var::TRUE if the ranges are selected
     * @param table_forms|null $form the form of the ranking or null for the table
     * @param array $problems the names of every problem of the ranking, sorted
     * @param user_message $msg the messages of the rendering, which explain a missing table or chart
     */
    private function assert_selection(
        test_cleanup $t,
        string       $page_html,
        string       $file_name,
        ?int         $size,
        ?int         $tiers,
        ?string      $range,
        ?table_forms $form,
        array        $problems,
        user_message $msg
    ): void
    {
        $test_name = $file_name . ' shows the "..." menu';
        $t->assert_text_contains($test_name, $page_html, styles::MENU_COLUMN);
        // more and all show every problem of the ranking; the names shown and the message of
        // the rendering are compared, so a failure says which rows are missing and why
        if ($size !== null) {
            $test_name = $file_name . ' shows every problem';
            $shown = array_values(array_filter($problems, fn($name) => str_contains($page_html, $name)));
            $result = implode(', ', $shown);
            if (!$msg->is_ok()) {
                $result .= ' (' . $msg->text() . ')';
            }
            $t->assert($test_name, $result, implode(', ', $problems));
        } else {
            // negative: the short list ends before the last problem of the ranking
            $test_name = $file_name . ' shows the short list only';
            $t->assert_false($test_name, str_contains($page_html, triple_names::PROPRIETARY_SOFTWARE));
        }
        // the ranges are shown in the table only, the range bars of a chart show them always
        if ($form == null) {
            $test_name = $file_name . ' shows the range only if selected';
            $t->assert($test_name, str_contains($page_html, value_list_ui::RANGE_SEP), $range == url_var::TRUE);
            // the reason columns are the main columns, which only the mayor table leaves out
            $test_name = $file_name . ' shows the main columns only if selected';
            $t->assert($test_name, str_contains($page_html, styles::COL_MAIN),
                $tiers !== null and $tiers <= value_list_ui::COLUMN_TIERS_EX_MINOR);
            // each tier adds its own columns: the main tier the reason, the minor tier the loss in
            // percent of the GDP, the initial effort and the loss reduction and only the full table
            // the marginal reward ratio; the names in the header are compared, so a failure says
            // which column is missing or too much
            $test_name = $file_name . ' shows the columns of its tiers';
            $lib = new library();
            $header = $lib->html_to_text($lib->str_left_of($page_html, '</tr>'));
            $last_tier = count(value_list_ui::COLUMN_TIER_NAMES) - ($tiers ?? value_list_ui::COLUMN_TIERS_EX_MAIN);
            $tier_columns = [
                word_names::REASON => 2,
                word_names::GDP => 3,
                triple_names::INITIAL_EFFORT => 3,
                triple_names::LOSS_REDUCTION => 3,
                triple_names::REWARD_RATIO => 4,
            ];
            $shown = array_keys(array_filter($tier_columns, fn($name) => str_contains($header, $name), ARRAY_FILTER_USE_KEY));
            $expected = array_keys(array_filter($tier_columns, fn($level) => $level <= $last_tier));
            $t->assert($test_name, implode(', ', $shown), implode(', ', $expected));
        }
    }

    /**
     * remove the snapshot of a selection that the sub menus do not offer any more
     *
     * @param test_cleanup $t the test environment
     * @param array $files the snapshot files of this run
     */
    private function delete_unused_snapshots(test_cleanup $t, array $files): void
    {
        $lib = new library();
        $dir = test_paths::RESOURCE . test_paths::HTML . test_paths::START_PAGE;
        if (is_dir($dir)) {
            foreach ($lib->dir_files($dir) as $path) {
                if (str_ends_with($path, test_files::HTML) and !in_array($path, $files)) {
                    $t->delete_path_file($path);
                }
            }
        }
    }

}
