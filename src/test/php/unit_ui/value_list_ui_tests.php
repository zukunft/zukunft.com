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
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files as files_shared;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
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
        $t_val = new test_values($t);
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
        $test_page .= 'as long list with small page: ' . $html->lf() .  $t_val->list_all_ui($msg)->list($msg_ui, $phr_lst_context_ui, '', '', 4) . '<br><br>';
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
        $tbl_html = $t_val->value_list_most_relevant_ui()->table_by_related_columns($msg_ui);
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
        // one header cell per column phrase, plus the empty top left cell and the "Values" cell
        // of the values that share no column phrase
        $test_name = 'never more phrase columns than fit on the widest screen';
        $t->assert_true($test_name,
            substr_count($tbl_html, '<th') <= position_types::MAX_SIDE_COLUMNS + 2);
        $test_name = 'the header is shown before the first row';
        $t->assert_text_order($test_name, $tbl_html, '<th', '<td');
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
        $lib = new library();
        $tbl_header = $t_val->value_list_most_relevant_ui()
            ->table_by_related_columns($msg_ui, $phr_lst_context_ui, '', [], true);
        $t->assert_text_contains($test_name,
            $lib->str_left_of($tbl_header, '<table'), '>' . word_names::INHABITANTS . '</a>');

        $t->subheader($ts . 'more tail');
        $tail_html = $t_val->list_all_ui($msg)->list($msg_ui, $phr_lst_context_ui, '', '', 1);
        $test_name = 'the more tail is a link to the phrase values view';
        $t->assert_text_contains($test_name, $tail_html, url_var::MASK . '=' . views::PHRASE_VALUES_ID);
        $test_name = 'the more tail link selects the page phrase';
        $t->assert_text_contains($test_name, $tail_html, 'id=' . $phr_inhabitant->id());
        $lst_all_ui = $t_val->list_all_ui($msg);
        $tail_plain = $lst_all_ui->list($msg_ui, new phrase_list_ui(), '', '', 1);
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
        $full_html = $lst_all_ui->list($msg_ui, $phr_lst_context_ui, '', '', $lst_all_ui->count());
        $test_name = 'a list that shows every value has no more tail';
        $t->assert_text_not_contains($test_name, $full_html, msg_id::MORE->text());

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