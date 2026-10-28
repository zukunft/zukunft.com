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

include_once html_paths::CONST . 'icons.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::HELPER . 'config_node.php';
include_once html_paths::HELPER . 'config_tree.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';
include_once test_paths::CREATE . 'test_config_trees.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config_node;
use Zukunft\ZukunftCom\main\php\web\helper\config_tree;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_names;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
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

        $t->subheader($ts . 'api mapper');

        // negative: a malformed child of the api message is skipped instead of stopping the page
        $test_name = 'a child that is no node is skipped';
        $cfn = new config_node([json_fields::NAME => test_config_trees::RETRY,
            json_fields::CONFIG_CHILDREN => [test_config_trees::SEC, [json_fields::NAME => test_config_trees::MAX_DELAY]]]);
        $t->assert($test_name, array_map(fn(config_node $child) => $child->name, $cfn->children), [test_config_trees::MAX_DELAY]);

        $test_name = 'the node mapper sets the value id';
        $cfn = new config_node();
        $cfn->api_mapper([json_fields::NAME => test_config_trees::MAX_DELAY, json_fields::ID => test_config_trees::MAX_DELAY_ID]);
        $t->assert($test_name, $cfn->id, test_config_trees::MAX_DELAY_ID);

        // negative: a malformed id names no value to overwrite, so the node has the id 0 of no value
        $test_name = 'the node mapper ignores a malformed value id';
        $cfn = new config_node();
        $cfn->api_mapper([json_fields::NAME => test_config_trees::MAX_DELAY, json_fields::ID => [test_config_trees::MAX_DELAY_ID]]);
        $t->assert($test_name, $cfn->id, 0);

        $test_name = 'the tree mapper adds the top level keys';
        $cft = new config_tree();
        $cft->api_mapper([[json_fields::NAME => test_config_trees::SYSTEM], [json_fields::NAME => test_config_trees::USER]]);
        $t->assert($test_name, array_map(fn(config_node $node) => $node->name, $cft->nodes), [test_config_trees::SYSTEM, test_config_trees::USER]);

        // negative: a top level entry that is no node is skipped instead of stopping the page
        $test_name = 'the tree mapper skips an entry that is no node';
        $cft = new config_tree();
        $cft->api_mapper([test_config_trees::SEC, [json_fields::NAME => test_config_trees::USER]]);
        $t->assert($test_name, array_map(fn(config_node $node) => $node->name, $cft->nodes), [test_config_trees::USER]);

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
        $t->assert($test_name, $hits[0][0] ?? [], [test_config_trees::SYSTEM, test_config_trees::DATABASE, test_config_trees::RETRY]);
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
            test_config_trees::SYSTEM . config_tree::PATH_SEPARATOR . test_config_trees::DATABASE
            . config_tree::PATH_SEPARATOR . test_config_trees::RETRY);

        $t->subheader($ts . 'scope');

        $test_name = 'the scope shows the branch of one top level key';
        $branch_names = array_map(fn($cfn) => $cfn->name, $cft->branch(test_config_trees::USER));
        $t->assert($test_name, $branch_names, [test_config_trees::USER]);
        $test_name = '... and all shows the whole tree';
        $branch_names = array_map(fn($cfn) => $cfn->name, $cft->branch(config_tree::SCOPE_ALL));
        $t->assert($test_name, $branch_names, [test_config_trees::SYSTEM, test_config_trees::USER, test_config_trees::SELECT]);
        // negative: a scope that is no top level key shows no branch but the hint
        $test_name = 'an unknown scope shows no branch';
        $t->assert($test_name, $cft->branch(test_config_trees::RETRY), []);
        $test_name = '... but says that this part does not exist';
        $page_unknown = $cft->html('', test_config_trees::RETRY);
        $t->assert_text_contains($test_name, $page_unknown, $mtr->txt(msg_id::CONFIG_TREE_SCOPE_UNKNOWN));

        $t->subheader($ts . 'columns');

        $test_name = 'the columns are select, user and system';
        $col_names = array_map(fn(array $col) => array_map(fn($cfn) => $cfn->name, $col), $cft->columns($cft->nodes));
        $t->assert($test_name, $col_names, [[test_config_trees::SELECT], [test_config_trees::USER], [test_config_trees::SYSTEM]]);
        // negative: an unknown scope has no top level node, so every column is empty
        $test_name = 'an unknown scope leaves all columns empty';
        $cols_unknown = $cft->columns($cft->branch(test_config_trees::RETRY));
        $t->assert($test_name, $cols_unknown, [[], [], []]);
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
        $sec_html = $html->span(test_config_trees::SEC, styles::CONFIG_UNIT);
        $test_name = 'a setting shows its value with the unit';
        $t->assert_text_contains($test_name, $page, '<dd>' . test_config_trees::MAX_DELAY_VALUE . $sec_html . '</dd>');
        $test_name = '... and the config.yaml value behind a database value that differs';
        $file_txt = $mtr->txt(msg_id::CONFIG_TREE_FILE_VALUE) . ' ' . test_config_trees::START_DELAY_VALUE . $sec_html;
        $file_html = $html->span($file_txt, styles::CONFIG_FILE_VALUE);
        $t->assert_text_contains($test_name, $page, '<dd>' . test_config_trees::START_DELAY_DB . $sec_html . $file_html . '</dd>');
        $test_name = 'a long tooltip is the full mouseover text';
        $t->assert_text_contains($test_name, $page, html_base::TITLE_HTML . '="' . test_config_trees::PAGE_TIP . '"');
        // negative: the tree shows all at once, so no tooltip hides its text behind a "..." button
        $test_name = '... without a button for more text';
        $t->assert_text_not_contains($test_name, $page, html_base::POPOVER_TARGET);
        $test_name = 'the only setting of a key shows the tooltip of the key';
        $row_limit_title = html_base::TITLE_HTML . '="' . test_config_trees::ROW_LIMIT_TIP . '"';
        $t->assert_text_contains($test_name, $page, '<' . html_names::SPAN . ' ' . $row_limit_title . '>' . test_config_trees::ROW_LIMIT_SETTING);
        // negative: the tooltip of the parent describes the parent, so the row of the setting does not carry it as an own tooltip
        $test_name = '... but not as the tooltip of the row';
        $t->assert_text_not_contains($test_name, $page, '<' . html_base::DT . ' ' . $row_limit_title);
        // negative: the tree uses no js plugin, so no mouseover text carries the data-toggle of one
        $test_name = '... and without the attribute of a js plugin';
        $t->assert_text_not_contains($test_name, $page, html_base::TOGGLE_TOOLTIP);
        // negative: a key with several keys below it describes only itself, e.g. database not its key retry
        $test_name = '... but not one of several keys';
        $db_title = html_base::TITLE_HTML . '="' . test_config_trees::DATABASE_TIP . '"';
        $t->assert($test_name, substr_count($page, $db_title), 1);
        $test_name = 'the search is a get form, so it never changes data';
        $t->assert_text_contains($test_name, $page, html_base::METHOD . '="' . html_base::METHOD_GET . '"');
        // negative: no submit field of a write form, the submit button of the search is fine
        $submit_field = html_base::NAME . '="' . url_var::POST_SUBMIT . '"';
        $test_name = '... without the submit field of a write form';
        $t->assert_text_not_contains($test_name, $page, $submit_field);

        $t->subheader($ts . 'edit');

        $cft->can_edit = true;
        $page_edit = $cft->html('', '');
        $id_name = html_base::NAME . '="' . url_var::ID . '" ' . html_base::VALUE . '="';
        $test_name = 'a logged-in user can save a number of the user or select settings as the own overwrite';
        $t->assert_text_contains($test_name, $page_edit, $id_name . test_config_trees::SELECT_INITIAL_ID . '"');
        // negative: a setting below system is a setting of the pod, which only an admin can change
        $test_name = '... but not a setting of the pod';
        $max_id_field = $id_name . test_config_trees::MAX_DELAY_ID . '"';
        $t->assert_text_not_contains($test_name, $page_edit, $max_id_field);
        $test_name = '... with a post form and the small save icon';
        $t->assert_text_contains($test_name, $page_edit, html_base::METHOD . '="' . html_base::METHOD_POST . '"');
        $t->assert_text_contains($test_name, $page_edit, icons::SAVE);
        $test_name = '... which a screen reader names';
        $save_label = html_base::ARIA_LABEL . '="' . $mtr->txt(msg_id::FORM_BUTTON_SAVE) . '"';
        $t->assert_text_contains($test_name, $page_edit, $save_label);
        // negative: a boolean is shown as true or false, so it has no number input, and a setting without an id has nothing to overwrite
        $test_name = '... but only for a number with a database value';
        $t->assert($test_name, substr_count($page_edit, $submit_field), 1);
        $default_field = html_base::NAME . '="' . url_var::SET_DEFAULT . '" ' . html_base::VALUE . '="' . url_var::TRUE . '"';
        // negative: a normal user saves the own overwrite and never the default of all users
        $test_name = '... as the own overwrite';
        $t->assert_text_not_contains($test_name, $page_edit, $default_field);
        $test_name = 'an admin saves the number as the default of all users';
        $cft->is_admin = true;
        $page_admin = $cft->html('', '');
        $t->assert_text_contains($test_name, $page_admin, $default_field);
        $test_name = '... also of a setting of the pod';
        $t->assert_text_contains($test_name, $page_admin, $max_id_field);
        $cft->is_admin = false;
        $cft->can_edit = false;
        $test_name = '... with the shown number and the name of the setting for the confirmation';
        $old_val = test_config_trees::SELECT_ENTRIES[test_config_trees::SELECT_INITIAL];
        $setting_key = implode(config_tree::NAME_SEPARATOR, [test_config_trees::SELECT, test_config_trees::SELECT_INITIAL]);
        $key_field = html_base::NAME . '="' . url_var::CONFIG_KEY . '" ' . html_base::VALUE . '="' . $setting_key . '"';
        $t->assert_text_contains($test_name, $page_edit, $key_field);
        $pre_var = url_var::PRE . url_var::NUMERIC_VALUE;
        $pre_field = html_base::NAME . '="' . $pre_var . '" ' . html_base::VALUE . '="' . $old_val . '"';
        $t->assert_text_contains($test_name, $page_edit, $pre_field);
        $ui = new frontend();
        $test_name = 'the confirmation names the setting, the new and the old number';
        $saved_url = [
            url_var::NUMERIC_VALUE => (string)test_config_trees::SELECT_INITIAL_NEW,
            $pre_var => (string)$old_val,
            url_var::CONFIG_KEY => $setting_key,
        ];
        $saved_txt = 'the setting for ' . $setting_key . ' has been updated to ' . test_config_trees::SELECT_INITIAL_NEW
            . ' (from ' . $old_val . ')';
        $t->assert($test_name, $ui->setting_saved_text($saved_url), $saved_txt);
        // negative: the name is free text of the form, so a placeholder in it is never filled
        $test_name = '... and keeps a placeholder in the name as text';
        $marker_key = msg_id::VAR_START . msg_id::VAR_VALUE . msg_id::VAR_END;
        $marker_url = array_merge($saved_url, [url_var::CONFIG_KEY => $marker_key]);
        $t->assert_text_contains($test_name, $ui->setting_saved_text($marker_url), $marker_key);
        $test_name = 'the confirmation of an admin names the default of all users';
        $default_url = array_merge($saved_url, [url_var::SET_DEFAULT => url_var::TRUE]);
        $default_txt = 'the default setting for ' . $setting_key . ' has been updated for all users to '
            . test_config_trees::SELECT_INITIAL_NEW . ' (from ' . $old_val . ')';
        $t->assert($test_name, $ui->setting_saved_text($default_url), $default_txt);
        // negative: an unchanged number has not been updated
        $test_name = '... but nothing is confirmed if the number is unchanged';
        $saved_url[url_var::NUMERIC_VALUE] = (string)$old_val;
        $t->assert($test_name, $ui->setting_saved_text($saved_url), '');
        // the shown number of an admin can be the own overwrite, so the same number can still be a new default
        $test_name = '... unless an admin has set it as the default';
        $default_url[url_var::NUMERIC_VALUE] = (string)$old_val;
        $t->assert_text_contains($test_name, $ui->setting_saved_text($default_url), $setting_key);

        // a text e.g. the view name of a pinned view, which an admin changes, because it is a setting of the pod
        $text_field = html_base::NAME . '="' . url_var::VALUE_TEXT . '"';
        $test_name = 'an admin can change a text setting with a text input';
        $cft->can_edit = true;
        $cft->is_admin = true;
        $page_text = $cft->html('', '');
        $t->assert_text_contains($test_name, $page_text, $text_field);
        $test_name = '... and add a text setting without a database value with the phrases of its yaml path';
        // the keys above the tree, which the backend tree also puts first (cfg/helper/config_tree::ROOT_KEYS)
        $view_names = array_merge(triples::CONFIG_ROOT,
            [test_config_trees::SYSTEM, test_config_trees::STARTUP, test_config_trees::PV, test_config_trees::VIEW_NAME]);
        $phr_ids = implode(',', array_map([$t_cft, 'phrase_id'], $view_names));
        $t->assert_text_contains($test_name, $page_text, html_base::NAME . '="' . url_var::PHRASE_LIST . '" ' . html_base::VALUE . '="' . $phr_ids . '"');
        $t->assert_text_contains($test_name, $page_text, html_base::NAME . '="' . url_var::MASK . '" ' . html_base::VALUE . '="' . views::VALUE_ADD_ID . '"');
        $cft->is_admin = false;
        // negative: the pinned views are a setting of the pod, so a normal user gets no text input
        $test_name = '... but a normal user cannot change a text setting of the pod';
        $t->assert_text_not_contains($test_name, $cft->html('', ''), $text_field);
        // negative: the edit icon of an empty text only opens the input, so a read only page never shows it
        $test_name = '... and sees no edit icon of an empty text';
        $t->assert_text_not_contains($test_name, $cft->html('', ''), icons::EDIT);
        $cft->can_edit = false;
        $test_name = 'the confirmation names the setting and the new text';
        $text_key = implode(config_tree::NAME_SEPARATOR, [test_config_trees::PV, test_config_trees::VIEW_NAME]);
        $text_url = [
            url_var::VALUE_TEXT => test_config_trees::VIEW_NAME_VALUE,
            url_var::PRE . url_var::VALUE_TEXT => '',
            url_var::CONFIG_KEY => $text_key,
        ];
        $t->assert_text_contains($test_name, $ui->setting_saved_text($text_url), test_config_trees::VIEW_NAME_VALUE);
        // negative: an empty text is not saved, so it is not confirmed either
        $test_name = '... but nothing is confirmed for an empty text';
        $text_url[url_var::VALUE_TEXT] = '';
        $t->assert($test_name, $ui->setting_saved_text($text_url), '');

        $t->subheader($ts . 'page cache');

        // the search and the scope are not part of the cache key, so such a page is rendered for each request
        $test_name = 'the configuration page without search is cached';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::SYSTEM_CONFIG_ID . '&id=0');
        $test_name = '... but not with a search';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID, url_var::PATTERN => 'cache'];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');
        $test_name = '... and not with a scope';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID, url_var::CONFIG_PART => test_config_trees::SYSTEM];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // the component renderer with the test tree, also as the snapshot of the page
        $sys_page = new system_page();
        $test_page = $html->text_h2('config tree');
        $test_page .= $sys_page->config_tree($msg, [], true, $t_cft->tree_ui());
        $t->html_page_test($test_page, 'config tree', 'config_tree', $msg);
        // the search and the scope are own pages like in the frontend, because each page has the
        // search list and the tree nodes with fixed ids, which one page must not hold twice
        $search_page = $html->text_h2('config tree search');
        $search_page .= $sys_page->config_tree($msg, [url_var::PATTERN => 'delay'], true, $t_cft->tree_ui());
        $t->html_page_test($search_page, 'config tree search', 'config_tree_search', $msg);
        $scope_page = $html->text_h2('config tree scope');
        $url_scope = [url_var::CONFIG_PART => test_config_trees::USER];
        $scope_page .= $sys_page->config_tree($msg, $url_scope, true, $t_cft->tree_ui());
        $t->html_page_test($scope_page, 'config tree scope', 'config_tree_scope', $msg);

        // the read only settings page as view 126 shows it, which the developer opens to check the layout
        $settings_page = $sys_page->system_tile(msg_id::SYSTEM_TITLE_SYSTEM_CONFIG);
        $settings_page .= $sys_page->config_tree($msg, [], true, $t_cft->tree_ui());
        $t->html_config_page_test($settings_page, 'system_config_read_only', $msg);
    }

}
