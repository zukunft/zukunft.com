<?php

/*

    test/php/unit_ui/config_tree_ui_tests.php - testing of the read only tree of the system configuration
    -----------------------------------------


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

include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::HELPER . 'config_tree.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED . 'url_var.php';
include_once test_paths::CREATE . 'test_config_trees.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config_tree;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_config_trees;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class config_tree_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        $html = new html_base();
        $t_cft = new test_config_trees($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html config tree ';
        $t->header($ts);

        $t->subheader($ts . 'search');

        $test_name = 'the search finds the keys by their name ignoring the case';
        $cft = $t_cft->tree_ui();
        $hits = $cft->hits($cft->nodes, 'DELAY');
        $hit_names = array_map(fn(array $hit) => $hit[1]->name, $hits);
        $t->assert($test_name, $hit_names, [test_config_trees::START_DELAY, test_config_trees::MAX_DELAY]);
        $test_name = '... and by their tooltip';
        $hits = $cft->hits($cft->nodes, 'connection retry');
        $hit_names = array_map(fn(array $hit) => $hit[1]->name, $hits);
        $t->assert($test_name, $hit_names, [test_config_trees::START_DELAY]);
        $test_name = '... each with the path of its parent keys';
        $t->assert($test_name, $hits[0][0] ?? [], [test_config_trees::DATABASE, test_config_trees::RETRY]);
        // negative: a search that no name and no tooltip contains has no hit
        $test_name = 'a search without a match has no hit';
        $t->assert($test_name, $cft->hits($cft->nodes, 'no-such-setting'), []);
        $test_name = '... and says so on the page';
        $page_no_hit = $cft->html('no-such-setting', '');
        $t->assert_text_contains($test_name, $page_no_hit, $mtr->txt(msg_id::CONFIG_TREE_NO_MATCH));
        $test_name = 'the match is highlighted';
        $t->assert($test_name, $cft->marked(test_config_trees::MAX_DELAY, 'DEL'), 'max <mark>del</mark>ay');
        $test_name = '... but an unmatched text not';
        $t->assert($test_name, $cft->marked(test_config_trees::MAX_DELAY, 'cache'), test_config_trees::MAX_DELAY);
        $test_name = 'the hit list names the path of each hit';
        $page_hit = $cft->html('delay', '');
        $t->assert_text_contains($test_name, $page_hit,
            test_config_trees::DATABASE . config_tree::PATH_SEPARATOR . test_config_trees::RETRY);

        $t->subheader($ts . 'scope');

        $test_name = 'the scope shows the branch of one top level key';
        $branch_names = array_map(fn($cfn) => $cfn->name, $cft->branch(test_config_trees::USER));
        $t->assert($test_name, $branch_names, [test_config_trees::USER]);
        $test_name = '... and all shows the whole tree';
        $branch_names = array_map(fn($cfn) => $cfn->name, $cft->branch(config_tree::SCOPE_ALL));
        $t->assert($test_name, $branch_names, [test_config_trees::DATABASE, test_config_trees::USER,
            test_config_trees::SELECT, test_config_trees::TEST, test_config_trees::POD]);
        // negative: a scope that is no top level key shows no branch but the hint
        $test_name = 'an unknown scope shows no branch';
        $t->assert($test_name, $cft->branch(test_config_trees::RETRY), []);
        $test_name = '... but says that this part does not exist';
        $page_unknown = $cft->html('', test_config_trees::RETRY);
        $t->assert_text_contains($test_name, $page_unknown, $mtr->txt(msg_id::CONFIG_TREE_SCOPE_UNKNOWN));

        $t->subheader($ts . 'columns');

        $test_name = 'the columns start with select, user and test and end with pod';
        $col_names = array_map(fn(array $col) => array_map(fn($cfn) => $cfn->name, $col), $cft->columns($cft->nodes));
        $t->assert($test_name, $col_names, [[test_config_trees::SELECT], [test_config_trees::USER],
            [test_config_trees::TEST, test_config_trees::DATABASE, test_config_trees::POD]]);
        $test_name = '... and the page shows the three columns';
        $col_class = html_base::CLASS_HTML . '="' . styles::CONFIG_COLUMN . '"';
        $t->assert($test_name, substr_count($cft->html('', ''), $col_class), 3);
        // negative: a scope with one top level key leaves the empty columns out
        $test_name = 'a scope with one top level key uses one column';
        $t->assert($test_name, substr_count($cft->html('', test_config_trees::USER), $col_class), 1);

        $t->subheader($ts . 'all entries');

        $test_name = 'a long level shows all its entries at once';
        $page_select = $cft->html('', test_config_trees::SELECT);
        $dt_tag = '<' . html_base::DT;
        $t->assert($test_name, substr_count($page_select, $dt_tag), count(test_config_trees::SELECT_ENTRIES));
        // negative: no entry is hidden in a "more" step, so the select key is the only details part
        $test_name = '... without a more step';
        $t->assert($test_name, substr_count($page_select, '<' . html_base::DETAILS), 1);

        $t->subheader($ts . 'tree');

        $page = $cft->html('', '');
        $test_name = 'a key with html is shown as text';
        $t->assert_text_not_contains($test_name, $page, test_config_trees::SCRIPT_KEY);
        $t->assert_text_contains($test_name, $page, test_config_trees::SCRIPT_KEY_ESCAPED);
        $test_name = 'a setting shows its value with the unit';
        $t->assert_text_contains($test_name, $page,
            '<dd>1' . $html->span(test_config_trees::SEC, styles::CONFIG_UNIT) . '</dd>');
        $test_name = 'a long tooltip gets a button with the full text';
        $t->assert_text_contains($test_name, $page, html_base::POPOVER_TARGET . '="' . config_tree::TIP_ID_PREFIX);
        // negative: a short tooltip is only the mouseover text
        $test_name = 'a short tooltip gets no button';
        $lan_tip_id = config_tree::TIP_ID_PREFIX . test_config_trees::USER
            . config_tree::ID_SEPARATOR . test_config_trees::LANGUAGE;
        $t->assert_text_not_contains($test_name, $page, $lan_tip_id);
        $test_name = 'the search is a get form, so it never changes data';
        $t->assert_text_contains($test_name, $page, html_base::METHOD . '="' . html_base::METHOD_GET . '"');
        // negative: no submit field of a write form, the submit button of the search is fine
        $test_name = '... without the submit field of a write form';
        $t->assert_text_not_contains($test_name, $page, html_base::NAME . '="' . url_var::POST_SUBMIT . '"');

        $t->subheader($ts . 'page cache');

        // the search and the scope are not part of the cache key, so such a page is rendered for each request
        $ui = new frontend();
        $test_name = 'the configuration page without search is cached';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::SYSTEM_CONFIG_ID . '&id=0');
        $test_name = '... but not with a search';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID, url_var::PATTERN => 'cache'];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');
        $test_name = '... and not with a scope';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID, url_var::CONFIG_PART => test_config_trees::DATABASE];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // the component renderer with the test tree, also as the snapshot of the page
        $sys_page = new system_page();
        $test_page = $html->text_h2('config tree');
        $test_page .= $sys_page->config_tree($msg, [], true, $t_cft->tree_ui());
        $test_page .= $html->text_h2('config tree search');
        $test_page .= $sys_page->config_tree($msg, [url_var::PATTERN => 'delay'], true, $t_cft->tree_ui());
        $test_page .= $html->text_h2('config tree scope');
        $url_scope = [url_var::CONFIG_PART => test_config_trees::USER];
        $test_page .= $sys_page->config_tree($msg, $url_scope, true, $t_cft->tree_ui());
        $t->html_page_test($test_page, 'config tree', 'config_tree', $msg);

        // the read only settings page as view 126 shows it, which the developer opens to check the layout
        $settings_page = $sys_page->system_tile(msg_id::SYSTEM_TITLE_SYSTEM_CONFIG);
        $settings_page .= $sys_page->config_tree($msg, [], true, $t_cft->tree_ui());
        $t->html_config_page_test($settings_page, 'system_config_read_only', $msg);
    }

}
